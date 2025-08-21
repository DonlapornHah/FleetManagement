<?php
include 'config.php';

// --------------------------
// Filter เลือกสาย
// --------------------------
$routeFilter = '';
if(!empty($_GET['route'])){
    $route_number = $conn->real_escape_string($_GET['route']);
    $routeFilter = " WHERE b.br_id = '$route_number' ";
}

// --------------------------
// ดึงข้อมูลสายรถสำหรับ dropdown
// --------------------------
$routes_result = $conn->query("SELECT route_number, route_name_th FROM route ORDER BY route_number ASC");
$all_routes_pool = [];
if($routes_result && $routes_result->num_rows > 0){
    while($r = $routes_result->fetch_assoc()){
        $all_routes_pool[] = $r;
    }
}

// --------------------------
// ดึงข้อมูลรถพร้อมชื่อสายจริง + ชื่อประเภทรถ
// --------------------------
$sql = "
SELECT 
    b.*, 
    r.route_name_th, 
    t.bt_name,
    CONCAT(d.first_name, ' ', d.last_name) AS main_driver_name
FROM bus_info b
LEFT JOIN route r ON b.br_id = r.route_number
LEFT JOIN bus_type t ON b.bus_type_id = t.bt_id
LEFT JOIN drivers d ON b.main_driver = d.id
$routeFilter
ORDER BY b.bus_id ASC
";

$result = $conn->query($sql);

// เก็บผลลัพธ์
$buses = [];
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $buses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการรถ</title>
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

/* Panel ด้านขวา */
.notes-panel {
  width: 0; 
  overflow: hidden;
  transition: width 0.3s;
  background: #fff;
  border-left: 1px solid #ccc;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: calc(100vh - 50px);
}
.notes-panel.active {
  width: 400px;
}
.notes-panel-header {
  background: #f8f9fa;
  border-bottom: 1px solid #ddd;
  padding: 10px 15px;
}
.notes-panel-body {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
}

/* เวลามี panel → ตารางย่อเหลือพื้นที่ */
#tableWrapper.shrink {
  flex: 1;
  transition: flex 0.3s;
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
<div id="dateFilterBar" style="overflow-x: auto;">
  <div class="container-fluid d-flex align-items-center justify-content-between py-2 flex-nowrap" style="min-width: max-content; gap: 0.5rem;">
    <div class="d-flex align-items-center flex-nowrap" style="gap: 0.5rem;">
      <p class="mb-0 px-3 py-1 rounded" style="background-color: #d4d4d4ff; white-space: nowrap;">จัดการรถประจำสาย</p>

      <label class="form-label mb-0" style="white-space: nowrap;">สายเดินรถ :</label>
      <select id="routeSelect" class="form-select form-select-sm" style="min-width: 200px; white-space: nowrap;">
    <option value="">กรุณาเลือกสายเดินรถ</option>
    <?php foreach($all_routes_pool as $route): ?>
        <option value="<?= $route['route_number'] ?>" <?= (!empty($_GET['route']) && $_GET['route']==$route['route_number'])?'selected':'' ?>>
            <?= $route['route_name_th'] ?>
        </option>
    <?php endforeach; ?>
</select>


      <label for="searchBus" class="form-label mb-0" style="white-space: nowrap;">ค้นหารถ (ทะเบียน / รหัสรถ) : </label>
      <input type="text" id="searchBus" class="form-control form-control-sm" style="min-width: 200px;" placeholder="พิมพ์ทะเบียน หรือ รหัสรถ">

      <button id="filterBtn" class="btn btn-primary btn-sm d-flex align-items-center">
        <i class="fas fa-filter me-1"></i> กรอง
      </button>
    </div>

    <div class="d-flex align-items-center gap-2 flex-nowrap">
    <button class="btn btn-success btn-sm" id="addBusBtn">
        + เพิ่มรถ
    </button>
</div>

  </div>
</div>

