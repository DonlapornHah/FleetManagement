<?php
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ฟังก์ชันดึงชื่อเส้นทาง (start - end)
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
    while($queue_num > count($ready)){
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
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการแผนเดินรถ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1140px;
            position: relative; /* รองรับ alert */
        }
        h1 {
            margin-top: 2rem;
            font-weight: 700;
            text-align: center;
        }
        .table thead th {
            background-color: #212529;
            color: white;
            text-align: center;
            vertical-align: middle;
        }
        .table tbody td {
            text-align: center;
            vertical-align: middle;
        }
        .badge {
            font-size: 0.85rem;
            padding: 0.4em 0.65em;
            user-select: none;
        }
        .section-title {
            margin-top: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
            color: #856404;
            border-left: 4px solid #ffc107;
            padding-left: 0.75rem;
            user-select: none;
        }
        .card-rest {
            border-left: 4px solid #ffc107;
            background-color: #fff8e1;
            box-shadow: 0 2px 6px rgb(0 0 0 / 0.08);
            margin-bottom: 1rem;
        }
        .card-header {
            font-weight: 700;
        }
        .btn-success {
            font-size: 1.1rem;
            padding: 0.6rem 2rem;
        }
        /* Hover effect */
        tbody tr:hover {
            background-color: #e9ecef;
            cursor: default;
            transition: background-color 0.15s ease-in-out;
        }
        @media (max-width: 767.98px) {
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
<div class="container py-4">

    <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
        <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h1>จัดการแผนเดินรถ</h1>

    <!-- ตารางหลัก -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">จัดการแผนการเดินรถ</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>พนักงานหลัก</th>
                            <th>คิว</th>
                            <th>รถ</th>
                            <th>เส้นทาง</th>
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
                                <td><?= $main[$i]['em_name'] . ' ' . $main[$i]['em_surname'] ?></td>
                                <td><span class="badge bg-primary"><?= $row['main_queue'] ?></span></td>
                                <td><?= $row['car'] ?></td>
                                <td><?= $row['route'] ?></td>
                                <td><?= $ex[$i]['em_name'] . ' ' . $ex[$i]['em_surname'] ?></td>
                                <td><span class="badge bg-success"><?= $row['ex_queue'] ?></span></td>
                                <td><?= $coach[$i]['em_name'] . ' ' . $coach[$i]['em_surname'] ?></td>
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

    <!-- ปุ่มส่ง -->
    <div class="text-center">
        <form action="manage_db.php" method="post">
            <input type="hidden" name="plan" value='<?= json_encode($plan, JSON_UNESCAPED_UNICODE) ?>'>
            <input type="hidden" name="main_not_ready" value='<?= json_encode($main_not_ready, JSON_UNESCAPED_UNICODE) ?>'>
            <input type="hidden" name="ex_not_ready" value='<?= json_encode($ex_not_ready, JSON_UNESCAPED_UNICODE) ?>'>
            <input type="hidden" name="coach_not_ready" value='<?= json_encode($coach_not_ready, JSON_UNESCAPED_UNICODE) ?>'>
            <button type="submit" class="btn btn-success shadow-sm">ส่งข้อมูลทั้งหมด</button>
        </form>
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
                                <?= $row['em_name'] ?> <?= $row['em_surname'] ?>
                                <span class="badge bg-primary"><?= $row['em_queue'] ?></span>
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
                                <?= $row['em_name'] ?> <?= $row['em_surname'] ?>
                                <span class="badge bg-success"><?= $row['em_queue'] ?></span>
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
                                <?= $row['em_name'] ?> <?= $row['em_surname'] ?>
                                <span class="badge bg-warning text-dark"><?= $row['em_queue'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">ไม่มีข้อมูล</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
      setTimeout(() => {
        const alert = bootstrap.Alert.getOrCreateInstance(alertBox);
        alert.close();
      }, 1500);
    }
  });
</script>

</body>
</html>
