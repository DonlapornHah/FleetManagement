<?php 
include 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ดึงข้อมูลภูมิภาค (bus_zone) สำหรับ dropdown
$zones = [];
$sql_zone = "SELECT bz_id, bz_name_th FROM bus_zone ORDER BY bz_name_th
";
$result_zone = mysqli_query($conn, $sql_zone);
while ($row = mysqli_fetch_assoc($result_zone)) {
    $zones[$row['bz_id']] = $row['bz_name_th'];
}

// รับค่าภาคที่เลือกจาก GET
$selected_zone = isset($_GET['zone']) && is_numeric($_GET['zone']) ? (int)$_GET['zone'] : null;

// ดึงข้อมูลสายรถ (กรองตาม zone ถ้ามี)
$route_ids = [];
$route_names = [];
$sql_routes = "
    SELECT 
        br.br_id, 
        CONCAT(lo_start.locat_name_th, ' - ', lo_end.locat_name_th) AS route_name
    FROM bus_routes br
    LEFT JOIN location lo_start ON br.br_start = lo_start.locat_id
    LEFT JOIN location lo_end ON br.br_end = lo_end.locat_id
";
if ($selected_zone) {
    $sql_routes .= " WHERE br.bz_id = $selected_zone ";
}
$sql_routes .= " ORDER BY route_name ASC";
$result_routes = mysqli_query($conn, $sql_routes);
while ($row = mysqli_fetch_assoc($result_routes)) {
    $route_ids[] = $row['br_id'];
    $route_names[$row['br_id']] = $row['route_name'];
    $route_name_to_id = array_flip($route_names);

    
}

// ดึงข้อมูล queue_request ตามสายที่ได้
$request = [];
if (!empty($route_ids)) {
    $sql_request = "SELECT * FROM queue_request WHERE br_id IN (" . implode(',', $route_ids) . ") ORDER BY br_id";
    $result_request = mysqli_query($conn, $sql_request);
    while ($row = mysqli_fetch_assoc($result_request)) {
        $qr_request = json_decode($row['qr_request'], true);
        $br_id = $row['br_id'];
        $request[$br_id]['request'] = $qr_request['request'] ?? [];
        $request[$br_id]['reserve'] = $qr_request['reserve'] ?? [];
        $request[$br_id]['time'] = $qr_request['time'] ?? [];
        $request[$br_id]['time_plus'] = $qr_request['time_plus'] ?? [];
        $request[$br_id]['point'] = $qr_request['point'] ?? [];
        $request[$br_id]['ex'] = $qr_request['ex'] ?? [];
    }
}

// ดึงข้อมูลจุดพัก
$point = [];
$sql_point = "
    SELECT 
        brk_in_route.br_id AS br_id,
        brk_in_route.bir_time AS bir_time,
        brk_in_route.brkp_id AS brkp_id,
        break_point.brkp_name AS brkp_name,
        brk_in_route.bir_type AS brkp_type,
        brk_in_route.bir_status AS brkp_status
    FROM brk_in_route
    LEFT JOIN break_point ON brk_in_route.brkp_id = break_point.brkp_id
";
$result_point = mysqli_query($conn, $sql_point);
while ($row = mysqli_fetch_assoc($result_point)) {
    $point[$row['br_id']][] = [
        'id' => $row['brkp_id'],
        'name' => $row['brkp_name'],
        'time' => $row['bir_time'],
        'status' => $row['brkp_status'],
        'type' => $row['brkp_type']
    ];
}

$all_routes = $route_names;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการคิวมาตรฐาน</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <head>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons (โหลดแค่ครั้งเดียว) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Font Awesome (ถ้าจะใช้) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <!-- Choices.js CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
  <link rel="stylesheet" href="styles.css"/>
</head>
</head>
 <style>
      /* ปุ่มเลือกทั้งหมด / ล้างทั้งหมด */
      #btn-group {
        display: flex !important;
        flex-direction: row !important;
        gap: 10px;
        margin-bottom: 8px;
      }
      #btn-group button {
        flex-shrink: 0; /* ป้องกันปุ่มยืดหรือย่อ */
      }

      /* ให้ช่อง input, select ในฟอร์มนี้สูงเท่ากัน */
      #filter-form .form-control,
      #filter-form .form-select {
        height: 40px;
      }

      /* ให้ select หลายตัว มี scroll และความสูงไม่เกิน */
      #route-select {
        max-height: 160px !important;
        overflow-y: auto !important;
      }

      /* กำหนดให้ col-md-* มี display flex และจัดเรียงแนวตั้ง */
      #filter-form .col-md-3,
      #filter-form .col-md-6 {
        display: flex;
        flex-direction: column;
      }

      /* ให้ label อยู่เหนือ input/select และเว้นระยะ */
      #filter-form label {
        margin-bottom: 6px;
      }

    </style>
</head>
<body class="sidebar-collapsed">

<div class="d-flex">
  <!-- Sidebar -->
  <div id="sidebar" class="sidebar collapsed p-3">
    <button class="btn btn-sm mb-3 align-self-end" onclick="toggleSidebar()">
      <i class="bi bi-list"></i>
    </button>
    <a href="#" class="nav-link"><i class="bi bi-house-door"></i><span class="nav-text">หน้าหลัก</span></a>
    <a href="#" class="nav-link"><i class="bi bi-gear"></i><span class="nav-text">แผนการเดินรถ(การขาย)</span></a>
    <a href="#" class="nav-link"><i class="bi bi-bus-front"></i><span class="nav-text">จัดการรถ</span></a>
    <a href="#" class="nav-link"><i class="bi bi-person-badge"></i><span class="nav-text">พนักงาน</span></a>
    <a href="#" class="nav-link"><i class="bi bi-clock-history"></i><span class="nav-text">รายงานและประวัติ</span></a>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1">
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm px-4 mb-0">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topbarNav" aria-controls="topbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topbarNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex align-items-center">
            <li class="nav-item d-flex align-items-center me-3">
  <a href="index.php"> <!-- หรือเปลี่ยนเป็นหน้าหลักที่คุณต้องการ -->
    <img src="https://img5.pic.in.th/file/secure-sv1/752440-01-removebg-preview.png" alt="Logo"
         style="width: 100px; height: auto; user-select: none;" />
  </a>
