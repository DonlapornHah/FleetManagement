<?php
include 'config.php';
$main_id = intval($_GET['main_id']);
$sql_main = "SELECT * FROM route WHERE route_id=$main_id AND type_route='สายหลัก'";
$res_main = $conn->query($sql_main);
$main = $res_main->fetch_assoc();
if(!$main){ echo "<p class='text-danger'>ไม่พบสายหลัก</p>"; exit; }

// ดึงสายย่อย
$sql = "SELECT * FROM route WHERE route_number='".$main['route_number']."' AND type_route='สายย่อย'";
$res = $conn->query($sql);

// คำนวณระยะทางและเวลาเริ่มต้นจากสายหลัก
$total_distance_main = (float)$main['distance_km'];
$total_time_main = isset($main['total_time']) ? (int)$main['total_time'] : 0; // ถ้ามีคอลัมน์เวลา
?>
<h6 class="mb-3 d-flex align-items-center justify-content-between">
  <span>สายหลัก : <?= htmlspecialchars($main['route_name_th']) ?> (<?= $main['route_number'] ?>)</span>
  <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSubRouteModal">
    + เพิ่มสายย่อย
  </button>
</h6>

<?php if($res && $res->num_rows>0): ?>
<div class="table-responsive">
<table class="table table-bordered table-sm">
<thead class="table-secondary">
<tr><th>ชื่อสายย่อย</th><th>ระยะทาง</th></tr>
</thead>
<tbody>
<?php while($row=$res->fetch_assoc()): 
    $distance = (float)$row['distance_km'];
    $time = isset($row['total_time']) ? (int)$row['total_time'] : 0;
?>
<tr>
  <td><?= htmlspecialchars($row['route_name_th']) ?></td>
  <td><?= $distance ?> กม.</td>
</tr>
<?php 
    // รวมระยะทางและเวลาเข้ากับสายหลัก
    $total_distance_main += $distance;
    $total_time_main += $time;
endwhile; ?>
</tbody>

</table>
</div>
<?php else: ?>
<p class="text-muted">ยังไม่มีสายย่อย</p>
<?php endif; ?>
