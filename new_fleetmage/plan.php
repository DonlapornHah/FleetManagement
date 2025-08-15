<?php
include 'config.php';

// ดึงข้อมูลแผนทั้งหมด เรียงตาม plan_id
$sql = "SELECT p.*, r.route_name_th
        FROM plan_route_wide p
        LEFT JOIN route r ON p.route_number = r.route_id
        ORDER BY p.plan_id ASC";
$result = $conn->query($sql);


// รายชื่อจุดจอด
$stops = [
    'stop_bkk_station' => 'กรุงเทพ สถานีเดินรถนครชัยแอร์',
    'stop_bkk_mochit' => 'กรุงเทพหมอชิต',
    'stop_rangsit' => 'รังสิต สถานีเดินรถนครชัยแอร์',
    'stop_wangnoi' => 'จุดปั๊มใบเวลา วังน้อย',
    'stop_prademchai' => 'ร้านอาหารประเดิมชัย',
    'stop_lamtakong_pump' => 'ลำตะคอง (จุดปั๊มใบเวลา)',
    'stop_police_khlongphai' => 'ป้อมตร.ทางหลวงคลองไผ่',
    'stop_lamtakong_change' => 'ลำตะคอง (จุดเปลี่ยนพ่วง)',
    'stop_banmittraphap_change' => 'บ้านกลางมิตรภาพ (จุดเปลี่ยนพ่วง)',
    'stop_lanphakdi' => 'ลานภักดี',
    'stop_korat_bus_station' => 'สถานีขนส่ง นครราชสีมา',
    'stop_bansom' => 'บ้านส้ม',
    'stop_taladkae' => 'ตลาดแค',
    'stop_nontaether' => 'โนนตาเถร',
    'stop_police_sida' => 'ป้อมตำรวจทงหลวง สีดา',
    'stop_sida' => 'สีดา',
    'stop_sida_station' => 'สีดา สถานีเดินรถนครชัยแอร์',
    'stop_bualai' => 'บัวลาย',
    'stop_police_amphoe_phon' => 'ป้อมตำรวจภูธร อำเภอพล',
    'stop_muangphon' => 'เมืองพล',
    'stop_amphoe_phon_station' => 'สถานีขนส่ง อำเภอพล',
    'stop_nonsila' => 'โนนศิลา',
    'stop_banphai' => 'บ้านไผ่',
    'stop_banphai_station' => 'สถานีขนส่ง บ้านไผ่',
    'stop_bankeng' => 'บ้านเกิ้ง',
    'stop_meechai_center' => 'ศูนย์มีชัย',
    'stop_banhed' => 'บ้านแฮด',
    'stop_sirindhorn_hospital' => 'รพ.สิรินธร',
    'stop_dongklang' => 'ดงกลาง',
    'stop_nongbuadee' => 'หนองบัวดีหมี',
    'stop_thapra' => 'ท่าพระ',
    'stop_bankudkwang' => 'บ้านกุดกว้าง',
    'stop_khonkaen_airstation' => 'สถานีขนส่ง ปรับอากาศขอนแก่น',
    'stop_jaerongsri' => 'แยกเจริญศรี',
    'stop_mtec' => 'ม.เทคโนภาค',
    'stop_kk3_station' => 'สถานีขนส่งขอนแก่นแห่งที่3'
];