</li>

            <li class="nav-item"><a class="nav-link" href="manageQueue.php">จัดคิวการเดินรถ</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="planDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                จัดการแผนการเดินรถ
              </a>
              <ul class="dropdown-menu shadow rounded-3" aria-labelledby="personnelDropdown">
                <li><a class="dropdown-item" href="planQueue.php"><i class="bi bi-calendar-check-fill me-2"></i>แผนเดินรถ</a></li>
<li><a class="dropdown-item" href="simpleQueue.php"><i class="bi bi-list-check me-2"></i>จัดการคิวมาตรฐาน</a></li>
<li><a class="dropdown-item" href="sale_request.php"><i class="bi bi-person-lines-fill me-2"></i>จัดการแผนเดินรถ (ฝ่ายขาย)</a></li>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="manageCar.php">จัดการรถ</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="personnelDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                จัดการบุคลากร
              </a>
              <ul class="dropdown-menu shadow rounded-3" aria-labelledby="personnelDropdown">
                <li><a class="dropdown-item" href="manageDriver.php"><i class="bi bi-person-vcard me-2"></i>พนักงานขับรถ</a></li>
                <li><a class="dropdown-item" href="manageAssist.php"><i class="bi bi-person-plus me-2"></i>พนักงานขับรถเสริม</a></li>
                <li><a class="dropdown-item" href="manageCoach.php"><i class="bi bi-people-fill me-2"></i>พนักงานบริการ</a></li>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link" href="report.php">รายงานและประวัติ</a></li>
          </ul>
          <span class="navbar-text text-muted" id="datetime"></span>
        </div>
      </div>
    </nav>

    <div class="container-fluid px-4 pt-4">
  <h4 class="text-center fw-bold py-3 mb-4 text-white" style="background-color: #16325cff; border-radius: 0.5rem;">
    จัดการคิวมาตรฐาน
  </h4>

<form method="get" id="filter-form">
    <div class="row g-4">
      <!-- ซ้าย -->
      <div class="col-md-4 left-column">
        <div class="card h-100">
          <div class="card-header fw-bold">เลือกภูมิภาค และ สายเดินรถ</div>
          <div class="card-body">
            <div class="mb-3">
              <label for="zone-select" class="form-label">เลือกภูมิภาค:</label>
              <select id="zone-select" name="zone" class="form-select" onchange="document.getElementById('filter-form').submit()">
                <option value="">-- เลือกทั้งหมด --</option>
                <?php foreach ($zones as $bz_id => $bz_name): ?>
                  <option value="<?= $bz_id ?>" <?= ($bz_id == $selected_zone) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($bz_name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-2 d-flex justify-content-between align-items-center">
              <label class="form-label mb-0">สายเดินรถ:</label>
              <div class="d-flex gap-2">
                <button type="button" id="select-all-routes" class="btn btn-sm btn-outline-primary">เลือกทั้งหมด</button>
                <button type="button" id="clear-all-routes" class="btn btn-sm btn-outline-secondary">ล้างทั้งหมด</button>
              </div>
            </div>

            <select id="route-select" name="routes[]" multiple class="form-select choices-multiple">
              <?php foreach ($all_routes as $br_id => $route_name): ?>
                <option value="<?= $br_id ?>" <?= in_array($br_id, $selected_routes) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($route_name) ?>
                </option>
              <?php endforeach; ?>
              </select>
          </div>
        </div>
      </div>

      <!-- ขวา -->
      <div class="col-md-8">
          <div class="card-body" id="request-tables">
            <?php if (empty($queue_data)): ?>
              <div class="alert alert-warning text-center">ยังไม่มีคิวรถมาตรฐาน</div>
            <?php else: ?>
              <?= $rendered_tables_html ?>
            <?php endif; ?>
      </div>
    </div>
  </form>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
  const routeNames = <?= json_encode($route_names, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script>
 function updateDateTime() {
  const now = new Date();
  const options = { timeZone: 'Asia/Bangkok', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute:'2-digit' };
  document.getElementById('datetime').textContent = now.toLocaleString('th-TH', options);
}
setInterval(updateDateTime, 1000);
updateDateTime();

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  document.body.classList.toggle('sidebar-collapsed');
  sidebar.classList.toggle('collapsed');
}
 
// แปลงข้อมูล request จาก PHP เป็น object ฝั่ง JS
const request = <?php echo json_encode($request); ?>;
const point = <?php echo json_encode($point); ?>;

// ========================
// เพิ่มตัวแปรเก็บสายที่เลือก
// ========================
let allRoutes = <?php echo json_encode($all_routes); ?>;
let selectedRoutes = []; // เริ่มต้นไม่มีสายถูกเลือก

document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('route-select');
    const choices = new Choices(selectElement, {
        removeItemButton: true,
        placeholder: true,
        placeholderValue: 'เลือกสาย...',
        searchPlaceholderValue: 'ค้นหาสาย...',
        shouldSort: false
    });

    choices.removeActiveItems();

    selectElement.addEventListener('change', function() {
        selectedRoutes = Array.from(selectElement.selectedOptions).map(opt => opt.value);
        renderTables();
    });

    document.getElementById('select-all-routes').addEventListener('click', function() {
        const allRouteValues = Object.keys(allRoutes);
        selectedRoutes = allRouteValues;
        choices.setChoiceByValue(allRouteValues);
        renderTables();
    });

    document.getElementById('clear-all-routes').addEventListener('click', function() {
        selectedRoutes = [];
        choices.removeActiveItems();
        renderTables();
    });
});

