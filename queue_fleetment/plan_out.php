<?php
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$route = [2, 3, 4];
$route_in = '(' . implode(',', $route) . ')';

// ดึงแผนเดินรถ
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

// ดึงพนักงานพัก
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

// เก็บข้อมูลพัก
$main_rest = [];
while ($row_main = mysqli_fetch_assoc($result_main)) $main_rest[] = $row_main;

$ex_rest = [];
while ($row_ex = mysqli_fetch_assoc($result_ex)) $ex_rest[] = $row_ex;

$coach_rest = [];
while ($row_coach = mysqli_fetch_assoc($result_coach)) $coach_rest[] = $row_coach;

// Group routes
$route_groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    $route_groups[$row['route']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สรุปแผนออกเดินรถ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); min-height: 100vh; }
        .container { margin-top: 40px; margin-bottom: 40px; }
        .modern-card {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 2rem 1.5rem;
            margin-bottom: 2.5rem;
        }
    
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-header text-white bg-dark">
            <h4 class="mb-0">แผนการเดินรถ</h4>
        </div>
        <div class="card-body">
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs mb-4" id="routeTab" role="tablist">
                <?php $first = true; foreach ($route_groups as $route => $rows): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $first ? 'active' : '' ?>" 
                                id="tab-<?= md5($route) ?>" 
                                data-bs-toggle="tab" 
                                data-bs-target="#content-<?= md5($route) ?>" 
                                type="button" role="tab" 
                                aria-controls="content-<?= md5($route) ?>" 
                                aria-selected="<?= $first ? 'true' : 'false' ?>">
                            <?= htmlspecialchars($route) ?>
                        </button>
                    </li>
                <?php $first = false; endforeach; ?>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="routeTabContent">
                <?php $first = true; foreach ($route_groups as $route => $rows): ?>
                    <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" 
                         id="content-<?= md5($route) ?>" 
                         role="tabpanel" 
                         aria-labelledby="tab-<?= md5($route) ?>">
                        <div class="modern-card">
                            <h5 class="mb-3 ">สายเดินรถ: <?= htmlspecialchars($route) ?></h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle mb-0">
                                    <thead class="table-primary fw-normal">
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>emM_id</th>
                                            <th>เส้นทาง</th>
                                            <th>br_id</th>
                                            <th>ทะเบียน</th>
                                            <th>พขร</th>
                                            <th>พขร พ่วง</th>
                                            <th>พขร พ่วง 2</th>
                                            <th>coach</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; foreach ($rows as $row): ?>
                                            <tr class="text-center">
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($row['emM_id']) ?></td>
                                                <td><?= htmlspecialchars($row['route']) ?></td>
                                                <td><?= htmlspecialchars($row['br_id']) ?></td>
                                                <td><?= htmlspecialchars($row['licen']) ?></td>
                                                <td><?= htmlspecialchars($row['emM']) ?> 
    <span class="text-secondary">(<?= htmlspecialchars($row['emM_que']) ?>)</span>
</td>
<td><?= htmlspecialchars($row['emX1']) ?> 
    <span class="text-secondary">(<?= htmlspecialchars($row['emX1_que']) ?>)</span>
</td>
<td><?= htmlspecialchars($row['emX2']) ?> 
    <span class="text-secondary">(<?= htmlspecialchars($row['emX2_que']) ?>)</span>
</td>
<td><?= htmlspecialchars($row['emC']) ?> 
    <span class="text-secondary">(<?= htmlspecialchars($row['emC_que']) ?>)</span>
</td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Column: พนักงานพัก -->
    <div class="row g-3 mt-4">
        <!-- Main -->
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">พขร พัก</div>
                <div class="card-body p-2">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>#</th><th>ชื่อ</th><th>คิว</th></tr></thead>
                        <tbody>
                        <?php $i=1; foreach($main_rest as $m): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($m['em_name'] . ' ' . $m['em_surname']) ?></td>
                                <td><?= htmlspecialchars($m['em_queue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- EX -->
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">สำรอง พัก</div>
                <div class="card-body p-2">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>#</th><th>ชื่อ</th><th>คิว</th></tr></thead>
                        <tbody>
                        <?php $i=1; foreach($ex_rest as $ex): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($ex['em_name'] . ' ' . $ex['em_surname']) ?></td>
                                <td><?= htmlspecialchars($ex['em_queue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Coach -->
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">โค้ช พัก</div>
                <div class="card-body p-2">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light"><tr><th>#</th><th>ชื่อ</th><th>คิว</th></tr></thead>
                        <tbody>
                        <?php $i=1; foreach($coach_rest as $co): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($co['em_name'] . ' ' . $co['em_surname']) ?></td>
                                <td><?= htmlspecialchars($co['em_queue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
