<?php
include 'config.php';
header('Content-Type: text/html; charset=UTF-8');

$plan_date = $_GET['date'] ?? date('Y-m-d');

$today = new DateTime();
$today->setTime(0, 0);
$selected_date = new DateTime($plan_date);
$is_editable = ($selected_date > $today);

// ========== ดึงภูมิภาค ==========
$zones = [];
$res_zones = $conn->query("SELECT bz_id, bz_name_th FROM bus_zone ORDER BY bz_name_th");
while ($row = $res_zones->fetch_assoc()) {
    $zones[$row['bz_id']] = $row['bz_name_th'];
}

// ---------- AJAX handler สำหรับโหลดสายเดินรถ ----------
if (isset($_GET['ajax']) && $_GET['ajax'] === 'routes_by_zone') {
    $zone_id = $_GET['zone'] ?? null;
    $routes = [];

    if ($zone_id === null || $zone_id === '') {
        // โหลดสายเดินรถทั้งหมด
        $sql = "
            SELECT br.br_id, CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
            FROM bus_routes br
            LEFT JOIN location loS ON br.br_start = loS.locat_id
            LEFT JOIN location loE ON br.br_end = loE.locat_id
            ORDER BY br.br_id
        ";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $routes[] = $row;
        }
    }
    elseif (is_numeric($zone_id)) {
        $stmt = $conn->prepare("
            SELECT br.br_id, CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
            FROM bus_routes br
            LEFT JOIN location loS ON br.br_start = loS.locat_id
            LEFT JOIN location loE ON br.br_end = loE.locat_id
            WHERE br.bz_id = ?
            ORDER BY br.br_id
        ");
        $stmt->bind_param('i', $zone_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $routes[] = $row;
        }
        $stmt->close();
    }

    echo json_encode($routes);
    exit;
}

// ---------- ดึงสายเดินรถที่จะแสดงเริ่มต้น ----------
$selected_zone_id = $_GET['zone'] ?? null;
$all_routes = [];

if ($selected_zone_id === null || $selected_zone_id === '') {
    // โหลดสายเดินรถทั้งหมด
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
elseif (is_numeric($selected_zone_id)) {
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
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการคิวมาตรฐาน</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

</head>
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

    <div class="container-fluid px-4 pt-4">
  <h4 class="text-center fw-bold py-3 mb-4 text-white" style="background-color: #16325cff; border-radius: 0.5rem;">
    จัดการคิวมาตรฐาน
  </h4>

  <form method="get" id="filter-form">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-header fw-bold">
            เลือกภูมิภาค และ สายเดินรถ
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="zone-select" class="form-label ">เลือกภูมิภาค:</label>
              <select id="zone-select" name="zone" class="form-select">
                <option value="">-- เลือกทั้งหมด --</option>
                <?php foreach ($zones as $bz_id => $bz_name): ?>
                  <option value="<?= $bz_id ?>" <?= ($bz_id == $selected_zone_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($bz_name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

<div class="mb-2 d-flex justify-content-between align-items-center">
  <label class="form-label  mb-0">สายเดินรถ:</label>
  <div id="btn-group" class="d-flex gap-2">
    <button type="button" id="select-all-routes" class="btn btn-sm btn-outline-primary">เลือกทั้งหมด</button>
    <button type="button" id="clear-all-routes" class="btn btn-sm btn-outline-secondary">ล้างทั้งหมด</button>
  </div>
</div>

<select id="route-select" name="routes[]" multiple class="form-select choices-multiple">
  <?php foreach ($all_routes as $br_id => $route_name): ?>
    <option value="<?= $br_id ?>"><?= htmlspecialchars($route_name) ?></option>
  <?php endforeach; ?>
</select>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div id="plan-tables" class="card p-3">
          <p class="text-muted">รอนิ่ง</p>
        </div>
      </div>
    </div>
  </form>
</div>
<!-- Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
function updateDateTime() {
  const now = new Date();
  const options = { timeZone: 'Asia/Bangkok', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute:'2-digit' };
  document.getElementById('datetime').textContent = now.toLocaleString('th-TH', options);
}
setInterval(updateDateTime, 1000);
updateDateTime();

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  document.body.classList.toggle('sidebar-collapsed');
  sidebar.classList.toggle('collapsed');
}

const routeSelect = document.getElementById('route-select');
const filterForm = document.getElementById('filter-form');
let choices = new Choices(routeSelect, {
  removeItemButton: true,
  placeholderValue: 'เลือกสายเดินรถ',
  searchPlaceholderValue: 'ค้นหา...',
  itemSelectText: ''
});

const selectAllBtn = document.getElementById('select-all-routes');
const clearAllBtn = document.getElementById('clear-all-routes');

// ✅ เลือกทั้งหมดเฉพาะรายการที่อยู่ใน DOM ปัจจุบัน
selectAllBtn.addEventListener('click', () => {
  const currentValues = Array.from(routeSelect.options).map(opt => opt.value);
  choices.setChoiceByValue(currentValues);
});

// ✅ ล้างทั้งหมด
clearAllBtn.addEventListener('click', () => {
  choices.removeActiveItems();
});

// ✅ เมื่อเปลี่ยนภูมิภาค โหลดสายใหม่ แล้ว reset Choices
document.getElementById('zone-select').addEventListener('change', function () {
  const zoneId = this.value;
  fetch('?ajax=routes_by_zone&zone=' + zoneId)
    .then(response => response.json())
    .then(data => {
      choices.destroy(); // ล้าง Choices เก่า
      routeSelect.innerHTML = ''; // ล้าง option เก่า

      // สร้าง option ใหม่
      data.forEach(route => {
        const option = document.createElement('option');
        option.value = route.br_id;
        option.textContent = route.route_name;
        routeSelect.appendChild(option);
      });

      // รีสร้าง Choices ใหม่
      choices = new Choices(routeSelect, {
        removeItemButton: true,
        placeholderValue: 'เลือกสายเดินรถ',
        searchPlaceholderValue: 'ค้นหา...',
        itemSelectText: ''
      });
    })
    .catch(err => console.error('โหลดสายเดินรถล้มเหลว:', err));
});

// ✅ ส่งฟอร์มเมื่อเปลี่ยนวันที่ (ถ้ามี input date-select)
const dateSelect = document.getElementById('date-select');
if (dateSelect) {
  dateSelect.addEventListener('change', () => filterForm.submit());
}
</script>

</body>
</html>