// ========================
// ฟังก์ชันสำหรับสร้างตัวเลือกทั้งหมดใน select (dropdown) ของแต่ละสาย
// ========================
function getAllCodeOptions(request) {
    const groupMap = {};
    Object.entries(request).forEach(([br_id, obj]) => {
        if (!groupMap[br_id]) groupMap[br_id] = [];
        const reqArr = obj.request || [];
        for (let i = 0; i < reqArr.length; i++) {
            let code = (i === reqArr.length - 1) ? `${br_id}-3-last` : `${br_id}-3-${i + 1}`;
            groupMap[br_id].push({ value: code, label: code });
        }
        const reserveArr = obj.reserve || [];
        for (let i = 0; i < reserveArr.length; i++) {
            let code = `${br_id}-1-${i + 1}`;
            groupMap[br_id].push({ value: code, label: code });
        }
    });
    // เพิ่มตัวเลือก '0' ใน optgroup "อื่นๆ"
    groupMap['อื่นๆ'] = [
        { value: '0', label: '0' },
        { value: '1', label: '1' },
        { value: '2', label: '2' }
    ];
    return groupMap;
}
// routeOptions จะถูกสร้างใหม่ทุกครั้งใน renderTables()

// ========================
// ฟังก์ชันสำหรับสร้าง select dropdown ในแต่ละ cell ของตาราง
// ========================
function createSelect(name, selected, routeOptions, br_id, type, idx, isDup) {
    // ถ้ามี isDup จะเพิ่ม class is-invalid เพื่อแสดง error
    let html = `<select name="${name}" class="form-select${isDup ? ' is-invalid' : ''}" onchange="onQueueChange('${br_id}','${type}',${idx},this)">`;
    // วนลูป optgroup (แต่ละสาย)
    Object.entries(routeOptions).forEach(([group, opts]) => {
        html += `<optgroup label="${group}">`;
        for (const opt of opts) {
            html += `<option value="${opt.value}" ${opt.value === selected ? 'selected' : ''}>${opt.label}</option>`;
        }
        html += `</optgroup>`;
    });
    html += '</select>';
    // ถ้ามีซ้ำและไม่ได้เลือก '0' ให้แสดงข้อความ error
    if (isDup && selected && selected !== '0') {
        html += '<div class="invalid-feedback">ซ้ำ</div>';
    }
    return html;
}

// ========================
// ฟังก์ชันเมื่อมีการเปลี่ยนค่า select (queue) ในแต่ละ cell
// ========================
function onQueueChange(br_id, type, idx, selectElem) {
    const newValue = selectElem.value;
    // อัปเดตข้อมูลใน request object
    request[br_id][type][idx] = newValue;
    // render ตารางใหม่เพื่ออัปเดต UI และตรวจสอบซ้ำ
    renderTables();
}

function onTimeChange(br_id, idx, inputElem) {
    if (!request[br_id].time) request[br_id].time = [];
    request[br_id].time[idx] = inputElem.value;
}

function onTimePlusChange(br_id, idx, inputElem) {
    if (!request[br_id].time_plus) request[br_id].time_plus[idx] = inputElem.value;
}

// ========================
// ฟังก์ชันสร้าง select จุดรับส่ง (multiple)
// ========================
function createPointSelect(br_id, idx, selectedPoints) {
    const pts = point[br_id] || [];
    let html = `<select class="form-select" multiple onchange="onPointChange('${br_id}',${idx},this)">`;
    pts.forEach((pt, i) => {
        const val = i.toString();
        const selected = selectedPoints && selectedPoints.includes(val) ? 'selected' : '';
        html += `<option value="${val}" ${selected}>${pt.name} (${pt.time} นาที)</option>`;
    });
    html += '</select>';
    return html;
}

// ========================
// ฟังก์ชันสร้าง checklist จุดรับส่ง (checkbox)
// ========================
function createPointChecklist(br_id, idx, selectedPoints) {
    const pts = point[br_id] || [];
    let html = `<div class="d-flex flex-wrap gap-2">`;
    pts.forEach((pt, i) => {
        const val = i.toString();
        const checked = selectedPoints && selectedPoints.includes(val) ? 'checked' : '';
        html += `<div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="point_${br_id}_${idx}_${i}" value="${val}" ${checked}
                onchange="onPointChecklistChange('${br_id}',${idx},this)">
            <label class="form-check-label" for="point_${br_id}_${idx}_${i}">${pt.name} (${pt.time} นาที)</label>
        </div>`;
    });
    html += `</div>`;
    return html;
}

// ========================
// เมื่อเลือก/ยกเลิก checklist จุดรับส่ง
// ========================
function onPointChecklistChange(br_id, idx, checkboxElem) {
    if (!request[br_id].point) request[br_id].point = [];
    let selected = request[br_id].point[idx] || [];
    if (!Array.isArray(selected)) selected = [];
    const val = checkboxElem.value;
    if (checkboxElem.checked) {
        if (!selected.includes(val)) selected.push(val);
    } else {
        selected = selected.filter(v => v !== val);
    }
    request[br_id].point[idx] = selected;
    // คำนวณเวลารวม
    let total = 0;
    (selected || []).forEach(val => {
        const pt = point[br_id][parseInt(val)];
        if (pt) total += parseInt(pt.time);
    });
    if (!request[br_id].time_plus) request[br_id].time_plus = [];
    request[br_id].time_plus[idx] = total.toString();
    // อัปเดต input number
    const input = document.querySelector(`input[name="time_plus[${br_id}][]"][data-idx="${idx}"]`);
    if (input) input.value = total;
    // อัปเดต hidden input
    const hidden = document.querySelector(`input[name="point[${br_id}][]"][data-idx="${idx}"]`);
    if (hidden) hidden.value = selected.join(',');
}

