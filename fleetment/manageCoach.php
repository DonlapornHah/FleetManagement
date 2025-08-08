<?php
include 'config.php';

// ดึงสถานะ (active/inactive/rest)
$statusOptions = [];
$status_sql = "SELECT status_id, status_name_th FROM status WHERE status_id IN (1,3,4)";
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

// รับค่า route filter
$route_filter = isset($_GET['route_filter']) ? (int)$_GET['route_filter'] : 0;

// SQL หลัก
$where = "WHERE e.et_id = 3";
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
        จัดการโค๊ช
      </h4>
<!-- Filter Route -->
      <form method="get" class="row g-3 mb-3">
        <div class="col-md-4">
          <label for="route_filter" class="form-label fw-semibold">เลือกสายเดินรถ:</label>
          <select name="route_filter" id="route_filter" class="form-select">
            <option value="0">-- แสดงทั้งหมด --</option>
            <?php if ($route_result && $route_result->num_rows > 0): ?>
              <?php while ($r = $route_result->fetch_assoc()): ?>
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

  <div class="card">
     <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
  <span>รายการโค๊ช</span>
      <button class="btn btn-light btn-sm" id="btnAddCoach" data-bs-toggle="modal" data-bs-target="#manageCoachModal"><i class="bi bi-plus-circle me-1"></i> เพิ่มโค๊ช</button>
    </div>
    <div class="card-body p-0">
      <table class="table table-bordered text-center mb-0">
        <thead class="table-secondary">
          <tr>
            <th style="font-weight: 300;">รหัส</th>
            <th style="font-weight: 300;">ชื่อ - นามสกุล</th>
            <th style="font-weight: 300;">ตำแหน่ง</th>
            <th style="font-weight: 300;">สายรถประจำ</th>
            <th style="font-weight: 300;">สถานะ</th>
            <th style="font-weight: 300;">แก้ไข</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr data-emid="<?= $row['em_id'] ?>" data-name="<?= htmlspecialchars($row['em_name']) ?>" data-surname="<?= htmlspecialchars($row['em_surname']) ?>" data-status="<?= $row['es_id'] ?>">
              <td><?= $row['em_id'] ?></td>
              <td><?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?></td>
              <td>โค๊ช</td>
              <td><?= htmlspecialchars($row['route_name'] ?? '-') ?></td>

              <td>
                <select class="form-select form-select-sm status-select" data-emid="<?= $row['em_id'] ?>">
                  <?php foreach ($statusOptions as $sid => $sname): ?>
                    <option value="<?= $sid ?>" <?= $sid == $row['es_id'] ? 'selected' : '' ?>><?= htmlspecialchars($sname) ?></option>
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
          <tr><td colspan="5" class="text-muted">ไม่พบข้อมูล</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal เพิ่ม/แก้ไข -->
<div class="modal fade" id="manageCoachModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="manageCoachForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="manageCoachModalLabel">เพิ่มโค๊ช</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="em_id" id="modal_em_id" value="0">
        <div class="mb-3">
          <label class="form-label">ชื่อ</label>
          <input type="text" class="form-control" name="em_name" id="coach_name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">นามสกุล</label>
          <input type="text" class="form-control" name="em_surname" id="coach_surname" required>
        </div>
        <div class="mb-3">
  <label class="form-label">สายรถประจำ</label>
  <select class="form-select" name="main_route" id="coach_route" required>
    <option value="">-- เลือกสายรถ --</option>
    <?php
    // วนลูปใหม่เพื่อให้ใช้ได้ทั้งใน Modal และตัวกรองด้านบน
    $route_result->data_seek(0); // reset pointer
    while ($r = $route_result->fetch_assoc()):
    ?>
      <option value="<?= $r['br_id'] ?>"><?= htmlspecialchars($r['route_name']) ?></option>
    <?php endwhile; ?>
  </select>
