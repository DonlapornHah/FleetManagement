<?php
// ---------- เชื่อมต่อฐานข้อมูล ----------
include 'config.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------- ดึงสถานะจากตาราง status เพื่อแปลง id -> ชื่อสถานะ ----------
$status_map = [];
$sql_status = "SELECT status_id, status_name_th FROM status";
$result_status = $conn->query($sql_status);
while ($row = $result_status->fetch_assoc()) {
    $status_map[$row['status_id']] = $row['status_name_th'];
}

// --- จัดการการยืนยัน/ยกเลิก/ย้อนกลับ แผน (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $redirect_url = "confirm_plan.php?" . http_build_query($_GET);

    if ($action === 'revert' && isset($_POST['br_id'], $_POST['date'])) {
        // Action: Revert a confirmed plan back to old status
        $br_id_to_revert = (int)$_POST['br_id'];
        $date_to_revert = $_POST['date'];

        $stmt_revert = $conn->prepare("UPDATE plan_request SET pr_status = 2 WHERE br_id = ? AND pr_date = ? AND pr_status = 1");
        $stmt_revert->bind_param('is', $br_id_to_revert, $date_to_revert);
        $stmt_revert->execute();
        $stmt_revert->close();

        header("Location: " . $redirect_url);
        exit;

    } elseif (($action === 'confirm' || $action === 'cancel') && isset($_POST['pr_id'])) {
        // Action: Confirm or Cancel a pending plan
        $pr_id_to_action = (int)$_POST['pr_id'];

        $conn->begin_transaction();
        try {
            $stmt_get = $conn->prepare("SELECT br_id, pr_name, pr_date, pr_request FROM plan_request WHERE pr_id = ? AND pr_status = 0");
            $stmt_get->bind_param('i', $pr_id_to_action);
            $stmt_get->execute();
            $plan = $stmt_get->get_result()->fetch_assoc();
            $stmt_get->close();

            if (!$plan) throw new Exception("ไม่พบแผนที่ต้องการดำเนินการ หรืออาจถูกดำเนินการไปแล้ว");

            $br_id = $plan['br_id'];
            $pr_date = $plan['pr_date'];

            if ($action === 'confirm') {
                // เปลี่ยนแผนที่เคยยืนยันแล้วให้เป็นเก่า
                $stmt_update_old = $conn->prepare("UPDATE plan_request SET pr_status = 2 WHERE br_id = ? AND pr_date = ? AND pr_status = 1");
                $stmt_update_old->bind_param('is', $br_id, $pr_date);
                $stmt_update_old->execute();
                $stmt_update_old->close();

                // อัปเดตสถานะแผนใหม่เป็นยืนยัน
                $stmt_confirm = $conn->prepare("UPDATE plan_request SET pr_status = 1 WHERE pr_id = ?");
                $stmt_confirm->bind_param('i', $pr_id_to_action);
                $stmt_confirm->execute();
                $stmt_confirm->close();

            } elseif ($action === 'cancel') {
                // ยกเลิกแผน เปลี่ยนสถานะเป็น 3
                $stmt_cancel = $conn->prepare("UPDATE plan_request SET pr_status = 3 WHERE pr_id = ? AND pr_status = 0");
                $stmt_cancel->bind_param('i', $pr_id_to_action);
                $stmt_cancel->execute();
                $stmt_cancel->close();
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            die("เกิดข้อผิดพลาด: " . $e->getMessage());
        }

        header("Location: " . $redirect_url);
        exit;
    }
}

// ---------- ดึงข้อมูล: วันที่, ภูมิภาค, สาย, แผน ----------
$plan_date = $_GET['date'] ?? date('Y-m-d');

$today = new DateTime();
$today->setTime(0, 0);
$selected_date = new DateTime($plan_date);
$is_editable = ($selected_date > $today);

// ดึงภูมิภาค (zones)
$zones = [];
$sql_zones = "SELECT bz_id, bz_name_th FROM bus_zone ORDER BY bz_name_th";
$res_zones = $conn->query($sql_zones);
while ($row = $res_zones->fetch_assoc()) {
    $zones[$row['bz_id']] = $row['bz_name_th'];
}

// รับภูมิภาคที่เลือก
$selected_zone_id = $_GET['zone'] ?? null;
if ($selected_zone_id !== null) {
    $selected_zone_id = (int)$selected_zone_id;
    if (!array_key_exists($selected_zone_id, $zones)) {
        $selected_zone_id = null;
    }
}

// ดึงสายเดินรถ
$all_routes = [];
if ($selected_zone_id) {
    $stmt = $conn->prepare("
        SELECT br.br_id, CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
        FROM bus_routes br
        LEFT JOIN location loS ON br.br_start = loS.locat_id
        LEFT JOIN location loE ON br.br_end = loE.locat_id
        WHERE br.bz_id = ?
        ORDER BY br.br_id
    ");
    $stmt->bind_param('i', $selected_zone_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $all_routes[$row['br_id']] = $row['route_name'];
    }
    $stmt->close();
} else {
    $sql = "
        SELECT br.br_id, CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
        FROM bus_routes br
        LEFT JOIN location loS ON br.br_start = loS.locat_id
        LEFT JOIN location loE ON br.br_end = loE.locat_id
        ORDER BY br.br_id
    ";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $all_routes[$row['br_id']] = $row['route_name'];
    }
}

// รับสายเดินรถที่เลือก
$selected_routes = $_GET['routes'] ?? [];
if (!is_array($selected_routes)) {
    $selected_routes = [$selected_routes];
}
$selected_routes = array_intersect($selected_routes, array_keys($all_routes));

$plans = [];