// ========================
// ฟังก์ชันสร้างปุ่มเปิด modal เลือกจุดรับส่ง (Checklist Popup)
// ========================
function createPointChecklistPopup(br_id, idx, selectedPoints) {
    const pts = point[br_id] || [];
    const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
    // รวม selectedPoints กับ requiredPoints แบบ unique
    let mergedSelected = Array.isArray(selectedPoints) ? [...selectedPoints] : [];
    mergedSelected = Array.from(new Set([...mergedSelected, ...requiredPoints]));

    // ปรับข้อความบนปุ่มให้เหมาะสม (ตรวจสอบว่าเลือกครบจริงหรือไม่)
    let label = '';
    if (mergedSelected.length === 0) {
        label = 'เลือกจุดรับส่ง';
    } else if (
        pts.length > 0 &&
        pts.every(pt => mergedSelected.includes(pt.id.toString()))
    ) {
        label = 'เลือกครบทุกจุด';
    } else if (mergedSelected.length === 1) {
        const pt = pts.find(pt => pt.id.toString() === mergedSelected[0]);
        label = pt ? pt.name : (pts[0] ? pts[0].name : 'เลือกจุดรับส่ง');
    } else {
        const firstPt = pts.find(pt => pt.id.toString() === mergedSelected[0]);
        const firstName = firstPt ? firstPt.name : (pts[0] ? pts[0].name : '');
        label = `${firstName} และอีก ${mergedSelected.length - 1} จุด`;
    }

    let html = `
        <button type="button" class="btn btn-outline-primary btn-sm w-100 text-truncate" data-bs-toggle="modal" data-bs-target="#pointModal_${br_id}_${idx}">
            ${label}
        </button>
        <input type="hidden" name="point[${br_id}][]" value="${Array.from(new Set(mergedSelected)).join(',')}" data-idx="${idx}">
        <!-- Modal -->
        <div class="modal fade" id="pointModal_${br_id}_${idx}" tabindex="-1" aria-labelledby="pointModalLabel_${br_id}_${idx}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="pointModalLabel_${br_id}_${idx}">เลือกจุดรับส่ง (Route ${br_id} ลำดับ ${idx+1})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-2 d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAllPoints('${br_id}',${idx})">เลือกทั้งหมด</button>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllPoints('${br_id}',${idx})">ล้างการเลือก</button>
                </div>
                <div class="d-flex flex-wrap gap-2">
    `;
    pts.forEach((pt) => {
        const val = pt.id.toString();
        const checked = mergedSelected.includes(val) ? 'checked' : '';
        const disabled = pt.status == 1 ? 'disabled' : '';
        html += `<div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="point_${br_id}_${idx}_${val}" value="${val}" ${checked} ${disabled}
                onchange="onPointChecklistPopupChange('${br_id}',${idx},this)">
            <label class="form-check-label" for="point_${br_id}_${idx}_${val}">${pt.name} (${pt.time} นาที)${pt.status == 1 ? ' <span class="text-danger">*</span>' : ''}</label>
        </div>`;
    });
    html += `
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="confirmPointChecklistPopup('${br_id}',${idx})">ยืนยัน</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
              </div>
            </div>
          </div>
        </div>
    `;
    return html;
}


// ========================
// เลือกจุดรับส่งทั้งหมด
// ========================
function selectAllPoints(br_id, idx) {
    const pts = point[br_id] || [];
    const allVals = pts.map(pt => pt.id.toString());
    request[br_id].point = request[br_id].point || [];
    request[br_id].point[idx] = allVals;
    // อัปเดต checkbox ทั้งหมดใน modal
    allVals.forEach(val => {
        const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
        if (cb && !cb.disabled) cb.checked = true;
    });
    // คำนวณเวลารวม
    let total = pts.reduce((sum, pt) => sum + parseInt(pt.time), 0);
    if (!request[br_id].time_plus) request[br_id].time_plus = [];
    request[br_id].time_plus[idx] = total.toString();
    const input = document.querySelector(`input[name="time_plus[${br_id}][]"][data-idx="${idx}"]`);
    if (input) input.value = total;
    const hidden = document.querySelector(`input[name="point[${br_id}][]"][data-idx="${idx}"]`);
    if (hidden) hidden.value = allVals.join(',');
}

function clearAllPoints(br_id, idx) {
    const pts = point[br_id] || [];
    const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
    request[br_id].point = request[br_id].point || [];
    // กำหนดเฉพาะ requiredPoints แบบ unique
    request[br_id].point[idx] = Array.from(new Set(requiredPoints));
    // อัปเดต checkbox ทั้งหมดใน modal
    pts.forEach((pt) => {
        const val = pt.id.toString();
        const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
        if (cb) cb.checked = requiredPoints.includes(val);
    });
    // อัปเดตเวลาเดินทาง
    let total = 0;
    requiredPoints.forEach(val => {
        const pt = pts.find(pt => pt.id.toString() === val);
        if (pt) total += parseInt(pt.time);
    });
    if (!request[br_id].time_plus) request[br_id].time_plus = [];
    request[br_id].time_plus[idx] = total.toString();
    const input = document.querySelector(`input[name="time_plus[${br_id}][]"][data-idx="${idx}"]`);
    if (input) input.value = total;
    const hidden = document.querySelector(`input[name="point[${br_id}][]"][data-idx="${idx}"]`);
    if (hidden) hidden.value = Array.from(new Set(requiredPoints)).join(',');
}

