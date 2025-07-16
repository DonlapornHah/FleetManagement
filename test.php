<?php
include("config.php"); // เชื่อมต่อฐานข้อมูล

$selectedRegion = $_GET['region'] ?? '';
$selectedRoute = $_GET['route'] ?? '';

$sql = "
SELECT 
  z.bz_name_th AS zone_name,
  z.bz_id,
  r.br_id,
  r.br_start,
  r.br_end,
  l1.locat_name_th AS start_name,
  l2.locat_name_th AS end_name,
  b.bi_licenseplate,
  1 AS bs_id,             -- กำหนดสถานะเป็น 1 (ปกติ)
  'ปกติ' AS bs_name      -- กำหนดชื่อสถานะเป็น ปกติ
FROM bus_info b
JOIN bus_routes r ON b.br_id = r.br_id
JOIN bus_zone z ON r.bz_id = z.bz_id
JOIN location l1 ON r.br_start = l1.locat_id
JOIN location l2 ON r.br_end = l2.locat_id
-- LEFT JOIN bus_status bs ON b.bs_id = bs.bs_id  <-- ตัด JOIN นี้ออก
WHERE 1
";



if (!empty($selectedRegion)) {
    $sql .= " AND z.bz_id = '" . $conn->real_escape_string($selectedRegion) . "'";
}
if (!empty($selectedRoute)) {
    list($start, $end) = explode(" - ", $selectedRoute);
    $sql .= " AND l1.locat_name_th = '" . $conn->real_escape_string($start) . "'";
    $sql .= " AND l2.locat_name_th = '" . $conn->real_escape_string($end) . "'";
}

$sql .= " ORDER BY z.bz_id, r.br_id, b.bi_licenseplate ASC";

$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $zone = $row['zone_name'];
    $route_name = $row['start_name'] . " - " . $row['end_name'];
    // เก็บทั้งทะเบียนและสถานะไว้ด้วย
    $data[$zone][$route_name][] = [
      'plate' => $row['bi_licenseplate'],
      'status_id' => $row['bs_id'],
      'status_name' => $row['bs_name']
    ];
  }
}

// ดึงข้อมูลภูมิภาค (bus_zone)
$zones = [];
$sql = "SELECT bz_id, bz_name_th FROM bus_zone ORDER BY bz_name_th";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $zones[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      overflow-x: hidden;
      background-color: #f0f2f5;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Sidebar ซ้าย */
    .sidebar {
      width: 250px;
      min-height: 100vh;
      transition: width 0.3s ease;
      background-color: rgb(72, 72, 72);
      color: #cfd8dc;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      padding: 1rem;
    }
    .sidebar.collapsed {
      width: 70px;
      padding: 1rem 0.3rem;
    }
    .sidebar .nav-link {
      color: #cfd8dc;
      white-space: nowrap;
      font-weight: 500;
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      transition: background-color 0.3s;
      cursor: pointer;
      border-radius: 4px;
      user-select: none;
      text-decoration: none;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: #2e3e55;
      color: #fff;
    }
    .sidebar.collapsed .nav-link {
      justify-content: center;
      padding: 0.75rem 0;
    }
    .sidebar.collapsed .nav-text {
      display: none;
    }
    .content {
      margin-left: 250px;
      padding: 1rem;
      transition: margin-left 0.3s ease;
    }
    .sidebar.collapsed ~ .content {
      margin-left: 70px;
    }

    #map {
      width: 100%;
      height: calc(100vh - 100px);
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    /* Sidebar ขวา */
    #infoSidebar {
      position: fixed;
      top: 0;
      right: -600px; /* เริ่มซ่อนนอกจอขวา */
      width: 820px;
      height: 100vh;
      background-color: #ffffff;
      box-shadow: -10px 0 8px rgba(0,0,0,0.1);
      transition: right 0.4s ease;
      z-index: 1050;
      padding: 1rem 1.5rem;
      overflow-y: auto;
      border-radius: 100px ;
      opacity: 90%;
    }
    #infoSidebar.active {
      right: 0 !important;
    }
    #infoSidebar .sidebar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: bold;
      margin-bottom: 1rem;
      font-size: 1.25rem;
    }
    #infoSidebar .close-btn {
      background: none;
      border: none;
      font-size: 1.5rem;
      color: #333;
      cursor: pointer;
      line-height: 1;
    }

    /* ปุ่ม toggle ลูกศรที่ขอบซ้าย sidebar ขวา */
    #toggleInfoSidebarBtn {
      position: fixed;
      top: 50%;
      right: 800px; /* ติดขอบ sidebar ตอนเปิด */
      transform: translateY(-50%);
      background-color: #0d6efd;
      border: none;
      color: #fff;
      border-radius: 30px;
      width: 50px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.15);
      transition: right 0.4s ease;
      z-index: 1100;
      user-select: none;
    }
    /* เวลาซ่อน sidebar ปุ่มต้องเลื่อนไปทางขวา */
    #toggleInfoSidebarBtn.collapsed {
      right: 200px; /* เริ่มซ่อนนอกจอขวา */
      border-radius: 30px;
    }
    .modal {
  z-index: 1200 !important;
}
.modal-backdrop {
  z-index: 1190 !important;
}
.modal-dialog-scrollable .modal-body {
  max-height: 70vh; /* หรือปรับตามชอบ */
  overflow-y: auto;
}
.modal-footer{
  z-index:1000;
}

  </style>