if (!empty($selected_routes)) {
    $placeholders = implode(',', array_fill(0, count($selected_routes), '?'));
    $types = str_repeat('i', count($selected_routes));

    // ดึงแผนยืนยัน (status=1)
    $bind_params_confirmed = array_merge([$plan_date], $selected_routes);
    $refs_confirmed = [];
    foreach ($bind_params_confirmed as $key => $value) {
        $refs_confirmed[$key] = &$bind_params_confirmed[$key];
    }
    $sql_confirmed = "SELECT br_id, pr_request FROM plan_request WHERE pr_date = ? AND pr_status = 1 AND br_id IN ($placeholders)";
    $stmt = $conn->prepare($sql_confirmed);
    $stmt->bind_param('s' . $types, ...$refs_confirmed);
    $stmt->execute();
    $result = $stmt->get_result();
    $confirmed_plans = [];
    while ($row = $result->fetch_assoc()) {
        $confirmed_plans[$row['br_id']] = json_decode($row['pr_request'], true);
    }
    $stmt->close();

    // ดึงแผนมาตรฐาน queue_request
    $bind_params_standard = $selected_routes;
    $refs_standard = [];
    foreach ($bind_params_standard as $key => $value) {
        $refs_standard[$key] = &$bind_params_standard[$key];
    }
    $sql_standard = "SELECT br_id, qr_request FROM queue_request WHERE br_id IN ($placeholders)";
    $stmt = $conn->prepare($sql_standard);
    $stmt->bind_param($types, ...$refs_standard);
    $stmt->execute();
    $result = $stmt->get_result();
    $standard_queues = [];
    while ($row = $result->fetch_assoc()) {
        $standard_queues[$row['br_id']] = json_decode($row['qr_request'], true);
    }
    $stmt->close();

    // ดึงแผนรอยืนยัน (status=0)
    $bind_params_pending = array_merge([$plan_date], $selected_routes);
    $refs_pending = [];
    foreach ($bind_params_pending as $key => $value) {
        $refs_pending[$key] = &$bind_params_pending[$key];
    }
    $sql_pending = "SELECT pr_id, br_id, pr_request FROM plan_request WHERE pr_date = ? AND pr_status = 0 AND br_id IN ($placeholders)";
    $stmt = $conn->prepare($sql_pending);
    $stmt->bind_param('s' . $types, ...$refs_pending);
    $stmt->execute();
    $result = $stmt->get_result();
    $pending_plans = [];
    while ($row = $result->fetch_assoc()) {
        $pending_plans[$row['br_id']] = [
            'pr_id' => $row['pr_id'],
            'data' => json_decode($row['pr_request'], true)
        ];
    }
    $stmt->close();

    // รวมข้อมูลแผนตาม route
    foreach ($selected_routes as $br_id) {
        if (isset($standard_queues[$br_id])) {
            $plans[$br_id] = [
                'standard' => $standard_queues[$br_id],
                'confirmed' => $confirmed_plans[$br_id] ?? null,
                'pending' => $pending_plans[$br_id] ?? null,
            ];
        }
    }
}

// ดึงข้อมูล break point
$point = [];
$sql_point = "SELECT 
                brk_in_route.br_id AS br_id,
                brk_in_route.bir_time AS bir_time,
                brk_in_route.brkp_id AS brkp_id,
                break_point.brkp_name AS brkp_name,
                brk_in_route.bir_type AS brkp_type,
                brk_in_route.bir_status AS brkp_status
            FROM brk_in_route 
            LEFT JOIN break_point ON brk_in_route.brkp_id = break_point.brkp_id";

$result_point = $conn->query($sql_point);
while($row = $result_point->fetch_assoc()) {
    $point[$row['br_id']][] = [
        'id' => $row['brkp_id'],
        'name' => $row['brkp_name'],
        'time' => $row['bir_time'],
        'status' => $row['brkp_status'],
        'type' => $row['brkp_type']
    ];
}

// encode $point สำหรับ JS
$point_json = json_encode($point);

// $status_map ใช้สำหรับแปลงสถานะ id เป็นชื่อสถานะเวลานำไปแสดงผล เช่น $status_map[$status_id]

$cancelled_plans_json = json_encode([]); // กรณีคุณยังไม่ได้ใช้ส่วนนี้ก็ใส่เป็น array เปล่าไว้
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>แผนเดินรถ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
      /* ปุ่มเลือกทั้งหมด / ล้างทั้งหมด */
      #btn-group {
        display: flex !important;
        flex-direction: row !important;
        gap: 10px;
        margin-bottom: 8px;
      }
      #btn-group button {
        flex-shrink: 0; /* ป้องกันปุ่มยืดหรือย่อ */
      }

      /* ให้ช่อง input, select ในฟอร์มนี้สูงเท่ากัน */
      #filter-form .form-control,
      #filter-form .form-select {
        height: 40px;
      }

      /* ให้ select หลายตัว มี scroll และความสูงไม่เกิน */
      #route-select {
        max-height: 160px !important;
        overflow-y: auto !important;
      }

      /* กำหนดให้ col-md-* มี display flex และจัดเรียงแนวตั้ง */
      #filter-form .col-md-3,
      #filter-form .col-md-6 {
        display: flex;
        flex-direction: column;
      }

      /* ให้ label อยู่เหนือ input/select และเว้นระยะ */
      #filter-form label {
        margin-bottom: 6px;
      }
    </style>
</head>
<body class="sidebar-collapsed">
<div class="d-flex">
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar collapsed p-3">
    <button class="btn btn-sm mb-3 align-self-end" onclick="toggleSidebar()">
      <i class="bi bi-list"></i>
    </button>
    <a href="#" class="nav-link"><i class="bi bi-house-door"></i><span class="nav-text">หน้าหลัก</span></a>
    <a href="#" class="nav-link"><i class="bi bi-gear"></i><span class="nav-text">แผนการเดินรถ(การขาย)</span></a>
    <a href="#" class="nav-link"><i class="bi bi-bus-front"></i><span class="nav-text">จัดการรถ</span></a>
    <a href="#" class="nav-link"><i class="bi bi-person-badge"></i><span class="nav-text">พนักงาน</span></a>
    <a href="#" class="nav-link"><i class="bi bi-clock-history"></i><span class="nav-text">รายงานและประวัติ</span></a>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1">
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm px-4 mb-0">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav" aria-controls="topbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topbarNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex align-items-center">
            <li class="nav-item d-flex align-items-center me-3">
  <a href="index.php"> <!-- หรือเปลี่ยนเป็นหน้าหลักที่คุณต้องการ -->
    <img src="https://img5.pic.in.th/file/secure-sv1/752440-01-removebg-preview.png" alt="Logo"
         style="width: 100px; height: auto; user-select: none;" />
  </a>
