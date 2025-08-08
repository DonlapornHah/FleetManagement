<?php
include 'config.php';
header('Content-Type: text/html; charset=UTF-8');

// filter by route
$route_filter = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;
$whereClause = $route_filter > 0 ? "WHERE bi.br_id = $route_filter" : "";

// get route list
$route_sql = "
SELECT br.br_id, CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
FROM bus_routes br
LEFT JOIN location loS ON br.br_start = loS.locat_id
LEFT JOIN location loE ON br.br_end = loE.locat_id
ORDER BY route_name ASC
";
$routes = $conn->query($route_sql);

// get status options
$statusOptions = [
  1 => 'พร้อม',
  2 => 'ซ่อม',
];

// get bus info
$sql = "
SELECT 
  bi.bi_id,
  bi.bi_licen,
  CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name,
  CASE bi.bt_id
    WHEN 1 THEN 'First Class'
    WHEN 2 THEN 'Gold Class'
    ELSE 'ไม่ระบุ'
  END AS class_name,
  bi.status_id
FROM bus_info bi
LEFT JOIN bus_routes br ON bi.br_id = br.br_id
LEFT JOIN location loS ON br.br_start = loS.locat_id
LEFT JOIN location loE ON br.br_end = loE.locat_id
$whereClause
ORDER BY bi.bi_id ASC
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
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
    จัดการรถ
  </h4>

  <!-- Route Filter -->
  <form method="get" class="row g-3 mb-3">
    <div class="col-md-4">
      <label for="route_id" class="form-label fw-semibold">เลือกสายเดินรถ :</label>
      <select name="route_id" id="route_id" class="form-select ">
        <option value="0">-- แสดงทั้งหมด --</option>
        <?php if ($routes && $routes->num_rows > 0): ?>
          <?php while ($r = $routes->fetch_assoc()): ?>
            <option value="<?= $r['br_id'] ?>" <?= ($route_filter == $r['br_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($r['route_name']) ?>
            </option>
          <?php endwhile; ?>
        <?php endif; ?>
      </select>
    </div>
    <div class="col-md-2 align-self-end">
      <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
    </div>
  </form>

  <!-- Bus Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
  <span>รายการรถทั้งหมด</span>
      <button class="btn btn-light btn-sm" id="btnAddCar" data-bs-toggle="modal" data-bs-target="#manageCarModal"><i class="bi bi-plus-circle me-1"></i> เพิ่มรถ</button>
          </div>
<div class="card-body table-responsive p-0">
  <table class="table table-bordered text-center align-middle mb-0">
    <thead class="table-secondary">
      <tr>
        <th style="font-weight: 300;">รหัสรถ</th>
        <th style="font-weight: 300;">ทะเบียนรถ</th>
        <th style="font-weight: 300;">สายเดินรถ</th>
        <th style="font-weight: 300;">ประเภทรถ</th>
        <th style="font-weight: 300;">สถานะ</th>
        <th style="font-weight: 300;">แก้ไข</th>
      </tr>
    </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['bi_id'] ?></td>
                <td><?= htmlspecialchars($row['bi_licen']) ?></td>
                <td><?= htmlspecialchars($row['route_name'] ?? '-') ?></td>
                <td><?= $row['class_name'] ?></td>
                <td>
                  <select class="form-select status-select" data-busid="<?= $row['bi_id'] ?>">
                    <?php foreach ($statusOptions as $sid => $sname): ?>
                      <option value="<?= $sid ?>" <?= $sid == $row['status_id'] ? 'selected' : '' ?>>
                        <?= $sname ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                <button class="btn btn-sm btn-warning btn-edit"><i class="fa fa-pen-to-square me-1"></i>จัดการ</button>
                <button class="btn btn-sm btn-danger btn-delete"><i class="bi bi-trash"></i> ลบ</button>
              </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-muted">ไม่พบข้อมูลรถ</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">แก้ไขข้อมูลรถ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="bi_id" id="edit_bi_id">
        <div class="mb-3">
          <label class="form-label">ทะเบียนรถ</label>
          <input type="text" name="bi_licen" id="edit_bi_licen" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">สถานะ</label>
          <select name="status_id" id="edit_status_id" class="form-select">
            <option value="1">พร้อม</option>
            <option value="2">ซ่อม</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">บันทึก</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: เพิ่มรถ -->
<div class="modal fade" id="manageCarModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="addCarForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">เพิ่มรถใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="bi_licen" class="form-label">ทะเบียนรถ</label>
          <input type="text" class="form-control" id="bi_licen" name="bi_licen" required>
        </div>
        <div class="mb-3">
          <label for="br_id" class="form-label">สายเดินรถ</label>
          <select class="form-select" id="br_id" name="br_id" required>
            <option value="">-- กรุณาเลือกสายเดินรถ --</option>
            <?php
            $routes->data_seek(0); // reset pointer
            while ($r = $routes->fetch_assoc()):
            ?>
              <option value="<?= $r['br_id'] ?>"><?= htmlspecialchars($r['route_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="bt_id" class="form-label">ประเภทรถ</label>
          <select class="form-select" id="bt_id" name="bt_id" required>
            <option value="1">First Class</option>
            <option value="2">Gold Class</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="status_id" class="form-label">สถานะ</label>
          <select class="form-select" id="status_id" name="status_id" required>
            <option value="1">พร้อม</option>
            <option value="2">ซ่อม</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">บันทึก</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
  const body = document.body;

  // toggle class "collapsed" ใน sidebar
  sidebar.classList.toggle('collapsed');

  // optional: toggle class บน body เพื่อเปลี่ยน layout ทั้งหน้า
  body.classList.toggle('sidebar-collapsed');
}

document.querySelectorAll('.status-select').forEach(select => {
  select.addEventListener('change', e => {
    const busId = select.dataset.busid;
    const statusId = select.value;

    fetch('update_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `bi_id=${busId}&status_id=${statusId}`
    })
    .then(res => res.text())
    .then(text => {
      if (text.trim() === 'OK') {
        alert('อัปเดตสถานะสำเร็จ');
      } else {
        alert('เกิดข้อผิดพลาด: ' + text);
      }
    })
    .catch(err => alert('เกิดข้อผิดพลาด: ' + err));
  });
});

//สีstatus
  function updateStatusColor(select) {
    const status = parseInt(select.value);
    select.classList.remove('bg-success', 'text-white', 'bg-warning');

    if (status === 1) {
      select.classList.add('bg-success', 'text-white'); // พร้อม = เขียว
    } else if (status === 2) {
      select.classList.add('bg-warning', 'text-black'); // ซ่อม = เหลือง
    }
  }

  // อัปเดตสีเมื่อโหลดหน้า
  document.querySelectorAll('.status-select').forEach(select => {
    updateStatusColor(select);
    select.addEventListener('change', () => updateStatusColor(select));
  });

  //ลบรถ
document.querySelectorAll('.btn-delete').forEach(button => {
  button.addEventListener('click', () => {
    if (confirm('คุณต้องการลบรถคันนี้ใช่หรือไม่?')) {
      const row = button.closest('tr');
      const busId = row.querySelector('.status-select').dataset.busid;

      fetch('delete_bus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `bi_id=${busId}`
      })
      .then(res => res.text())
      .then(response => {
        if (response.trim() === 'OK') {
          alert('ลบข้อมูลรถสำเร็จ');
          row.remove(); // ลบแถวออกจากตาราง
        } else {
          alert('เกิดข้อผิดพลาด: ' + response);
        }
      })
      .catch(err => alert('เกิดข้อผิดพลาด: ' + err));
    }
  });
});

//จัดการรถ
let editModal = new bootstrap.Modal(document.getElementById('editModal'));

document.querySelectorAll('.btn-edit').forEach(button => {
  button.addEventListener('click', () => {
    const row = button.closest('tr');
    const bi_id = row.querySelector('.status-select').dataset.busid;
    const bi_licen = row.children[1].textContent.trim();
    const status_id = row.querySelector('.status-select').value;

    document.getElementById('edit_bi_id').value = bi_id;
    document.getElementById('edit_bi_licen').value = bi_licen;
    document.getElementById('edit_status_id').value = status_id;

    editModal.show();
  });
});
//ส่ไปอีดิทบัส
document.getElementById('editForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('edit_bus.php', {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() === 'OK') {
      alert('อัปเดตข้อมูลรถเรียบร้อยแล้ว');
      location.reload();
    } else {
      alert('เกิดข้อผิดพลาด: ' + response);
    }
  })
  .catch(err => alert('เกิดข้อผิดพลาด: ' + err));
});

//ส่งฟอมไป add_bus
document.getElementById('addCarForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const formData = new FormData(this);

  fetch('add_bus.php', {
    method: 'POST',
    body: new URLSearchParams(formData)
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() === 'OK') {
      alert('เพิ่มรถสำเร็จ');
      location.reload();
    } else {
      alert('เกิดข้อผิดพลาด: ' + response);
    }
  })
  .catch(err => alert('ข้อผิดพลาด: ' + err));
});

</script>

</body>
</html>
