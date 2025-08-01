<?php
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงรายชื่อสายเดินรถพร้อมชื่อสถานที่เริ่มต้น-สิ้นสุด
$sql_routes = "
SELECT 
    br.br_id,
    CONCAT(loS.locat_name_th, ' - ', loE.locat_name_th) AS route_name
FROM bus_routes br
LEFT JOIN location loS ON br.br_start = loS.locat_id
LEFT JOIN location loE ON br.br_end = loE.locat_id
ORDER BY br.br_id
";
$res_routes = mysqli_query($conn, $sql_routes);
$route_list = [];
while ($row_route = mysqli_fetch_assoc($res_routes)) {
    $route_list[$row_route['br_id']] = $row_route['route_name'];
}
if (empty($route_list)) {
    die("Error: ไม่มีข้อมูลสายเดินรถในระบบ");
}
$route_ids = array_keys($route_list);
$route_in = '(' . implode(',', $route_ids) . ')';

// ดึงค่า br_start ของแต่ละสายเพื่อใช้ตรวจสอบสายที่มีขากลับ
$sql_br_start = "SELECT br_id, br_start FROM bus_routes";
$res_br_start = mysqli_query($conn, $sql_br_start);
$route_br_start = [];
while ($row = mysqli_fetch_assoc($res_br_start)) {
    $route_br_start[$row['br_id']] = intval($row['br_start']);
}

// รับสายที่เลือกจาก GET
$selected = isset($_GET['route']) ? intval($_GET['route']) : 0;

// กำหนดเงื่อนไขสายเดินรถ
$route_filter = ($selected && in_array($selected, $route_ids)) 
    ? "br.br_id = $selected" 
    : "br.br_id IN $route_in";

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
    $route_filter
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

// จัดกลุ่มข้อมูลตาม route
$route_groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    $route_groups[$row['route']][] = $row;
}

// กรองข้อมูลแค่สายที่เลือก หรือแสดงทั้งหมด พร้อมเพิ่มแท็บ "ขากลับ" สำหรับสายที่ br_start = 1
$display_routes = [];

if ($selected != 0) {
    $found = false;
    foreach ($route_groups as $key => $val) {
        if (strpos($key, $route_list[$selected]) !== false) {
            $display_routes[$key] = $val;
            $found = true;

            // หา br_id ของสายนี้ (key ใน $route_list ที่ตรงกับ $key)
            $matched_br_id = null;
            foreach ($route_list as $br_id => $name) {
                if (trim($name) === trim($key)) {
                    $matched_br_id = $br_id;
                    break;
                }
            }

            // ถ้า br_start = 1 ให้เพิ่มแท็บขากลับ (สลับตำแหน่งจุดเริ่มต้น-สิ้นสุด)
            if ($matched_br_id !== null && isset($route_br_start[$matched_br_id]) && $route_br_start[$matched_br_id] === 1) {
                $parts = explode(' - ', $key);
                if (count($parts) === 2) {
                    $reverse_route = $parts[1] . ' - ' . $parts[0];
                    if (!isset($display_routes[$reverse_route])) {
                        $display_routes[$reverse_route] = []; // ขากลับยังไม่มีข้อมูล แต่สร้างแท็บให้
                    }
                }
            }
        }
    }
    if (!$found) {
        $display_routes = []; // ไม่มีข้อมูล
    }
} else {
    // กรณีเลือกทั้งหมด
    $display_routes = $route_groups;

    // เพิ่มแท็บขากลับ สำหรับสายที่ br_start = 1
    foreach ($route_groups as $key => $val) {
        $matched_br_id = null;
        foreach ($route_list as $br_id => $name) {
            if (trim($name) === trim($key)) {
                $matched_br_id = $br_id;
                break;
            }
        }

        if ($matched_br_id !== null && isset($route_br_start[$matched_br_id]) && $route_br_start[$matched_br_id] === 1) {
            $parts = explode(' - ', $key);
            if (count($parts) === 2) {
                $reverse_route = $parts[1] . ' - ' . $parts[0];
                if (!isset($display_routes[$reverse_route])) {
                    $display_routes[$reverse_route] = [];
                }
            }
        }
    }
}

$route_names = array_keys($display_routes);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>สรุปแผนออกเดินรถ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            min-height: 100vh;
        }
        .modern-card {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 2rem 1.5rem;
            margin-bottom: 2.5rem;
        }
        table {
            width: 100%;
            table-layout: auto;
        }
        th, td {
            padding: 10px 15px;
            white-space: nowrap;
            text-align: center;
        }
    </style>