</li>

            <li class="nav-item"><a class="nav-link" href="manageQueue.php">จัดคิวการเดินรถ</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="planDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                จัดการแผนการเดินรถ
              </a>
              <ul class="dropdown-menu shadow rounded-3" aria-labelledby="personnelDropdown">
                <li><a class="dropdown-item" href="planQueue.php"><i class="bi bi-calendar-check-fill me-2"></i>แผนเดินรถ</a></li>
<li><a class="dropdown-item" href="simpleQueue.php"><i class="bi bi-list-check me-2"></i>จัดการคิวมาตรฐาน</a></li>
<li><a class="dropdown-item" href="sale_request.php"><i class="bi bi-person-lines-fill me-2"></i>จัดการแผนเดินรถ (ฝ่ายขาย)</a></li>

              </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="manageCar.php">จัดการรถ</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="personnelDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                จัดการบุคลากร
              </a>
              <ul class="dropdown-menu shadow rounded-3" aria-labelledby="personnelDropdown">
                <li><a class="dropdown-item" href="manageDriver.php"><i class="bi bi-person-vcard me-2"></i>พนักงานขับรถ</a></li>
                <li><a class="dropdown-item" href="manageAssist.php"><i class="bi bi-person-plus me-2"></i>พนักงานขับรถเสริม</a></li>
                <li><a class="dropdown-item" href="manageCoach.php"><i class="bi bi-people-fill me-2"></i>พนักงานบริการ</a></li>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="report.php">รายงานและประวัติ</a></li>
          </ul>
          <span class="navbar-text text-muted" id="datetime"></span>
        </div>
      </div>
    </nav>

    <!-- หัวข้อหลัก -->
<div class="container-fluid px-4 pt-4">
 <h4 class="text-center fw-bold py-3 mb-4 text-white" style="background-color: #16325cff; border-radius: 0.5rem;">
 ภาพรวมและแก้ไขแผนการเดินรถ
</h4>


  <!-- Filter Form -->
