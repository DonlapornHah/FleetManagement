<?php
include 'config.php';

// ดึงสถานะ (active/inactive)
$statusOptions = [];
$status_sql = "SELECT status_id, status_name_th FROM status WHERE status_id IN (1,3)";
$status_result = $conn->query($status_sql);
while ($row = $status_result->fetch_assoc()) {
    $statusOptions[$row['status_id']] = $row['status_name_th'];
}

// ดึงรายการสายรถ
$route_sql = "
SELECT br_id, CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
FROM bus_routes
LEFT JOIN location loS ON bus_routes.br_start = loS.locat_id
LEFT JOIN location loE ON bus_routes.br_end = loE.locat_id
ORDER BY route_name ASC
";
$route_result = $conn->query($route_sql);

// เก็บสายรถไว้ใน array สำหรับ select ใน modal
$routes = [];
if ($route_result && $route_result->num_rows > 0) {
  while ($r = $route_result->fetch_assoc()) {
    $routes[] = $r;
  }
}

// รับค่า route filter
$route_filter = isset($_GET['route_filter']) ? (int)$_GET['route_filter'] : 0;

// SQL หลัก
$where = "WHERE e.et_id = 2"; // et_id=2 คือพนักงานขับรถพ่วง
if ($route_filter > 0) {
    $where .= " AND e.main_route = $route_filter";
}
$sql = "
SELECT 
  e.em_id,
  e.em_name,
  e.em_surname,
  e.es_id,
  e.main_route,
  CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
FROM employee e
LEFT JOIN bus_routes br ON e.main_route = br.br_id
LEFT JOIN location loS ON br.br_start = loS.locat_id
LEFT JOIN location loE ON br.br_end = loE.locat_id
$where
ORDER BY e.em_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>จัดการคิวการเดินรถ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    
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
      .table tbody tr:hover td {
  background-color: #d4d4d4ff;
  cursor: pointer;
  transition: background-color 0.1s ease;
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
                <li><a class="dropdown-item" href="ManageCoach.php"><i class="bi bi-people-fill me-2"></i>พนักงานบริการ</a></li>
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
    จัดการพนักงานขับรถ พ่วง
  </h4>

  <!-- Filter Route -->
  <form method="get" class="row g-3 mb-3">
    <div class="col-md-4">
      <label for="route_filter" class="form-label fw-semibold">เลือกสายเดินรถ:</label>
      <select name="route_filter" id="route_filter" class="form-select">
        <option value="0">-- แสดงทั้งหมด --</option>
        <?php foreach ($routes as $r): ?>
          <option value="<?= $r['br_id'] ?>" <?= ($route_filter == $r['br_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($r['route_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 align-self-end">
      <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
    </div>
  </form>

  <div class="card shadow-sm">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
      <span>รายการพนักงานขับรถพ่วง</span>
      <button class="btn btn-sm btn-light text-dark" id="btnAddDriver">
        <i class="bi bi-plus-circle me-1"></i> เพิ่มพนักงานขับรถพ่วง
      </button>
    </div>
    <table class="table table-bordered text-center align-middle">
      <thead class="table-secondary">
        <tr>
          <th style="font-weight: 300;">รหัส</th>
          <th style="font-weight: 300;">ชื่อ - สกุล</th>
          <th style="font-weight: 300;">ตำแหน่ง</th>
          <th style="font-weight: 300;">สายรถประจำ</th>
          <th style="font-weight: 300;">สถานะ</th>
          <th style="font-weight: 300;">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr data-emid="<?= $row['em_id'] ?>" 
                data-name="<?= htmlspecialchars($row['em_name'], ENT_QUOTES) ?>" 
                data-surname="<?= htmlspecialchars($row['em_surname'], ENT_QUOTES) ?>" 
                data-status="<?= $row['es_id'] ?>"
                data-route="<?= $row['main_route'] ?>">
              <td><?= $row['em_id'] ?></td>
              <td><?= htmlspecialchars($row['em_name']) . ' ' . htmlspecialchars($row['em_surname']) ?></td>
              <td>พนักงานขับรถ พ่วง</td>
              <td><?= htmlspecialchars($row['route_name'] ?? '-') ?></td>
              <td>
                <select class="form-select status-select" data-emid="<?= $row['em_id'] ?>">
                  <?php foreach ($statusOptions as $sid => $sname): ?>
                    <option value="<?= $sid ?>" <?= $sid == $row['es_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($sname) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <button class="btn btn-warning btn-sm btn-edit"><i class="fa fa-pen-to-square me-1"></i>จัดการ</button>
                <button class="btn btn-danger btn-sm btn-delete"><i class="bi bi-trash"></i> ลบ</button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6">ไม่พบข้อมูลพนักงานขับรถพ่วง</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal เพิ่ม / แก้ไข -->
<div class="modal fade" id="manageDriverModal" tabindex="-1" aria-labelledby="manageDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="manageDriverForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="manageDriverModalLabel">เพิ่มพนักงานขับรถพ่วง</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="modal_em_id" name="em_id" value="0" />
          <div class="mb-3">
            <label for="driver_name" class="form-label">ชื่อ</label>
            <input type="text" id="driver_name" name="em_name" class="form-control" required />
          </div>
          <div class="mb-3">
            <label for="driver_surname" class="form-label">นามสกุล</label>
            <input type="text" id="driver_surname" name="em_surname" class="form-control" required />
          </div>
          <div class="mb-3">
  <label for="driver_route" class="form-label">สายรถประจำ</label>
  <select id="driver_route" name="main_route" class="form-select" required>
    <option value="">-- เลือกสายรถ --</option>
    <?php
      // สมมติว่ามี $route_result จาก query ดึงสายรถ
      $route_result->data_seek(0); // รีเซ็ต pointer ถ้าเคยวนแล้ว
      while ($route = $route_result->fetch_assoc()) {
        echo '<option value="' . $route['br_id'] . '">' . htmlspecialchars($route['route_name']) . '</option>';
      }
    ?>
  </select>
</div>

          <div class="mb-3 d-none">
            <label for="status_id" class="form-label">สถานะ</label>
            <input type="hidden" id="status_id" name="es_id" value="1" />
            <!-- ปกติสถานะเริ่มต้นเป็น 1 (Active) -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">บันทึก</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const manageModal = new bootstrap.Modal(document.getElementById('manageDriverModal'));
  const manageForm = document.getElementById('manageDriverForm');

  // ฟังก์ชันเปลี่ยนสี select ตามสถานะ
  function updateStatusColor(select) {
    select.classList.remove('bg-success', 'bg-danger', 'text-white');
    if (select.value == '1') {
      select.classList.add('bg-success', 'text-white');
    } else if (select.value == '3') {
      select.classList.add('bg-danger', 'text-white');
    }
  }

  // initial สีสถานะทุก select ในตาราง
  document.querySelectorAll('.status-select').forEach(updateStatusColor);

  // เปลี่ยนสถานะผ่าน select ในตาราง (Ajax update)
  document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', e => {
      const em_id = select.dataset.emid;
      const es_id = select.value;
      updateStatusColor(select);

      fetch('update_assistant_driver.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `em_id=${encodeURIComponent(em_id)}&es_id=${encodeURIComponent(es_id)}`
      })
      .then(res => res.text())
      .then(text => {
        if(text.trim() === 'OK') {
          alert('อัปเดตสถานะสำเร็จ');
        } else {
          alert('เกิดข้อผิดพลาด: ' + text);
        }
      })
      .catch(err => alert('Error: ' + err));
    });
  });

