<?php
// ---------- เชื่อมต่อฐานข้อมูล ----------
include 'config.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- จัดการการยืนยัน/ยกเลิก/ย้อนกลับ แผน (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $redirect_url = "planQueue.php?" . http_build_query($_GET);

    if ($action === 'revert' && isset($_POST['br_id'], $_POST['date'])) {
        // Action: Revert a confirmed plan back to standard
        $br_id_to_revert = (int)$_POST['br_id'];
        $date_to_revert = $_POST['date'];

        // Change status of confirmed plan (status=1) to old (status=2)
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
            // ดึงข้อมูลแผนที่จะดำเนินการ (ต้องมีสถานะ 0 คือรอยืนยัน)
            $stmt_get = $conn->prepare("SELECT br_id, pr_date, pr_request FROM plan_request WHERE pr_id = ? AND pr_status = 0");
            $stmt_get->bind_param('i', $pr_id_to_action);
            $stmt_get->execute();
            $plan = $stmt_get->get_result()->fetch_assoc();
            $stmt_get->close();

            if (!$plan) throw new Exception("ไม่พบแผนที่ต้องการดำเนินการ หรืออาจถูกดำเนินการไปแล้ว");

            $br_id = $plan['br_id'];
            $pr_date = $plan['pr_date'];
            $pr_request_json = $plan['pr_request'];

            if ($action === 'confirm') {
                // 1. ทำให้แผนที่เคยยืนยันแล้ว (status=1) กลายเป็นแผนเก่า (status=2)
                $stmt_update_old = $conn->prepare("UPDATE plan_request SET pr_status = 2 WHERE br_id = ? AND pr_date = ? AND pr_status = 1");
                $stmt_update_old->bind_param('is', $br_id, $pr_date);
                $stmt_update_old->execute();
                $stmt_update_old->close();

                // 2. อัปเดตสถานะแผนใหม่ที่เพิ่งยืนยันเป็น 1 (ยืนยันแล้ว)
                $stmt_confirm = $conn->prepare("UPDATE plan_request SET pr_status = 1 WHERE pr_id = ?");
                $stmt_confirm->bind_param('i', $pr_id_to_action);
                $stmt_confirm->execute();
                $stmt_confirm->close();

            } elseif ($action === 'cancel') {
                // ยกเลิกแผน: เปลี่ยนสถานะเป็น 3 (ไม่ใช้งาน/ปฏิเสธ)
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
        
        // Redirect กลับไปหน้าเดิมเพื่อรีเฟรชข้อมูล
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

// ดึงภูมิภาค (zones) จากฐานข้อมูล bus_zone
$zones = [];
$sql_zones = "SELECT bz_id, bz_name_th FROM bus_zone ORDER BY bz_name_th";
$res_zones = $conn->query($sql_zones);
while ($row = $res_zones->fetch_assoc()) {
    $zones[$row['bz_id']] = $row['bz_name_th'];
}

// รับภูมิภาคที่เลือก (bz_id) จาก GET
$selected_zone_id = $_GET['zone'] ?? null;
if ($selected_zone_id !== null) {
    $selected_zone_id = (int)$selected_zone_id;
    if (!array_key_exists($selected_zone_id, $zones)) {
        $selected_zone_id = null; // ถ้าไม่ถูกต้อง ให้เป็น null
    }
}

// ดึงสายเดินรถ ตามภูมิภาค หรือทั้งหมด
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

// รับสายเดินรถที่เลือกจาก GET (array)
$selected_routes = $_GET['routes'] ?? [];
if (!is_array($selected_routes)) {
    $selected_routes = [$selected_routes];
}
// กรองเฉพาะสายที่มีจริงในฐานข้อมูล
$selected_routes = array_intersect($selected_routes, array_keys($all_routes));

// ดึงข้อมูลแผน
$plans = [];
if (!empty($selected_routes)) {
    // แผนที่ยืนยันแล้ว (status=1)
    $confirmed_plans = [];
    $stmt = $conn->prepare("
        SELECT br_id, pr_request 
        FROM plan_request 
        WHERE pr_date = ? AND pr_status = 1 
        AND br_id IN (" . implode(',', $selected_routes) . ")
    ");
    $stmt->bind_param('s', $plan_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $confirmed_plans[$row['br_id']] = json_decode($row['pr_request'], true);
    }
    $stmt->close();

    // แผนมาตรฐานจาก queue_request
    $standard_queues = [];
    $result = $conn->query("
        SELECT br_id, qr_request 
        FROM queue_request 
        WHERE br_id IN (" . implode(',', $selected_routes) . ")
    ");
    while ($row = $result->fetch_assoc()) {
        $standard_queues[$row['br_id']] = json_decode($row['qr_request'], true);
    }

    // แผนรอยืนยัน (status=0)
    $pending_plans = [];
    $stmt = $conn->prepare("
        SELECT pr_id, br_id, pr_request 
        FROM plan_request 
        WHERE pr_date = ? AND pr_status = 0 
        AND br_id IN (" . implode(',', $selected_routes) . ")
    ");
    $stmt->bind_param('s', $plan_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pending_plans[$row['br_id']] = [
            'pr_id' => $row['pr_id'],
            'data'  => json_decode($row['pr_request'], true)
        ];
    }
    $stmt->close();

    // รวมข้อมูลแผนตาม route
    foreach ($selected_routes as $br_id) {
        if (isset($standard_queues[$br_id])) {
            $plans[$br_id] = [
                'standard'  => $standard_queues[$br_id],
                'confirmed' => $confirmed_plans[$br_id] ?? null,
                'pending'   => $pending_plans[$br_id] ?? null,
            ];
        }
    }
}
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
 แก้ไขแผนการเดินรถ
</h4>


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
            <div>
              <label for="zone-select" class="form-label ">เลือกภูมิภาค :</label>
              <select id="zone-select" name="zone" class="form-select">
                <option value="">-- เลือกภูมิภาคทั้งหมด --</option>
                <?php foreach ($zones as $bz_id => $bz_name):
                  $selected = ($bz_id == $selected_zone_id) ? 'selected' : ''; ?>
                  <option value="<?= $bz_id ?>" <?= $selected ?>><?= htmlspecialchars($bz_name) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="card-header fw-semibold">เลือกสายเดินรถ</div>
          <div class="card-body d-flex flex-column">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>สายเดินรถ</div>
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
          </div>
        </div>
      </div>

      <!-- คอลัมน์ขวา: แสดงแผน -->
      <div class="col-md-8">
        <div id="plan-tables">
          <!-- ตารางแผนจะถูกโหลดโดย JavaScript หรือ PHP -->
        </div>
      </div>
    </div>
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

// State to hold editable plans and display source
let editablePlans = {};
let displaySource = {}; // { br_id: 'standard' | 'confirmed' }

// Initialize editablePlans based on displaySource
function initializeEditableData() {
  editablePlans = {};
  Object.keys(allPlansData).forEach(br_id => {
    if (!displaySource[br_id]) {
      displaySource[br_id] = allPlansData[br_id].confirmed ? 'confirmed' : 'standard';
    }
    const source = displaySource[br_id];
    const dataToEdit = allPlansData[br_id][source];
    if (dataToEdit) {
      editablePlans[br_id] = {
        request: [...(dataToEdit.request || [])],
        reserve: [...(dataToEdit.reserve || [])],
        time: [...(dataToEdit.time || [])],
        time_plus: [...(dataToEdit.time_plus || [])]
      };
    }
  });
}

// Modal for comparing plan
const compareModal = new bootstrap.Modal(document.getElementById('compareModal'));

function createStaticPlanTable(data) {
  const requests = data.request || [];
  const times = data.time || [];
  const time_pluses = data.time_plus || [];
  if (requests.length === 0) return '<p class="text-muted">ไม่มีข้อมูล</p>';
  let tableHtml = '<table class="table table-sm table-bordered"><thead><tr><th>ลำดับ</th><th>Code</th><th>เวลา</th><th>เวลาเดินทาง</th></tr></thead><tbody>';
  requests.forEach((req, idx) => {
    tableHtml += `<tr><td>${idx + 1}</td><td>${req}</td><td>${times[idx] || '-'}</td><td>${time_pluses[idx] || '90'} นาที</td></tr>`;
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
  document.getElementById('active-plan-title').innerText = `แผนปัจจุบัน (ที่มา : ${sourceText})`;
  document.getElementById('active-plan-table').innerHTML = createStaticPlanTable(activePlanData);
  document.getElementById('pending-plan-table').innerHTML = createStaticPlanTable(planData.pending.data);
  document.getElementById('modal-pr-id').value = planData.pending.pr_id;
  document.getElementById('modal-action-form').onsubmit = () => confirm('คุณแน่ใจหรือไม่?');
  compareModal.show();
}

function toggleSource(br_id, source) {
  displaySource[br_id] = source;
  initializeEditableData();
  renderTables();
}

// สร้าง options สำหรับ select queue
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
  html += '</select>';
  return html;
}

function onQueueChange(br_id, type, idx, selectElem) {
  editablePlans[br_id][type][idx] = selectElem.value;
  renderTables();
}

function onTimeChange(br_id, idx, inputElem) {
  if (!editablePlans[br_id].time) editablePlans[br_id].time = [];
  editablePlans[br_id].time[idx] = inputElem.value;
}

function onTimePlusChange(br_id, idx, inputElem) {
  if (!editablePlans[br_id].time_plus) editablePlans[br_id].time_plus = [];
  editablePlans[br_id].time_plus[idx] = inputElem.value;
}

function removeRow(br_id, type, idx) {
  let arr = editablePlans[br_id][type] || [];
  if (arr.length > 0) {
    arr.splice(idx, 1);
    if (type === 'request') {
      (editablePlans[br_id].time || []).splice(idx, 1);
      (editablePlans[br_id].time_plus || []).splice(idx, 1);
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
    editablePlans[br_id].time_plus.splice(insertIdx, 0, '90');
  }
  renderTables();
}

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
  const routeNameMap = <?php echo json_encode($all_routes); ?>;

  Object.entries(editablePlans).forEach(([br_id, plan_data]) => {
    const originalPlan = allPlansData[br_id];
    const currentSource = displaySource[br_id];
    const sourceText = currentSource === 'confirmed' ? 'แผนยืนยันแล้ว' : 'คิวมาตรฐาน';

    html += `
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #d6d6d6ff; color: black; padding: 0.7rem 1rem">
      <h6 class="mb-0">สายเดินรถ : ${routeNameMap[br_id] || br_id} (ที่มา : ${sourceText})</h6>
      <div>
`;

    if (originalPlan.confirmed && isEditable) {
      html += `<div class="btn-group btn-group-sm me-2">
                  <button type="button" class="btn btn-light" onclick="toggleSource('${br_id}', 'standard')" ${currentSource === 'standard' ? 'disabled' : ''}>ใช้แผนมาตรฐาน</button>
                  <button type="button" class="btn btn-warning" onclick="toggleSource('${br_id}', 'confirmed')" ${currentSource === 'confirmed' ? 'disabled' : ''}>ใช้แผนยืนยัน</button>
               </div>`;
    }

    if (originalPlan.pending) {
      html += `<button type="button" class="btn btn-warning btn-sm" onclick="showCompareModal('${br_id}')">มีแผนใหม่รอยืนยัน</button>`;
    }
    html += `</div></div><div class="card-body"><div class="table-responsive">
                <input type="hidden" name="source[${br_id}]" value="${sourceText}">
                <table class="table table-sm table-bordered align-middle">
                <thead class="table-primary"><tr><th>ลำดับ</th><th>Queue Request</th><th>เวลา</th><th>เวลาเดินทาง (นาที)</th><th>Action</th></tr></thead><tbody>`;
    
    const reqArr = plan_data.request || [];
    reqArr.forEach((qr_request, idx) => {
      html += `<tr><td>${idx + 1}</td>
          <td>${createSelect(`request[${br_id}][]`, qr_request, routeOptions, br_id, 'request', idx)}</td>
          <td><input type="time" class="form-control form-control-sm" name="time[${br_id}][]" value="${(plan_data.time || [])[idx] || ''}" onchange="onTimeChange('${br_id}', ${idx}, this)" ${disabledAttr}></td>
          <td><input type="number" class="form-control form-control-sm" name="time_plus[${br_id}][]" value="${(plan_data.time_plus || [])[idx] || '90'}" onchange="onTimePlusChange('${br_id}', ${idx}, this)" ${disabledAttr} min="0"></td>
          <td><div class="btn-group btn-group-sm">
              <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'before')" ${disabledAttr}>แทรกก่อน</button>
              <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'after')" ${disabledAttr}>แทรกหลัง</button>
              <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','request',${idx})" ${disabledAttr}>ลบ</button>
          </div></td></tr>`;
    });

    // เพิ่ม input ซ่อนสำหรับ reserve (ไม่แก้ไข)
    const reserveArr = plan_data.reserve || [];
    reserveArr.forEach((qr_reserve, idx) => {
      html += `<input type="hidden" name="reserve[${br_id}][]" value="${qr_reserve}">`;
    });

    if (isEditable) {
      html += `<tr><td>ใหม่</td>
          <td>${createSelect(`request[${br_id}][]`, '2', routeOptions, br_id, 'request', reqArr.length)}</td>
          <td><input type="time" class="form-control form-control-sm" name="time[${br_id}][]" onchange="onTimeChange('${br_id}', ${reqArr.length}, this)"></td>
          <td><input type="number" class="form-control form-control-sm" name="time_plus[${br_id}][]" value="90" onchange="onTimePlusChange('${br_id}', ${reqArr.length}, this)" min="0"></td>
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
}

// Main init after DOM ready
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('filter-form');
  const dateSelect = document.getElementById('date-select');
  const routeSelectEl = document.getElementById('route-select');

  // Reinitialize Choices.js for route select (avoid double init)
  new Choices(routeSelectEl, {
    removeItemButton: true,
    placeholderValue: 'เลือกสาย...',
    searchPlaceholderValue: 'ค้นหาสาย...'
  });

  dateSelect.addEventListener('change', () => form.submit());
  routeSelectEl.addEventListener('change', () => form.submit());

  initializeEditableData();
  renderTables();

  // Debug: ฟัง event submit
  document.addEventListener('submit', e => {
    if(e.target && e.target.id === 'edit-plan-form') {
      console.log('ส่งฟอร์มแผนใหม่...');
    }
  });
});

</script>
</body>
</html>