</head>
<body class="sidebar-collapsed">
<div class="d-flex">
  <!-- Sidebar ซ้าย -->
  <div id="sidebar" class="sidebar collapsed">
    <button class="btn btn-sm mb-3 align-self-end" onclick="toggleSidebar()">
      <i class="bi bi-list"></i>
    </button>
    <a href="#" class="nav-link"><i class="bi bi-house-door"></i><span class="nav-text">หน้าหลัก</span></a>
    <a href="#" class="nav-link"><i class="bi bi-bus-front"></i><span class="nav-text">จัดการรถ</span></a>
    <a href="#" class="nav-link"><i class="bi bi-person-badge"></i><span class="nav-text">พนักงาน</span></a>
    <a href="#" class="nav-link"><i class="bi bi-clock-history"></i><span class="nav-text">ประวัติ</span></a>
    <a href="#" class="nav-link"><i class="bi bi-gear"></i><span class="nav-text">ตั้งค่า</span></a>
  </div>
  <!-- เนื้อหาหลัก -->
  <div class="content flex-grow-1">
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4 px-4">
      <div class="container-fluid">
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#topbarNav"
          aria-controls="topbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topbarNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex align-items-center">
            <li class="nav-item d-flex align-items-center me-3">
              <img
                src="https://img5.pic.in.th/file/secure-sv1/752440-01-removebg-preview.png"
                alt="Logo"
                style="width: 100px; height: auto; user-select: none;"
              />
            </li>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="manage2.php">คิวการเดินรถ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="bus_schedule.php">จัดการการเดินรถ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="car_edit.php">วางแผนรถ</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">รายงานและประวัติ</a>
            </li>
          </ul>
          <span class="navbar-text text-muted" id="datetime"></span>
        </div>
      </div>
    </nav>
    <!-- แผนที่ -->
    <div id="map"></div>
  </div>