document.querySelector('table').addEventListener('click', e => {
  if (e.target.classList.contains('btn-edit')) {
    const tr = e.target.closest('tr');
    document.getElementById('modal_em_id').value = tr.dataset.emid;
    document.getElementById('driver_name').value = tr.dataset.name;
    document.getElementById('driver_surname').value = tr.dataset.surname;
    document.getElementById('driver_route').value = tr.dataset.route || '';
    document.getElementById('manageDriverModalLabel').textContent = 'แก้ไขพนักงานขับรถพ่วง';
    manageModal.show();
  }
});

document.getElementById('btnAddDriver').addEventListener('click', () => {
  manageForm.reset();
  document.getElementById('modal_em_id').value = 0;
  document.getElementById('manageDriverModalLabel').textContent = 'เพิ่มพนักงานขับรถพ่วง';
  document.getElementById('driver_route').value = '';  // เคลียร์ค่า
  manageModal.show();
});


  // ลบข้อมูล
  document.querySelector('table').addEventListener('click', e => {
    if (e.target.classList.contains('btn-delete')) {
      if (confirm('ต้องการลบพนักงานขับรถพ่วงคนนี้หรือไม่?')) {
        const tr = e.target.closest('tr');
        const em_id = tr.dataset.emid;
        fetch('delete_assistant_driver.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `em_id=${encodeURIComponent(em_id)}`
        })
        .then(res => res.text())
        .then(text => {
          if(text.trim() === 'OK') {
            alert('ลบข้อมูลสำเร็จ');
            location.reload();
          } else {
            alert('เกิดข้อผิดพลาด: ' + text);
          }
        })
        .catch(err => alert('Error: ' + err));
      }
    }
  });

  // ส่งฟอร์มเพิ่ม/แก้ไข
  manageForm.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(manageForm);
    fetch('update_assistant_driver.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(text => {
      if(text.trim() === 'OK') {
        alert('บันทึกข้อมูลสำเร็จ');
        location.reload();
      } else {
        alert('เกิดข้อผิดพลาด: ' + text);
      }
    })
    .catch(err => alert('Error: ' + err));
  });
});
</script>

</body>
</html>