// ========================
// เมื่อเลือก/ยกเลิก checklist จุดรับส่งใน popup (ไม่ปิด modal ทันที)
// ========================
function onPointChecklistPopupChange(br_id, idx, checkboxElem) {
    if (!request[br_id].point) request[br_id].point = [];
    let selected = request[br_id].point[idx] || [];
    if (!Array.isArray(selected)) selected = [];
    const val = checkboxElem.value;
    const pts = point[br_id] || [];
    const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
    if (checkboxElem.disabled) return;
    if (checkboxElem.checked) {
        if (!selected.includes(val)) selected.push(val);
    } else {
        selected = selected.filter(v => v !== val);
    }
    // รวม requiredPoints แบบ unique
    selected = Array.from(new Set([...selected, ...requiredPoints]));
    request[br_id].point[idx] = selected;
    // คำนวณเวลารวม
    let total = 0;
    (selected || []).forEach(val => {
        const pt = pts.find(pt => pt.id.toString() === val);
        if (pt) total += parseInt(pt.time);
    });
    if (!request[br_id].time_plus) request[br_id].time_plus = [];
    request[br_id].time_plus[idx] = total.toString();
    // อัปเดต input number
    const input = document.querySelector(`input[name="time_plus[${br_id}][]"][data-idx="${idx}"]`);
    if (input) input.value = total;
    // อัปเดต hidden input
    const hidden = document.querySelector(`input[name="point[${br_id}][]"][data-idx="${idx}"]`);
    if (hidden) hidden.value = Array.from(new Set(selected)).join(',');
}

// ========================
// เมื่อกดปุ่ม "ยืนยัน" ใน modal checklist จุดรับส่ง
// ========================
function confirmPointChecklistPopup(br_id, idx) {
    // ปิด modal
    const modal = bootstrap.Modal.getInstance(document.getElementById(`pointModal_${br_id}_${idx}`));
    if (modal) modal.hide();
    // อัปเดตข้อมูลใน request object จาก checkbox ที่เลือกใน modal
    const pts = point[br_id] || [];
    const requiredPoints = pts.filter(pt => pt.status == 1).map(pt => pt.id.toString());
    let selected = [];
    pts.forEach((pt) => {
        const val = pt.id.toString();
        const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
        if (cb && cb.checked) selected.push(val);
    });
    // รวม requiredPoints แบบ unique
    selected = Array.from(new Set([...selected, ...requiredPoints]));
    request[br_id].point[idx] = selected;
    // คำนวณเวลารวมใหม่
    let total = 0;
    selected.forEach(val => {
        const pt = pts.find(pt => pt.id.toString() === val);
        if (pt) total += parseInt(pt.time);
    });
    if (!request[br_id].time_plus) request[br_id].time_plus = [];
    request[br_id].time_plus[idx] = total.toString();
    // อัปเดต input number
    const input = document.querySelector(`input[name="time_plus[${br_id}][]"][data-idx="${idx}"]`);
    if (input) input.value = total;
    // อัปเดต hidden input
    const hidden = document.querySelector(`input[name="point[${br_id}][]"][data-idx="${idx}"]`);
    if (hidden) hidden.value = Array.from(new Set(selected)).join(',');
    // อัปเดตป้าย label บนปุ่ม
    setTimeout(() => renderTables(), 0);
}


// ฟังก์ชัน normalize point ให้เป็น array ของ string id ที่ไม่ซ้ำ
function normalizePointData() {
    Object.entries(request).forEach(([br_id, obj]) => {
        if (!obj.point) obj.point = [];
        obj.point = obj.point.map(p => {
            if (typeof p === 'string') {
                // แปลง string "1,2,3" เป็น array
                p = p.split(',').map(x => x.trim()).filter(x => x !== '');
            }
            if (!Array.isArray(p)) p = [];
            // บังคับ unique และเป็น string
            return Array.from(new Set(p.map(x => x.toString())));
        });
    });
}

// ฟังก์ชัน normalize ex ให้เป็น array ของ object {start1:"", end1:"", start2:"", end2:""} (string id)
function normalizeExData() {
    Object.entries(request).forEach(([br_id, obj]) => {
        if (!obj.ex) obj.ex = [];
        obj.ex = obj.ex.map(e => {
            // ถ้า e เป็น array (แบบเก่า) หรือไม่ใช่ object ให้แปลงเป็น object ที่มี string
            if (!e || typeof e !== 'object' || Array.isArray(e)) {
                e = {start1:"", end1:"", start2:"", end2:""};
            }
            // ถ้าเป็น array ให้เอา index 0 หรือ "" (รองรับโครงสร้างเก่า)
            const getVal = v => Array.isArray(v) ? (v[0] !== undefined ? v[0].toString() : "") : (v !== undefined ? v.toString() : "");
            e.start1 = getVal(e.start1);
            e.end1 = getVal(e.end1);
            e.start2 = getVal(e.start2);
            e.end2 = getVal(e.end2);
            return e;
        });
    });
}

// ฟังก์ชันสร้าง select จุดจอดขึ้น/ลง สำหรับ ex driver (single-select, แยกคนที่ 1/2)
function createExPointSelect(br_id, idx, selected, type, person) {
    // type: 'start' or 'end', person: 1 or 2
    // เลือกเฉพาะ point ที่ type == 2
    const pts = (point[br_id] || []).filter(pt => pt.type == 2);
    let html = `<select class="form-select" name="ex_${type}${person}[${br_id}][]" data-idx="${idx}" onchange="onExPointChange('${br_id}',${idx},this,'${type}${person}')">`;
    html += `<option value="">- ไม่เลือก -</option>`;
    pts.forEach((pt) => {
        const val = pt.id.toString();
        // selected เป็น string id
        const isSelected = (selected === val) ? 'selected' : '';
        html += `<option value="${val}" ${isSelected}>${pt.name}</option>`;
    });
    html += `</select>`;
    return html;
}

// ฟังก์ชันเมื่อเลือก ex จุดจอดขึ้น/ลง (single-select)
function onExPointChange(br_id, idx, selectElem, type) {
    if (!request[br_id].ex) request[br_id].ex = [];
    if (!request[br_id].ex[idx]) request[br_id].ex[idx] = {start1:"", end1:"", start2:"", end2:""};
    const val = selectElem.value;
    request[br_id].ex[idx][type] = val ? val : "";
}


// เพิ่มฟังก์ชันควบคุมชื่อแผนตามประเภทแผน
function onPlanTypeChange(br_id) {
    const standardRadio = document.getElementById('plan_type_standard_' + br_id);
    const planNameInput = document.getElementById('plan_name_' + br_id);
    if (standardRadio && standardRadio.checked) {
        planNameInput.value = 'คิวมาตรฐานของสาย ' + br_id;
        planNameInput.readOnly = true;
        planNameInput.classList.remove('is-invalid');
    } else {
        planNameInput.value = '';
        planNameInput.readOnly = false;
    }
}

