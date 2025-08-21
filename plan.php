<?php
include 'config.php';

// --------------------------
// รับค่าจาก filter (เลขสายรถ)
// --------------------------
$routeFilter = '';
if(!empty($_GET['route'])){
    $route_number = $conn->real_escape_string($_GET['route']);
    $routeFilter = " AND p.route_number = '$route_number' ";
}

// --------------------------
// ดึงข้อมูลสายรถทั้งหมด สำหรับ dropdown
// --------------------------
$routes_result = $conn->query("SELECT route_number, route_name_th FROM route ORDER BY route_number ASC");
$all_routes_pool = [];
if($routes_result && $routes_result->num_rows > 0){
    while($r = $routes_result->fetch_assoc()){
        $all_routes_pool[] = $r;
    }
}

// --------------------------
// SQL ดึงแผนการเดินรถ
// --------------------------
// query plan
$sql = "
SELECT 
    p.*,
    r.route_name_th
FROM plan_route_wide p
LEFT JOIN route r ON p.route_number = r.route_number
WHERE 1=1
$routeFilter
ORDER BY p.plan_id ASC
";
$result = $conn->query($sql);

// เก็บผลลัพธ์
$plans = [];
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $plans[] = $row;
    }
}





// --------------------------
// รายชื่อจุดจอด
// --------------------------
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

