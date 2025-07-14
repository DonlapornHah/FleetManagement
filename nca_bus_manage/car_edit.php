<?php
include('config.php');

$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $perPage;

$filter_line = $_GET['line'] ?? '';
$filter_date = $_GET['date'] ?? '';

$where = [];
if (!empty($filter_line)) {
    $where[] = "br.br_name LIKE '%" . mysqli_real_escape_string($conn, $filter_line) . "%'";
}
if (!empty($filter_date)) {
    $where[] = "DATE(bi.some_date_column) = '" . mysqli_real_escape_string($conn, $filter_date) . "'";
}
$whereSQL = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

$countSQL = "SELECT COUNT(*) as total FROM bus_info AS bi
    LEFT JOIN bus_routes AS br ON bi.br_id = br.br_id
    $whereSQL";

$countResult = mysqli_query($conn, $countSQL);
$totalRow = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRow / $perPage);

$sql = "SELECT *, lo_start.locat_name_th as lo_start, lo_end.locat_name_th AS lo_end
    FROM bus_info AS bi
    LEFT JOIN bus_routes AS br ON bi.br_id = br.br_id
    LEFT JOIN location AS lo_start ON br.br_start = lo_start.locat_id
    LEFT JOIN location AS lo_end ON br.br_end = lo_end.locat_id
    LEFT JOIN bus_type AS bt ON bi.bt_id = bt.bt_id
    LEFT JOIN bus_sub_class AS bsc ON bt.bsc_id = bsc.bsc_id
    LEFT JOIN bus_status AS bs ON bi.bs_id = bs.bs_id
    $whereSQL
    LIMIT $start, $perPage";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>จัดการข้อมูลรถโดยสาร</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      overflow-x: hidden;
      background-color: #f0f2f5;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .sidebar {
      width: 250px;
      min-height: 100vh;
      transition: width 0.3s ease;
      background-color: #484848ff;
      color: #cfd8dc;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      display: flex;
      flex-direction: column;
    }

    .logo {
      text-align: center;
      margin-bottom: 1rem;
      user-select: none;
    }

    .logo img {
      width: 200px;
      height: auto;
      transition: width 0.3s ease;
    }

    .sidebar.collapsed {
      width: 70px;
    }

    .sidebar.collapsed .logo img {
      width: 50px;
    }

    .sidebar.collapsed .logo br {
      display: none;
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
    }

    .sidebar .nav-link:hover, .sidebar .nav-link.active {
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

    table.table {
      width: 100%;
      background-color: #fff;
      border-radius: 0.5rem;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.07);
    }

    table.table thead {
      background-color: #27496d;
      color: white;
    }

    table.table tbody tr:hover {
      background-color: #e1e8f0;
    }

    table.table th, table.table td {
      padding: 0.75rem;
      text-align: center;
    }

    .btn-sm {
      padding: 0.3rem 0.7rem;
    }

    #sidebar .btn-sm {
      background-color: #27496d;
      border: none;
      color: white;
    }

    #sidebar .btn-sm:hover {
      background-color: #3b5a82;
    }
.navbar-nav .nav-link {
  position: relative;
  padding: 0.5rem 1rem;
  font-weight: 500;
  color: #495057;
  transition: all 0.3s ease;
  border-radius: 0.375rem;
}

.navbar-nav .nav-link:hover {
  background-color: #e7f1ff;
  color: #0d6efd;
}

.navbar-nav .nav-link.active {
  background-color: #d0e6ff;
  color: #0d6efd;
  font-weight: 600;
  box-shadow: inset 0 -2px 0 #0d6efd;
}

</style>

    
  </style>
</head>
<body class="sidebar-collapsed">

<div class="d-flex">
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar collapsed p-3">
    <button class="btn btn-sm mb-3 align-self-end" onclick="toggleSidebar()">
      <i class="bi bi-list"></i>
    </button>
    <div class="logo">
      <img src="https://img5.pic.in.th/file/secure-sv1/752440-01-removebg-preview.png" alt="Logo">
    </div>
    <a href="#" class="nav-link"><i class="bi bi-house-door"></i><span class="nav-text">หน้าหลัก</span></a>
    <a href="#" class="nav-link"><i class="bi bi-bus-front"></i><span class="nav-text">จัดการรถ</span></a>
    <a href="#" class="nav-link"><i class="bi bi-person-badge"></i><span class="nav-text">พนักงาน</span></a>
    <a href="#" class="nav-link"><i class="bi bi-clock-history"></i><span class="nav-text">ประวัติ</span></a>
    <a href="#" class="nav-link"><i class="bi bi-gear"></i><span class="nav-text">ตั้งค่า</span></a>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1">
    <!-- Topbar พร้อมเมนู -->
<nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4 px-4">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav" aria-controls="topbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="#">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">จัดคิวการเดินรถ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">วางแผนรถ</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">รายงานและประวัติ</a>
        </li>
      </ul>
      <span class="navbar-text text-muted" id="datetime"></span>
    </div>
  </div>
</nav>


   <div class="container-fluid py-4">
  <h1 class="mb-4">จัดการข้อมูลรถโดยสาร</h1>

  <!-- ค้นหา + ปุ่มเพิ่ม อยู่บรรทัดเดียวกัน -->
  <form method="GET" class="row g-3 align-items-end mb-4">
    <div class="col-md-4">
      <label for="line" class="form-label">สายรถ</label>
      <input type="text" class="form-control" id="line" name="line" value="<?= htmlspecialchars($filter_line) ?>" placeholder="ค้นหาสายรถ...">
    </div>
    <div class="col-md-auto">
      <label class="form-label d-block">&nbsp;</label>
      <button type="submit" class="btn btn-primary">ค้นหา</button>
    </div>
    <div class="col-md-auto ms-auto">
      <label class="form-label d-block">&nbsp;</label>
      <button class="btn btn-success" onclick="openAddModal()" type="button">+ เพิ่มรถใหม่</button>
    </div>
  </form>