</div>

        <div class="mb-3">
          <label class="form-label">สถานะ</label>
          <select class="form-select" name="es_id" id="coach_status" required>
            <?php foreach ($statusOptions as $sid => $sname): ?>
              <option value="<?= $sid ?>"><?= htmlspecialchars($sname) ?></option>
            <?php endforeach; ?>
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
    function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const body = document.body;

  // toggle class "collapsed" ใน sidebar
  sidebar.classList.toggle('collapsed');

  // optional: toggle class บน body เพื่อเปลี่ยน layout ทั้งหน้า
  body.classList.toggle('sidebar-collapsed');
}
    function updateDateTime() {
  const now = new Date();
  const options = { timeZone: 'Asia/Bangkok', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute:'2-digit' };
  document.getElementById('datetime').textContent = now.toLocaleString('th-TH', options);
}
setInterval(updateDateTime, 1000);
updateDateTime();
document.addEventListener('DOMContentLoaded', () => {
  const modal = new bootstrap.Modal(document.getElementById('manageCoachModal'));

  document.querySelectorAll('.status-select').forEach(select => {
  setStatusColor(select); // ตั้งสีพื้นหลังตอนโหลด

  select.addEventListener('change', () => {
    const emId = select.dataset.emid;
    const statusId = select.value;

    fetch('update_coach_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `em_id=${emId}&es_id=${statusId}`
    })
    .then(res => res.text())
    .then(text => {
      if (text.trim() === 'OK') {
        setStatusColor(select);
        alert('✅ อัปเดตสถานะโค๊ชสำเร็จ');
      } else {
        alert('❌ เกิดข้อผิดพลาด: ' + text);
      }
    })
    .catch(err => {
      alert('⚠️ เกิดข้อผิดพลาด: ' + err);
    });
  });
});

// ฟังก์ชันเปลี่ยนสีพื้นหลัง select ตามสถานะ
function setStatusColor(select) {
  select.classList.remove('bg-success', 'bg-danger', 'bg-info', 'text-white');

  switch (select.value) {
    case '1': // Active
      select.classList.add('bg-success', 'text-white');
      break;
    case '3': // Inactive
      select.classList.add('bg-danger', 'text-white');
      break;
    case '4': // Rest
      select.classList.add('bg-info');
      break;
  }
}


  function setStatusColor(select) {
    select.classList.remove('bg-success', 'bg-danger', 'bg-info', 'text-white');
    if (select.value === '1') select.classList.add('bg-success', 'text-white');
    else if (select.value === '3') select.classList.add('bg-danger', 'text-white');
    else if (select.value === '4') select.classList.add('bg-info');
  }

  // เพิ่มโค๊ช
  document.getElementById('btnAddCoach').addEventListener('click', () => {
    document.getElementById('manageCoachForm').reset();
    document.getElementById('modal_em_id').value = '0';
    document.getElementById('manageCoachModalLabel').textContent = 'เพิ่มโค๊ช';
  });

  // แก้ไข
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', e => {
    const row = e.target.closest('tr');
    document.getElementById('modal_em_id').value = row.dataset.emid;
    document.getElementById('coach_name').value = row.dataset.name;
    document.getElementById('coach_surname').value = row.dataset.surname;
    document.getElementById('coach_status').value = row.dataset.status;
    document.getElementById('coach_route').value = row.dataset.route || '';
    document.getElementById('manageCoachModalLabel').textContent = 'แก้ไขโค๊ช';
    modal.show();
  });
});

  // ลบ
  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', e => {
      const row = e.target.closest('tr');
      if (confirm('คุณต้องการลบโค๊ชคนนี้หรือไม่?')) {
        fetch('delete_coach_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `em_id=${row.dataset.emid}`
        }).then(res => res.text()).then(txt => {
          if (txt.trim() === 'OK') location.reload();
          else alert('ลบไม่สำเร็จ: ' + txt);
        });
      }
    });
  });

  // Submit ฟอร์ม
  document.getElementById('manageCoachForm').addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    fetch('add_coach_status.php', {
      method: 'POST',
      body: formData
    }).then(res => res.text()).then(txt => {
      if (txt.trim() === 'OK') location.reload();
      else alert('บันทึกไม่สำเร็จ: ' + txt);
    });
  });
});
</script>

</body>
</html>
