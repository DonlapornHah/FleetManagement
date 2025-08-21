<?php
include 'config.php';

// --------------------------
// รับค่าจาก filter (เลขสายรถ)
// --------------------------
$routeFilter = '';
if (!empty($_GET['route'])) {
    $route_number = $conn->real_escape_string($_GET['route']);
    $routeFilter = " AND p.route_number = '$route_number' ";
}

// --------------------------
// ดึงข้อมูลสายรถทั้งหมด สำหรับ dropdown
// --------------------------
$routes_result = $conn->query("SELECT route_number, route_name_th FROM route ORDER BY route_number ASC");
$all_routes_pool = [];
if ($routes_result && $routes_result->num_rows > 0) {
    while ($r = $routes_result->fetch_assoc()) {
        $all_routes_pool[] = $r;
    }
}

// --------------------------
// ดึงแผนการเดินรถ
// --------------------------
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

$plans = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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

// --------------------------
// ข้อมูลจุดจอดของแต่ละ plan จาก DB
// --------------------------
$planStops = [];
foreach ($plans as $p) {
    $planStops[$p['plan_id']] = [];
    foreach ($stops as $key => $name) {
        $planStops[$p['plan_id']][$key] = isset($p[$key]) ? intval($p[$key]) : 0;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดแผนการเดินรถ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- ใช้ flatpickr สำหรับ date range -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
#planCards { display: flex; flex-wrap: wrap; gap:10px; margin-bottom:20px; }
.plan-card { border:1px solid #ccc; border-radius:8px; padding:10px; background:#f8f9fa; cursor:grab; }
.plan-card:hover { box-shadow:0 2px 8px rgba(0,0,0,0.2); }
.table tbody tr.dragging { background-color:#e2f0d9; }
/* ไฮไลต์แถวเมื่อ hover */
#dropTable tbody tr:hover {
    background-color: #fff3cd;  /* สีอ่อน ๆ เหลือง */
    cursor: pointer;
    transition: background-color 0.2s;
}


</style>
</head>
<body class="p-4">

<h5>แผนทั้งหมด</h5>

<!-- Filter -->
<form method="get" class="d-flex align-items-end mb-3 w-100">

  <!-- ซ้าย: Dropdown เลือกสายเดินรถ + ปุ่มกรอง -->
  <div class="d-flex gap-2 align-items-end">
    <div>
      <label class="form-label">สายเดินรถ:</label>
      <select name="route" class="form-select form-select-sm">
        <option value="">-- ทุกสาย --</option>
        <?php foreach($all_routes_pool as $r): ?>
        <option value="<?= $r['route_number'] ?>" <?= (!empty($_GET['route']) && $_GET['route']==$r['route_number'])?'selected':'' ?>>
          <?= $r['route_name_th'] ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm align-self-end">กรอง</button>
  </div>

  <!-- ขวา: ปุ่มเพิ่มแผนรถ -->
  <div class="ms-auto">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPlanModal">เพิ่มแผนรถ</button>
  </div>
</form>

<!-- CardBox แสดงแผน -->
<div id="planCards">
<?php foreach($plans as $p): ?>
<div class="plan-card"
data-id="<?= $p['plan_id'] ?>"
data-name="<?= htmlspecialchars($p['plan_name']) ?>"
data-route="<?= htmlspecialchars($p['route_number']) ?>"
data-type="<?= htmlspecialchars($p['plan_type']) ?>"
data-distance="<?= htmlspecialchars($p['total_distance']) ?>"
data-duration="<?= htmlspecialchars($p['total_time']) ?>"
data-junction1="<?= htmlspecialchars($p['junction1'] ?? '') ?>"
data-junction2="<?= htmlspecialchars($p['junction2'] ?? '') ?>"
data-stops='<?= json_encode($planStops[$p['plan_id']]) ?>'>
<strong><?= htmlspecialchars($p['plan_name']) ?></strong><br>
สาย: <?= htmlspecialchars($p['route_number']) ?><br>
ประเภท: <?= htmlspecialchars($p['plan_type']) ?>
</div>
<?php endforeach; ?>
</div>

<hr>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">ตารางแผนที่เลือก</h4>
  <button class="btn btn-primary" id="saveAllBtn">บันทึกทั้งหมด</button>
</div>

<div id="tableContainer">
<table class="table table-bordered" id="dropTable">
<thead class="table-dark text-center">
<tr>
<th>เวลาเดินรถ</th>
<th>ชื่อแผน</th>
<th>เลขสาย</th>
<th>ประเภท</th>
<th>ระยะทาง</th>
<th>เวลาเดินทาง</th>
<th>Junction 1</th>
<th>Junction 2</th>
<th>จัดการจุดจอด</th>
<th>ลบ</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<!-- Modal เพิ่มแผนใหม่ -->
<div class="modal fade" id="addPlanModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">เพิ่มแผนใหม่</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<form id="addPlanForm">
<div class="row">
<div class="col-md-6 mb-2">
<label>ชื่อแผน</label>
<input type="text" class="form-control" name="plan_name" required>
</div>
<div class="col-md-6 mb-2">
<label>เลขสาย</label>
<select class="form-select" name="route_number" required>
<option value="">-- เลือกสาย --</option>
<?php foreach($all_routes_pool as $r): ?>
<option value="<?= $r['route_number'] ?>"><?= $r['route_name_th'] ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-6 mb-2">
<label>ประเภท</label>
<select class="form-select" name="plan_type" required>
<option value="">-- เลือกประเภทแผน --</option>
<option value="มาตรฐาน">มาตรฐาน</option>
<option value="เสริม">เสริม</option>
</select>
</div>
<div class="col-md-3 mb-2">
<label>ระยะทาง (กม.)</label>
<input type="number" class="form-control" name="total_distance">
</div>
<div class="col-md-3 mb-2">
<label>เวลาเดินทาง</label>
<input type="text" class="form-control" name="total_time">
</div>
<div class="col-md-6 mb-2">
<label>Junction 1</label>
<input type="text" class="form-control" name="junction1">
</div>
<div class="col-md-6 mb-2">
<label>Junction 2</label>
<input type="text" class="form-control" name="junction2">
</div>
</div>

<hr>
<h6>จัดการจุดจอด</h6>
<div class="row" style="max-height:300px; overflow-y:auto;">
<?php 
$mandatoryStops = ['stop_bkk_station', 'stop_wangnoi', 'stop_lamtakong_change', 'stop_kk3_station'];
foreach($stops as $key=>$name): 
$required = in_array($key,$mandatoryStops);
?>
<div class="col-md-4">
<div class="form-check">
<input class="form-check-input stop-checkbox" type="checkbox" 
name="stops[<?= $key ?>]" value="1" id="add_<?= $key ?>" <?= $required?'checked disabled':'' ?>>
<label class="form-check-label" for="add_<?= $key ?>"><?= $name ?> <?= $required?'(บังคับ)':'' ?></label>
</div>
</div>
<?php endforeach; ?>
</div>

</form>
</div>
<div class="modal-footer">
<button class="btn btn-primary" id="saveNewPlanBtn">เพิ่มแผนใหม่</button>
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
</div>
</div>
</div>
</div>


<!-- Offcanvas จัดการจุดจอด -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="stopsOffcanvas">
<div class="offcanvas-header">
<h5 class="offcanvas-title">จัดการจุดจอด</h5>
<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
</div>
<div class="offcanvas-body">
<form id="stopsForm">
<?php foreach($stops as $key => $name): 
$required = in_array($key, $mandatoryStops); ?>
<div class="form-check">
<input class="form-check-input stop-checkbox" type="checkbox"
value="<?= $key ?>" id="<?= $key ?>" data-stop-key="<?= $key ?>" <?= $required?'checked disabled':'' ?>>
<label class="form-check-label" for="<?= $key ?>">
<?= htmlspecialchars($name) ?> <?= $required?'(บังคับ)':'' ?>
</label>
</div>
<?php endforeach; ?>
</form>
</div>
<div class="offcanvas-footer p-3">
<button class="btn btn-primary w-100" id="saveStopsBtn">บันทึก</button>
</div>
</div>

<!-- Modal เลือกวันที่ -->
<div class="modal fade" id="saveDateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">เลือกช่วงวันที่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label>วันที่เริ่มต้น</label>
          <input type="date" id="startDate" class="form-control">
        </div>
        <div class="mb-2">
          <label>วันที่สิ้นสุด</label>
          <input type="date" id="endDate" class="form-control">
        </div>
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="todayOnly">
          <label class="form-check-label" for="todayOnly">ใช้เฉพาะวันนี้</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button class="btn btn-primary" id="confirmSaveBtn">ยืนยันบันทึก</button>
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// ------------------
// ตัวแปรหลัก
// ------------------
const planCards = document.getElementById('planCards');
const tableBody = document.querySelector('#dropTable tbody');
let currentStopsRow = null;
let currentStopsData = {};
const allStopKeys = <?= json_encode(array_keys($stops)) ?>;

// ------------------
// Drag & Drop Cards
// ------------------
new Sortable(planCards, { 
    group: { name:'shared', pull:'clone', put:false },
    sort: false,
    animation: 150
});

// ------------------
// Drag & Drop Table
// ------------------
new Sortable(tableBody, {
    group: { name:'shared', pull:false, put:true },
    animation: 150,
    ghostClass: 'dragging',
    onAdd: function(evt){
        const card = evt.item;

        const stopsObjRaw = JSON.parse(card.dataset.stops || '{}');
        const stopsObj = {};
        allStopKeys.forEach(k => stopsObj[k] = stopsObjRaw[k] || 0);

        const row = document.createElement('tr');
        const selectedCount = Object.values(stopsObj).filter(v=>v==1).length;

        const junction1 = card.dataset.junction1 || '';
        const junction2 = card.dataset.junction2 || '';

        row.dataset.stops = JSON.stringify(stopsObj);
        row.dataset.id = card.dataset.id || '';

        row.innerHTML = `
            <td><input type="time" class="form-control form-control-sm queue-time" value=""></td>
            <td>${card.dataset.name}</td>
            <td>${card.dataset.route}</td>
            <td>${card.dataset.type}</td>
            <td>${card.dataset.distance}</td>
            <td>${card.dataset.duration}</td>
            <td>${junction1}</td>
            <td>${junction2}</td>
            <td><button class="btn btn-info btn-sm manage-stops">จัดการจุดจอด (${selectedCount})</button></td>
            <td><button class="btn btn-danger btn-sm remove-row">ลบ</button></td>
        `;
        tableBody.appendChild(row);

        row.querySelector('.remove-row').addEventListener('click', ()=>row.remove());
        row.querySelector('.manage-stops').addEventListener('click', openStopsOffcanvasFunc);

        evt.item.parentNode.removeChild(evt.item);
    }
});

// ------------------
// Offcanvas จัดการจุดจอด
// ------------------
const stopsOffcanvasEl = document.getElementById('stopsOffcanvas');
const offcanvas = new bootstrap.Offcanvas(stopsOffcanvasEl);

function openStopsOffcanvasFunc(event){
    currentStopsRow = event.target.closest('tr');
    const stopsDataRaw = JSON.parse(currentStopsRow.dataset.stops || '{}');
    const stopsData = {};
    allStopKeys.forEach(k => stopsData[k] = stopsDataRaw[k] || 0);
    currentStopsData = stopsData;

    document.querySelectorAll('#stopsOffcanvas .stop-checkbox').forEach(cb=>{
        const key = cb.dataset.stopKey;
        cb.checked = stopsData[key]==1;
    });

    offcanvas.show();
}

document.getElementById('saveStopsBtn').addEventListener('click', ()=>{
    const checked = {};
    document.querySelectorAll('#stopsOffcanvas .stop-checkbox').forEach(cb=>{
        const key = cb.dataset.stopKey;
        checked[key] = cb.checked ? 1 : 0;
    });
    if(currentStopsRow){
        currentStopsRow.dataset.stops = JSON.stringify(checked);
        const count = Object.values(checked).filter(v=>v==1).length;
        currentStopsRow.querySelector('.manage-stops').innerText = `จัดการจุดจอด (${count})`;
    }
    offcanvas.hide();
});

// ------------------
// เพิ่มแผนใหม่
// ------------------
document.getElementById('saveNewPlanBtn').addEventListener('click', ()=>{
    const form = document.getElementById('addPlanForm');
    const stopsData = {};
    form.querySelectorAll('.stop-checkbox').forEach(cb=>{
        const key = cb.id.replace('add_','');
        stopsData[key] = cb.checked ? 1 : 0;
    });

    const formData = Object.fromEntries(new FormData(form).entries());
    formData.stops = stopsData;

    fetch('add_plan.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify(formData)
    })
    .then(res=>res.json())
    .then(data=>{
        if(!data.success){ alert('เพิ่มแผนไม่สำเร็จ: '+data.message); return; }
        const plan = data.plan;

        const card = document.createElement('div');
        card.className='plan-card';
        card.dataset.id = plan.plan_id;
        card.dataset.name = plan.plan_name;
        card.dataset.route = plan.route_number;
        card.dataset.type = plan.plan_type;
        card.dataset.distance = plan.total_distance;
        card.dataset.duration = plan.total_time;
        card.dataset.junction1 = plan.junction1 || '';
        card.dataset.junction2 = plan.junction2 || '';
        card.dataset.stops = JSON.stringify(plan.stops);
        card.innerHTML = `<strong>${plan.plan_name}</strong> <span class="text-success">(แผนใหม่)</span><br>สาย: ${plan.route_number}<br>ประเภท: ${plan.plan_type}`;
        planCards.prepend(card);

        bootstrap.Modal.getInstance(document.getElementById('addPlanModal')).hide();
        form.reset();
    })
    .catch(err=>{ console.error(err); alert('เกิดข้อผิดพลาดเพิ่มแผน'); });
});

// ------------------
// บันทึกทั้งหมด → เปิด modal
// ------------------
document.getElementById("saveAllBtn").addEventListener("click", function () {
    let modal = new bootstrap.Modal(document.getElementById("saveDateModal"));
    modal.show();
});

// ------------------
// ยืนยันบันทึก → ส่งไป request_db.php
// ------------------
document.getElementById("confirmSaveBtn").addEventListener("click", function () {
    let startDate = document.getElementById("startDate").value;
    let endDate   = document.getElementById("endDate").value;
    let todayOnly = document.getElementById("todayOnly").checked ? 1 : 0;

    if(!startDate){ alert("กรุณาเลือกวันที่เริ่มต้น"); return; }
    if(!endDate){ endDate = startDate; }

    let plans = [];
    document.querySelectorAll("#dropTable tbody tr").forEach(tr=>{
        const timeVal = tr.querySelector(".queue-time")?.value;
        if(!timeVal){ alert("กรุณากรอกเวลาเดินรถให้ครบทุกแถว"); throw new Error("Missing queue-time"); }

        plans.push({
            plan_id: tr.dataset.id,
            name: tr.cells[1].innerText,
            route: tr.cells[2].innerText,
            time: timeVal,
            type: tr.cells[3].innerText,
            distance: tr.cells[4].innerText,
            duration: tr.cells[5].innerText,
            junction1: tr.cells[6].innerText,
            junction2: tr.cells[7].innerText,
            stops: JSON.parse(tr.dataset.stops)
        });
    });

    fetch("request_db.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ plans, startDate, endDate, todayOnly })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            alert("บันทึกแผนทั้งหมดเรียบร้อยแล้ว");
            bootstrap.Modal.getInstance(document.getElementById("saveDateModal")).hide();
            tableBody.innerHTML = ''; // เคลียร์ตารางหลังบันทึก
        } else {
            alert("เกิดข้อผิดพลาด:\n" + data.message);
        }
    })
    .catch(err=>{ console.error(err); alert('เกิดข้อผิดพลาด'); });
});
</script>

</body>
</html>