<!-- Modal แก้ไข/เพิ่ม -->
<div class="modal fade" id="busModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="busForm" action="update_bus.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">ข้อมูลรถ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="bi_id" id="bi_id">
        
        <div class="col-md-6">
          <label>เลขทะเบียน</label>
          <input type="text" name="licenseplate" id="licenseplate" class="form-control" required>
        </div>
        
        <div class="col-md-6">
          <label>ชื่อรุ่น</label>
          <input type="text" name="model" id="model" class="form-control" required>
        </div>
        
        <div class="col-md-4">
          <label>จำนวนที่นั่ง</label>
          <input type="number" name="capacity" id="capacity" class="form-control" required>
        </div>
        
        <div class="col-md-4">
          <label>ประเภทรถ</label>
          <select name="bus_type" id="bus_type" class="form-select" required>
            <option value="1">ปรับอากาศ</option>
            <option value="2">รถธรรมดา</option>
          </select>
        </div>
        
        <div class="col-md-4">
          <label>ประเภทย่อย</label>
          <select name="bus_sub_type" id="bus_sub_type" class="form-select" required>
            <option value="1">VIP</option>
            <option value="2">ชั้น 1</option>
          </select>
        </div>
        
        <div class="col-md-6">
          <label>ต้นทาง</label>
          <input type="text" name="start_location" id="start_location" class="form-control" required>
        </div>
        
        <div class="col-md-6">
          <label>ปลายทาง</label>
          <input type="text" name="end_location" id="end_location" class="form-control" required>
        </div>
        
        <div class="col-md-6">
          <label>สถานะ</label>
          <select name="status" id="status" class="form-select" required>
            <option value="1">พร้อม</option>
            <option value="2">ซ่อม</option>
          </select>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">บันทึก</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </form>
  </div>
</div>



      <!-- ตาราง -->
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>#</th>
            <th>ทะเบียน</th>
            <th>รุ่น</th>
            <th>ที่นั่ง</th>
            <th>ประเภท</th>
            <th>ย่อย</th>
            <th>ต้นทาง</th>
            <th>ปลายทาง</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
        <?php $i = $start + 1; while($row = mysqli_fetch_assoc($result)): ?>
          <tr data-json='<?= json_encode($row) ?>'>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['bi_licenseplate']) ?></td>
            <td><?= htmlspecialchars($row['bi_model']) ?></td>
            <td><?= htmlspecialchars($row['bi_capacity']) ?></td>
            <td><?= htmlspecialchars($row['bt_name']) ?></td>
            <td><?= htmlspecialchars($row['bsc_name']) ?></td>
            <td><?= htmlspecialchars($row['lo_start']) ?></td>
            <td><?= htmlspecialchars($row['lo_end']) ?></td>
            <td><?= htmlspecialchars($row['bs_name']) ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" onclick="openEditModal(this)">แก้ไข</button>
              <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $row['bi_id'] ?>')">ลบ</button>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <nav>
        <ul class="pagination justify-content-center">
          <?php if($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>&line=<?= urlencode($filter_line) ?>">ก่อนหน้า</a></li>
          <?php endif; ?>
          <?php for($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&line=<?= urlencode($filter_line) ?>"><?= $p ?></a></li>
          <?php endfor; ?>
          <?php if($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>&line=<?= urlencode($filter_line) ?>">ถัดไป</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </div>
</div>

<!-- Scripts -->
<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('collapsed');
  document.body.classList.toggle('sidebar-collapsed');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<script>
function updateDateTime() {
  const now = new Date();
  const options = { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit', 
    minute: '2-digit', 
    second: '2-digit',
    hour12: false
  };
  const formatter = new Intl.DateTimeFormat('th-TH', options);
  document.getElementById('datetime').innerText = formatter.format(now);
}
setInterval(updateDateTime, 1000);
updateDateTime();

function openEditModal(btn) {
  const rowData = JSON.parse(btn.closest('tr').dataset.json);
  document.getElementById('formAction').value = 'update';
  document.getElementById('modalTitle').textContent = 'แก้ไขข้อมูลรถ';
  document.getElementById('bi_id').value = rowData.bi_id;
  document.getElementById('licenseplate').value = rowData.bi_licenseplate;
  document.getElementById('model').value = rowData.bi_model;
  document.getElementById('capacity').value = rowData.bi_capacity;
  document.getElementById('bus_type').value = rowData.bt_id;
  document.getElementById('bus_sub_type').value = rowData.bsc_id;
  document.getElementById('start_location').value = rowData.lo_start;
  document.getElementById('end_location').value = rowData.lo_end;
  document.getElementById('status').value = rowData.bs_id;

  new bootstrap.Modal(document.getElementById('busModal')).show();
}

function openAddModal() {
  document.getElementById('busForm').reset();
  document.getElementById('formAction').value = 'add';
  document.getElementById('modalTitle').textContent = 'เพิ่มรถใหม่';
  document.getElementById('bi_id').value = '';
  new bootstrap.Modal(document.getElementById('busModal')).show();
}

function confirmDelete(id) {
  if (confirm("คุณต้องการลบรถคันนี้ใช่หรือไม่?")) {
    window.location.href = 'update_bus.php?action=delete&id=' + id;
  }
}

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  sidebar.classList.toggle('collapsed');
  document.body.classList.toggle('sidebar-collapsed');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
