<?php
include 'config.php';
header('Content-Type: text/html; charset=UTF-8');

// รับค่า route_id จาก GET เพื่อกรอง
$route_filter = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;

// สร้างเงื่อนไข WHERE ถ้ามีการกรองสายเดินรถ
$whereClause = "WHERE e.et_id = 1"; // เริ่มต้นเอาเฉพาะพนักงานขับรถ
if ($route_filter > 0) {
    // กรองโดยใช้ bi.br_id
    $whereClause .= " AND b.br_id = $route_filter";
}

// ดึงสายเดินรถทั้งหมดสำหรับ dropdown
$route_sql = "
SELECT br.br_id, CONCAT(ls.locat_name_th, ' - ', le.locat_name_th) AS route_name
FROM bus_routes br
LEFT JOIN location ls ON br.br_start = ls.locat_id
LEFT JOIN location le ON br.br_end = le.locat_id
ORDER BY route_name
";
$route_result = $conn->query($route_sql);

// ดึงสถานะที่ต้องการเฉพาะ status_id = 1, 3
$statusOptions = [];
$status_sql = "SELECT status_id, status_name_th FROM status WHERE status_id IN (1,3)";
$status_result = $conn->query($status_sql);
if ($status_result && $status_result->num_rows > 0) {
    while ($row = $status_result->fetch_assoc()) {
        $statusOptions[$row['status_id']] = $row['status_name_th'];
    }
}

// ดึงข้อมูลพนักงานขับรถพร้อมกรองสายเดินรถ
$sql = "
SELECT 
  e.em_id,
  CONCAT(e.em_name, ' ', e.em_surname) AS fullname,
  b.bi_licen,
  CONCAT(ls.locat_name_th, ' - ', le.locat_name_th) AS route_name,
  'พนักงานขับรถ' AS position,
  e.es_id
FROM employee e
LEFT JOIN bus_info b ON e.main_car = b.bi_id
LEFT JOIN bus_routes br ON b.br_id = br.br_id
LEFT JOIN location ls ON br.br_start = ls.locat_id
LEFT JOIN location le ON br.br_end = le.locat_id
$whereClause
ORDER BY e.em_id
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>จัดการพนักงานขับรถ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="styles.css">
    <style>
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
              <a href="index.php">
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
        จัดการพนักงานขับรถ
      </h4>

      <!-- ฟอร์มกรองสายเดินรถ -->
      <form method="get" class="row g-3 mb-4">
        <div class="col-md-4">
          <label for="route_id" class="form-label fw-semibold">เลือกสายเดินรถ :</label>
          <select name="route_id" id="route_id" class="form-select">
            <option value="0" <?= $route_filter == 0 ? 'selected' : '' ?>>-- แสดงทั้งหมด --</option>
            <?php if ($route_result && $route_result->num_rows > 0): ?>
              <?php while ($r = $route_result->fetch_assoc()): ?>
                <option value="<?= $r['br_id'] ?>" <?= $route_filter == $r['br_id'] ? 'selected' : '' ?>>
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
  <!-- Driver Table!-->
      <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
  <span>รายการพนักงานขับรถ</span>
  <button class="btn btn-sm btn-light text-dark" data-bs-toggle="modal" data-bs-target="#addDriverModal">
    <i class="bi bi-plus-circle me-1"></i> เพิ่มพนักงานขับรถ
  </button>
</div>

        <div class="card-body table-responsive">
          <table class="table table-bordered text-center align-middle">
            <thead class="table-secondary">
              <tr>
                <th>รหัส</th>
                <th>ชื่อ-นามสกุล</th>
                <th>ทะเบียนรถประจำ</th>
                <th>สายเดินรถ</th>
                <th>ตำแหน่ง</th>
                <th>สถานะ</th>
                <th>แก้ไข</th>

              </tr>
            </thead>
            <tbody>
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['em_id'] ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['bi_licen'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['route_name'] ?? '-') ?></td>
                    <td><?= $row['position'] ?></td>
                    <td>
                      <select class="form-select status-select" data-emid="<?= $row['em_id'] ?>">
                        <?php foreach ($statusOptions as $sid => $sname): ?>
                          <option value="<?= $sid ?>" <?= $sid == $row['es_id'] ? 'selected' : '' ?>>
                            <?= $sname ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                    <td>
  <button type="button" class="btn btn-sm btn-warning btn-edit" 
        data-emid="<?= $row['em_id'] ?>" 
        data-name="<?= htmlspecialchars($row['fullname'], ENT_QUOTES) ?>" 
        data-licen="<?= htmlspecialchars($row['bi_licen'] ?? '', ENT_QUOTES) ?>"
        data-route="<?= htmlspecialchars($row['route_name'] ?? '', ENT_QUOTES) ?>"
        data-status="<?= $row['es_id'] ?>">
  <i class="fa fa-pen-to-square me-1"></i> จัดการ
</button>
  <button type="button" class="btn btn-sm btn-danger btn-delete ms-1" 
          data-emid="<?= $row['em_id'] ?>" 
          onclick="deleteDriver(<?= $row['em_id'] ?>)">
    <i class="bi bi-trash"></i> ลบ
  </button>
</td>

                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-muted">ไม่พบข้อมูลพนักงานขับรถ</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal แก้ไขข้อมูลพนักงานขับรถ -->
