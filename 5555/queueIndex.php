<?php
include 'config.php';
if (!$conn) die("Connection failed: " . mysqli_connect_error());


// ดึงรายการสายเดินรถทั้งหมดจากฐานข้อมูลเพื่อแสดงใน dropdown
$routeQuery = "SELECT br.br_id, CONCAT(s.locat_name_th, ' - ', e.locat_name_th) AS route_name
               FROM bus_routes br
               LEFT JOIN location s ON br.br_start = s.locat_id
               LEFT JOIN location e ON br.br_end = e.locat_id
               ORDER BY route_name";
$routeResult = mysqli_query($conn, $routeQuery);

// รับค่าที่เลือกจาก GET
$selectedRoute = $_GET['route'] ?? '';
$selectedDate = $_GET['date'] ?? date('Y-m-d');


// ฟังก์ชันดึงชื่อเส้นทาง
function getRouteName($conn, $br_id) {
    $sql = "SELECT 
                s.locat_name_th AS start_name,
                e.locat_name_th AS end_name
            FROM bus_routes br
            LEFT JOIN location s ON br.br_start = s.locat_id
            LEFT JOIN location e ON br.br_end = e.locat_id
            WHERE br.br_id = $br_id
            LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($res)) {
        return $row['start_name'] . ' - ' . $row['end_name'];
    }
    return '-';
}


// ฟังก์ชันดึงทะเบียนรถ
function getBusLicense($conn, $bi_id) {
    $sql = "SELECT bi_licen FROM bus_info WHERE bi_id = $bi_id LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($res)) {
        return $row['bi_licen'];
    }
    return '-';
}

// ฟังก์ชันจัดกลุ่มพนักงาน
function groupEmployees($conn, $et_id, $queue_num, $queue_prefix) {
    $sql = "SELECT * FROM employee WHERE main_route = 1 AND et_id = $et_id ORDER BY em_queue";
    $result = mysqli_query($conn, $sql);

    $ready = [];
    $not_ready = [];
    $reserve = false;
    $num = 1;
    $a = 1;

    while($row = mysqli_fetch_assoc($result)) {
        $route_name = getRouteName($conn, $row['main_route']);
        $license = getBusLicense($conn, $row['main_car']);

        if($row['es_id'] != 1 || $reserve) {
            $not_ready[] = [
                'em_id' => $row['em_id'],
                'em_name' => $row['em_name'],
                'em_surname' => $row['em_surname'],
                'car' => $license,
                'route' => $route_name,
                'em_queue' => '1-'.$a
            ];
            $a++;
        } else {
            $ready[] = [
                'em_id' => $row['em_id'],
                'em_name' => $row['em_name'],
                'em_surname' => $row['em_surname'],
                'car' => $license,
                'route' => $route_name,
                'em_queue' => $queue_prefix.'-'.$num
            ];
            $num++;
            if($num > $queue_num) {
                $reserve = true;
            }
        }
    }

    // วนกลับหัวถ้ายังไม่ครบ
    $x = 0;
    while($queue_num > count($ready) && count($ready) > 0){
        $ready[] = [
            'em_id' => $ready[$x]['em_id'],
            'em_name' => $ready[$x]['em_name'],
            'em_surname' => $ready[$x]['em_surname'],
            'car' => $ready[$x]['car'],
            'route' => $ready[$x]['route'],
            'em_queue' => $queue_prefix.'-'.$num
        ];
        $num++;
        $x++;
    }

    return [$ready, $not_ready];
}

$queue_num = 5;

list($main, $main_not_ready) = groupEmployees($conn, 1, $queue_num, '3');
list($ex, $ex_not_ready) = groupEmployees($conn, 2, $queue_num, '2');
list($coach, $coach_not_ready) = groupEmployees($conn, 3, $queue_num, '2');

$plan = [];
for ($x = 0; $x < $queue_num; $x++) {
    $plan[] = [
        'em_id' => $main[$x]['em_id'],
        'main_queue' => $main[$x]['em_queue'],
        'car' => $main[$x]['car'],
        'route' => $main[$x]['route'],
        'ex_id' => $ex[$x]['em_id'],
        'ex_queue' => $ex[$x]['em_queue'],
        'coach_id' => $coach[$x]['em_id'],
        'coach_queue' => $coach[$x]['em_queue']
    ];
}