<!-- Filter Form -->
<!-- Filter Form -->
<form method="get" id="filter-form">
  <div class="row g-4">
    <!-- คอลัมน์ซ้าย: วันที่ + ภูมิภาค + เลือกสายเดินรถ -->
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header fw-semibold">เลือกวันที่ และ ภูมิภาค</div>
        <div class="card-body">
          <div class="mb-3">
            <label for="date-select" class="form-label">เลือกวันที่ :</label>
            <input type="date" id="date-select" name="date" class="form-control" value="<?= htmlspecialchars($plan_date) ?>" />
          </div>
          <div class="mb-3">
            <label for="zone-select" class="form-label">เลือกภูมิภาค :</label>
            <select id="zone-select" name="zone" class="form-select">
              <option value="">-- เลือกภูมิภาคทั้งหมด --</option>
              <?php foreach ($zones as $bz_id => $bz_name):
                $selected = ($bz_id == $selected_zone_id) ? 'selected' : ''; ?>
                <option value="<?= $bz_id ?>" <?= $selected ?>><?= htmlspecialchars($bz_name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>สายเดินรถ : </div>
            <div id="btn-group" class="d-flex gap-2">
              <button type="button" id="select-all-routes" class="btn btn-sm btn-outline-primary">เลือกทั้งหมด</button>
              <button type="button" id="clear-all-routes" class="btn btn-sm btn-outline-secondary">ล้างทั้งหมด</button>
            </div>
          </div>
          <select id="route-select" name="routes[]" multiple class="form-select" 
                  style="height: 180px; overflow-y: auto; width: 100px;">
            <?php foreach ($all_routes as $br_id => $route_name):
              $isSelected = in_array($br_id, $selected_routes) ? 'selected' : ''; ?>
              <option value="<?= $br_id ?>" <?= $isSelected ?>>
                <?= htmlspecialchars($route_name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div> <!-- ปิด card-body -->
      </div> <!-- ปิด card -->
    </div> <!-- ปิด col-md-4 -->

    <!-- คอลัมน์ขวา: แสดงแผน -->
    <div class="col-md-8">
      <div id="plan-tables">
        <!-- ตารางแผนจะถูกโหลดโดย JavaScript หรือ PHP -->
      </div>
    </div>
  </div> <!-- ปิด row -->
</form>

  <!-- Modal: เปรียบเทียบแผน -->
  <div class="modal fade" id="compareModal" tabindex="-1" aria-labelledby="compareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="compareModalLabel">เปรียบเทียบแผน Route: </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6 id="active-plan-title">แผนปัจจุบัน</h6>
              <div class="table-responsive" id="active-plan-table"></div>
            </div>
            <div class="col-md-6">
              <h6>แผนใหม่ (รอยืนยัน)</h6>
              <div class="table-responsive" id="pending-plan-table"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <form id="modal-action-form" method="POST" action="">
            <input type="hidden" name="pr_id" id="modal-pr-id">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            <button type="submit" name="action" value="cancel" class="btn btn-danger">ปฏิเสธแผนใหม่</button>
            <button type="submit" name="action" value="confirm" class="btn btn-primary">ยืนยันแผนใหม่</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  const routeNames = <?= json_encode($all_routes, JSON_UNESCAPED_UNICODE) ?>;
  
</script>

<script>
// อัปเดตวันที่เวลาปัจจุบันแบบไทม์โซน Asia/Bangkok
function updateDateTime() {
  const now = new Date();
  const options = {
    timeZone: 'Asia/Bangkok',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  const el = document.getElementById('datetime');
  if (el) el.textContent = now.toLocaleString('th-TH', options);
}
setInterval(updateDateTime, 1000);
updateDateTime();

// Choices.js setup for route-select
const routeSelect = document.getElementById('route-select');
const filterForm = document.getElementById('filter-form');

const choices = new Choices(routeSelect, {
  removeItemButton: true,
  placeholderValue: 'เลือกสายเดินรถ',
  searchPlaceholderValue: 'ค้นหา...'
});

// ปุ่มเลือกทั้งหมด และ ล้างทั้งหมด
document.getElementById('select-all-routes').addEventListener('click', () => {
  const allValues = Array.from(routeSelect.options).map(opt => opt.value);
  choices.setChoiceByValue(allValues);
  filterForm.submit();
});

document.getElementById('clear-all-routes').addEventListener('click', () => {
  choices.removeActiveItems();
  filterForm.submit();
});

// ส่งฟอร์มเมื่อเปลี่ยนวันที่หรือโซน
document.getElementById('date-select').addEventListener('change', () => filterForm.submit());
document.getElementById('zone-select').addEventListener('change', () => filterForm.submit());

// Toggle sidebar function
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  if (sidebar) sidebar.classList.toggle('collapsed');
}

// --- Data from PHP ---
        const allPlansData = <?php echo json_encode($plans); ?>;
        const planDate = '<?php echo $plan_date; ?>';
        const isEditable = <?php echo json_encode($is_editable); ?>;
        const pointData = <?php echo $point_json; ?>;
        const cancelledPlans = <?php echo $cancelled_plans_json; ?>; // เพิ่มข้อมูลแผน pr_status=3

        // --- State Management ---
        let editablePlans = {};
        let displaySource = {}; // { br_id: 'standard' | 'confirmed' }

        // --- Initialization ---
        function initializeEditableData() {
            editablePlans = {};
            Object.keys(allPlansData).forEach(br_id => {
                // Set initial display source: 'confirmed' if available, otherwise 'standard'
                if (!displaySource[br_id]) {
                    displaySource[br_id] = allPlansData[br_id].confirmed ? 'confirmed' : 'standard';
                }
                const source = displaySource[br_id];
                const dataToEdit = allPlansData[br_id][source];
                // Deep copy the data for editing
                if (dataToEdit) {
                    editablePlans[br_id] = {
                        request: [...(dataToEdit.request || [])],
                        reserve: [...(dataToEdit.reserve || [])],
                        time: [...(dataToEdit.time || [])],
                        time_plus: [...(dataToEdit.time_plus || [])],
                        stops: dataToEdit.stops ? dataToEdit.stops.map(arr => Array.isArray(arr) ? [...arr] : []) : (dataToEdit.point ? dataToEdit.point.map(arr => Array.isArray(arr) ? [...arr] : []) : []),
                        ex: Array.isArray(dataToEdit.ex) ? dataToEdit.ex.map(e => ({ ...e })) : []
                    };
                }
            });
        }

        // --- Modal Checklist จุดพัก ---
        function createPointChecklistPopup(br_id, idx, selectedPoints, disabledAttr) {
            const pts = pointData[br_id] || [];
            const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
            let mergedSelected = Array.isArray(selectedPoints) ? [...selectedPoints] : [];
            requiredPoints.forEach(val => {
                if (!mergedSelected.includes(val)) mergedSelected.push(val);
            });

            // ปรับข้อความบนปุ่ม
            let label = '';
            if (mergedSelected.length === 0) {
                label = 'เลือกจุดรับส่ง';
            } else if (
                pts.length > 0 &&
                pts.every(pt => mergedSelected.includes(pt.id.toString()))
            ) {
                label = 'เลือกครบทุกจุด';
            } else if (mergedSelected.length === 1) {
                const pt = pts.find(pt => pt.id.toString() === mergedSelected[0]);
                label = pt ? pt.name : (pts[0] ? pts[0].name : 'เลือกจุดรับส่ง');
            } else {
                const firstPt = pts.find(pt => pt.id.toString() === mergedSelected[0]);
                const firstName = firstPt ? firstPt.name : (pts[0] ? pts[0].name : '');
                label = `${firstName} และอีก ${mergedSelected.length - 1} จุด`;
            }

            // --- แก้ไขจุดนี้: input hidden ส่งเฉพาะ selectedPoints จริง ไม่ใช่ mergedSelected ---
            let html = `
                <button type="button" class="btn btn-outline-primary btn-sm w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#pointModal_${br_id}_${idx}" ${disabledAttr}>
                    ${label}
                </button>
                <input type="hidden" name="point[${br_id}][]" value="${Array.isArray(selectedPoints) ? selectedPoints.join(',') : ''}" data-idx="${idx}">
                <!-- Modal -->
                <div class="modal fade" id="pointModal_${br_id}_${idx}" tabindex="-1" aria-labelledby="pointModalLabel_${br_id}_${idx}" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="pointModalLabel_${br_id}_${idx}">
    เลือกจุดรับส่ง สาย ${routeNames[br_id] || br_id} ลำดับ ${idx + 1}
  </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-2 d-flex gap-2">
                          <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAllPoints('${br_id}',${idx})" ${disabledAttr}>เลือกทั้งหมด</button>
                          <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllPoints('${br_id}',${idx})" ${disabledAttr}>ล้างการเลือก</button>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
            `;
            pts.forEach((pt) => {
                const val = pt.id.toString();
                const checked = mergedSelected.includes(val) ? 'checked' : '';
                const disabled = pt.status == 1 ? 'disabled' : '';
                html += `<div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="point_${br_id}_${idx}_${val}" value="${val}" ${checked} ${disabled} ${disabledAttr}
                        onchange="onPointChecklistPopupChange('${br_id}',${idx},this)">
                    <label class="form-check-label" for="point_${br_id}_${idx}_${val}">${pt.name} (${pt.time} นาที)${pt.status == 1 ? ' <span class="text-danger">*</span>' : ''}</label>
                </div>`;
            });
            html += `
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="confirmPointChecklistPopup('${br_id}',${idx})" ${disabledAttr}>ยืนยัน</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                      </div>
                    </div>
                  </div>
                </div>
            `;
            return html;
        }

        // --- Modal Checklist Logic ---
        function selectAllPoints(br_id, idx) {
            const pts = pointData[br_id] || [];
            const allVals = pts.map(pt => pt.id.toString());
            editablePlans[br_id].stops = editablePlans[br_id].stops || [];
            editablePlans[br_id].stops[idx] = allVals;
            updateTimePlus(br_id, idx);
            // อัปเดต checkbox ใน modal เฉพาะ
            pts.forEach((pt) => {
                const val = pt.id.toString();
                const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
                if (cb) cb.checked = true;
            });
            // ไม่ต้อง renderTables() ทันที เพื่อไม่ให้ modal หาย
        }
        function clearAllPoints(br_id, idx) {
            const pts = pointData[br_id] || [];
            const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
            editablePlans[br_id].stops = editablePlans[br_id].stops || [];
            editablePlans[br_id].stops[idx] = [...requiredPoints];
            updateTimePlus(br_id, idx);
            // อัปเดต checkbox ใน modal เฉพาะ
            pts.forEach((pt) => {
                const val = pt.id.toString();
                const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
                if (cb) cb.checked = requiredPoints.includes(val);
            });
            // ไม่ต้อง renderTables() ทันที เพื่อไม่ให้ modal หาย
        }
        function onPointChecklistPopupChange(br_id, idx, checkboxElem) {
            editablePlans[br_id].stops = editablePlans[br_id].stops || [];
            let selected = editablePlans[br_id].stops[idx] || [];
            if (!Array.isArray(selected)) selected = [];
            const val = checkboxElem.value;
            const pts = pointData[br_id] || [];
            const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
            if (checkboxElem.disabled) return;
            if (checkboxElem.checked) {
                if (!selected.includes(val)) selected.push(val);
            } else {
                selected = selected.filter(v => v !== val);
            }
            requiredPoints.forEach(rid => {
                if (!selected.includes(rid)) selected.push(rid);
            });
            editablePlans[br_id].stops[idx] = selected;
            updateTimePlus(br_id, idx);
        }
        function confirmPointChecklistPopup(br_id, idx) {
            const modal = bootstrap.Modal.getInstance(document.getElementById(`pointModal_${br_id}_${idx}`));
            if (modal) modal.hide();
            const pts = pointData[br_id] || [];
            const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
            const selected = [];
            pts.forEach((pt) => {
                const val = pt.id.toString();
                const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
                if (cb && cb.checked) selected.push(val);
            });
            requiredPoints.forEach(rid => {
                if (!selected.includes(rid)) selected.push(rid);
            });
            editablePlans[br_id].stops[idx] = selected;
            updateTimePlus(br_id, idx);
            setTimeout(() => renderTables(), 0);
        }
        function updateTimePlus(br_id, idx) {
            const stops = editablePlans[br_id].stops[idx] || [];
            const pts = pointData[br_id] || [];
            let total = 0;
            (stops || []).forEach(val => {
                const pt = pts.find(pt => pt.id.toString() === val);
                if (pt) total += parseInt(pt.time);
            });
            if (!editablePlans[br_id].time_plus) editablePlans[br_id].time_plus = [];
            editablePlans[br_id].time_plus[idx] = total.toString();
        }

        // --- Toggling and Editing Logic ---
        function toggleSource(br_id, source) {
            displaySource[br_id] = source;
            initializeEditableData();
            renderTables();
        }

        function getAllCodeOptions(plans) {
            const groupMap = {};
            Object.entries(plans).forEach(([br_id, obj]) => {
                if (!groupMap[br_id]) groupMap[br_id] = [];
                (obj.request || []).forEach((req, i, arr) => {
                    groupMap[br_id].push({ value: `${br_id}-3-${i === arr.length - 1 ? 'last' : i + 1}`, label: `${br_id}-3-${i === arr.length - 1 ? 'last' : i + 1}` });
                });
                (obj.reserve || []).forEach((res, i) => {
                    groupMap[br_id].push({ value: `${br_id}-1-${i + 1}`, label: `${br_id}-1-${i + 1}` });
                });
            });
            groupMap['อื่นๆ'] = [{ value: '0', label: '0' }, { value: '1', label: '1' }, { value: '2', label: '2' }];
            return groupMap;
        }

        function createSelect(name, selected, routeOptions, br_id, type, idx) {
            const disabledAttr = isEditable ? '' : 'disabled';
            let html = `<select name="${name}" class="form-select form-select-sm" onchange="onQueueChange('${br_id}','${type}',${idx},this)" ${disabledAttr}>`;
            Object.entries(routeOptions).forEach(([group, opts]) => {
                html += `<optgroup label="${group}">`;
                opts.forEach(opt => {
                    html += `<option value="${opt.value}" ${opt.value === selected ? 'selected' : ''}>${opt.label}</option>`;
                });
                html += `</optgroup>`;
            });
            return html + '</select>';
        }

        function onQueueChange(br_id, type, idx, selectElem) {
            editablePlans[br_id][type][idx] = selectElem.value;
            renderTables();
        }

        function removeRow(br_id, type, idx) {
            let arr = editablePlans[br_id][type] || [];
            if (arr.length > 0) {
                arr.splice(idx, 1);
                if (type === 'request') {
                    (editablePlans[br_id].time || []).splice(idx, 1);
                    (editablePlans[br_id].time_plus || []).splice(idx, 1);
                    (editablePlans[br_id].stops || []).splice(idx, 1);
                }
                renderTables();
            }
        }

        function insertRow(br_id, type, idx, pos) {
            let arr = editablePlans[br_id][type] || [];
            let insertIdx = pos === 'before' ? idx : idx + 1;
            arr.splice(insertIdx, 0, '2');
            if (type === 'request') {
                if (!editablePlans[br_id].time) editablePlans[br_id].time = [];
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                editablePlans[br_id].time.splice(insertIdx, 0, now.toISOString().slice(11, 16));
                if (!editablePlans[br_id].time_plus) editablePlans[br_id].time_plus = [];
                editablePlans[br_id].time_plus.splice(insertIdx, 0, '0');
                if (!editablePlans[br_id].stops) editablePlans[br_id].stops = [];
                editablePlans[br_id].stops.splice(insertIdx, 0, []);
            }
            renderTables();
        }

        // ฟังก์ชัน normalize ex ให้เป็น array ของ object {start1:"", end1:"", start2:"", end2:""} (string id)
        function normalizeExData() {
            Object.entries(editablePlans).forEach(([br_id, obj]) => {
                if (!obj.ex) obj.ex = [];
                obj.ex = obj.ex.map(e => {
                    // ถ้า e เป็น array (แบบเก่า) หรือไม่ใช่ object ให้แปลงเป็น object ที่มี string
                    if (!e || typeof e !== 'object' || Array.isArray(e)) {
                        e = {start1:"", end1:"", start2:"", end2:""};
                    }
                    // แปลง array เป็น string ตัวแรก หรือ "" ถ้าไม่มีข้อมูล
                    const arrToStr = v => Array.isArray(v) ? (v.length > 0 ? v[0].toString() : "") : (v !== undefined ? v.toString() : "");
                    e.start1 = arrToStr(e.start1);
                    e.end1 = arrToStr(e.end1);
                    e.start2 = arrToStr(e.start2);
                    e.end2 = arrToStr(e.end2);
                    return e;
                });
            });
        }

        // ฟังก์ชันสร้าง select จุดจอดขึ้น/ลง สำหรับ ex driver (single-select, แยกคนที่ 1/2)
        function createExPointSelect(br_id, idx, selected, type, person) {
            // type: 'start' or 'end', person: 1 or 2
            // เลือกเฉพาะ point ที่ type == 2
            const pts = (pointData[br_id] || []).filter(pt => pt.type == 2);
            let html = `<select class="form-select" name="ex_${type}${person}[${br_id}][]" data-idx="${idx}" onchange="onExPointChange('${br_id}',${idx},this,'${type}${person}')">`;
            html += `<option value="">- ไม่เลือก -</option>`;
            pts.forEach((pt) => {
                const val = pt.id.toString();
                // selected เป็น string id
                const isSelected = (selected === val) ? 'selected' : '';
                html += `<option value="${val}" ${isSelected}>${pt.name}</option>`;
            });
            html += `</select>`;
            return html;
        }
  
        // --- ปรับ renderTables ให้ใช้ modal checklist จุดพัก ---
        function renderTables() {
            const container = document.getElementById('plan-tables');
            const routeOptions = getAllCodeOptions(editablePlans);
            let html = `<form method='post' action='confirm_plan_db.php' id='edit-plan-form'>`;
            html += `<input type="hidden" name="plan_date" value="${planDate}">`;

            if (Object.keys(editablePlans).length === 0) {
                container.innerHTML = '<div class="alert alert-info">ไม่พบข้อมูลสำหรับสายที่เลือก</div>';
                return;
            }

            const disabledAttr = isEditable ? '' : 'disabled';

            Object.entries(editablePlans).forEach(([br_id, plan_data]) => {
                const originalPlan = allPlansData[br_id];
                const currentSource = displaySource[br_id];
                let sourceText = '';
                if (currentSource === 'confirmed') {
                    sourceText = 'แผนยืนยันแล้ว';
                } else if (currentSource === 'special') {
                    sourceText = 'แผนที่บันทึกไว้';
                } else {
                    sourceText = 'คิวมาตรฐาน';
                }

             html += `<div class="card mb-4 style="box-shadow: none;">
  <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white" 
    <h6 class="mb-0">สายเดินรถ : ${routeNames[br_id] || br_id} (ที่มา: ${sourceText})</h6>
    <div>`;
      
  if (originalPlan && originalPlan.confirmed && isEditable) {
    html += `<div class="btn-group btn-group-sm me-2">
                <button type="button" class="btn btn-light" onclick="toggleSource('${br_id}', 'standard')" ${currentSource === 'standard' ? 'disabled' : ''}>ใช้แผนมาตรฐาน</button>
                <button type="button" class="btn btn-warning" onclick="toggleSource('${br_id}', 'confirmed')" ${currentSource === 'confirmed' ? 'disabled' : ''}>ใช้แผนยืนยัน</button>
             </div>`;
  }

  if ((cancelledPlans[br_id] || []).length > 0 && isEditable) {
    html += `<button type="button" class="btn btn-outline-dark btn-sm me-2" onclick="showCancelledPlansSelector('${br_id}')">เลือกแผนที่บันทึกไว้</button>`;
  }

  if (originalPlan && originalPlan.pending) {
    html += `<button type="button" class="btn btn-warning btn-sm" onclick="showCompareModal('${br_id}')">มีแผนใหม่รอยืนยัน</button>`;
  }
  
  html += `</div></div>
  <div class="card-body" style="padding: 0; ">
    <div class="table-responsive" style="margin: 0;">
      <input type="hidden" name="source[${br_id}]" value="${sourceText}">
      <table class="table table-sm table-bordered align-middle" style="margin: 0;">
        <thead class="table-secondary">
          <tr>
            <th class="text-center" style="font-weight: 300;">ลำดับ</th>
            <th class="text-center" style="font-weight: 300;">Queue Request</th>
            <th class="text-center" style="font-weight: 300;">เวลา</th>
            <th class="text-center" style="font-weight: 300;">เวลาเดินทาง(จุดจอด)</th>
            <th class="text-center" style="font-weight: 300;">Action</th>
          </tr>
        </thead>
        <tbody>`;

                
                const reqArr = plan_data.request || [];
                const stopsArr = plan_data.stops || [];
                const timeArr = plan_data.time || [];
                const exArr = plan_data.ex || [];

                reqArr.forEach((qr_request, idx) => {
                    const selectedPoints = stopsArr[idx] || [];
                    const timePlusVal = plan_data.time_plus && plan_data.time_plus[idx] ? plan_data.time_plus[idx] : '0';
                    const timeVal = timeArr[idx] || '';
                    const exObj = exArr[idx] || {start1:[], end1:[], start2:[], end2:[]};
                    html += `<tr>
                                <td class="text-center" align-middle" rowspan="2" >${idx + 1}</td>
                                <td class="text-center" >${createSelect(`request[${br_id}][]`, qr_request, routeOptions, br_id, 'request', idx)}</td>
                                <td class="text-center">
                                    <input type="time" class="form-control form-control-sm" name="time[${br_id}][]" value="${timeVal}" ${disabledAttr}>
                                </td>
                                <td class="text-center">
                                    ${createPointChecklistPopup(br_id, idx, selectedPoints, disabledAttr)}
                                    <input type="number" class="form-control mt-1" name="time_plus[${br_id}][]" value="${timePlusVal}" data-idx="${idx}" readonly>
                                </td>
                                    <td class="text-center"><div class="btn-group btn-group-sm">
                                        <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'before')" ${disabledAttr}>แทรกก่อน</button>
                                        <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'after')" ${disabledAttr}>แทรกหลัง</button>
                                        <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','request',${idx})" ${disabledAttr}>ลบ</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5">
                                    <div class="row g-2 align-items-center" >
                                        <div class="col-auto ">จุดจอดขึ้น (ex driver คนที่ 1) : </div>
                                        <div class="col">${createExPointSelect(br_id, idx, exObj.start1, 'start', 1)}</div>
                                        <div class="col-auto">จุดจอดลง (ex driver คนที่ 1) :</div>
                                        <div class="col">${createExPointSelect(br_id, idx, exObj.end1, 'end', 1)}</div>
                                    </div>
                                    <div class="row g-2 align-items-center mt-2">
                                        <div class="col-auto">จุดจอดขึ้น (ex driver คนที่ 2):</b></div>
                                        <div class="col">${createExPointSelect(br_id, idx, exObj.start2, 'start', 2)}</div>
                                        <div class="col-auto">จุดจอดลง (ex driver คนที่ 2):</b></div>
                                        <div class="col">${createExPointSelect(br_id, idx, exObj.end2, 'end', 2)}</div>
                                    </div>
                                </td>
                            </tr>`;
                });

                // Add hidden inputs for reserve data
                const reserveArr = plan_data.reserve || [];
                reserveArr.forEach((qr_reserve, idx) => {
                    html += `<input type="hidden" name="reserve[${br_id}][]" value="${qr_reserve}">`;
                });

                if (isEditable) {
                    html += `<tr><td>ใหม่</td>
                        <td>${createSelect('', '2', routeOptions, br_id, 'request', reqArr.length)}</td>
                        <td></td>
                        <td>
                            ${createPointChecklistPopup(br_id, reqArr.length, [], disabledAttr)}
                            <input type="number" class="form-control mt-1" name="time_plus[${br_id}][]" value="0" data-idx="${reqArr.length}" readonly>
                        </td>
                        <td><button type='button' class="btn btn-success btn-sm" onclick="insertRow('${br_id}','request',${reqArr.length - 1},'after')">เพิ่ม</button></td>
                    </tr>`;
                }
                html += `</tbody></table></div></div></div>`;
            });

            if (isEditable) {
                html += `<div class='my-3'><button type='submit' class="btn btn-success btn-lg w-100">บันทึกเป็นแผนใหม่ (ส่งเพื่อยืนยัน)</button></div>`;
            }
            html += `</form>`;
            container.innerHTML = html;

            // --- sync checkbox modal กับ selectedPoints ทุกครั้งที่ modal เปิด ---
            Object.entries(editablePlans).forEach(([br_id, plan_data]) => {
                const reqArr = plan_data.request || [];
                const stopsArr = plan_data.stops || [];
                reqArr.forEach((_, idx) => {
                    const modalId = `pointModal_${br_id}_${idx}`;
                    const modalElem = document.getElementById(modalId);
                    if (modalElem) {
                        modalElem.removeEventListener('shown.bs.modal', modalElem._syncPointsListener || (()=>{}));
                        const syncPointsListener = function() {
                            const selectedPoints = (stopsArr[idx] || []).map(String);
                            const pts = pointData[br_id] || [];
                            pts.forEach((pt) => {
                                const val = pt.id.toString();
                                const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
                                if (cb) cb.checked = selectedPoints.includes(val);
                            });
                        };
                        modalElem.addEventListener('shown.bs.modal', syncPointsListener);
                        modalElem._syncPointsListener = syncPointsListener;
                    }
                });
            });
        }

        // --- Modal Logic: เปรียบเทียบแผน/ยืนยัน/ปฏิเสธ ---
        const compareModal = new bootstrap.Modal(document.getElementById('compareModal'));

        function createStaticPlanTable(data) {
            const requests = data.request || [];
            const times = data.time || [];
            const time_pluses = data.time_plus || [];
            if (requests.length === 0) return '<p class="text-muted">ไม่มีข้อมูล</p>';
            let tableHtml = '<table class="table table-sm table-bordered"><thead><tr><th>ลำดับ</th><th>Code</th><th>เวลา</th><th>เวลาเดินทาง</th></tr></thead><tbody>';
            requests.forEach((req, idx) => {
                tableHtml += `<tr><td>${idx + 1}</td><td>${req}</td><td>${times[idx] || '-'}</td><td>${time_pluses[idx] || '0'} นาที</td></tr>`;
            });
            tableHtml += '</tbody></table>';
            return tableHtml;
        }

        function showCompareModal(br_id) {
            const planData = allPlansData[br_id];
            if (!planData || !planData.pending) return;
            const activePlanSource = displaySource[br_id];
            const activePlanData = allPlansData[br_id][activePlanSource];
            const sourceText = activePlanSource === 'confirmed' ? 'แผนยืนยันแล้ว' : 'คิวมาตรฐาน';

            document.getElementById('compareModalLabel').innerText = `เปรียบเทียบแผน Route: ${br_id}`;
            document.getElementById('active-plan-title').innerText = `แผนปัจจุบัน (ที่มา: ${sourceText})`;
            document.getElementById('active-plan-table').innerHTML = createStaticPlanTable(activePlanData);
            document.getElementById('pending-plan-table').innerHTML = createStaticPlanTable(planData.pending.data);
            document.getElementById('modal-pr-id').value = planData.pending.pr_id;
            document.getElementById('modal-action-form').onsubmit = () => confirm('คุณแน่ใจหรือไม่?');
            compareModal.show();
        }

        // --- เพิ่มฟังก์ชันสำหรับเลือกแผนที่บันทึกไว้ (pr_status=3) พร้อมเปรียบเทียบก่อนยืนยัน ---
        function showCancelledPlansSelector(br_id) {
            const plans = cancelledPlans[br_id] || [];
            if (plans.length === 0) {
                alert('ไม่มีแผนที่บันทึกไว้สำหรับสายนี้');
                return;
            }
            let modalId = `cancelledPlansModal_${br_id}`;
            let modalElem = document.getElementById(modalId);
            if (modalElem) modalElem.remove();
            let html = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="${modalId}_label" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="${modalId}_label">เลือกแผนที่บันทึกไว้ (Route ${br_id})</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group mb-3">
                            ${plans.map((plan, idx) => `
                                <button type="button" class="list-group-item list-group-item-action"
                                    onclick="showCompareSpecialPlan('${br_id}', ${idx})">
                                    วันที่: ${plan.pr_name}</span>
                                </button>
                            `).join('')}
                        </div>
                        <div id="specialPlanCompare_${br_id}"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                    </div>
                </div>
            </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
            let modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        }

        // เปรียบเทียบแผนปัจจุบันกับแผนพิเศษที่เลือก และให้กดยืนยันเพื่อใช้งาน
        function showCompareSpecialPlan(br_id, idx) {
            const plans = cancelledPlans[br_id] || [];
            const plan = plans[idx];
            if (!plan) return;
            // แผนปัจจุบัน
            const currentPlan = editablePlans[br_id];

            // ฟังก์ชันแสดงจุดจอด
            function renderStops(stopsArr, br_id_for_stops) {
                if (!Array.isArray(stopsArr)) return '-';
                const pts = pointData[br_id_for_stops] || [];
                return stopsArr.map((stopList, i) => {
                    if (!Array.isArray(stopList)) return '-';
                    const names = stopList.map(id => {
                        const pt = pts.find(p => String(p.id) === String(id));
                        return pt ? pt.name : id;
                    });
                    return `<div><span class="badge bg-secondary">${i + 1}</span> ${names.join(', ')}</div>`;
                }).join('');
            }

            // ฟังก์ชันสร้างตาราง
            function createStaticPlanTable(data, br_id_for_stops) {
                const requests = data.request || [];
                const times = data.time || [];
                const time_pluses = data.time_plus || [];
                // รองรับ stops ทั้งแบบ stops และ point (สำหรับแผนเก่า)
                const stops = Array.isArray(data.stops) ? data.stops : (Array.isArray(data.point) ? data.point : []);
                if (requests.length === 0) return '<p class="text-muted">ไม่มีข้อมูล</p>';
                let tableHtml = '<table class="table table-sm table-bordered"><thead><tr><th>ลำดับ</th><th>Code</th><th>เวลา</th><th>เวลาเดินทาง</th><th>จุดจอด</th></tr></thead><tbody>';
                requests.forEach((req, idx) => {
                    tableHtml += `<tr>
                        <td>${idx + 1}</td>
                        <td>${req}</td>
                        <td>${times[idx] || '-'}</td>
                        <td>${time_pluses[idx] || '0'} นาที</td>
                        <td>${renderStops([stops[idx]], br_id_for_stops)}</td>
                    </tr>`;
                });
                tableHtml += '</tbody></table>';
                return tableHtml;
            }

            // HTML เปรียบเทียบ
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>แผนปัจจุบัน</h6>
                        <div class="table-responsive">${createStaticPlanTable(currentPlan, br_id)}</div>
                    </div>
                    <div class="col-md-6">
                        <h6>แผนที่บันทึกไว้ (${plan.pr_name})</h6>
                        <div class="table-responsive">${createStaticPlanTable(plan.data, br_id)}</div>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-success" onclick="confirmUseSpecialPlan('${br_id}', ${idx})">ยืนยันการใช้แผนนี้</button>
                </div>
            `;
            document.getElementById(`specialPlanCompare_${br_id}`).innerHTML = html;
        }

        

        // เมื่อกดยืนยันการใช้แผนพิเศษ
        function confirmUseSpecialPlan(br_id, idx) {
            const plan = cancelledPlans[br_id][idx];
            if (!plan) return;
            if (!confirm('คุณต้องการใช้แผนนี้แทนแผนปัจจุบันหรือไม่?')) return;
            editablePlans[br_id] = {
                request: [...(plan.data.request || [])],
                reserve: [...(plan.data.reserve || [])],
                time: [...(plan.data.time || [])],
                time_plus: [...(plan.data.time_plus || [])],
                stops: plan.data.stops ? plan.data.stops.map(arr => Array.isArray(arr) ? [...arr] : []) : (plan.data.point ? plan.data.point.map(arr => Array.isArray(arr) ? [...arr] : []) : [])
            };
            // เปลี่ยนที่มาเป็น "แผนที่บันทึกไว้"
            displaySource[br_id] = 'special';
            // ปิด modal
            const modalElem = document.getElementById(`cancelledPlansModal_${br_id}`);
            if (modalElem) {
                const modal = bootstrap.Modal.getInstance(modalElem);
                if (modal) modal.hide();
                setTimeout(() => modalElem.remove(), 500);
            }
            renderTables();
        }
        // --- Main Execution ---
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filter-form');
            const dateSelect = document.getElementById('date-select');
            const routeSelectEl = document.getElementById('route-select');

            const choices = new Choices(routeSelectEl, {
                removeItemButton: true, placeholder: true, placeholderValue: 'เลือกสาย...', searchPlaceholderValue: 'ค้นหาสาย...',
            });

            dateSelect.addEventListener('change', () => form.submit());
            routeSelectEl.addEventListener('change', () => form.submit());

            initializeEditableData();
            // กำหนด stops array ถ้ายังไม่มี
            Object.keys(editablePlans).forEach(br_id => {
                if (!editablePlans[br_id].stops) {
                    editablePlans[br_id].stops = [];
                }
            });
            renderTables();

            // --- ลบ input ของแถว "ใหม่" ออกจากฟอร์มก่อน submit ---
            document.addEventListener('submit', function(e) {

                if (e.target && e.target.id === 'edit-plan-form') {
                    normalizeExData(); // แปลงข้อมูล ex ก่อน submit
                    Object.entries(editablePlans).forEach(([br_id, obj]) => {
                        const reqArr = obj.request || [];
                        const form = e.target;
                        // ลบ input[name="request[br_id][]"] ของแถวใหม่
                        let inputs = form.querySelectorAll(`select[name="request[${br_id}][]"]`);
                        if (inputs.length > reqArr.length) {
                            inputs[inputs.length - 1].remove();
                        }
                        // ลบ input[name="time[br_id][]"] ของแถวใหม่
                        inputs = form.querySelectorAll(`input[name="time[${br_id}][]"]`);
                        if (inputs.length > reqArr.length) {
                            inputs[inputs.length - 1].remove();
                        }
                        // ลบ input[name="time_plus[br_id][]"] ของแถวใหม่
                        inputs = form.querySelectorAll(`input[name="time_plus[${br_id}][]"]`);
                        if (inputs.length > reqArr.length) {
                            inputs[inputs.length - 1].remove();
                        }
                        // ลบ input[name="point[br_id][]"] ของแถวใหม่
                        inputs = form.querySelectorAll(`input[name="point[${br_id}][]"]`);
                        if (inputs.length > reqArr.length) {
                            inputs[inputs.length - 1].remove();
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