<div class="modal fade" id="editDriverModal" tabindex="-1" aria-labelledby="editDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editDriverForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editDriverModalLabel">แก้ไขข้อมูลพนักงานขับรถ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="em_id" id="em_id" />

          <div class="mb-3">
            <label for="driver_name" class="form-label">ชื่อ-นามสกุล</label>
            <input type="text" class="form-control" id="driver_name" name="driver_name"  />
          </div>

          <div class="mb-3">
            <label for="license" class="form-label">ทะเบียนรถประจำ</label>
            <input type="text" class="form-control" id="license" name="license"  />
          </div>
          

          <div class="mb-3">
            <label for="status" class="form-label">สถานะ</label>
            <select id="status" name="status_id" class="form-select">
              <?php
                foreach ($statusOptions as $sid => $sname) {
                    echo "<option value=\"$sid\">$sname</option>";
                }
              ?>
            </select>
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
<!-- Modal เพิ่มพนักงานขับรถ -->
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addDriverForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addDriverModalLabel">เพิ่มพนักงานขับรถ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label for="new_driver_name" class="form-label">ชื่อ-นามสกุล</label>
            <input type="text" class="form-control" id="new_driver_name" name="fullname" required>
          </div>

          <div class="mb-3">
            <label for="new_license" class="form-label">ทะเบียนรถ</label>
            <input type="text" class="form-control" id="new_license" name="license" required>
          </div>

          <div class="mb-3">
            <label for="new_route_id" class="form-label">สายเดินรถ</label>
            <select class="form-select" id="new_route_id" name="route_id" required>
              <option value="">-- เลือกสายเดินรถ --</option>
              <?php
              // ใช้ $route_result เดิม (ถูกดึงไว้ก่อนหน้า)
              mysqli_data_seek($route_result, 0); // รีเซ็ต pointer
              while ($r = $route_result->fetch_assoc()):
              ?>
                <option value="<?= $r['br_id'] ?>"><?= htmlspecialchars($r['route_name']) ?></option>
              <?php endwhile; ?>
            </select>
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


<script>
    function updateDateTime() {
  const now = new Date();
  const options = { timeZone: 'Asia/Bangkok', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute:'2-digit' };
  document.getElementById('datetime').textContent = now.toLocaleString('th-TH', options);
}
setInterval(updateDateTime, 1000);
updateDateTime();
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('collapsed');
}
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const body = document.body;

  // toggle class "collapsed" ใน sidebar
  sidebar.classList.toggle('collapsed');

  // optional: toggle class บน body เพื่อเปลี่ยน layout ทั้งหน้า
  body.classList.toggle('sidebar-collapsed');
}
// ฟังก์ชันอัปเดตสถานะด้วย AJAX
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', e => {
      const emId = select.dataset.emid;
      const statusId = select.value;

      fetch('update_driver_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `em_id=${emId}&status_id=${statusId}`
      })
      .then(res => res.text())
      .then(text => {
        if (text.trim() === 'OK') {
          alert(' ✅ อัปเดตสถานะพนักงานขับรถสำเร็จ');
        } else {
          alert('เกิดข้อผิดพลาด: ' + text);
        }
      })
      .catch(err => alert('เกิดข้อผิดพลาด: ' + err));
    });
  });
});

document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', e => {
    const emId = btn.getAttribute('data-emid');
    const name = btn.getAttribute('data-name');
    const license = btn.getAttribute('data-licen');
    const status = btn.getAttribute('data-status');

    document.getElementById('em_id').value = emId;
    document.getElementById('driver_name').value = name;
    document.getElementById('license').value = license;
    document.getElementById('status').value = status;

    const editModal = new bootstrap.Modal(document.getElementById('editDriverModal'));
    editModal.show();
  });
});

// ส่งข้อมูลแก้ไขผ่าน AJAX
document.getElementById('editDriverForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('update_driver.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(text => {
    if (text.trim() === 'OK') {
      alert('บันทึกสำเร็จ');
      location.reload();
    } else {
      alert('บันทึกไม่สำเร็จ: ' + text);
    }
  })
  .catch(err => {
    alert('เกิดข้อผิดพลาด: ' + err);
  });
});
//สีstatus
  function updateStatusColor(select) {
    const status = parseInt(select.value);
    select.classList.remove('bg-success', 'text-white', 'bg-danger');

    if (status === 1) {
      select.classList.add('bg-success', 'text-white'); // พร้อม = เขียว
    } else if (status === 3) {
      select.classList.add('bg-danger', 'text-white'); // ลาป่วย = แดง
    }
  }

  // อัปเดตสีเมื่อโหลดหน้า
  document.querySelectorAll('.status-select').forEach(select => {
    updateStatusColor(select);
    select.addEventListener('change', () => updateStatusColor(select));
  });

  document.getElementById('addDriverForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('add_driver.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.text())
  .then(data => {
    if (data === 'OK') {
      alert('เพิ่มพนักงานสำเร็จ');
      location.reload();
    } else {
      alert('เกิดข้อผิดพลาด: ' + data);
    }
  })
  .catch(err => alert('Error: ' + err));
});

function deleteDriver(em_id) {
  if (confirm("คุณต้องการลบพนักงานคนนี้ใช่หรือไม่?")) {
    fetch('delete_driver.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'em_id=' + encodeURIComponent(em_id)
    })
    .then(res => res.text())
    .then(data => {
      if (data === 'OK') {
        alert('ลบข้อมูลเรียบร้อยแล้ว');
        location.reload();
      } else {
        alert('เกิดข้อผิดพลาด: ' + data);
      }
    })
    .catch(err => alert('Error: ' + err));
  }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