$note = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = $_POST['note'] ?? '';
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>จัดคิวการเดินรถ</title>
   <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
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
        .column-side {
    position: relative;
    transition: all 0.3s ease;
    min-width: 280px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    overflow: visible;
    padding-left: 1rem;   /* เพิ่มเติม */
    padding-right: 1rem;  /* เพิ่มเติม */
}

        .column-side.collapsed {
            width: 20px !important;
            min-width: 20px !important;
            padding: 0 !important;
        }
        .toggle-btn {
            position: absolute;
            top: 8px;
            z-index: 1050;
            background: #0d6efd;
            color: white;
            border: none;
            font-size: 0.75rem;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            line-height: 24px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 0 5px rgba(0,0,0,0.3);
        }
        /* ปุ่มฝั่งซ้าย อยู่มุมบนขวาของกล่องซ้าย */
        #leftColumn .toggle-btn {
            right: 8px;
        }
        /* ปุ่มฝั่งขวา อยู่มุมบนซ้ายของกล่องขวา */
        #rightColumn .toggle-btn {
            left: 8px;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        h1 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .card-rest {
            border-left: 4px solid #ffc107;
            background-color: #fff8e1;
            box-shadow: 0 2px 6px rgb(0 0 0 / 0.08);
        }
        .rest-item {
            padding: 0.3rem 0.5rem;
            border-bottom: 1px solid #ddd;
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
    <div class="card shadow-sm mb-4 border-0 rounded">
  <div class="card-header bg-secondary text-white text-center fw-bold">
    จัดคิวการเดินรถ
  </div>
  <div class="card-body py-4 px-4">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label for="route" class="form-label fw-semibold">สายเดินรถ</label>
        <select name="route" id="route" class="form-select shadow-sm rounded">
          <option value="">-- เลือกสายเดินรถ --</option>
          <?php while($r = mysqli_fetch_assoc($routeResult)): ?>
            <option value="<?= $r['br_id'] ?>" <?= ($r['br_id'] == $selectedRoute) ? 'selected' : '' ?>>
              <?= htmlspecialchars($r['route_name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label for="date" class="form-label fw-semibold">วันที่</label>
        <input type="date" name="date" id="date" class="form-control shadow-sm rounded" value="<?= htmlspecialchars($selectedDate) ?>">
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
          🔍 ค้นหา
        </button>
      </div>
    </form>
  </div>
</div>
    <br>
    <div class="d-flex flex-lg-row flex-column gap-4">
        <!-- ซ้าย: แผนจัดคิวใหม่ -->
        <section class="flex-fill column-side p-3" id="leftColumn">
            <button class="toggle-btn" onclick="toggleColumn('left')">⯇</button>
            <br>
            <h4 class="mb-4 text-center">จัดการแผนเดินรถ

  <img src="today-3-5813.gif" 
       alt="Today" 
       width="60" 
       height="30" 
       style="border-radius: 8px;"></h4>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">จัดการแผนการเดินรถ</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>เส้นทาง</th>
                                    <th>พนักงานหลัก</th>
                                    <th>คิว</th>
                                    <th>รถ</th>
                                    <th>Ex</th>
                                    <th>คิว</th>
                                    <th>Coach</th>
                                    <th>คิว</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($plan as $i => $row): ?>
                                    <tr>
                                        <td><?= $i+1 ?></td>
                                        <td><?= htmlspecialchars($row['route']) ?></td>
                                        <td><?= htmlspecialchars($main[$i]['em_name'] . ' ' . $main[$i]['em_surname']) ?></td>
                                        <td><span class="badge bg-primary"><?= $row['main_queue'] ?></span></td>
                                        <td><?= htmlspecialchars($row['car']) ?></td>
                                        <td><?= htmlspecialchars($ex[$i]['em_name'] . ' ' . $ex[$i]['em_surname']) ?></td>
                                        <td><span class="badge bg-success"><?= $row['ex_queue'] ?></span></td>
                                        <td><?= htmlspecialchars($coach[$i]['em_name'] . ' ' . $coach[$i]['em_surname']) ?></td>
                                        <td><span class="badge bg-warning text-dark"><?= $row['coach_queue'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(count($plan) === 0): ?>
                                    <tr><td colspan="9" class="text-center text-danger">ไม่มีข้อมูลแผนเดินรถ</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- พนักงานไม่พร้อม -->
            <div class="row g-4 mt-5">
                <div class="col-md-4">
                    <div class="card card-rest">
                        <div class="card-header">🛌 พขร สำรอง/ไม่พร้อม</div>
                        <div class="card-body">
                            <?php if(count($main_not_ready) > 0): ?>
                                <?php foreach($main_not_ready as $row): ?>
                                    <div class="mb-2">
                                        <?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($row['em_queue']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted">ไม่มีข้อมูล</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-rest">
                        <div class="card-header">🛌 Ex สำรอง/ไม่พร้อม</div>
                        <div class="card-body">
                            <?php if(count($ex_not_ready) > 0): ?>
                                <?php foreach($ex_not_ready as $row): ?>
                                    <div class="mb-2">
                                        <?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?>
                                        <span class="badge bg-success"><?= htmlspecialchars($row['em_queue']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted">ไม่มีข้อมูล</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-rest">
                        <div class="card-header">🛌 Coach สำรอง/ไม่พร้อม</div>
                        <div class="card-body">
                            <?php if(count($coach_not_ready) > 0): ?>
                                <?php foreach($coach_not_ready as $row): ?>
                                    <div class="mb-2">
                                        <?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?>
                                        <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['em_queue']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted">ไม่มีข้อมูล</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
   <!-- หมายเหตุ -->
<div class="mt-4">
  <label for="note" class="form-label fw-bold">📝 หมายเหตุ</label>
<textarea id="note" name="note" class="form-control" rows="3" placeholder="ระบุข้อมูลเพิ่มเติม..."><?= htmlspecialchars($note) ?></textarea>
</div>

<!-- ปุ่มส่ง -->
<div class="text-center mt-4">
  <form id="planForm" method="POST" action="your_submit_handler.php">
    <input type="hidden" name="plan" id="planInput" value='<?= json_encode($plan, JSON_UNESCAPED_UNICODE) ?>'>
    <input type="hidden" name="main_not_ready" id="mainNotReadyInput" value='<?= json_encode($main_not_ready, JSON_UNESCAPED_UNICODE) ?>'>
    <input type="hidden" name="ex_not_ready" id="exNotReadyInput" value='<?= json_encode($ex_not_ready, JSON_UNESCAPED_UNICODE) ?>'>
    <input type="hidden" name="coach_not_ready" id="coachNotReadyInput" value='<?= json_encode($coach_not_ready, JSON_UNESCAPED_UNICODE) ?>'>
    <!-- ซ่อน input note เพื่อส่งค่าจาก textarea ด้วย JS -->
    <input type="hidden" name="note" id="noteInput" value="">
    <button type="submit" class="btn btn-success shadow-sm w-100 py-2 fs-5">ส่งข้อมูลทั้งหมด</button>
  </form>
</div>
 </section>
       <!-- ขวา: แผนจากฐานข้อมูล -->
<section class="flex-fill column-side p-3" id="rightColumn">
    <button class="toggle-btn" onclick="toggleColumn('right')">⯈</button>
    <div class="container py-4 px-3">
        <h4 class="mb-4 text-center d-flex align-items-center justify-content-center gap-2">  แผนการเดินรถ </h4>


        <!-- ตารางหลัก -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">แผนการเดินรถ</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>รหัสแผน</th>
                                <th>เส้นทาง</th>
                                <th>ทะเบียนรถ</th>
                                <th>พขร หลัก</th>
                                <th>Ex 1</th>
                                <th>Ex 2</th>
                                <th>Coach</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT 
                                bp.bp_id AS id,
                                CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route,
                                br.br_id AS br_id,
                                bi.bi_licen AS licen,
                                emM.em_id AS emM_id,
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
                            WHERE emM.main_route = 1
                                AND bp.bp_id > (
                                    SELECT IFNULL(MIN(t.bp_id), 0)
                                    FROM (
                                        SELECT bp_id FROM bus_plan ORDER BY bp_id DESC LIMIT 1 OFFSET 5
                                    ) AS t
                                )
                            ORDER BY bp.bp_id ASC;";

                            $result = mysqli_query($conn, $sql);

                            $sql_main = "SELECT * FROM employee WHERE et_id = 1 AND em_queue < '3-1' AND main_route = 1";
                            $sql_ex = "SELECT * FROM employee WHERE et_id = 2 AND em_queue < '2-1' AND main_route = 1";
                            $sql_coach = "SELECT * FROM employee WHERE et_id = 3 AND em_queue < '2-1' AND main_route = 1";

                            $result_main = mysqli_query($conn, $sql_main);
                            $result_ex = mysqli_query($conn, $sql_ex);
                            $result_coach = mysqli_query($conn, $sql_coach);

                          $i = 1;
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$i}</td>
        <td>{$row['id']}</td>
        <td>" . htmlspecialchars($row['route']) . "</td>
        <td>{$row['licen']}</td>
        <td>{$row['emM']} <span class='badge bg-primary'>{$row['emM_que']}</span></td>
        <td>{$row['emX1']} <span class='badge bg-secondary'>{$row['emX1_que']}</span></td>
        <td>{$row['emX2']} <span class='badge bg-secondary'>{$row['emX2_que']}</span></td>
        <td>{$row['emC']} <span class='badge bg-warning text-dark'>{$row['emC_que']}</span></td>
    </tr>";
    $i++;
}
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- พนักงานสำรอง/ไม่พร้อม -->
        <div class="row g-4 mt-5">
            <div class="col-md-4">
                <div class="card card-rest">
                    <div class="card-header">🛌 พขร สำรอง/ไม่พร้อม</div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result_main) > 0): ?>
                            <?php while ($row_main = mysqli_fetch_assoc($result_main)): ?>
                                <div class="mb-2">
                                    <?= htmlspecialchars($row_main['em_name'] . ' ' . $row_main['em_surname']) ?>
                                    <span class="badge bg-primary"><?= htmlspecialchars($row_main['em_queue']) ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-muted">ไม่มีข้อมูล</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-rest">
                    <div class="card-header">🛌 Ex สำรอง/ไม่พร้อม</div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result_ex) > 0): ?>
                            <?php while ($row_ex = mysqli_fetch_assoc($result_ex)): ?>
                                <div class="mb-2">
                                    <?= htmlspecialchars($row_ex['em_name'] . ' ' . $row_ex['em_surname']) ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($row_ex['em_queue']) ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-muted">ไม่มีข้อมูล</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-rest">
                    <div class="card-header">🛌 Coach สำรอง/ไม่พร้อม</div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result_coach) > 0): ?>
                            <?php while ($row_coach = mysqli_fetch_assoc($result_coach)): ?>
                                <div class="mb-2">
                                    <?= htmlspecialchars($row_coach['em_name'] . ' ' . $row_coach['em_surname']) ?>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($row_coach['em_queue']) ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-muted">ไม่มีข้อมูล</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- หมายเหตุแสดงผล -->