// --------------------------
// จุดจอดบังคับ
// --------------------------
$mandatoryStops = ['stop_bkk_station', 'stop_wangnoi', 'stop_lamtakong_change', 'stop_kk3_station'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แผนการเดินรถสาย</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
<style>
/* Sidebar */
.sidebar { width: 250px; transition: width 0.3s; height: 100vh; position: fixed; top: 0; left: 0; background-color: #151616; color: #fff; overflow-x: hidden; padding-top: 1rem; z-index: 1000; }
.sidebar.collapsed { width: 60px; }
.sidebar .nav-link { color: #fff; display: flex; align-items: center; white-space: nowrap; }
.sidebar .nav-link i { margin-right: 10px; font-size: 1.2rem; width: 20px; text-align: center; }
.sidebar.collapsed .nav-link span { display: none; }
.sidebar .nav-link:hover { background-color: #495057; }
.sidebar .sidebar-toggle { cursor: pointer; color: #fff; padding: 0.5rem 1rem; text-align: center; border-radius: 10px; display: flex; flex-direction: column; align-items: center; }
.sidebar .sidebar-toggle:hover { background-color: #495057; }
.sidebar-logo img { width: 46px; height: 46px; object-fit: cover; border-radius: 10px; margin-bottom: 5px; }

/* Content */
.content-wrapper { margin-left: 250px; transition: margin-left 0.3s; padding: 2rem; margin-top: 50px; }
.content-wrapper.collapsed { margin-left: 60px; }

/* Filter bar */
#dateFilterBar { position: fixed; top: 0; left: 250px; width: calc(100% - 250px); background-color: #ecececff; border-bottom: 1px solid #ccc; z-index: 1050; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: left 0.3s, width 0.3s; }
.sidebar.collapsed + #dateFilterBar { left: 60px; width: calc(100% - 60px); }

/* Table */
thead th { font-weight: 200; text-align: center; padding: 15px 23px; min-width: 120px; white-space: nowrap; font-size: 14px; }
table th, table td { text-align: center; padding: 10px 15px; font-size: 14px; }
.table-responsive { width: 100%; overflow-x: auto; }
.step-container {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin: 30px 0;
}
.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1;
    text-align: center;
}
.step-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background-color: #007bff;
  color: white;
  display: flex;
  flex-direction: column;   /* เรียงจากบนลงล่าง */
  align-items: center;
  justify-content: center;
  font-size: 14px;
  position: relative;
  z-index: 1;
}

.step-icon {
  font-size: 16px; /* ขนาด emoji */
  line-height: 1;
}

.step-number {
  font-size: 18px; /* ขนาดเลข */
  line-height: 1;
}
.step-label {
    margin-top: 8px;
    font-size: 14px;
}
.step::before {
    content: '';
    position: absolute;
    top: 20px; /* ครึ่งของ circle */
    left: 0;
    width: 100%;
    height: 6px;
    background-color: #dcdcdc;
    z-index: 0;
}
.step:first-child::before { left: 50%; width: 50%; }
.step:last-child::before { width: 50%; }
.step.active .step-circle { background-color: #0d6efd; }
.step.active::before { background-color: #0d6efd; }
table tbody tr:hover {
    background-color: #f2f2f2;  /* สีพื้นหลังตอน hover */
    cursor: pointer;             /* เปลี่ยน cursor เป็น pointer */
}
</style>
</head>
<body>

<!-- Sidebar --> 
<div class="sidebar collapsed" id="sidebar">
    <div class="sidebar-toggle mb-3" id="toggleSidebar">
        <a href="#" class="sidebar-logo">
          <img src="https://img2.pic.in.th/pic/unnamed-1d3fa7687b93ead9f.md.jpg" alt="Logo" />
        </a>
        <i class="bi bi-list"></i>
    </div>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-speedometer2"></i> <span>แดชบอร์ด</span></a></li>
      <li class="nav-item"><a class="nav-link" href="plan.php"><i class="bi bi-calendar2-week"></i> <span>แผนการเดินรถ</span></a></li>
      <li class="nav-item"><a class="nav-link" href="managebus.php"><i class="bi bi-bus-front"></i> <span>จัดการรถประจำสาย</span></a></li>
      <li class="nav-item"><a class="nav-link" href="manage_routes.php"><i class="bi bi-diagram-3"></i> <span>จัดการสายเดินรถ</span></a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-people"></i> <span>พนักงาน</span></a></li>
      <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-gear"></i> <span>ตั้งค่า</span></a></li>
    </ul>
</div>

<!-- Filter bar -->
<div id="dateFilterBar">
  <div class="container-fluid d-flex align-items-center justify-content-between py-2 flex-nowrap">

    <!-- ซ้าย: ชื่อแผน + dropdown + วันที่ + ปุ่มกรอง -->
    <div class="d-flex align-items-center" style="gap: 0.5rem; min-width: 0; flex-wrap: nowrap;">
      <p class="mb-0 px-3 py-1 rounded" style="background-color: #d4d4d4ff; white-space: nowrap;">แผนการเดินรถ</p>

      <label class="form-label mb-0 ms-3" style="white-space: nowrap;">สายเดินรถ :</label>
      <select id="routeSelect" class="form-select form-select-sm" style="width: 200px; white-space: nowrap;">
          <option value="">-- เลือกสาย --</option>
          <?php foreach($all_routes_pool as $route): ?>
              <option value="<?= $route['route_number'] ?>" <?= (!empty($_GET['route']) && $_GET['route']==$route['route_number'])?'selected':'' ?>>
                  <?= $route['route_name_th'] ?>
              </option>
          <?php endforeach; ?>
      </select>

      <label class="form-label mb-0 ms-2" style="white-space: nowrap;">วันที่:</label>
      <input type="date" id="planDate" class="form-control form-control-sm" style="width: 150px;">

      <button id="filterBtn" class="btn btn-primary btn-sm ms-2">
        <i class="fas fa-filter"></i>&nbsp;กรอง
      </button>
    </div>

    <!-- ขวา: ปุ่มบันทึกและเพิ่มแผน -->
    <div class="d-flex align-items-center gap-2 flex-nowrap">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#saveAllPlansModal">
            <i class="fas fa-floppy-disk"></i>&nbsp;บันทึกแผนทั้งหมด
        </button>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPlanModal">
            + เพิ่มแผน
        </button>
    </div>

  </div>
</div>



<!-- Main content -->
<div class="content-wrapper collapsed p-0" id="mainContent">
   
    <div class="table-responsive p-0">
    <table class="table table-bordered table-striped table-sm">
        <thead class="table-dark">
        <tr class="text-center">
            <th>ลำดับ</th>
            <th>ชื่อแผน</th>
            <th>เลขสายเดินรถ</th>
            <th>เวลาคิวรถ</th>
            <th>ประเภทแผน</th>
            <th>จุดส่งพ่วง 1</th>
            <th>จุดส่งพ่วง 2</th>
            <th>ระยะทางทั้งหมด</th>
            <th>ระยะเวลาทั้งหมด</th>
            <th>สถานะอนุมัติ</th>
            <th>จัดการ</th>
        </tr>
        </thead>
        <tbody>
<?php if(count($plans) > 0): ?>
    <?php $i = 1; // ตัวนับลำดับ ?>
    <?php foreach($plans as $row): ?>
        <tr class="text-center">
            <td><?= $i++ ?></td> <!-- ลำดับเรียงต่อเนื่อง -->
            <td class="drop-target" ondrop="drop(event)" ondragover="allowDrop(event)">
                <a href="#" class="plan-link text-decoration-underline" 
                   data-bs-toggle="modal" 
                   data-bs-target="#processModal<?= $row['plan_id'] ?>" 
                   style="cursor:pointer;">
                   <?= htmlspecialchars($row['plan_name']) ?>
                </a>
            </td>
            <td><?= htmlspecialchars($row['route_number']) ?></td>
            <td><?= htmlspecialchars($row['quetime']) ?></td>
            <td><?= htmlspecialchars($row['plan_type']) ?></td>
            <td><?= htmlspecialchars($row['junction1']) ?></td>
            <td><?= htmlspecialchars($row['junction2']) ?></td>
            <td><?= htmlspecialchars($row['total_distance']) ?></td>
            <td><?= htmlspecialchars($row['total_time']) ?></td>
            <td><?= $row['approved']==1 ? '✅':'❌' ?></td>
            <td>
    <button type="button" class="btn btn-warning btn-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#modalStop<?= $row['plan_id'] ?>">
        <i class="bi bi-gear-fill"></i> จัดการ
    </button>
    <a href="delete_plan.php?plan_id=<?= $row['plan_id'] ?>" 
       class="btn btn-danger btn-sm"
       onclick="return confirm('ยืนยันลบแผน <?= htmlspecialchars($row['plan_name']) ?> ?');">
        <i class="bi bi-trash-fill"></i> ลบ
    </a>
</td>

        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="11" class="text-center">ไม่มีข้อมูล</td></tr>
<?php endif; ?>
</tbody>

    </table>
</div>
</div>
<!-- Modal confirm บันทึกแผนทั้งหมด -->
<div class="modal fade" id="saveAllPlansModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">บันทึกแผนทั้งหมด</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="saveAllPlansForm" method="post" action="save_all_plans.php">
    <div class="mb-3">
        <label class="form-label">เลือกช่วงวันที่ที่จะใช้แผนนี้</label>
        <div class="d-flex gap-2">
            <input type="date" name="start_date" class="form-control" required>
            <span class="align-self-center">ถึง</span>
            <input type="date" name="end_date" class="form-control" required>
        </div>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="only_today" id="onlyToday">
        <label class="form-check-label" for="onlyToday">เฉพาะวันนี้</label>
    </div>
</form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="button" class="btn btn-success" id="confirmSaveAll">บันทึก</button>
</div>
</div>
</div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
  <div id="saveAllToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        บันทึกเรียบร้อย
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<!-- Modal แก้ไขแผน + จัดการจุดจอด -->
<?php foreach($plans as $row): ?>
<div class="modal fade" id="modalStop<?= $row['plan_id'] ?>" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">แก้ไขแผนและจุดจอด - <?= $row['plan_name'] ?></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form method="post" action="update_stop_wide.php">
<input type="hidden" name="plan_id" value="<?= $row['plan_id'] ?>">

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label">ชื่อแผน</label>
        <input type="text" name="plan_name" class="form-control" value="<?= htmlspecialchars($row['plan_name']) ?>" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">เลขสายเดินรถ</label>
        <input type="text" name="route_number" class="form-control" value="<?= htmlspecialchars($row['route_number']) ?>" readonly>
    </div>
    <div class="col-md-3">
        <label class="form-label">เวลาคิวรถ</label>
        <input type="time" name="quetime" class="form-control" value="<?= $row['quetime'] ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">ประเภทแผน</label>
        <select name="plan_type" class="form-select" required>
            <option value="แผนมาตรฐาน" <?= $row['plan_type']=='แผนมาตรฐาน'?'selected':'' ?>>แผนมาตรฐาน</option>
            <option value="แผนเสริม" <?= $row['plan_type']=='แผนเสริม'?'selected':'' ?>>แผนเสริม</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">จุดส่งพ่วง 1</label>
        <input type="text" name="junction1" class="form-control" value="<?= htmlspecialchars($row['junction1']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">จุดส่งพ่วง 2</label>
        <input type="text" name="junction2" class="form-control" value="<?= htmlspecialchars($row['junction2']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">ระยะทางทั้งหมด</label>
        <input type="number" name="total_distance" class="form-control" step="0.1" value="<?= $row['total_distance'] ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">ระยะเวลาทั้งหมด</label>
        <input type="text" name="total_time" class="form-control" value="<?= $row['total_time'] ?>">
    </div>
    
</div>

<hr>
<h6>จัดการจุดจอด</h6>
<table class="table table-bordered table-sm">
<thead>
<tr><th class="table-secondary">จุดจอด</th><th class="table-secondary">เลือก</th></tr>
</thead>
<tbody>
<?php
foreach($stops as $col => $name){
    $isMandatory = in_array($col, $mandatoryStops);
    $checked = ($row[$col]=='1' || $isMandatory) ? "checked" : "";
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
<button type="submit" class="btn btn-success">บันทึก</button>
</div>
</form>
</div>
</div>
</div>
</div>
<?php endforeach; ?>

<!-- Modal เพิ่มแผน -->
<div class="modal fade" id="addPlanModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
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
<label class="form-label">เวลาคิวรถ</label>
<input type="time" name="quetime" class="form-control">
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
<input type="text" name="junction1" class="form-control">
</div>
<div class="col-md-3">
<label class="form-label">จุดส่งพ่วง 2</label>
<input type="text" name="junction2" class="form-control">
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
<tr><th class="bg-secondary text-white">จุดจอด</th><th class="bg-secondary text-white">เลือก</th></tr>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle sidebar
const sidebar = document.getElementById('sidebar');
const content = document.getElementById('mainContent');
document.getElementById('toggleSidebar').addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    content.classList.toggle('collapsed');
});

// Filter
document.getElementById('filterBtn').addEventListener('click', () => {
    const route = document.getElementById('routeSelect').value;
    const start = document.getElementById('filterStartDate').value;
    const end = document.getElementById('filterEndDate').value;

    let params = new URLSearchParams();
    if(route) params.append('route', route);
    if(start) params.append('start', start);
    if(end) params.append('end', end);

    window.location.href = window.location.pathname + '?' + params.toString();
});

document.getElementById('filterBtn').addEventListener('click', function() {
    const route = document.getElementById('routeSelect').value;
    const date = document.getElementById('planDate').value;

    let url = 'plan.php?';
    if(route) url += 'route=' + encodeURIComponent(route) + '&';
    if(date) url += 'date=' + encodeURIComponent(date);

    window.location.href = url;
});

document.getElementById('confirmSaveAll').addEventListener('click', function() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const onlyToday = document.getElementById('onlyToday').checked;

    let message = "คุณแน่ใจว่าจะบันทึกแผนทั้งหมดใช่หรือไม่?\n";
    if(onlyToday){
        const today = new Date().toISOString().split('T')[0];
        message += `วันที่: ${today} (วันนี้)`;
    } else {
        message += `วันที่: ${startDate} ถึง ${endDate}`;
    }

    if(confirm(message)){
        document.getElementById('saveAllPlansForm').submit();
    }
});
</script>
</body>
</html>
