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
    <a href="#" class="nav-link"><i class="bi bi-gear"></i><span class="nav-text">แผนการเดินรถ(การขาย)</span></a>
    <a href="#" class="nav-link"><i class="bi bi-bus-front"></i><span class="nav-text">จัดการรถ</span></a>
    <a href="#" class="nav-link"><i class="bi bi-person-badge"></i><span class="nav-text">พนักงาน</span></a>
    <a href="#" class="nav-link"><i class="bi bi-clock-history"></i><span class="nav-text">รายงานและประวัติ</span></a>
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
  <a href="index.php"> <!-- หรือเปลี่ยนเป็นหน้าหลักที่คุณต้องการ -->
    <img src="https://img5.pic.in.th/file/secure-sv1/752440-01-removebg-preview.png" alt="Logo"
         style="width: 100px; height: auto; user-select: none;" />
  </a>
</li>

  <li class="nav-item">
    <a class="nav-link" href="manageQueue.php">จัดคิวการเดินรถ</a>
  </li>
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
  <li class="nav-item">
    <a class="nav-link" href="manageCar.php">จัดการรถ</a>
  </li>
  <!-- จัดการบุคลากร -->
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
  <li class="nav-item">
    <a class="nav-link" href=" report.php">รายงานและประวัติ</a>
  </li>

  </ul>
  <span class="navbar-text text-muted" id="datetime"></span>
</div>

  </div>
</nav>
<div class="container-fluid py-4">


<!-- ปุ่มลอยด้านล่างขวา -->
<button class="btn btn-success position-fixed bottom-0 end-0 m-4 shadow-lg rounded-pill px-4 py-2 d-flex align-items-center gap-2"
        style="z-index: 1050; font-size: 1.1rem;"
        data-bs-toggle="modal" 
        data-bs-target="#helpModal">
  <i class="bi bi-question-circle-fill fs-4"></i>
  วิธีใช้งาน
</button>

<!-- Modal แสดงวิธีการใช้งาน -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-success text-white rounded-top-4">
        <h5 class="modal-title" id="helpModalLabel">
          <i class="bi bi-info-circle-fill me-2"></i>วิธีการใช้งานระบบจัดการแผนเดินรถ
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
      </div>
      <div class="modal-body">
        <h6>📌 เมนูด้านซ้าย</h6>
        <ul>
          <li><b>หน้าหลัก</b> – เข้าสู่ภาพรวมระบบ</li>
          <li><b>แผนการเดินรถ(การขาย)</b> – จัดการแผนตามคำขอจากฝ่ายขาย</li>
          <li><b>จัดการรถ</b> – เพิ่ม/แก้ไขข้อมูลรถโดยสาร</li>
          <li><b>พนักงาน</b> – จัดการข้อมูลพนักงานขับรถ, เสริม และโค๊ช</li>
          <li><b>รายงานและประวัติ</b> – ตรวจสอบข้อมูลย้อนหลัง</li>
        </ul>
        <h6>📌 การใช้งานทั่วไป</h6>
        <p>ใช้เมนูด้านบนเพื่อเข้าถึงหน้าต่างๆ สำหรับจัดการแผนการเดินรถและคิว เช่น:</p>
        <ul>
          <li><b>จัดคิวการเดินรถ</b> – สร้างและแก้ไขคิวสำหรับแต่ละเส้นทาง</li>
          <li><b>แผนเดินรถ</b> – ตรวจสอบ/แก้ไขแผนที่สร้างแล้ว</li>
          <li><b>จัดการคิวมาตรฐาน</b> – จัดการคิวพื้นฐานล่วงหน้า</li>
        </ul>
        <p class="text-muted small">หากต้องการความช่วยเหลือเพิ่มเติม กรุณาติดต่อผู้ดูแลระบบ</p>
      </div>
      <div class="modal-footer bg-light rounded-bottom-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
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