</div>
<div id="infoSidebar" class="active">
  <!-- ฟอร์มตัวกรอง -->
  <div class="card shadow-sm border-0 rounded-4 p-4 mb-4 mx-3">
    <form method="GET">
      <div class="row justify-content-center align-items-end g-3" style="opacity:100%">
        <div class="col-md-6">
          <label for="regionFilter" class="form-label fw-semibold">ภูมิภาค</label>
          <select id="regionFilter" name="region" class="form-select shadow-sm rounded-3">
            <option value="">-- ทั้งหมด --</option>
            <?php foreach ($zones as $z): ?>
              <option value="<?= $z['bz_id'] ?>" <?= $selectedRegion == $z['bz_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($z['bz_name_th']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label for="routeFilter" class="form-label fw-semibold">สายเดินรถ</label>
          <select id="routeFilter" name="route" class="form-select shadow-sm rounded-3">
            <option value="">-- ทั้งหมด --</option>
            <?php foreach ($data as $zone => $routes): ?>
              <?php foreach ($routes as $routeName => $buses): ?>
                <option value="<?= htmlspecialchars($routeName) ?>" <?= $selectedRoute == $routeName ? 'selected' : '' ?>>
                  <?= htmlspecialchars($routeName) ?>
                </option>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 d-grid mt-3">
          <button class="btn btn-primary shadow-sm rounded-3 fw-semibold" type="submit">
            <i class="bi bi-search me-1"></i> ค้นหา
          </button>
        </div>
      </div>
    </form>
  </div>
  
  <!-- เนื้อหาแสดงข้อมูลรถ -->
  <div class="p-3" style="overflow-y:auto; height: calc(100vh - 190px);">
    <div id="sidebarContent"></div>
    <?php if (!empty($data)): ?>
      <?php foreach ($data as $zoneName => $routes): ?>
        <div class="region-container mb-4">
          <h5 class="region-label fw-bold"><?= htmlspecialchars($zoneName) ?></h5>

          <?php foreach ($routes as $routeName => $buses): ?>
            <div class="route-container mb-3">
              <div class="route-header mb-2 d-flex justify-content-between align-items-center">
                <span class="route-name fw-semibold"><?= htmlspecialchars($routeName) ?></span>
                <span class="route-badge badge bg-primary rounded-pill">จำนวนรถทั้งหมด : <?= count($buses) ?> คัน</span>
              </div>

              <div class="d-flex flex-wrap gap-2 px-1">
                <?php foreach ($buses as $bus):
                  $plate = $bus['plate'];
                  $statusName = $bus['status_name'];
                  $statusId = $bus['status_id'];

                  // สีพื้นหลังและสีตัวอักษร
                  $bgColor = '#f8f8f8'; // default
                  $textColor = '#111';

                  switch ($statusId) {
                      case 2: $bgColor = '#dc3545'; $textColor = '#fff'; break;
                      case 3: $bgColor = '#ffc107'; $textColor = '#000'; break;
                      case 4: $bgColor = '#0dcaf0'; $textColor = '#fff'; break;
                      case 5: $bgColor = '#212529'; $textColor = '#fff'; break;
                      case 6: $bgColor = '#0d6efd'; $textColor = '#fff'; break;
                  }
                ?>
                <div 
                  class="card small-card"
                  title="สถานะ: <?= htmlspecialchars($statusName) ?>"
                  data-status="<?= strtolower($statusName) ?>"
                  data-plate="<?= htmlspecialchars($plate) ?>"
                  data-statusname="<?= htmlspecialchars($statusName) ?>"
                  data-statusid="<?= htmlspecialchars($statusId) ?>"
                  style="background-color: <?= htmlspecialchars($bgColor) ?>; cursor:pointer; min-width: 90px;"
                >
                  <div class="card-body p-2 text-center">
                    <h6 class="card-title mb-0" style="color: <?= htmlspecialchars($textColor) ?>;">
                      <?= htmlspecialchars($plate) ?>
                    </h6>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-muted mt-4">ไม่พบข้อมูล</p>
    <?php endif; ?>
  </div>
</div>

<!-- ปุ่ม toggle sidebar ขวา -->
<div id="toggleInfoSidebarBtn" title="เปิด/ปิด รายงานผล">
  <i id="toggleIcon" class="bi bi-arrow-left"></i>
</div>
<!-- Modal -->
<div class="modal fade" id="busStatusModal" tabindex="-1" aria-labelledby="busStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="busStatusForm">
        <div class="modal-header">
          <h5 class="modal-title" id="busStatusModalLabel">แก้ไขสถานะรถ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="plate" id="modalPlate">

          <!-- ข้อมูลสถานะ -->
           <div class="text-dark" style="background-color: #ecececff; padding: 20px;">
          <div class="mb-3">
            <label for="modalStatus" class="form-label">สถานะปัจจุบัน</label>
            <select class="form-select" id="modalStatus" name="status_id" required>
              <option value="1">ปกติ</option>
              <option value="2">ไม่พร้อม</option>
              <option value="3">รถรอซ่อม</option>
            </select>
          </div>
          <!-- ข้อมูลตำแหน่ง -->
          <div class="mb-3">
            <label class="form-label">ตำแหน่งรถปัจจุบัน</label>
            <input type="text" class="form-control" id="modalLocation" readonly>
          </div>
    </div>

          <!-- ข้อมูลรถ -->
           <div class="text-dark" style="background-color: #ffffffff; padding: 10px;">
          <h6 class="fw-bold text-dark mb-3">ข้อมูลรถ</h6>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">ทะเบียนรถ</label>
              <input type="text" class="form-control" id="modalPlateDisplay">
            </div>
            <div class="col-md-6">
              <label class="form-label">เลขถัง</label>
              <input type="text" class="form-control" id="modalChassisNo">
            </div>
            <div class="col-md-6">
              <label class="form-label">ยี่ห้อ</label>
              <input type="text" class="form-control" id="modalBrand">
            </div>
            <div class="col-md-6">
              <label class="form-label">รุ่น</label>
              <input type="text" class="form-control" id="modalModel">
            </div>
          </div>
    </div>
                    <!-- ข้อมูลพนักงานขับรถ -->
          <div class="text-dark" style="background-color: #ecececff; padding: 20px;">
          <h6 class="fw-bold text-White mb-3">ข้อมูลพนักงานขับรถ</h6>
          <div class="row g-3 mb-3" >
            <div class="col-md-6">
              <label class="form-label">ชื่อคนขับ</label>
              <input type="text" class="form-control" id="modalDriverName">
            </div>
            <div class="col-md-6">
              <label class="form-label">เบอร์โทรศัพท์</label>
              <input type="text" class="form-control" id="modalPhone" >
            </div>
            <div class="col-md-4">
              <label class="form-label">เพศ</label>
              <input type="text" class="form-control" id="modalDriverGender" >
            </div>
            <div class="col-md-4">
              <label class="form-label">อายุ</label>
              <input type="text" class="form-control" id="modalDriverAge" readonly>
            </div>
          </div>
    </div>
          <!-- แผนที่ -->
           <div class="text-dark" style="background-color: #ffffffff; padding: 20px;">
          <div class="mb-3">
            <label class="form-label">แผนที่ปัจจุบัน</label>
            <div id="modalMap" style="width: 100%; height: 300px;"></div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">บันทึก</button>
          <button type="button" class="btn btn-info" id="btnViewHistory">ดูประวัติ</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Simplemaps -->
<script src="0mapdata.js"></script>
<script src="countrymap.js"></script>
<script>
// ⏱ โหลดเมื่อเปิดหน้า
window.onload = function () {
  if (typeof simplemaps_countrymap !== "undefined") {
    simplemaps_countrymap.load();
  }

  const datetime = document.getElementById("datetime");
  const now = new Date();
  datetime.innerText = now.toLocaleString("th-TH", {
    dateStyle: "long",
    timeStyle: "short",
  });

  toggleInfoSidebar(true); // เปิด sidebar ขวาโดยอัตโนมัติ
};

// ✅ Toggle Sidebar ซ้าย
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
  document.querySelector(".content").classList.toggle("collapsed");
}

