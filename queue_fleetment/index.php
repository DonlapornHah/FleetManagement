<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการแผนเดินรถ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="sidebar-collapsed">

<div class="d-flex">
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar collapsed p-3">
    <button class="btn btn-sm mb-3 align-self-end" onclick="toggleSidebar()">
      <i class="bi bi-list"></i>
    </button>
   
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
 <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex align-items-center">
  <li class="nav-item d-flex align-items-center me-3">
    <img src="https://img5.pic.in.th/file/secure-sv1/752440-01-removebg-preview.png" alt="Logo" style="width: 100px; height: auto; user-select: none;">
  </li>
  <li class="nav-item">
    <a class="nav-link" href="test.php">Overview</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="manage2.php">คิวการเดินรถ</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="car_edit.php">วางแผนรถ</a>
  </li>
  <!-- จัดการบุคลากร -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="personnelDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      จัดการบุคลากร
    </a>
    <ul class="dropdown-menu shadow rounded-3" aria-labelledby="personnelDropdown">
      <li><a class="dropdown-item" href="personnel.php"><i class="bi bi-person-vcard me-2"></i>พนักงานขับรถ</a></li>
      <li><a class="dropdown-item" href="assistants.php"><i class="bi bi-person-plus me-2"></i>พนักงานขับรถเสริม</a></li>
      <li><a class="dropdown-item" href="coach.php"><i class="bi bi-people-fill me-2"></i>พนักงานบริการ</a></li>
    </ul>
  </li>
  <li class="nav-item">
    <a class="nav-link" href=" report.php">รายงานและประวัติ</a>
  </li>

  </ul>
  <span class="navbar-text text-muted" id="datetime"></span>
</div>

  </div>
</nav>
<div class="container-fluid py-4">


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
  const sidebar = document.getElementById('sidebar');
  const body = document.body;

  // toggle class "collapsed" ใน sidebar
  sidebar.classList.toggle('collapsed');

  // optional: toggle class บน body เพื่อเปลี่ยน layout ทั้งหน้า
  body.classList.toggle('sidebar-collapsed');
}
</script>

</body>
</html>