<!-- Main content -->
<div class="content-wrapper collapsed p-0 d-flex" id="mainContent">
    <!-- ตารางรถ -->
    <div id="tableWrapper" class="flex-grow-1">
        <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" id="busTable">
            <thead class="table-dark">
                <tr>
                    <th>ลำดับ</th>
                    <th>สายเดินรถ</th>
                    <th>รหัสรถ</th>
                    <th>รหัสรถเต็มๆ</th>
                    <th>ทะเบียนรถ</th>
                    <th>คนขับหลัก</th>
                    <th>รหัสเครื่องยนต์</th>
                    <th>รหัสเชสซีล์</th>
                    <th>ประเภทรถ</th>
                    <th>สถานะ</th>
                    <th>หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($buses as $index => $bus): ?>
                <tr data-id="<?= $bus['bus_id'] ?>">
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($bus['route_name_th'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($bus['bus_number']) ?></td>
                    <td><?= htmlspecialchars($bus['full_bus_number']) ?></td>
                    <td><?= htmlspecialchars($bus['license_plate']) ?></td>
                    <td><?= htmlspecialchars($bus['main_driver_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($bus['engine_number']) ?></td>
                    <td><?= htmlspecialchars($bus['chassis_number']) ?></td>
                    <td><?= htmlspecialchars($bus['bt_name'] ?? '-') ?></td>
                    <td>
                        <?php if($bus['in_service'] == 1): ?>
                            <span class="text-success">พร้อมใช้งาน</span>
                                <?php else: ?>
                                    <span class="text-danger">ไม่พร้อมใช้งาน</span>
                                <?php endif; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($bus['notes']) ?>
                        <button type="button" class="btn btn-sm btn-warning ms-2" onclick="editBus(<?= $bus['bus_id'] ?>)">
                            <i class="bi bi-gear-fill"></i> จัดการ
                        </button>

                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Panel แก้ไขข้อมูลรถ -->
    <div id="busPanel" class="notes-panel d-none">
        <div class="notes-panel-header d-flex justify-content-between align-items-center " style="background-color: #ebebebff; border-bottom: 1px solid #ddd;">
            <p class="mb-0">แก้ไขข้อมูลรถ</p>
            <button type="button" class="btn-close" aria-label="Close" onclick="closeBusPanel()"></button>
        </div>
        <div class="notes-panel-body p-3">
            <form id="busForm">
                <input type="hidden" id="busId" name="bus_id">

                <div class="mb-3">
                    <label class="form-label">เลขสาย</label>
                    <input type="text" class="form-control" id="routeName" name="route_name" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">รหัสรถ</label>
                    <input type="text" class="form-control" id="busNumber" name="bus_number" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รหัสรถเต็ม</label>
                    <input type="text" class="form-control" id="fullBusNumber" name="full_bus_number">
                </div>

                <div class="mb-3">
                    <label class="form-label">ทะเบียนรถ</label>
                    <input type="text" class="form-control" id="licensePlate" name="license_plate">
                </div>

                <div class="mb-3">
                    <label class="form-label">รหัสเครื่องยนต์</label>
                    <input type="text" class="form-control" id="engineNumber" name="engine_number">
                </div>

                <div class="mb-3">
                    <label class="form-label">รหัสเชสซีล์</label>
                    <input type="text" class="form-control" id="chassisNumber" name="chassis_number">
                </div>

                <div class="mb-3">
                    <label class="form-label">ประเภทรถ</label>
                    <select class="form-select" id="busType" name="bt_id" required>
                        <option value="">-- เลือกประเภทรถ --</option>
                            <?php
                                $busTypes = $conn->query("SELECT bt_id, bt_name FROM bus_type ORDER BY bt_name ASC");
                                while($type = $busTypes->fetch_assoc()) {
                                echo "<option value='{$type['bt_id']}'>{$type['bt_name']}</option>";
                                }
                            ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">คนขับหลัก</label>
                    <input type="text" class="form-control" id="main_drivers" name="main_drivers">
                </div>

                <div class="mb-3">
                    <label class="form-label">หมายเหตุ</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select" id="inService" name="in_service">
                      <option value="1">พร้อมใช้งาน</option>
                      <option value="0">ไม่พร้อมใช้งาน</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">บันทึก</button>
            </form>
        </div>
    </div>
</div>

<!-- Modal เพิ่มรถใหม่ -->
<div class="modal fade" id="addBusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="addBusForm">
        <div class="modal-header" style="background-color: #ebebebff;">
          <p class="modal-title">เพิ่มรถใหม่</p>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>สายเดินรถ</label>
              <select class="form-select" name="br_id" required>
                  <option value="">-- เลือกสาย --</option>
                  <?php foreach($all_routes_pool as $route): ?>
                      <option value="<?= $route['route_number'] ?>"><?= $route['route_name_th'] ?></option>
                  <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label>รหัสรถ</label>
              <input type="text" name="bus_number" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>รหัสรถเต็ม</label>
              <input type="text" name="full_bus_number" class="form-control">
            </div>
            <div class="col-md-6">
              <label>ทะเบียนรถ</label>
              <input type="text" name="license_plate" class="form-control">
            </div>
            <div class="col-md-6">
              <label>รหัสเครื่องยนต์</label>
              <input type="text" name="engine_number" class="form-control">
            </div>
            <div class="col-md-6">
              <label>รหัสเชสซีล์</label>
              <input type="text" name="chassis_number" class="form-control">
            </div>
            <div class="col-md-6">
              <label>หมายเหตุ</label>
              <textarea name="notes" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
              <label>สถานะ</label>
              <select class="form-select" name="in_service">
                <option value="1">พร้อมใช้งาน</option>
                <option value="0">ไม่พร้อมใช้งาน</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">บันทึก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Sidebar toggle
document.getElementById('toggleSidebar').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.getElementById('mainContent').classList.toggle('collapsed');
    document.getElementById('dateFilterBar').classList.toggle('collapsed');
});

// Filter
document.getElementById('filterBtn').addEventListener('click', () => {
    const route = document.getElementById('routeSelect').value;
    let params = new URLSearchParams();
    if(route) params.append('route', route);
    window.location.href = window.location.pathname + '?' + params.toString();
});

// Realtime search
$('#searchBus').on('keyup', function(){
    let value = $(this).val().toLowerCase();
    $('#busTable tbody tr').filter(function(){
        $(this).toggle(
            $(this).find('td:nth-child(3)').text().toLowerCase().indexOf(value) > -1 ||
            $(this).find('td:nth-child(5)').text().toLowerCase().indexOf(value) > -1
        );
    });
});

// เปิด panel
function editBus(busId) {
    fetch('get_bus.php?bus_id=' + busId)
    .then(res => res.json())
    .then(data => {
        if(data.error) return alert(data.error);

        document.getElementById('busId').value = data.bus_id;
        document.getElementById('routeName').value = data.route_name_th || '-';
        document.getElementById('busNumber').value = data.bus_number;
        document.getElementById('fullBusNumber').value = data.full_bus_number;
        document.getElementById('licensePlate').value = data.license_plate;
        document.getElementById('engineNumber').value = data.engine_number;
        document.getElementById('chassisNumber').value = data.chassis_number;
        document.querySelector('select[name="bt_id"]').value = data.bt_id || '';
        document.getElementById('notes').value = data.notes;
        document.getElementById('inService').value = data.in_service;

        document.getElementById('busPanel').classList.add('active');
        document.getElementById('busPanel').classList.remove('d-none');
        document.getElementById('tableWrapper').classList.add('shrink');
    });
}


// ปิด panel
function closeBusPanel() {
    document.getElementById('busPanel').classList.remove('active');
    setTimeout(() => document.getElementById('busPanel').classList.add('d-none'), 300);
    document.getElementById('tableWrapper').classList.remove('shrink');
}

// บันทึกข้อมูลแบบ realtime (แก้ไข)
document.getElementById('busForm').addEventListener('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);

    fetch('update_bus.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(resp => {
        if(resp.status === 'success') {
            alert('อัปเดตเสร็จเรียบร้อยแล้ว');

            // อัปเดต row ในตารางทันที
            let row = document.querySelector(`#busTable tbody tr[data-id='${resp.bus_id}']`);
            if(row) {
                row.querySelector('td:nth-child(3)').textContent = resp.bus_number;
                row.querySelector('td:nth-child(4)').textContent = resp.full_bus_number;
                row.querySelector('td:nth-child(5)').textContent = resp.license_plate;
                row.querySelector('td:nth-child(6)').textContent = resp.main_driver_name || '-';
                row.querySelector('td:nth-child(7)').textContent = resp.engine_number;
                row.querySelector('td:nth-child(8)').textContent = resp.chassis_number;
                row.querySelector('td:nth-child(9)').textContent = resp.bt_name;

                // ✅ อัปเดตสถานะพร้อมสี
                let statusCell = row.querySelector('td:nth-child(10)');
                statusCell.textContent = (resp.in_service == 1) ? "พร้อมใช้งาน" : "ไม่พร้อมใช้งาน";
                statusCell.className = (resp.in_service == 1) ? "text-success" : "text-danger";

                row.querySelector('td:nth-child(11)').innerHTML =
                    resp.notes + ` <button type="button" class="btn btn-sm btn-warning ms-2" onclick="editBus(${resp.bus_id})"><i class="bi bi-gear-fill"></i> จัดการ</button>`;
            }

            closeBusPanel();
        } else {
            alert('เกิดข้อผิดพลาด: ' + resp.message);
        }
    });
});