// ✅ Toggle Sidebar ขวา
function toggleInfoSidebar(show = null) {
  const sidebar = document.getElementById("infoSidebar");
  const toggleBtn = document.getElementById("toggleInfoSidebarBtn");
  const toggleIcon = document.getElementById("toggleIcon");
  if (show === true) {
    sidebar.classList.add("active");
    toggleBtn.classList.remove("collapsed");
    toggleIcon.className = "bi bi-arrow-right";
  } else if (show === false) {
    sidebar.classList.remove("active");
    toggleBtn.classList.add("collapsed");
    toggleIcon.className = "bi bi-arrow-left";
  } else {
    sidebar.classList.toggle("active");
    toggleBtn.classList.toggle("collapsed");
    toggleIcon.className = sidebar.classList.contains("active") ? "bi bi-arrow-right" : "bi bi-arrow-left";
  }
}

// ✅ ปุ่ม toggle sidebar ขวา
document.getElementById("toggleInfoSidebarBtn").addEventListener("click", toggleInfoSidebar);

// ✅ ฟังก์ชันเปลี่ยน URL parameter
function updateQueryStringParameter(uri, key, value) {
  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
  if (uri.match(re)) {
    return uri.replace(re, '$1' + key + "=" + value + '$2');
  }
  return uri + separator + key + "=" + value;
}

// ✅ เมื่อเปลี่ยนค่าภูมิภาค
document.getElementById('regionFilter').addEventListener('change', function() {
  let url = window.location.href.split('?')[0];
  const params = new URLSearchParams(window.location.search);
  const region = this.value;
  region ? params.set('region', region) : params.delete('region');
  params.delete('page');
  window.location.href = url + '?' + params.toString();
});

// ✅ เมื่อเปลี่ยนค่าสายเดินรถ
document.getElementById('routeFilter').addEventListener('change', function() {
  let url = window.location.href.split('?')[0];
  const params = new URLSearchParams(window.location.search);
  const route = this.value;
  route ? params.set('route', route) : params.delete('route');
  params.delete('page');
  window.location.href = url + '?' + params.toString();
});

// ✅ ตั้งค่า dropdown เมื่อโหลดหน้า
window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  document.getElementById('regionFilter').value = params.get('region') || '';
  document.getElementById('routeFilter').value = params.get('route') || '';
});