// เรียก onPlanTypeChange ทุกครั้งหลัง renderTables
function renderTables() {
    normalizePointData();
    normalizeExData();
    const container = document.getElementById('request-tables');
    const routeOptions = getAllCodeOptions(request);

    // หา code ที่ซ้ำกันในทุกสาย (request + reserve)
    let allSelected = [];
    let codeLocation = {};
    Object.entries(request).forEach(([br_id, obj]) => {
        (obj.request || []).forEach((qr_request, idx) => {
            if (qr_request && qr_request !== '0') {
                allSelected.push(qr_request);
                if (!codeLocation[qr_request]) codeLocation[qr_request] = [];
                codeLocation[qr_request].push(`Route ${br_id} - Request ลำดับ ${idx + 1}`);
            }
        });
        (obj.reserve || []).forEach((qr_request, idx) => {
            if (qr_request && qr_request !== '0') {
                allSelected.push(qr_request);
                if (!codeLocation[qr_request]) codeLocation[qr_request] = [];
                codeLocation[qr_request].push(`Route ${br_id} - Reserve ลำดับ ${idx + 1}`);
            }
        });
    });
    let seen = new Set();
    let duplicateCodes = new Set();
    allSelected.forEach(code => {
        if (code === '1' || code === '2') return;
        if (seen.has(code)) duplicateCodes.add(code);
        else seen.add(code);
    });

    let html = '';
    html += `<div id="dup-alert"></div>`;
    html += `<form method='post' action='request_db.php' id='all-route-form' >`;

    // วนลูปแต่ละสาย (br_id)
    Object.entries(request).forEach(([br_id, obj]) => {
        if (!selectedRoutes.includes(br_id)) return; // แสดงเฉพาะสายที่เลือก
const routeNames = <?php echo json_encode($route_names, JSON_UNESCAPED_UNICODE); ?>;

        // --- ปุ่ม toggle สำหรับเลือกสถานะการบันทึก (มาตรฐาน/แผนที่บันทึกไว้) ---
        html += `<div class="card mb-4 ">
            <div class="card-header d-flex justify-content-between align-items-center" 
     style="background-color: #d6d6d6ff; color: black;">
    
    <h6 class="mb-0">สายเดินรถ : ${routeNames[br_id] || br_id}</h6>

    <div class="btn-group btn-group-sm" role="group" aria-label="plan type toggle align-items-center">
        <input type="radio" class="btn-check" 
               name="plan_type[${br_id}]" 
               id="plan_type_standard_${br_id}" 
               value="standard" 
               autocomplete="off" 
               checked 
               onchange="onPlanTypeChange('${br_id}')">
        <label class="btn btn-outline-dark" for="plan_type_standard_${br_id}">
            แผนมาตรฐาน
        </label>

        <input type="radio" class="btn-check" 
               name="plan_type[${br_id}]" 
               id="plan_type_special_${br_id}" 
               value="special" 
               autocomplete="off" 
               onchange="onPlanTypeChange('${br_id}')">
        <label class="btn btn-outline-dark" for="plan_type_special_${br_id}">
            แผนที่บันทึกไว้
        </label>
    </div>
</div>

            <div class="card-body">
                <div class="mb-3">
                    <label for="plan_name_${routeNames[br_id] || br_id}" class="form-label"><b>ชื่อแผนสำหรับสายนี้ :</b></label>
                    <input type="text" class="form-control" name="plan_name[${routeNames[br_id] || br_id}]" id="plan_name_${br_id}" placeholder="ระบุชื่อแผน (เช่น แผนเดินรถรอบเช้า)" >
                </div>
                <b>Request</b>
                <div class="table-responsive">
                <table class="table table-bordered align-middle table-sm">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">ลำดับ</th>
                            <th class="text-center">รหัสประจำคิว</th>
                            <th class="text-center">queue request</th>
                            <th class="text-center">เวลา</th>
                            <th class="text-center">เวลาเดินทาง (นาที)</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-${br_id}-request">`;
        // วนลูปแต่ละแถวของ request
        const reqArr = obj.request || [];
        const timeArr = obj.time || [];
        const timePlusArr = obj.time_plus || [];
        const pointArr = obj.point || [];
        const exArr = obj.ex || [];
        reqArr.forEach((qr_request, idx) => {
            let code = (idx === reqArr.length - 1) ? `${br_id}-3-last` : `${br_id}-3-${idx + 1}`;
            const isDup = duplicateCodes.has(qr_request) && qr_request !== '0';
            const timeVal = timeArr[idx] || '';
            const timePlusVal = timePlusArr[idx] || '0';
            const selectedPoints = pointArr[idx] || [];
            // --- ex driver fields ---
            const exObj = exArr[idx] || {start1:[], end1:[], start2:[], end2:[]};
            html += `<tr>
    <td class="text-center align-middle" rowspan="2">${idx + 1}</td>
    <td class="text-center">${code}</td>
    <td>${createSelect(`request[${br_id}][]`, qr_request, routeOptions, br_id, 'request', idx, isDup)}</td>
    <td><input type="time" class="form-control" name="time[${br_id}][]" value="${timeVal}" onchange="onTimeChange('${br_id}', ${idx}, this)"></td>
    <td>
        ${createPointChecklistPopup(br_id, idx, selectedPoints)}
        <input type="number" class="form-control mt-1" name="time_plus[${br_id}][]" value="${timePlusVal}" data-idx="${idx}" readonly>
    </td>
    <td class="align-item-center">
        <div class="btn-group btn-group-sm" role="group">
            <button type='button' class="btn btn-outline-secondary align-item-center" onclick="insertRow('${br_id}','request',${idx},'before')">แทรกก่อน</button>
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'after')">แทรกหลัง</button>
            <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','request',${idx})">ลบ</button>
        </div>
    </td>
</tr>
<tr>
    <td colspan="5" class="align-middle">
        <div class="row g-2 align-items-center">
            <div class="col-auto text-end ">จุดจอดขึ้น (ex driver คนที่ 1):</div>
            <div class="col">${createExPointSelect(br_id, idx, exObj.start1, 'start', 1)}</div>
            <div class="col-auto text-end ">จุดจอดลง (ex driver คนที่ 1):</div>
            <div class="col">${createExPointSelect(br_id, idx, exObj.end1, 'end', 1)}</div>
        </div>
        <div class="row g-2 align-items-center mt-2">
            <div class="col-auto text-end ">จุดจอดขึ้น (ex driver คนที่ 2):</div>
            <div class="col">${createExPointSelect(br_id, idx, exObj.start2, 'start', 2)}</div>
            <div class="col-auto text-end ">จุดจอดลง (ex driver คนที่ 2):</div>
            <div class="col">${createExPointSelect(br_id, idx, exObj.end2, 'end', 2)}</div>
        </div>
    </td>
</tr>


`;
        });
        // แถวสำหรับเพิ่มข้อมูลใหม่ (request)
        html += `<tr>
            <td>ใหม่</td>
            <td>${br_id}-3-ใหม่</td>
            <td></td>
            <td></td>
            <td></td>
            <td><button type='button' class="btn btn-success btn-sm" onclick="insertRow('${br_id}','request',${reqArr.length-1},'after')">เพิ่ม</button></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="5"></td>
        </tr>`;
        // ========================
        // ส่วนของ Reserve
        html += `
                <div class="table-responsive">
                <table class="table table-bordered align-middle table-sm">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">ลำดับ</th>
                            <th class="text-center">รหัสประจำคิว</th>
                            <th class="text-center">Queue reserve</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-${br_id}-reserve">`;
        // วนลูปแต่ละแถวของ reserve
        const reserveArr = obj.reserve || [];
        reserveArr.forEach((qr_reserve, idx) => {
            let code = `${br_id}-1-${idx + 1}`;
            const isDup = duplicateCodes.has(qr_reserve) && qr_reserve !== '0';
            html += `<tr>
                <td>${idx + 1}</td>
                <td>${code}</td>
                <td>${createSelect(`reserve[${br_id}][]`, qr_reserve, routeOptions, br_id, 'reserve', idx, isDup)}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','reserve',${idx},'before')">แทรกก่อน</button>
                        <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','reserve',${idx},'after')">แทรกหลัง</button>
                        <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','reserve',${idx})">ลบ</button>
                    </div>
                </td>
            </tr>`;
        });
      
        // แถวสำหรับเพิ่มข้อมูลใหม่ (reserve)
        html += `<tr>
            <td>ใหม่</td>
            <td>${br_id}-1-ใหม่</td>
            <td></td>
            <td><button type='button' class="btn btn-success btn-sm" onclick="insertRow('${br_id}','reserve',${reserveArr.length-1},'after')">เพิ่ม</button></td>
        </tr>`;
        html += '</tbody></table></div>';
        html += '</div></div>';
    });
    // ปุ่มบันทึกข้อมูลทั้งหมด
    html += `<div class='my-3'><button type='submit' class="btn btn-primary btn-lg w-100" id="submit-btn">บันทึกทั้งหมด</button></div>`;
    html += `</form>`;
    container.innerHTML = html;

    // ปิดปุ่ม submit ถ้ามีซ้ำ
    setTimeout(() => {
        const btn = document.getElementById('submit-btn');
        if (btn) btn.disabled = duplicateCodes.size > 0;
    }, 0);

    // --- sync checkbox modal กับ selectedPoints ทุกครั้งที่ modal เปิด ---
    Object.entries(request).forEach(([br_id, obj]) => {
        const reqArr = obj.request || [];
        const pointArr = obj.point || [];
        reqArr.forEach((_, idx) => {
            const modalId = `pointModal_${br_id}_${idx}`;
            const modalElem = document.getElementById(modalId);
            if (modalElem) {
                // ลบ event เดิมก่อน (ป้องกันซ้อน)
                modalElem.removeEventListener('shown.bs.modal', modalElem._syncPointsListener || (()=>{}));
                // สร้าง event ใหม่
                const syncPointsListener = function() {
                    // selectedPoints เป็น array ของ id (string)
                    const selectedPoints = (pointArr[idx] || []).map(String);
                    const pts = point[br_id] || [];
                    pts.forEach((pt) => {
                        const val = pt.id.toString();
                        const cb = document.getElementById(`point_${br_id}_${idx}_${val}`);
                        if (cb) cb.checked = selectedPoints.includes(val);
                    });
                };
                modalElem.addEventListener('shown.bs.modal', syncPointsListener);
                modalElem._syncPointsListener = syncPointsListener;
            }
        });
    });

    // เรียก onPlanTypeChange สำหรับทุกสายหลัง render
    setTimeout(() => {
        Object.keys(request).forEach(br_id => {
            if (!selectedRoutes.includes(br_id)) return;
            onPlanTypeChange(br_id);
        });
    }, 0);

    // ถ้าไม่มีสายถูกเลือก ให้แสดงข้อความ
    if (selectedRoutes.length === 0) {
        container.innerHTML = `<div class="alert alert-info">กรุณาเลือกสายที่ต้องการจัดการ</div>`;
        return;
    }
}