<div class="card mt-4">
    <div class="card-header bg-info text-white fw-bold">📝 หมายเหตุจากการส่งข้อมูล</div>
    <div class="card-body">
        <?php if (!empty($note)): ?>
            <div class="p-2 border rounded" style="background-color: #f0f8ff;">
                <?= nl2br(htmlspecialchars($note)) ?>
            </div>
        <?php else: ?>
            <div class="text-muted fst-italic">ไม่มีหมายเหตุ</div>
        <?php endif; ?>
    </div>
</div>
</section>

<script>
function toggleColumn(side) {
    const col = document.getElementById(side === 'left' ? 'leftColumn' : 'rightColumn');
    const btn = col.querySelector('.toggle-btn');
    col.classList.toggle('collapsed');
    btn.innerHTML = col.classList.contains('collapsed')
        ? (side === 'left' ? '⯈' : '⯇')
        : (side === 'left' ? '⯇' : '⯈');
}

//reload ใหม่หลังจากส่งคิวแล้ว
document.getElementById('planForm').addEventListener('submit', function(e) {
  e.preventDefault(); // ป้องกัน form submit แบบปกติ (reload หน้า)

  const form = e.target;
  const formData = new FormData(form);

  fetch('manage_db.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    alert('ส่งคิวรถพรุ่งนี้สำเร็จแล้ว');
    // รีเฟรชหน้าใหม่หลังจากปิด alert
    window.location.reload();
  })
  .catch(error => {
    alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
    console.error('Error:', error);
  });
});

// เมื่อส่งฟอร์ม นำค่าหมายเหตุจาก textarea ไปใส่ hidden input ก่อนส่ง
   document.getElementById('planForm').addEventListener('submit', function(e){
    // นำค่าจาก textarea ใส่ hidden input ก่อนส่งจริง
    document.getElementById('noteInput').value = document.getElementById('note').value.trim();
  });
</script>
</body>
</html>
