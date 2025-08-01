<?php
include 'config.php';
include 'function/groupEmployee.php';

// =========ของปุ่ม Request Reserve===============
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$route = [2,3,4];
    $sql_request = "SELECT * FROM `queue_request` WHERE br_id IN (" . implode(',', $route) . ") ORDER BY br_id";
    $result_request = mysqli_query($conn, $sql_request);

    $request = [];
    while ($row = mysqli_fetch_assoc($result_request)) {
        $qr_request = json_decode($row['qr_request'], true);
        $request[$row['br_id']]['request'] = $qr_request['request'];
        $request[$row['br_id']]['reserve'] = $qr_request['reserve'];
    }
// ======= แผนใหม่ (ฝั่งซ้าย) =======
$route = [2, 3, 4]; // หรือใช้ array_keys($plan) ก็ได้ถ้า plan มีข้อมูลทุกสาย
$routeNames = [];

$sql = "SELECT 
            br.br_id, 
            CONCAT(loc_start.locat_name_th, ' - ', loc_end.locat_name_th) AS route_name
        FROM bus_route br
        JOIN location loc_start ON br.loc_start_id = loc_start.locat_id
        JOIN location loc_end ON br.loc_end_id = loc_end.locat_id
        WHERE br.br_id IN (" . implode(',', array_map('intval', $route)) . ")";

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $routeNames[$row['br_id']] = $row['route_name'];
}

$normal_code = [3, 2, 1];

list($re, $main, $main_re, $break) = getMainDriver($conn, $route);
list($new_plan, $main, $x) = groupMainDriver($re, $main, $main_re, $normal_code);

foreach ($re as $key => $value) {
    $queue_num[$key] = count($value);
}

list($new_ex, $exnotredy) = getEmployee($conn, $route, $queue_num, $x, 2);
list($new_coach, $coachnotredy) = getEmployee($conn, $route, $queue_num, $x, 3);

$new_main = [];
foreach ($main as $value) {
    $r = $value['em_queue'][0];
    $new_main[$r][] = $value;
}
$main_break = groupByRouteWithNewQueue($new_main, []);
$main_break = groupByRouteWithNewQueue($break, $main_break);

$plan = [];
foreach ($queue_num as $key => $v) {
    $num = 1;
    while ($num <= $v) {
        $plan[$key][] = [
            'em_id' => $new_plan[$key][$num]['em_id'],
            'em_name' => $new_plan[$key][$num]['em_name'],
            'em_surname' => $new_plan[$key][$num]['em_surname'],
            'car' => $new_plan[$key][$num]['car'],
            'licen' => $new_plan[$key][$num]['licen'],
            'em_queue' => $new_plan[$key][$num]['em_queue'],
            'new_queue' => $new_plan[$key][$num]['new_queue'],
            'ex_id' => $new_ex[$key][$num - 1]['em_id'],
            'ex_name' => $new_ex[$key][$num - 1]['em_name'],
            'ex_surname' => $new_ex[$key][$num - 1]['em_surname'],
            'ex_queue' => $new_ex[$key][$num - 1]['em_queue'],
            'ex_new_queue' => $new_ex[$key][$num - 1]['new_queue'],
            'coach_id' => $new_coach[$key][$num - 1]['em_id'],
            'coach_name' => $new_coach[$key][$num - 1]['em_name'],
            'coach_surname' => $new_coach[$key][$num - 1]['em_surname'],
            'coach_new_queue' => $new_coach[$key][$num - 1]['new_queue'],
        ];
        $num++;
    }
}

// ======= แผนเมื่อวาน (ฝั่งขวา) =======
$route_in = '(' . implode(',', $route) . ')';

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
WHERE 
    emM.main_route IN $route_in
    AND bp.bp_id > (
        SELECT IFNULL(MIN(t.bp_id), 0)
        FROM (
            SELECT bp.bp_id
            FROM bus_plan bp
            LEFT JOIN bus_group bg ON bp.bg_id = bg.gb_id
            LEFT JOIN employee emM ON bg.main_dri = emM.em_id
            WHERE emM.main_route IN $route_in
            ORDER BY bp.bp_id DESC
            LIMIT 1 OFFSET 9
        ) AS t
    )
ORDER BY bp.bp_id ASC";

