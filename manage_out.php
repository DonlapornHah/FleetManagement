<?php
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// ดึงพนักงาน main_route 2-4 และ et_id=1
$sql_main = "SELECT * FROM `employee` WHERE main_route >= 2 AND main_route <= 4 AND et_id = 1 ORDER BY em_queue";
$result_main = mysqli_query($conn, $sql_main);
if (!$result_main) die("Query failed: " . mysqli_error($conn));

// ดึง queue_request ทั้งหมด
$sql_re = "SELECT * FROM `queue_request`";
$result_re = mysqli_query($conn, $sql_re);
$queue = [];
$re = [];
while ($row = mysqli_fetch_assoc($result_re)) {
    $re[$row['br_id']][] = $row['qr_request'];
    if ($row['qr_request'] != '0') {
        $queue[] = $row['qr_request'];
    }
}

$queue_num = 5;
$route_name = [];
$main = [];
$main_re = [];

while ($row_main = mysqli_fetch_assoc($result_main)) {
    if (!in_array($row_main['main_route'], $route_name)) {
        $route_name[] = $row_main['main_route'];
    }
    if (in_array($row_main['em_queue'], $queue)) {
        $main_re[] = $row_main;
    } else {
        $main[] = $row_main;
    }
}

$plan = [3, 3, 3]; // จำนวนแถวในแต่ละ route

$new_plan = [];
$new_break = [];

$i = 0;
foreach ($plan as $key => $value) {
    $j = 1;
    $x = 1;
    $r_key = $route_name[$i];
    $re_count = isset($re[$r_key]) ? count($re[$r_key]) : 0;
    while ($j <= $value || $j <= $re_count) {
        $re_value = ($j - 1 < $re_count) ? $re[$r_key][$j - 1] : '0';
        if ($j <= $value) {
            if ($re_value == '0') {
                // กรองพนักงานที่พร้อมใน main ตาม route และสถานะ
                $filtered = array_filter($main, function ($item) use ($r_key) {
                    return $item['main_route'] == $r_key && $item['es_id'] == '1';
                });
                $first = reset($filtered);
                $firstKey = key($filtered);
                if ($firstKey !== null && $first !== false) {
                    $new_plan[$r_key][$j] = $first['em_name'] . ' ' . $first['em_surname'] . ' (' . $first['em_queue'] . ')';
                    unset($main[$firstKey]);
                    $main = array_values($main);
                } else {
                    // กรณีไม่มีข้อมูลพนักงานเหลือ ให้ใช้ข้อมูลเก่าที่ x (loop)
                    $new_plan[$r_key][$j] = $new_plan[$r_key][$x] ?? '-';
                    $x++;
                }
            } else {
                // หา em_queue ใน main_re แล้วแสดงชื่อ-นามสกุล
                $idx = array_search($re_value, array_column($main_re, 'em_queue'));
                if ($idx !== false && isset($main_re[$idx])) {
                    $emp = $main_re[$idx];
                    $new_plan[$r_key][$j] = $emp['em_name'] . ' ' . $emp['em_surname'] . ' (' . $emp['em_queue'] . ')';
                } else {
                    $new_plan[$r_key][$j] = '-';
                }
            }
        } else {
            $new_break[$r_key][$j] = $re_value;
        }
        $j++;
    }
    $i++;
}

// แยก $main ที่เหลือไปตาม main_route
$main_2 = [];
$main_3 = [];
$main_4 = [];

foreach ($main as $item) {
    if (isset($item['main_route'])) {
        if ($item['main_route'] == 2) {
            $main_2[] = $item;
        } elseif ($item['main_route'] == 3) {
            $main_3[] = $item;
        } elseif ($item['main_route'] == 4) {
            $main_4[] = $item;
        }
    }
}

// นำข้อมูล new_break ไปแยกใส่ใน main_2, main_3, main_4 ตาม main_route
foreach ($new_break as $route => $vals) {
    foreach ($vals as $idx => $v) {
        if ($v === '0' || $v === '-' || empty($v)) continue;
        $emp = null;
        $find_idx = array_search($v, array_column($main_re, 'em_queue'));
        if ($find_idx !== false && isset($main_re[$find_idx])) {
            $emp = $main_re[$find_idx];
        }
        if ($emp) {
            if ($route == '2') {
                $main_2[] = $emp;
            } elseif ($route == '3') {
                $main_3[] = $emp;
            } elseif ($route == '4') {
                $main_4[] = $emp;
            }
        }
    }
}

function renderTable($title, $data) {
    echo "<div class='mb-4'>";
    echo "<h4 class='text-primary'>$title</h4>";
    if (!empty($data)) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-striped'>";
        echo "<thead class='table-light'><tr><th>ชื่อ</th><th>นามสกุล</th><th>คิว</th></tr></thead><tbody>";
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['em_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['em_surname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['em_queue']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
    } else {
        echo "<p><i>ไม่มีข้อมูล</i></p>";
    }
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>แสดงแผนคิวพนักงาน</title>
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

        <h3>แผนการจัดคิว (new_plan)</h3>
        <?php if (!empty($new_plan)) : ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>สายเดินรถ (Route)</th>
                            <th>ลำดับคิว (Queue)</th>
                            <th>พนักงาน (Employee)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($new_plan as $route => $vals): ?>
                        <?php foreach ($vals as $idx => $emp): ?>
                            <tr>
                                <td><?= htmlspecialchars($route) ?></td>
                                <td><?= htmlspecialchars($idx) ?></td>
                                <td><?= htmlspecialchars($emp) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p><i>ไม่มีข้อมูลแผนการจัดคิว</i></p>
        <?php endif; ?>

        <h3>รายการเบรก (new_break)</h3>
        <?php if (!empty($new_break)) : ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-info">
                        <tr>
                            <th> (Route)</th>
                            <th> (Queue)</th>
                            <th> (Break Value)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($new_break as $route => $vals): ?>
                        <?php foreach ($vals as $idx => $val): ?>
                            <tr>
                                <td><?= htmlspecialchars($route) ?></td>
                                <td><?= htmlspecialchars($idx) ?></td>
                                <td><?= htmlspecialchars($val) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p><i>ไม่มีข้อมูลรายการเบรก</i></p>
        <?php endif; ?>

        <hr>
        <h2 class="section-title">พนักงานที่เหลือในแต่ละสาย</h2>

        <?php renderTable("สาย 2", $main_2); ?>
        <?php renderTable("สาย 3", $main_3); ?>
        <?php renderTable("สาย 4", $main_4); ?>

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
