<?php
include 'config.php';
include 'function/groupEmployee.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$route = [2, 3, 4];
$normal_code = [3, 2, 1];

list($re, $main, $main_re, $break) = getMainDriver($conn, $route);
list($new_plan, $main, $x) = groupMainDriver($re, $main, $main_re, $normal_code);

foreach ($re as $key => $value) {
    $queue_num[$key] = count($value);
}

list($new_ex, $exnotredy) = getEmployee($conn, $route, $queue_num, $x, 2);
list($new_coach, $coachnotredy) = getEmployee($conn, $route, $queue_num, $x, 3);

$main_break = [];
$new_main = [];

foreach ($main as $key => $value) {
    $route = $value['em_queue'][0];
    $new_main[$route][] = $value;
}
$main_break = groupByRouteWithNewQueue($new_main, $main_break);
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
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Manage Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<style>
    .card-body {
        display: flex;
        flex-direction: column;
    }
</style>

<body class="bg-light">
<div class="container py-5">
    <div class="card mb-4 shadow-lg">
        <div class="card-header bg-dark text-white">
      <h4 class="mb-0">ตัวอย่างข้อมูลแผน (Plan) ที่จะส่ง</h4>
    </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="routeTab" role="tablist">
                <?php $first = true; foreach ($plan as $route => $rows): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $first ? 'active' : '' ?>" id="tab-<?= $route ?>"
                                data-bs-toggle="tab" data-bs-target="#content-<?= $route ?>" type="button"
                                role="tab" aria-controls="content-<?= $route ?>" aria-selected="<?= $first ? 'true' : 'false' ?>">
                            สาย <?= htmlspecialchars($route) ?>
                        </button>
                    </li>
                <?php $first = false; endforeach; ?>
            </ul>

            <div class="tab-content" id="routeTabContent">
                <?php $first = true; foreach ($plan as $route => $rows): ?>
                    <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="content-<?= $route ?>"
                         role="tabpanel" aria-labelledby="tab-<?= $route ?>">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" style="table-layout: auto; white-space: nowrap;">
                                <thead class="table-primary fw-normal">
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รหัสพขร.</th>
                                    <th>ชื่อ-นามสกุล พขร</th>
                                    <th>ทะเบียนรถ</th>
                                    <th>คิวพขร</th>
                                    <th>คิวถัดไปพขร</th>
                                    <th>รหัสพขร พ่วง</th>
                                    <th>ชื่อ-นามสกุล พ่วง</th>
                                    <th>คิวพขร พ่วง</th>
                                    <th>คิวถัดไป พขร พ่วง</th>
                                    <th>รหัสโค้ช</th>
                                    <th>ชื่อ-นามสกุล โค้ช</th>
                                    <th>คิวถัดไป โค้ช</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rows as $idx => $row): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><?= htmlspecialchars($row['em_id']) ?></td>
                                        <td><?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?></td>
                                        <td><?= htmlspecialchars($row['car']) ?></td>
                                        <td><?= htmlspecialchars($row['em_queue']) ?></td>
                                        <td><?= htmlspecialchars($row['new_queue']) ?></td>
                                        <td><?= htmlspecialchars($row['ex_id']) ?></td>
                                        <td><?= htmlspecialchars($row['ex_name'] . ' ' . $row['ex_surname']) ?></td>
                                        <td><?= htmlspecialchars($row['ex_queue']) ?></td>
                                        <td><?= htmlspecialchars($row['ex_new_queue']) ?></td>
                                        <td><?= htmlspecialchars($row['coach_id']) ?></td>
                                        <td><?= htmlspecialchars($row['coach_name'] . ' ' . $row['coach_surname']) ?></td>
                                        <td><?= htmlspecialchars($row['coach_new_queue']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php $first = false; endforeach; ?>
                                </div>
        
                                    </div>
                                </div>
<div class="row g-3">
    <!-- พขร พัก -->
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">พขร พัก</div>
            <div class="card-body p-3">
                <?php if (!empty($main_break)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered text-center mb-0">
                            <thead class="table-light fw-normal">
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>ชื่อ - นามสกุล</th>
                                    <th>คิว</th>
                                    <th>คิวถัดไป</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($main_break as $v): ?>
                                    <?php foreach ($v as $idx => $row): ?>
                                        <?php if (!isset($row['em_id'])) continue; ?>
                                        <tr>
                                            <td><?= $idx++ ?></td>
                                            <td><?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?></td>
                                            <td><?= htmlspecialchars($row['em_queue']) ?></td>
                                            <td><?= htmlspecialchars($row['new_queue']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center"><i>ไม่มีข้อมูล</i></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- พขร พ่วง พัก/ไม่พร้อม -->
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">พขร พ่วง พัก/ไม่พร้อม</div>
            <div class="card-body p-3">
                <?php foreach ($exnotredy as $route => $list): ?>
                    <div class="mb-2">
                        <strong class="text-muted">สาย <?= htmlspecialchars($route) ?></strong>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center mb-0">
                                <thead class="table-light fw-normal">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ชื่อ - นามสกุล</th>
                                        <th>คิว</th>
                                        <th>คิวถัดไป</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $idx = 1; foreach ($list as $row): ?>
                                        <tr>
                                            <td><?= $idx++ ?></td>
                                            <td><?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?></td>
                                            <td><?= htmlspecialchars($row['em_queue']) ?></td>
                                            <td><?= htmlspecialchars($row['new_queue']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- โค้ช พัก/ไม่พร้อม -->
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">โค้ช พัก/ไม่พร้อม</div>
            <div class="card-body p-3">
                <?php foreach ($coachnotredy as $route => $list): ?>
                    <div class="mb-2">
                        <strong class="text-muted">สาย <?= htmlspecialchars($route) ?></strong>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center mb-0">
                                <thead class="table-light fw-normal">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ชื่อ - นามสกุล</th>
                                        <th>คิว</th>
                                        <th>คิวถัดไป</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $idx = 1; foreach ($list as $row): ?>
                                        <tr>
                                            <td><?= $idx++ ?></td>
                                            <td><?= htmlspecialchars($row['em_name'] . ' ' . $row['em_surname']) ?></td>
                                            <td><?= htmlspecialchars($row['em_queue']) ?></td>
                                            <td><?= htmlspecialchars($row['new_queue']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <form method="post" action="manage_db.php" id="plan-form">
        <input type="hidden" name="plan_data" id="plan_data">
        <input type="hidden" name="main_break_data" id="main_break_data">
        <input type="hidden" name="exnotredy_data" id="exnotredy_data">
        <input type="hidden" name="coachnotredy_data" id="coachnotredy_data">
        <div class="text-center mb-4">
            <button type="submit" class="btn btn-success px-5">บันทึกแผน</button>
        </div>
    </form>

</div>
    

<script>
    document.getElementById('plan-form').addEventListener('submit', function () {
        document.getElementById('plan_data').value = JSON.stringify(<?= json_encode($plan) ?>);
        document.getElementById('main_break_data').value = JSON.stringify(<?= json_encode($main_break) ?>);
        document.getElementById('exnotredy_data').value = JSON.stringify(<?= json_encode($exnotredy) ?>);
        document.getElementById('coachnotredy_data').value = JSON.stringify(<?= json_encode($coachnotredy) ?>);
    });


    
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