$result = mysqli_query($conn, $sql) or die("Query failed: " . mysqli_error($conn));

$route_groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    $route_groups[$row['route']][] = $row;
}

// ======= พนักงานพักฝั่งขวา (เมื่อวาน) =======
$sql_main = "SELECT * FROM employee
             WHERE main_route > 1 AND et_id = 1 AND (
                (em_queue LIKE '2-%' AND em_queue < '2-3-1') OR
                (em_queue LIKE '3-%' AND em_queue < '3-3-1') OR
                (em_queue LIKE '4-%' AND em_queue < '4-3-1')
             )
             ORDER BY em_queue";

$sql_ex = "SELECT * FROM employee
           WHERE main_route > 1 AND et_id = 2 AND (
                (em_queue LIKE '2-%' AND em_queue < '2-2-1') OR
                (em_queue LIKE '3-%' AND em_queue < '3-2-1') OR
                (em_queue LIKE '4-%' AND em_queue < '4-2-1')
           )";

$sql_coach = "SELECT * FROM employee
              WHERE main_route > 1 AND et_id = 3 AND (
                (em_queue LIKE '2-%' AND em_queue < '2-2-1') OR
                (em_queue LIKE '3-%' AND em_queue < '3-2-1') OR
                (em_queue LIKE '4-%' AND em_queue < '4-2-1')
              )";

$result_main = mysqli_query($conn, $sql_main);
$result_ex = mysqli_query($conn, $sql_ex);
$result_coach = mysqli_query($conn, $sql_coach);

$main_rest = [];
while ($row_main = mysqli_fetch_assoc($result_main)) $main_rest[] = $row_main;

$ex_rest = [];
while ($row_ex = mysqli_fetch_assoc($result_ex)) $ex_rest[] = $row_ex;

$coach_rest = [];
while ($row_coach = mysqli_fetch_assoc($result_coach)) $coach_rest[] = $row_coach;

$remarksSummary = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarksSummary = $_POST['remarks_summary'] ?? '';
}


?>

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
        <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
  <i class="fa-solid fa-magnifying-glass me-2"></i> ค้นหา
</button>

      </div>
    </form>
  </div>
</div>
  <div id="pageContainer" >

    <!-- LEFT wrapper -->
    <div class="left-column-wrapper flex-grow-1 d-flex flex-column" style="transition: width 0.3s ease; min-width: 0;">
      <div id="leftColumn" class="card shadow-sm h-100 d-flex flex-column">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <span>จัดการแผนเดินรถ</span>
          <button class="btn btn-sm btn-light toggle-left" onclick="toggleLeftColumn()" title="ย่อ/ขยาย" id="toggleLeftBtn">
            <i class="bi bi-arrow-left"></i>
          </button>
        </div>
        <div class="card-body flex-grow-1 overflow-auto">
          

          <!-- Tab nav -->
          <ul class="nav nav-tabs mb-3" id="planTab" role="tablist">
            <?php $first = true; foreach ($plan as $route => $rows): ?>
              <li class="nav-item" role="presentation">
                <button class="nav-link <?= $first ? 'active' : '' ?>" id="tab-<?= $route ?>" data-bs-toggle="tab" data-bs-target="#content-<?= $route ?>" type="button" role="tab" aria-controls="content-<?= $route ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                  <?= htmlspecialchars($routeNames[$route] ?? "สาย $route") ?>

                </button>
              </li>
            <?php $first = false; endforeach; ?>
          </ul>

          <!-- Tab content -->
          <div class="tab-content" id="routeRightTabContent">
<?php
$first = true;
foreach ($route_groups as $routeName => $rows):
?>
  <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="tab-right-<?= md5($routeName) ?>" role="tabpanel">
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>รหัสแผน</th>
            <th>ทะเบียนรถ</th>
            <th>พขร</th>
            <th>พขร พ่วง 1</th>
            <th>พขร พ่วง 2</th>
            <th>โค้ช</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $row): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($row['id']) ?></td>
              <td><?= htmlspecialchars($row['licen']) ?></td>
              <td><?= htmlspecialchars($row['emM']) ?><br><small><?= htmlspecialchars($row['emM_que']) ?></small></td>
              <td><?= htmlspecialchars($row['emX1']) ?><br><small><?= htmlspecialchars($row['emX1_que']) ?></small></td>
              <td><?= htmlspecialchars($row['emX2']) ?><br><small><?= htmlspecialchars($row['emX2_que']) ?></small></td>
              <td><?= htmlspecialchars($row['emC']) ?><br><small><?= htmlspecialchars($row['emC_que']) ?></small></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php
