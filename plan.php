<?php
include 'config.php';
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// MAIN SQL
$sql = "SELECT 
            bp.bp_id AS id,
            CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route,
            br.br_id AS br_id,
            bi.bi_licen AS licen,
            CONCAT(emM.em_name, ' ', emM.em_surname) AS emM,
            emM.em_queue AS emM_que,
            CONCAT(emX1.em_name, ' ', emX1.em_surname) AS emX1,
            emX1.em_queue AS emX1_que,
            CONCAT(emX2.em_name, ' ', emX2.em_surname) AS emX2,
            emX2.em_queue AS emX2_que,
            CONCAT(emC.em_name, ' ', emC.em_surname) AS emC,
            emC.em_queue AS emC_que
        FROM 
            bus_plan AS bp
        LEFT JOIN bus_routes AS br ON bp.br_id = br.br_id
        LEFT JOIN location AS loS ON br.br_start = loS.locat_id
        LEFT JOIN location AS loE ON br.br_end = loE.locat_id
        LEFT JOIN bus_group AS bg ON bp.bg_id = bg.gb_id
        LEFT JOIN bus_info AS bi ON bg.bi_id = bi.bi_id
        LEFT JOIN employee AS emM ON bg.main_dri = emM.em_id
        LEFT JOIN employee AS emX1 ON bg.ex_1 = emX1.em_id
        LEFT JOIN employee AS emX2 ON bg.ex_2 = emX2.em_id
        LEFT JOIN employee AS emC ON bg.coach = emC.em_id
        WHERE br.br_id = 1";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// ดึงพนักงานที่พัก
$result_main = mysqli_query($conn, "SELECT * FROM `employee` WHERE et_id = 1 AND em_queue < '3-1'");
$result_ex = mysqli_query($conn, "SELECT * FROM `employee` WHERE et_id = 2 AND em_queue < '2-1'");
$result_coach = mysqli_query($conn, "SELECT * FROM `employee` WHERE et_id = 3 AND em_queue < '2-1'");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แผนรถและพนักงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
          overflow-x: hidden;
          background-color: #f0f2f5;
          font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
          transition: margin-left 0.3s ease;
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
          padding: 1rem 0;
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
          min-height: 100vh;
        }

        .sidebar.collapsed ~ .content {
          margin-left: 70px;
        }
    </style>
</head>
<body class="sidebar-collapsed">

<div class="d-flex">
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar collapsed">
    <button class="btn btn-sm mb-3 align-self-end me-2" onclick="toggleSidebar()" aria-label="Toggle sidebar">
      <i class="bi bi-list" style="font-size: 1.5rem; color: #cfd8dc;"></i>
    </button>
    
    <a href="#" class="nav-link active"><i class="bi bi-house-door"></i><span class="nav-text">หน้าหลัก</span></a>
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
          </ul>
        </div>
      </div>
    </nav>

    <h2 class="mb-4">แผนรถสายที่ 1 และพนักงานประจำ</h2>
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>รหัสแผน</th>
                <th>สายเดินรถ</th>
                <th>รหัสสาย</th>
                <th>ทะเบียนรถ</th>
                <th>พขร หลัก</th>
                <th>พขร สำรอง 1</th>
                <th>พขร สำรอง 2</th>
                <th>โค้ช</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$i}</td>
                    <td>{$row['id']}</td>
                    <td>{$row['route']}</td>
                    <td>{$row['br_id']}</td>
                    <td>{$row['licen']}</td>
                    <td>{$row['emM']} ({$row['emM_que']})</td>
                    <td>{$row['emX1']} ({$row['emX1_que']})</td>
                    <td>{$row['emX2']} ({$row['emX2_que']})</td>
                    <td>{$row['emC']} ({$row['emC_que']})</td>
                </tr>";
                $i++;
            }
            ?>
        </tbody>
    </table>

    <hr class="my-4">

    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <h5 class="text-danger">🛑 พขร พัก</h5>
            <ul class="list-group">
                <?php while ($row = mysqli_fetch_assoc($result_main)) {
                    echo "<li class='list-group-item'>{$row['em_name']} {$row['em_surname']} ({$row['em_queue']})</li>";
                } ?>
            </ul>
        </div>
        <div class="col-md-4 mb-3">
            <h5 class="text-warning">🟡 พขร สำรอง พัก</h5>
            <ul class="list-group">
                <?php while ($row = mysqli_fetch_assoc($result_ex)) {
                    echo "<li class='list-group-item'>{$row['em_name']} {$row['em_surname']} ({$row['em_queue']})</li>";
                } ?>
            </ul>
        </div>
        <div class="col-md-4 mb-3">
            <h5 class="text-success">🟢 โค้ช พัก</h5>
            <ul class="list-group">
                <?php while ($row = mysqli_fetch_assoc($result_coach)) {
                    echo "<li class='list-group-item'>{$row['em_name']} {$row['em_surname']} ({$row['em_queue']})</li>";
                } ?>
            </ul>
        </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
}
</script>
</body>
</html>