// ========================
// ฟังก์ชันลบแถว (request/reserve) ตามสายและ index ที่เลือก
// ========================
function removeRow(br_id, type, idx) {
    let arr = request[br_id][type] || [];
    if (arr.length > 0) {
        arr.splice(idx, 1);
        if (type === 'request') {
            let timeArr = request[br_id].time || [];
            timeArr.splice(idx, 1);
            let timePlusArr = request[br_id].time_plus || [];
            timePlusArr.splice(idx, 1);
            let pointArr = request[br_id].point || [];
            pointArr.splice(idx, 1);
        }
        renderTables();
    }
}
// ========================
// ฟังก์ชันเพิ่มแถวใหม่ (request/reserve) ก่อนหรือหลัง index ที่เลือก
// ========================
function insertRow(br_id, type, idx, pos) {
    let arr = request[br_id][type] || [];
    let insertIdx = pos === 'before' ? idx : idx + 1;
    arr.splice(insertIdx, 0, '0');
    if (type === 'request') {
        if (!request[br_id].time) request[br_id].time = [];
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        const currentTime = now.toISOString().slice(11, 16);
        request[br_id].time.splice(insertIdx, 0, currentTime);
        if (!request[br_id].time_plus) request[br_id].time_plus = [];
        request[br_id].time_plus.splice(insertIdx, 0, '0');
        if (!request[br_id].point) request[br_id].point = [];
        request[br_id].point.splice(insertIdx, 0, []);
    }
    renderTables();
    setTimeout(() => {
        const tbody = document.getElementById(`tbody-${br_id}-${type}`);
        if (tbody && tbody.children[insertIdx]) {
            tbody.children[insertIdx].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 100);
}

// ========================
// เรียก renderTables() ครั้งแรกเมื่อโหลดหน้า
// ========================
renderTables();

// ========================
// ดัก submit ฟอร์มรวมทุกสายเพื่อป้องกันการส่งถ้ามีซ้ำ
// ========================
document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'all-route-form') {
        normalizePointData();
        // ลบ input ของแถว "ใหม่" (index สุดท้าย) ออกจากฟอร์มก่อน submit
        Object.entries(request).forEach(([br_id, obj]) => {
            const reqArr = obj.request || [];
            const form = e.target;
            // ลบ input[name="time[br_id][]"] ของแถวใหม่
            let inputs = form.querySelectorAll(`input[name="time[${br_id}][]"]`);
            if (inputs.length > reqArr.length) {
                inputs[inputs.length - 1].remove();
            }
            // ลบ input[name="time_plus[br_id][]"] ของแถวใหม่
            inputs = form.querySelectorAll(`input[name="time_plus[${br_id}][]"]`);
            if (inputs.length > reqArr.length) {
                inputs[inputs.length - 1].remove();
            }
            // ลบ input[name="point[br_id][]"] ของแถวใหม่
            inputs = form.querySelectorAll(`input[name="point[${br_id}][]"]`);
            if (inputs.length > reqArr.length) {
                inputs[inputs.length - 1].remove();
            }
        });
        // ตรวจสอบการซ้ำอีกครั้งหลังลบแถวใหม่
        let allSelected = [];
        let codeLocation = {};
        Object.entries(request).forEach(([br_id, obj]) => {
            (obj.request || []).forEach((qr_request, idx) => {
                allSelected.push(qr_request);
                if (!codeLocation[qr_request]) codeLocation[qr_request] = [];
                codeLocation[qr_request].push(`Route ${br_id} - Request ลำดับ ${idx + 1}`);
            });
            (obj.reserve || []).forEach((qr_request, idx) => {
                allSelected.push(qr_request);
                if (!codeLocation[qr_request]) codeLocation[qr_request] = [];
                codeLocation[qr_request].push(`Route ${br_id} - Reserve ลำดับ ${idx + 1}`);
            });
        });
        let seen = new Set();
        let duplicateCodes = new Set();
        let hasZero = false;
        allSelected.forEach(code => {
            if (code === '0') hasZero = true;
            if (code === '1' || code === '2' || code === '0') return;
            if (seen.has(code)) duplicateCodes.add(code);
            else seen.add(code);
        });
        if (duplicateCodes.size > 0 || hasZero) {
            e.preventDefault();
            let msg = '';
            if (hasZero) {
                msg = 'พบตัวเลือกที่ยังเป็น 0 กรุณาเปลี่ยนก่อนบันทึก';
            } else {
                msg = 'พบการเลือก queue ซ้ำกัน:\n';
                duplicateCodes.forEach(code => {
                    msg += `- ${code} : ${codeLocation[code].join(', ')}\n`;
                });
            }
            alert(msg);
            return false;
        }
    }
});

