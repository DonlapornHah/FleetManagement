<?php include 'config.php'; ?>
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
 รายงานและประวัติ
</h4>


  <!-- ฟอร์มค้นหา -->
  <div class="container-fluid py-4">
    <form class="row g-3 align-items-end" method="GET" action="report.php">
      <div class="col-md-3">
        <label for="startDate" class="form-label">วันที่เริ่มต้น</label>
        <input type="date" class="form-control" id="startDate" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
      </div>
      <div class="col-md-3">
        <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
        <input type="date" class="form-control" id="endDate" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
      </div>
      <div class="col-md-4">
        <label for="route" class="form-label">สายเดินรถ</label>
        <select class="form-select" id="route" name="route_id">
          <option value="">-- เลือกสายเดินรถ --</option>
          <?php
            $sql = "SELECT br_id, CONCAT(lo1.locat_name_th, ' - ', lo2.locat_name_th) AS route_name 
                    FROM bus_routes 
                    LEFT JOIN location lo1 ON bus_routes.br_start = lo1.locat_id
                    LEFT JOIN location lo2 ON bus_routes.br_end = lo2.locat_id
                    ORDER BY route_name ASC";
            $result = mysqli_query($conn, $sql);
            $selectedRoute = $_GET['route_id'] ?? '';
            while ($row = mysqli_fetch_assoc($result)) {
              $selected = ($row['br_id'] == $selectedRoute) ? "selected" : "";
              echo "<option value='{$row['br_id']}' $selected>{$row['route_name']}</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> ค้นหา</button>
      </div>
    </form>
  </div>

  <!-- ตารางผลลัพธ์ -->
  <div class="container-fluid">
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['start_date'], $_GET['end_date'])) {
        $start = $_GET['start_date'];
        $end = $_GET['end_date'];
        $route = $_GET['route_id'] ?? '';

        $where = "WHERE p.plan_date BETWEEN '$start' AND '$end'";
        if (!empty($route)) {
            $where .= " AND p.br_id = '$route'";
        }

        // แก้ชื่อ table และ field ให้ตรงกับของจริงในระบบคุณ
        $sql_report = "
          SELECT p.plan_date, 
                 CONCAT(lo1.locat_name_th, ' - ', lo2.locat_name_th) AS route_name,
                 e.em_name 
          FROM plan_data p
          LEFT JOIN bus_routes br ON p.br_id = br.br_id
          LEFT JOIN location lo1 ON br.br_start = lo1.locat_id
          LEFT JOIN location lo2 ON br.br_end = lo2.locat_id
          LEFT JOIN employee e ON p.em_id = e.em_id
          $where
          ORDER BY p.plan_date ASC
        ";
        $result = mysqli_query($conn, $sql_report);

        if (mysqli_num_rows($result) > 0) {
            echo '<div class="table-responsive mt-4">';
            echo '<table class="table table-bordered table-striped">';
            echo '<thead class="table-dark"><tr><th>วันที่</th><th>สายเดินรถ</th><th>ชื่อพนักงาน</th></tr></thead><tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>';
                echo '<td>' . date('d/m/Y', strtotime($row['plan_date'])) . '</td>';
                echo '<td>' . $row['route_name'] . '</td>';
                echo '<td>' . $row['em_name'] . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        } else {
            echo '<div class="alert alert-warning mt-4">ไม่พบข้อมูลตามเงื่อนไขที่ค้นหา</div>';
        }
    }
    ?>
  </div>
</div>

</body>
</html>