</head>
<?php include 'index.php'; ?>
<body>
  
    <div class="card shadow-lg mb-4">
        <div class="card-header text-white bg-dark">
            <h4 class="mb-0">เลือกสายเดินรถ</h4>
        </div>
        <div class="card-body">
            <form method="get" class="d-flex align-items-center gap-3 flex-wrap">
                <label for="routeSelect" class="mb-0 fw-semibold">สายเดินรถ:</label>
                <select name="route" id="routeSelect" class="form-select" style="width: 300px;">
                    <option value="0" <?= ($selected == 0) ? 'selected' : '' ?>>ทั้งหมด</option>
                    <?php foreach ($route_list as $br_id => $route_name): ?>
                        <option value="<?= $br_id ?>" <?= ($selected == $br_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($route_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">ค้นหา</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: แท็บสายเดินรถ -->
        <div class="col-md-6">
            <?php if (count($display_routes) === 0): ?>
                <div class="alert alert-warning">ไม่มีข้อมูลสำหรับสาย <?= htmlspecialchars($selected != 0 ? $route_list[$selected] : 'ทั้งหมด') ?></div>
            <?php else: ?>
                <ul class="nav nav-tabs mb-3" id="routeTab" role="tablist">
                    <?php foreach ($route_names as $index => $route_name): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $index === 0 ? 'active' : '' ?>"
                                    id="tab-<?= md5($route_name) ?>"
                                    data-bs-toggle="tab"
                                    data-bs-target="#tab-content-<?= md5($route_name) ?>"
                                    type="button" role="tab"
                                    aria-controls="tab-content-<?= md5($route_name) ?>"
                                    aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                <?= htmlspecialchars($route_name) ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php foreach ($display_routes as $route_name => $rows): ?>
                        <div class="tab-pane fade <?= ($route_name === $route_names[0]) ? 'show active' : '' ?>"
                             id="tab-content-<?= md5($route_name) ?>"
                             role="tabpanel"
                             aria-labelledby="tab-<?= md5($route_name) ?>">
                            <div class="modern-card">
                                <?php if (count($rows) === 0): ?>
                                    <p class="text-muted text-center">ไม่มีข้อมูลเดินรถในเส้นทางนี้</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped align-middle mb-0">
                                            <thead class="table-primary fw-normal">
                                            <tr class="text-center">
                                                <th>#</th>
                                                <th>emM_id</th>
                                                <th>เส้นทาง</th>
                                                <th>ทะเบียน</th>
                                                <th>พขร</th>
                                                <th>พขร พ่วง</th>
                                                <th>พขร พ่วง 2</th>
                                                <th>coach</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $i = 1;
                                            foreach ($rows as $row): ?>
                                                <tr class="text-center">
                                                    <td><?= $i++ ?></td>
                                                    <td><?= htmlspecialchars($row['emM_id']) ?></td>
                                                    <td><?= htmlspecialchars($row['route']) ?></td>
                                                    <td><?= htmlspecialchars($row['licen']) ?></td>
                                                    <td><?= htmlspecialchars($row['emM']) ?>
                                                        <br><small class="text-secondary">(<?= htmlspecialchars($row['emM_que']) ?>)</small>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['emX1']) ?>
                                                        <br><small class="text-secondary">(<?= htmlspecialchars($row['emX1_que']) ?>)</small>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['emX2']) ?>
                                                        <br><small class="text-secondary">(<?= htmlspecialchars($row['emX2_que']) ?>)</small>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['emC']) ?>
                                                        <br><small class="text-secondary">(<?= htmlspecialchars($row['emC_que']) ?>)</small>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- ตารางพนักงานพัก 3 คอลัมน์ -->
                <div class="row g-3 mt-4">
                    <!-- Main -->
                    <div class="col-md-4">
                        <div class="card border-danger h-100">
                            <div class="card-header bg-danger text-white">พขร พัก</div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light"><tr><th>#</th><th>ชื่อ</th></tr></thead>
                                    <tbody>
                                    <?php $i=1; foreach($main_rest as $m): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($m['em_name'] . ' ' . $m['em_surname'] . ' ' . $m['em_queue']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- EX -->
                    <div class="col-md-4">
                        <div class="card border-warning h-100">
                            <div class="card-header bg-warning text-dark">สำรอง พัก</div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light"><tr><th>#</th><th>ชื่อ</th></tr></thead>
                                    <tbody>
                                    <?php $i=1; foreach($ex_rest as $ex): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($ex['em_name'] . ' ' . $ex['em_surname'] . ' ' . $ex['em_queue'] ) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Coach -->
                    <div class="col-md-4">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white">โค้ช พัก</div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light"><tr><th>#</th><th>ชื่อ</th></tr></thead>
                                    <tbody>
                                    <?php $i=1; foreach($coach_rest as $co): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($co['em_name'] . ' ' . $co['em_surname'] . ' ' . $co['em_queue']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT COLUMN: ยังไม่มีข้อมูล -->
        <div class="col-md-6">
            <div class="card shadow-sm p-4 text-center bg-light border-dashed" style="height: 100%;">
                <h5 class="text-muted">🛈 </h5>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