// ✅ กรองสถานะการ์ด
function filterStatus(status) {
  const allRegions = document.querySelectorAll('.region-container');
  let anyCardVisible = false;
  allRegions.forEach(region => {
    let anyRouteVisibleInRegion = false;
    const routes = region.querySelectorAll('.route-container');
    routes.forEach(route => {
      let anyCardVisibleInRoute = false;
      const cards = route.querySelectorAll('.small-card');
      cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        const match = (status === 'all' || cardStatus === status);
        card.style.display = match ? '' : 'none';
        if (match) {
          anyCardVisibleInRoute = true;
          anyCardVisible = true;
        }
      });
      route.style.display = anyCardVisibleInRoute ? '' : 'none';
      if (anyCardVisibleInRoute) anyRouteVisibleInRegion = true;
    });
    region.style.display = anyRouteVisibleInRegion ? '' : 'none';
  });
  const noData = document.getElementById('noDataMessage');
  if (noData) noData.style.display = anyCardVisible ? 'none' : 'block';
}

// ✅ Modal Map และการคลิกการ์ด
document.addEventListener('DOMContentLoaded', () => {
  const modal = new bootstrap.Modal(document.getElementById('busStatusModal'));
  document.querySelectorAll('.small-card').forEach(card => {
    card.addEventListener('click', () => {
      document.getElementById('modalPlate').value = card.dataset.plate;
      document.getElementById('modalStatus').value = card.dataset.statusid;
      document.getElementById('modalPhone').value = card.dataset.phone || '-';
      document.getElementById('modalDriverName').value = card.dataset.drivername || '-';
      document.getElementById('modalLocation').value = card.dataset.location || 'ไม่ระบุ';
      modal.show();
    });
  });
});

// ✅ อัปเดตสถานะ
document.getElementById('busStatusForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('update_bus_status.php', {
    method: 'POST',
    body: formData
  }).then(res => res.text())
    .then(response => {
      alert('อัปเดตสถานะเรียบร้อย');
      location.reload();
    });
});

// ✅ Map click จาก simplemaps
simplemaps_countrymap.hooks.click_state = function(id) {
  const regionMap = {
    "th-1": "1",
    "th-2": "2",
    "th-3": "3",
    "th-4": "4",
    "th-5": "5",
    "th-6": "6"
  };
  const regionId = regionMap[id];
  if (!regionId) return;

  fetch(`get_region_routes.php?region=${regionId}`)
    .then(res => res.json())
    .then(data => {
      console.log("ข้อมูลโหลดจากแผนที่:", data);
      renderSidebarData(data);
      toggleInfoSidebar(true);
    })
    .catch(err => console.error("โหลดข้อมูลไม่สำเร็จ", err));
};

// ✅ แสดงข้อมูลภายใน sidebar จาก API
function renderSidebarData(data) {
  const container = document.getElementById('sidebarContent');
  container.innerHTML = '';

  for (const zoneName in data) {
    const zoneRoutes = data[zoneName];

    const zoneTitle = document.createElement('h5');
    zoneTitle.textContent = zoneName;
    zoneTitle.classList.add('mt-3', 'mb-2', 'fw-bold');
    container.appendChild(zoneTitle);

    for (const routeName in zoneRoutes) {
      const buses = zoneRoutes[routeName];

      const routeTitle = document.createElement('h6');
      routeTitle.textContent = routeName;
      routeTitle.classList.add('mb-1', 'text-primary');
      container.appendChild(routeTitle);

      const ul = document.createElement('div');
      ul.classList.add('d-flex', 'flex-wrap', 'gap-2', 'mb-3');

      buses.forEach(bus => {
        const card = document.createElement('div');
        card.className = 'card small-card';
        card.setAttribute('data-plate', bus.plate);
        card.setAttribute('data-status', bus.status_name.toLowerCase());
        card.setAttribute('data-statusid', bus.status_id);
        card.setAttribute('title', 'สถานะ: ' + bus.status_name);
        card.style.minWidth = '90px';
        card.style.cursor = 'pointer';

        const [bgColor, textColor] = bgColorMap[bus.status_id] || ['#eee', '#000'];
        card.style.backgroundColor = bgColor;

        card.innerHTML = `
          <div class="card-body p-2 text-center">
            <h6 class="card-title mb-0" style="color: ${textColor}">${bus.plate}</h6>
          </div>
        `;

        ul.appendChild(card);
      });

      container.appendChild(ul);
    }
  }
}
</script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
