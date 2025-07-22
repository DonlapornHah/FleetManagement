<?php
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

$sql_main = "SELECT * FROM `employee` WHERE main_route = 1 AND et_id = 1 ORDER BY em_queue";
$sql_ex = "SELECT * FROM `employee` WHERE main_route = 1 AND et_id = 2 ORDER BY em_queue";
$sql_coach = "SELECT * FROM `employee` WHERE main_route = 1 AND et_id = 3 ORDER BY em_queue";

$result_main = mysqli_query($conn, $sql_main);
if (!$result_main) die("Query failed: " . mysqli_error($conn));
$result_ex = mysqli_query($conn, $sql_ex);
if (!$result_ex) die("Query failed: " . mysqli_error($conn));
$result_coach = mysqli_query($conn, $sql_coach);
if (!$result_coach) die("Query failed: " . mysqli_error($conn));

$main = [];
$ex = [];
$coach = [];
$notredy = [];
$exnotredy = [];
$coachnotredy = [];

$queue_num = 5;

$i = 1; $a = 1; $reserve = false;
while($row_main = mysqli_fetch_assoc($result_main)) {
    if($row_main['es_id'] != 1 || $reserve) {
        $notredy[] = [
            'em_name' => $row_main['em_name'],
            'em_surname' => $row_main['em_surname'],
            'em_queue' => '1-'.$a++
        ];
    } else {
        $main[] = [
            'em_name' => $row_main['em_name'],
            'em_surname' => $row_main['em_surname'],
            'em_queue' => '3-'.$i++
        ];
        if($i > $queue_num) $reserve = true;
    }
}

$ex = [];
$exnotredy = [];
$reserve = false;
$i = 1; $a = 1;
while($row_ex = mysqli_fetch_assoc($result_ex)) {
    if($row_ex['es_id'] != 1 || $reserve) {
        $exnotredy[] = [
            'em_name' => $row_ex['em_name'],
            'em_surname' => $row_ex['em_surname'],
            'em_queue' => '1-'.$a++
        ];
    } else {
        $ex[] = [
            'em_name' => $row_ex['em_name'],
            'em_surname' => $row_ex['em_surname'],
            'em_queue' => '2-'.$i++
        ];
        if($i > $queue_num) $reserve = true;
    }
}

$coach = [];
$coachnotredy = [];
$reserve = false;
$i = 1; $a = 1;
while($row_coach = mysqli_fetch_assoc($result_coach)) {
    if($row_coach['es_id'] != 1 || $reserve) {
        $coachnotredy[] = [
            'em_name' => $row_coach['em_name'],
            'em_surname' => $row_coach['em_surname'],
            'em_queue' => '1-'.$a++
        ];
    } else {
        $coach[] = [
            'em_name' => $row_coach['em_name'],
            'em_surname' => $row_coach['em_surname'],
            'em_queue' => '2-'.$i++
        ];
        if($i > $queue_num) $reserve = true;
    }
}

// เติมกลับหัวให้ main
$x = 0;
while($queue_num > count($main)){
    $main[] = [
        'em_name' => $main[$x]['em_name'],
        'em_surname' => $main[$x]['em_surname'],
        'em_queue' => '3-'.$i++
    ];
    $x++;
}

// เติมกลับหัวให้ ex
$x = 0;
$i = count($ex) > 0 ? (int)explode('-', $ex[count($ex)-1]['em_queue'])[1] + 1 : 1;
while($queue_num > count($ex)){
    $ex[] = [
        'em_name' => $ex[$x]['em_name'],
        'em_surname' => $ex[$x]['em_surname'],
        'em_queue' => '2-'.$i++
    ];
    $x++;
}

// เติมกลับหัวให้ coach
$x = 0;
$i = count($coach) > 0 ? (int)explode('-', $coach[count($coach)-1]['em_queue'])[1] + 1 : 1;
while($queue_num > count($coach)){
    $coach[] = [
        'em_name' => $coach[$x]['em_name'],
        'em_surname' => $coach[$x]['em_surname'],
        'em_queue' => '2-'.$i++
    ];
    $x++;
}

function renderTable($title, $data, $color = 'primary') {
    echo "<div class='mb-5'>";
    echo "<h3 class='text-$color mb-3'>$title</h3>";

    if (count($data) > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-striped align-middle'>";
        echo "<thead class='table-$color'>";
        echo "<tr><th>ลำดับคิว</th><th>ชื่อ</th><th>นามสกุล</th></tr>";
        echo "</thead><tbody>";
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>{$row['em_queue']}</td>";
            echo "<td>{$row['em_name']}</td>";
            echo "<td>{$row['em_surname']}</td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
        echo "<div class='text-muted'>จำนวนทั้งหมด: ".count($data)." คน</div>";
    } else {
        echo "<i class='text-muted fst-italic'>ไม่มีข้อมูล</i>";
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>แสดงคิวพนักงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
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
    <div class="container py-5">
        <h1 class="mb-4 text-center text-primary">📋 แสดงคิวพนักงานสาย 1</h1>

        <?php
        renderTable("รายชื่อหลัก", $main, "success");
        renderTable("รายชื่อสำรอง/ไม่พร้อม", $notredy, "secondary");
        renderTable("รายชื่อ Ex", $ex, "warning");
        renderTable("รายชื่อ Ex สำรอง/ไม่พร้อม", $exnotredy, "secondary");
        renderTable("รายชื่อ Coach", $coach, "info");
        renderTable("รายชื่อ Coach สำรอง/ไม่พร้อม", $coachnotredy, "secondary");
        ?>
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