$first = false;
endforeach;
?>
</div>

          <hr>

          <!-- ตารางพนักงานพัก (main_break, exnotredy, coachnotredy) -->
          <div class="row g-3">
            <!-- พขร พัก -->
            <div class="col-md-4 col-12">
              <div class="card card-rest ">
                <div class="card-header text-dark">🛌 พขร พัก (แผนใหม่)</div>
                <div class="card-body p-2 scrollable-table" style="max-height: 250px; overflow-y: auto;">
                  <?php if (!empty($main_break)): ?>
                    <?php foreach ($main_break as $v): ?>
                      <?php foreach ($v as $idx => $row): ?>
                        <?php if (!isset($row['em_id'])) continue; ?>
                        <div class="rest-item">
                          <strong><?= $idx + 1 ?>.</strong> <?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?><br>
                          <small class="text-secondary"><?= htmlspecialchars($row['em_queue']) ?> → <?= htmlspecialchars($row['new_queue']) ?></small>
                        </div>
                      <?php endforeach; ?>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="text-center"><i>ไม่มีข้อมูล</i></p>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <!-- พขร พ่วง พัก -->
            <div class="col-md-4 col-12">
              <div class="card card-rest">
                <div class="card-header  text-dark">🛌 พขร พ่วง พัก <br>(แผนใหม่)</div>
                <div class="card-body p-2 scrollable-table" style="max-height: 250px; overflow-y: auto;">
                  <?php foreach ($exnotredy as $route => $list): ?>
                    <div class="rest-route mb-1">
                      <span class="badge bg-secondary">สาย <?= htmlspecialchars($route) ?></span>
                    </div>
                    <?php $idx = 1; foreach ($list as $row): ?>
                      <div class="rest-item">
                        <strong><?= $idx++ ?>.</strong> <?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?><br>
                        <small class="text-secondary"><?= htmlspecialchars($row['em_queue']) ?> → <?= htmlspecialchars($row['new_queue']) ?></small>
                      </div>
                    <?php endforeach; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- โค้ช พัก -->
            <div class="col-md-4 col-12">
              <div class="card card-rest">
                <div class="card-header  text-dark">🛌 โค้ช พัก (แผนใหม่)</div>
                <div class="card-body p-2 scrollable-table" style="max-height: 250px; overflow-y: auto;">
                  <?php foreach ($coachnotredy as $route => $list): ?>
                    <div class="rest-route mb-1">
                      <span class="badge bg-secondary">สาย <?= htmlspecialchars($route) ?></span>
                    </div>
                    <?php $idx = 1; foreach ($list as $row): ?>
                      <div class="rest-item">
                        <strong><?= $idx++ ?>.</strong> <?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?><br>
                        <small class="text-secondary"><?= htmlspecialchars($row['em_queue']) ?> → <?= htmlspecialchars($row['new_queue']) ?></small>
                      </div>
                    <?php endforeach; ?>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
                       <!-- เพิ่มช่องหมายเหตุ -->
   <div class="mb-3">
  <label for="note" class="form-label fw-semibold text-primary">หมายเหตุ (ถ้ามี)</label>
  <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="เช่น รถเสีย 1 คัน, พนักงานลา 2 คน..."></textarea>
  <div class="form-text text-muted fst-italic">หากไม่มีหมายเหตุสามารถเว้นว่างไว้ได้</div>