// จุดจอดบังคับ
$mandatoryStops = ['stop_bkk_station', 'stop_wangnoi', 'stop_lamtakong_change', 'stop_kk3_station'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แผนการเดินรถสาย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<style>
thead th {
    font-weight: 200;      
    text-align: center;    
    padding: 15px 23px;    
    min-width: 120px;     
    white-space: nowrap;   
    font-size: 14px; 
}
table th, table td {
    text-align: center;    
    padding: 10px 15px;    
    font-size: 14px;        
}
</style>
<body>
  <?php include 'index.php'; ?>
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>แผนการเดินรถ สาย <?= htmlspecialchars($route_name) ?></h5>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPlanModal">
            + เพิ่มแผน
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm">
<thead class="table-dark">
<tr class="text-center ">
<th>ลำดับ</th>
<th>ชื่อแผน</th>
<th>เลขสายเดินรถ</th>
<th>ประเภทแผน</th>
<th>จุดส่งพ่วง 1</th>
<th>จุดส่งพ่วง 2</th>
<th>ระยะทางทั้งหมด</th>
<th>ระยะเวลาทั้งหมด</th>
<th>สถานะอนุมัติ</th>
<th>จัดการจุดจอด</th>
</tr>
</thead>
<tbody>
<?php if($result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()): ?>
<tr class="text-center">
<td><?= $row['plan_id'] ?></td>
<td><?= $row['plan_name'] ?></td>
<td><?= $row['route_number'] ?></td>
<td><?= $row['plan_type'] ?></td>
<td><?= $row['junction1'] ?></td>
<td><?= $row['junction2'] ?></td>
<td><?= $row['total_distance'] ?></td>
<td><?= $row['total_time'] ?></td>
<td>
    <?= $row['approved'] == 1 ? '✅' : '❌' ?>
</td>
<td>
    <!-- ปุ่มแก้ไขจุดจอด -->
    <button type="button" class="btn btn-primary btn-sm" 
        data-bs-toggle="modal" 
        data-bs-target="#modalStop<?= $row['plan_id'] ?>">
        จัดการ
    </button>

    <!-- ปุ่มลบ -->
    <a href="delete_plan.php?plan_id=<?= $row['plan_id'] ?>" 
       class="btn btn-danger btn-sm"
       onclick="return confirm('ยืนยันลบแผน <?= $row['plan_name'] ?> ?');">
       ลบ
    </a>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8" class="text-center">ไม่มีข้อมูล</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>

<!-- Modal แก้ไขจุดจอด -->
<?php
$result->data_seek(0);
while($row = $result->fetch_assoc()):
?>
<div class="modal fade" id="modalStop<?= $row['plan_id'] ?>" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">จัดการจุดจอด - แผน <?= $row['plan_name'] ?></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<!-- Form จุดจอด -->
<form id="formStop<?= $row['plan_id'] ?>" method="post" action="update_stop_wide.php">
<input type="hidden" name="plan_id" value="<?= $row['plan_id'] ?>">
<table class="table table-bordered table-sm">
<thead>
<tr>
<th class="table-secondary">จุดจอด</th>
<th class="table-secondary">เลือก</th>
</tr>
</thead>
<tbody>
<?php
foreach($stops as $col => $name){
    $isMandatory = in_array($col, $mandatoryStops);
    $checked = ($row[$col] === '1') ? "checked" : "";
    $disabled = $isMandatory ? "disabled" : "";
    echo "<tr>";
    echo "<td>$name" . ($isMandatory ? " <span class='text-danger'> (บังคับจอด)</span>" : "") . "</td>";
    echo "<td><input type='checkbox' name='selected_stop[]' value='$col' $checked $disabled></td>";
    echo "</tr>";
}
foreach($mandatoryStops as $mStop){
    echo "<input type='hidden' name='selected_stop[]' value='$mStop'>";
}
?>
</tbody>
</table>

<div class="d-flex justify-content-end">

    <!-- ปุ่มบันทึกจุดจอด -->
    <button type="submit" form="formStop<?= $row['plan_id'] ?>" class="btn btn-success">บันทึก</button>
</div>
</form>

</div>
</div>
</div>
</div>
<?php endwhile; ?>

<!-- Modal เพิ่มแผน -->
<div class="modal fade" id="addPlanModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content ">
<div class="modal-header  ">
<h5 class="modal-title">เพิ่มแผนการเดินรถ</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form method="post" action="insert_plan_route_wide.php">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">ชื่อแผน</label>
<input type="text" name="plan_name" class="form-control" required>
</div>
<div class="col-md-3">
<label class="form-label">เลขสายเดินรถ</label>
<input type="text" name="route_number" class="form-control" required>
</div>
<div class="col-md-3">
<label class="form-label">ประเภทแผน</label>
<select name="plan_type" class="form-select" required>
    <option value="">-- เลือกประเภทแผน --</option>
    <option value="แผนมาตรฐาน">แผนมาตรฐาน</option>
    <option value="แผนเสริม">แผนเสริม</option>
</select>
</div>
<div class="col-md-3">
    <label class="form-label">จุดส่งพ่วง 1</label>
    <input type="text" name="send_point1" class="form-control">
</div>
<div class="col-md-3">
    <label class="form-label">จุดส่งพ่วง 2</label>
    <input type="text" name="send_point2" class="form-control">
</div>
<div class="col-md-3">
<label class="form-label">ระยะทางทั้งหมด</label>
<input type="number" name="total_distance" class="form-control" step="0.1">
</div>
<div class="col-md-3">
<label class="form-label">ระยะเวลาทั้งหมด</label>
<input type="text" name="total_time" class="form-control">
</div>
</div>
<hr>
<h6>เลือกจุดจอด</h6>
<table class="table table-bordered table-sm">
<thead>
<tr ><th class="bg-secondary text-white">จุดจอด</th><th class="bg-secondary text-white">เลือก</th></tr>
</thead>
<tbody>
<?php
foreach($stops as $col => $name){
    $isMandatory = in_array($col, $mandatoryStops);
    $checked = $isMandatory ? "checked" : "";
    $disabled = $isMandatory ? "disabled" : "";
    echo "<tr>";
    echo "<td>$name" . ($isMandatory ? " <span class='text-danger'> (บังคับจอด)</span>" : "") . "</td>";
    echo "<td><input type='checkbox' name='selected_stop[]' value='$col' $checked $disabled></td>";
    echo "</tr>";
}
foreach($mandatoryStops as $mStop){
    echo "<input type='hidden' name='selected_stop[]' value='$mStop'>";
}
?>
</tbody>
</table>
<div class="text-end">
<button type="submit" class="btn btn-success">บันทึกแผนใหม่</button>
</div>
</form>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