// เปิด modal เพิ่มรถ
document.getElementById('addBusBtn').addEventListener('click', () => {
    var addBusModal = new bootstrap.Modal(document.getElementById('addBusModal'));
    addBusModal.show();
});

// submit เพิ่มรถ
document.getElementById('addBusForm').addEventListener('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);

    fetch('add_bus.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(resp => {
        if(resp.status === 'success'){
            alert('เพิ่มรถใหม่เรียบร้อยแล้ว');

            // เพิ่ม row ใหม่ลงในตาราง
            let tbody = document.querySelector('#busTable tbody');
            let index = tbody.querySelectorAll('tr').length + 1;

            let newRow = document.createElement('tr');
            newRow.setAttribute('data-id', resp.bus_id);
            newRow.innerHTML = `
                <td>${index}</td>
                <td>${resp.route_name_th}</td>
                <td>${resp.bus_number}</td>
                <td>${resp.full_bus_number}</td>
                <td>${resp.license_plate}</td>
                <td>${resp.main_driver_name || '-'}</td>
                <td>${resp.engine_number}</td>
                <td>${resp.chassis_number}</td>
                <td>${resp.bt_name}</td>
                <td class="${resp.in_service == 1 ? 'text-success':'text-danger'}">
                    ${resp.in_service == 1 ? 'พร้อมใช้งาน':'ไม่พร้อมใช้งาน'}
                </td>
                <td>${resp.notes} 
                    <button type="button" class="btn btn-sm btn-warning ms-2" onclick="editBus(${resp.bus_id})">
                        <i class="bi bi-gear-fill"></i> จัดการ
                    </button>
                </td>
            `;
            tbody.appendChild(newRow);

            // ปิด modal
            var addBusModalEl = document.getElementById('addBusModal');
            bootstrap.Modal.getInstance(addBusModalEl).hide();
            this.reset();
        } else {
            alert('เกิดข้อผิดพลาด: ' + resp.message);
        }
    });
});


</script>
</body>
</html>