</div><input type="hidden" name="remarks_summary" id="remarks_summary">
        </div>
         <!-- card-body -->
        <form method="post" action="manage_db.php" id="plan-form">
        <input type="hidden" name="plan_data" id="plan_data">
        <input type="hidden" name="main_break_data" id="main_break_data">
        <input type="hidden" name="exnotredy_data" id="exnotredy_data">
        <input type="hidden" name="coachnotredy_data" id="coachnotredy_data">
        <div class="text-center mb-4">
            <button type="submit" class="btn btn-success px-5 w-100">บันทึกแผน</button>
        </div>
    </form>
      </div> <!-- card -->
    </div> <!-- left-column-wrapper -->

    <!-- RIGHT wrapper -->
    <div class="right-column-wrapper flex-grow-1 d-flex flex-column" style="transition: width 0.3s ease; min-width: 0;">
      <div id="rightColumn" class="card shadow-sm h-100 d-flex flex-column">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <button class="btn btn-sm btn-light toggle-right" onclick="toggleRightColumn()" title="ย่อ/ขยาย" id="toggleRightBtn">
            <i class="bi bi-arrow-right"></i>
          </button>
          <span>แผนการเดินรถ</span>
        </div>
        <div class="card-body flex-grow-1 overflow-auto">

          <!-- Tab Nav for แผนเมื่อวาน -->
          <ul class="nav nav-tabs mb-3" id="routeTab" role="tablist">
            <?php $first = true; foreach ($route_groups as $route => $rows): ?>
              <li class="nav-item" role="presentation">
                <button class="nav-link <?= $first ? 'active' : '' ?>" id="tab-<?= md5($route) ?>" data-bs-toggle="tab" data-bs-target="#content-<?= md5($route) ?>" type="button" role="tab" aria-controls="content-<?= md5($route) ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                  <?= htmlspecialchars($route) ?>
                </button>
              </li>
            <?php $first = false; endforeach; ?>
          </ul>

          <!-- Tab content -->
          <div class="tab-content" style="max-height: 460px; overflow-y: auto;">
            <?php $first = true; foreach ($route_groups as $route => $rows): ?>
              <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="content-<?= md5($route) ?>" role="tabpanel" aria-labelledby="tab-<?= md5($route) ?>">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped align-middle text-center mb-0" style="white-space: nowrap;">
                    <thead class="table-primary">
                      <tr>
                        <th>#</th>
                        <th>รหัสแผน</th>
                        <th>เส้นทาง</th>
                        <th>ทะเบียนรถ</th>
                        <th>พขร</th>
                        <th>พขร พ่วง</th>
                        <th>โค้ช</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rows as $idx => $row): ?>
                        <tr>
                          <td><?= $idx + 1 ?></td>
                          <td><?= htmlspecialchars($row['id']) ?></td>
                          <td><?= htmlspecialchars($row['route']) ?></td>
                          <td><?= htmlspecialchars($row['licen']) ?></td>

                          <!-- พขร -->
                          <td>
                            <?= htmlspecialchars($row['emM']) ?>
                            <br>
                            <small class="text-secondary"><?= htmlspecialchars($row['emM_que']) ?></small>
                          </td>

                          <!-- พขร พ่วง -->
                          <td>
                            <?= htmlspecialchars($row['emX1']) ?>
                            <br>
                            <small class="text-secondary"><?= htmlspecialchars($row['emX1_que']) ?></small>
                          </td>

                          <!-- โค้ช -->
                          <td>
                            <?= htmlspecialchars($row['emC']) ?>
                            <br>
                            <small class="text-secondary"><?= htmlspecialchars($row['emC_que']) ?></small>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php $first = false; endforeach; ?>
          </div>

          <hr>

         <!-- ตารางพนักงานพัก (main_rest, ex_rest, coach_rest) -->
<div class="rest-columns row g-3">
  <!-- พขร พัก -->
  <div class="rest-column col-md-4 col-12">
    <div class="card card-rest ">
      <div class="card-header text-dark">🛌 พขร พัก</div>
      <div class="card-body p-2 scrollable-table" style="max-height: 250px; overflow-y: auto;">
        <?php if (!empty($main_rest)): ?>
          <?php $i=1; foreach($main_rest as $m): ?>
            <div class="rest-item">
              <strong><?= $i++ ?>.</strong>
              <?= htmlspecialchars($m['em_name'] . ' ' . $m['em_surname']) ?>
              <small class="text-secondary"> <?= htmlspecialchars($m['em_queue']) ?></small>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center"><i>ไม่มีข้อมูล</i></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- EX -->
  <div class="rest-column col-md-4 col-12">
    <div class="card card-rest">
      <div class="card-header  text-dark">🛌 พขร พ่วง พัก</div>
      <div class="card-body p-2 scrollable-table" style="max-height: 250px; overflow-y: auto;">
        <?php if (!empty($ex_rest)): ?>
          <?php $i=1; foreach($ex_rest as $ex): ?>
            <div class="rest-item">
              <strong><?= $i++ ?>.</strong>
              <?= htmlspecialchars($ex['em_name'] . ' ' . $ex['em_surname'] ) ?>
              <small class="text-secondary"> <?= htmlspecialchars($ex['em_queue']) ?></small>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center"><i>ไม่มีข้อมูล</i></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Coach -->
  <div class="rest-column col-md-4 col-12">
    <div class="card card-rest">
      <div class="card-header  text-dark">🛌 โค้ช พัก</div>
      <div class="card-body p-2 scrollable-table" style="max-height: 250px; overflow-y: auto;">
        <?php if (!empty($coach_rest)): ?>
          <?php $i=1; foreach($coach_rest as $co): ?>
            <div class="rest-item">
              <strong><?= $i++ ?>.</strong>
              <?= htmlspecialchars($co['em_name'] . ' ' . $co['em_surname'] ) ?>
              <small class="text-secondary"> <?= htmlspecialchars($co['em_queue']) ?></small>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center"><i>ไม่มีข้อมูล</i></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
        <!-- mock up-->