// ปรับตรวจสอบชื่อแผนก่อน submit ให้รองรับ readonly
document.addEventListener('submit', function(e) {
    if (e.target && e.target.id === 'all-route-form') {
        // ตรวจสอบชื่อแผน
        let missingPlanName = false;
        let missingPlanNameRoutes = [];
        Object.entries(request).forEach(([br_id, obj]) => {
            if (!selectedRoutes.includes(br_id)) return;
            const planNameInput = document.getElementById('plan_name_' + br_id);
            const standardRadio = document.getElementById('plan_type_standard_' + br_id);
            if (standardRadio && standardRadio.checked) {
                // ถ้าเป็นมาตรฐาน ต้องเป็นชื่อที่ fix เท่านั้น
                if (!planNameInput || planNameInput.value !== 'คิวมาตรฐานของสาย ' + routeNames[br_id]) {
                    missingPlanName = true;
                    missingPlanNameRoutes.push(br_id);
                    if (planNameInput) planNameInput.classList.add('is-invalid');
                } else {
                    planNameInput.classList.remove('is-invalid');
                }
            } else {
                // ถ้าเป็นแผนที่บันทึกไว้ ต้องไม่ว่าง
                if (!planNameInput || !planNameInput.value.trim()) {
                    missingPlanName = true;
                    missingPlanNameRoutes.push(br_id);
                    if (planNameInput) planNameInput.classList.add('is-invalid');
                } else {
                    planNameInput.classList.remove('is-invalid');
                }
            }
        });
        if (missingPlanName) {
            e.preventDefault();
            alert('กรุณาระบุชื่อแผนสำหรับสาย: ' + missingPlanNameRoutes.join(', '));
            return false;
        }
    }
});
</script>