<div class="card border-secondary">
  <div class="card-header bg-secondary text-white">หมายเหตุ</div>
  <div class="card-body p-2">
    <ul class="list-group custom-list-lg">
      <li class="list-group-item d-flex justify-content-between align-items-center">
        พนักงานขับรถ ลาป่วยกระทันหัน 
        <span class="badge bg-danger rounded-pill">2 ราย</span>
      </li>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        รถเสีย/ไม่พร้อมใช้งาน 
        <span class="badge bg-warning rounded-pill text-dark">1 คัน</span>
      </li>
    </ul>
  </div>
</div>


    </div> <!-- right-column-wrapper -->
  </div> <!-- pageContainer -->

<!-- Floating Button -->
<button class="btn btn-danger btn-lg request-button" onclick="showRequestModal()">
  <i class="bi bi-send-fill me-1"></i> Request & Reserve
</button>

<!-- Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="requestModalLabel">จัดการ Request & Reserve</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="request-tables"></div>
      </div>
    </div>
  </div>
</div>


<script>
      document.getElementById('plan-form').addEventListener('submit', function () {
        document.getElementById('plan_data').value = JSON.stringify(<?= json_encode($plan) ?>);
        document.getElementById('main_break_data').value = JSON.stringify(<?= json_encode($main_break) ?>);
        document.getElementById('exnotredy_data').value = JSON.stringify(<?= json_encode($exnotredy) ?>);
        document.getElementById('coachnotredy_data').value = JSON.stringify(<?= json_encode($coachnotredy) ?>);
    });

function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('collapsed');
  document.body.classList.toggle('sidebar-collapsed');
}
function toggleLeftColumn() {
  const container = document.getElementById('pageContainer');
  const btn = document.getElementById('toggleLeftBtn');
  const icon = btn.querySelector('i');

  // ถ้าฝั่งขวากำลังย่ออยู่ ให้ขยายก่อน
  if (container.classList.contains('collapsed-right')) {
    container.classList.remove('collapsed-right');
    // เปลี่ยนไอคอนปุ่มขวาให้เป็น arrow-right (สถานะปกติ)
    const rightBtn = document.getElementById('toggleRightBtn');
    const rightIcon = rightBtn.querySelector('i');
    rightIcon.classList.remove('bi-arrow-left', 'bi-arrow-right');
    rightIcon.classList.add('bi-arrow-right');
  }

  // สลับสถานะฝั่งซ้าย
  container.classList.toggle('collapsed-left');

  // ถ้าหดฝั่งซ้ายแล้ว (collapsed-left = true) ให้ฝั่งขวาขยายเต็ม 85% (ถ้ามี)
  // แต่ถ้าขยายแล้ว (collapsed-left = false) ให้กลับมา 50%-50%
  if (container.classList.contains('collapsed-left')) {
    // ซ่อนซ้าย ย่อซ้ายเหลือ 15%
    // ขวาจะกว้าง 85% โดย CSS
    icon.classList.remove('bi-arrow-left', 'bi-arrow-right');
    icon.classList.add('bi-arrow-right');
  } else {
    // กลับมาสถานะเต็มหน้าจอ 50%-50%
    icon.classList.remove('bi-arrow-left', 'bi-arrow-right');
    icon.classList.add('bi-arrow-left');
  }
}

function toggleRightColumn() {
  const container = document.getElementById('pageContainer');
  const btn = document.getElementById('toggleRightBtn');
  const icon = btn.querySelector('i');

  // ถ้าฝั่งซ้ายกำลังย่ออยู่ ให้ขยายก่อน
  if (container.classList.contains('collapsed-left')) {
    container.classList.remove('collapsed-left');
    // เปลี่ยนไอคอนปุ่มซ้ายให้เป็น arrow-left (สถานะปกติ)
    const leftBtn = document.getElementById('toggleLeftBtn');
    const leftIcon = leftBtn.querySelector('i');
    leftIcon.classList.remove('bi-arrow-left', 'bi-arrow-right');
    leftIcon.classList.add('bi-arrow-left');
  }

  // สลับสถานะฝั่งขวา
  container.classList.toggle('collapsed-right');

  if (container.classList.contains('collapsed-right')) {
    icon.classList.remove('bi-arrow-left', 'bi-arrow-right');
    icon.classList.add('bi-arrow-left');
  } else {
    icon.classList.remove('bi-arrow-left', 'bi-arrow-right');
    icon.classList.add('bi-arrow-right');
  }
}

//request
const request = <?php echo json_encode($request); ?>;

function getAllCodeOptions(request) {
    const groupMap = {};
    Object.entries(request).forEach(([br_id, obj]) => {
        groupMap[br_id] = [];
        (obj.request || []).forEach((_, i, arr) => {
            let code = (i === arr.length - 1) ? `${br_id}-3-last` : `${br_id}-3-${i+1}`;
            groupMap[br_id].push({ value: code, label: code });
        });
        (obj.reserve || []).forEach((_, i) => {
            groupMap[br_id].push({ value: `${br_id}-1-${i+1}`, label: `${br_id}-1-${i+1}` });
        });
    });
    groupMap['อื่นๆ'] = [ { value: '0', label: '0' }, { value: '1', label: '1' }, { value: '2', label: '2' } ];
    return groupMap;
}

function createSelect(name, selected, routeOptions, br_id, type, idx, isDup) {
    let html = `<select name="${name}" class="form-select${isDup ? ' is-invalid' : ''}" onchange="onQueueChange('${br_id}','${type}',${idx},this)">`;
    Object.entries(routeOptions).forEach(([group, opts]) => {
        html += `<optgroup label="${group}">`;
        opts.forEach(opt => {
            html += `<option value="${opt.value}" ${opt.value === selected ? 'selected' : ''}>${opt.label}</option>`;
        });
        html += '</optgroup>';
    });
    html += '</select>';
    if (isDup && selected !== '0') html += '<div class="invalid-feedback">ซ้ำ</div>';
    return html;
}

function onQueueChange(br_id, type, idx, selectElem) {
    request[br_id][type][idx] = selectElem.value;
    renderTables();
}

function renderTables() {
  const container = document.getElementById('request-tables');
  const routeOptions = getAllCodeOptions(request);
  let allSelected = [], codeLocation = {}, seen = new Set(), duplicateCodes = new Set();

  Object.entries(request).forEach(([br_id, obj]) => {
    ['request', 'reserve'].forEach(type => {
      (obj[type] || []).forEach((val, idx) => {
        if (val && val !== '0') {
          allSelected.push(val);
          codeLocation[val] = codeLocation[val] || [];
          codeLocation[val].push(`Route ${br_id} - ${type} ลำดับ ${idx + 1}`);
        }
      });
    });
  });

  allSelected.forEach(code => {
    if (code === '1' || code === '2') return;
    if (seen.has(code)) duplicateCodes.add(code);
    else seen.add(code);
  });

  let html = `<div id="all-route-form">`;

  Object.entries(request).forEach(([br_id, obj]) => {
    html += `
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-secondary text-white"><h5 class="mb-0">Route: ${br_id}</h5></div>
      <div class="card-body">
        <div class="row">
          <!-- Request Column -->
          <div class="col-md-6">
            <b>Request</b>
            <table class="table table-bordered table-sm align-middle">
              <thead class="table-light"><tr><th>#</th><th>Code</th><th>Queue</th><th>Action</th></tr></thead>
              <tbody>`;
              
    (obj.request || []).forEach((val, idx, arr) => {
      const code = (idx === arr.length - 1) ? `${br_id}-3-last` : `${br_id}-3-${idx + 1}`;
      const isDup = duplicateCodes.has(val);
      html += `<tr><td>${idx+1}</td><td>${code}</td><td>${createSelect(`request[${br_id}][]`, val, routeOptions, br_id, 'request', idx, isDup)}</td>
        <td>
          <div class="btn-group btn-group-sm" role="group">
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'before')">แทรกก่อน</button>
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'after')">แทรกหลัง</button>
            <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','request',${idx})">ลบ</button>
          </div>
        </td></tr>`;
    });

    html += `<tr><td>ใหม่</td><td>${br_id}-3-ใหม่</td><td>${createSelect('', '0', routeOptions, br_id, 'request', obj.request.length, false)}</td>
        <td><button type='button' class="btn btn-success btn-sm" onclick="insertRow('${br_id}','request',${obj.request.length-1},'after')">เพิ่ม</button></td></tr>`;

    html += `
              </tbody>
            </table>
          </div>

          <!-- Reserve Column -->
          <div class="col-md-6">
            <b>Reserve</b>
            <table class="table table-bordered table-sm align-middle">
              <thead class="table-light"><tr><th>#</th><th>Code</th><th>Queue</th><th>Action</th></tr></thead>
              <tbody>`;

    (obj.reserve || []).forEach((val, idx) => {
      const code = `${br_id}-1-${idx + 1}`;
      const isDup = duplicateCodes.has(val);
      html += `<tr><td>${idx+1}</td><td>${code}</td><td>${createSelect(`reserve[${br_id}][]`, val, routeOptions, br_id, 'reserve', idx, isDup)}</td>
        <td>
          <div class="btn-group btn-group-sm" role="group">
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','reserve',${idx},'before')">แทรกก่อน</button>
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','reserve',${idx},'after')">แทรกหลัง</button>
            <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','reserve',${idx})">ลบ</button>
          </div>
        </td></tr>`;
    });

    html += `<tr><td>ใหม่</td><td>${br_id}-1-ใหม่</td><td>${createSelect('', '0', routeOptions, br_id, 'reserve', obj.reserve.length, false)}</td>
        <td><button type='button' class="btn btn-success btn-sm" onclick="insertRow('${br_id}','reserve',${obj.reserve.length-1},'after')">เพิ่ม</button></td></tr>`;

    html += `
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>`;
  });

  html += `<div class='my-3'><button type='button' class="btn btn-primary w-100" onclick="submitQueueData()">บันทึกทั้งหมด</button></div></div>`;
  container.innerHTML = html;
}

function removeRow(br_id, type, idx) {
    request[br_id][type].splice(idx, 1);
    renderTables();
}

function insertRow(br_id, type, idx, pos) {
    let insertIdx = pos === 'before' ? idx : idx + 1;
    request[br_id][type].splice(insertIdx, 0, '0');
    renderTables();
    setTimeout(() => {
        const row = document.getElementById(`tbody-${br_id}-${type}`)?.children[insertIdx];
        if (row) row.scrollIntoView({ behavior: 'smooth' });
    }, 100);
}

function showRequestModal() {
  renderTables();
  new bootstrap.Modal(document.getElementById('requestModal')).show();
}

function submitQueueData() {
  // รวบรวมข้อมูลจาก request object
  const data = new FormData();
  for (const [br_id, obj] of Object.entries(request)) {
    if (obj.request) {
      obj.request.forEach(val => data.append(`request[${br_id}][]`, val));
    }
    if (obj.reserve) {
      obj.reserve.forEach(val => data.append(`reserve[${br_id}][]`, val));
    }
  }

  // ส่งข้อมูลด้วย fetch POST ไปยัง request_db.php
  fetch('request_db.php', {
    method: 'POST',
    body: data
  })
  .then(response => response.text())
  .then(result => {
    alert("บันทึกข้อมูลสำเร็จ");
    // สามารถปิด modal ได้หากต้องการ:
    const modal = bootstrap.Modal.getInstance(document.getElementById('requestModal'));
    if (modal) modal.hide();
  })
  .catch(error => {
    console.error('Error:', error);
    alert("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
  });
}
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
