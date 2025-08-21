<?php
include 'config.php';

// ดึงข้อมูลสายหลักและสายย่อย
$sql = "SELECT * FROM route ORDER BY route_number ASC, type_route ASC";
$result = $conn->query($sql);

$routes = [];
if($result && $result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $route_number = $row['route_number'];
        if($row['type_route'] == 'สายหลัก'){
            $routes[$route_number] = [
                'main' => $row,
                'sub' => []
            ];
        } else {
            if(!isset($routes[$route_number])){
                $routes[$route_number] = [
                    'main' => null,
                    'sub' => []
                ];
            }
            $routes[$route_number]['sub'][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสายเดินรถ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
.table-responsive { width: 100%; overflow-x: auto; transition: all 0.3s ease; }
#tableWrapper.shrink { width: calc(100% - 420px); margin-right: 420px; }

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

    <!-- ซ้าย: ชื่อหน้า + ช่องค้นหา + ฟิลเตอร์ -->
    <div class="d-flex align-items-center gap-2">

      <!-- ชื่อหน้า -->
      <p class="mb-0 px-3 py-1 rounded" style="background-color: #d4d4d4ff; white-space: nowrap;">
        จัดการสายเดินรถ
      </p>

      <!-- ช่องค้นหาสาย -->
      <div class="d-flex align-items-center gap-1">
        <span style="white-space: nowrap;">ค้นหาสาย :</span>
        <input type="text" id="routeSearch" class="form-control form-control-sm" placeholder="พิมพ์ชื่อสายรถ..." style="min-width: 180px;">
        <button class="btn btn-secondary btn-sm" id="clearSearch" title="ล้างข้อความค้นหา">
          <i class="bi bi-x-circle"></i>
        </button>
      </div>

      <!-- Dropdown กรองประเภทสาย -->
      <div class="d-flex align-items-center gap-1">
        <span style="white-space: nowrap;">ประเภท :</span>
        <select id="routeTypeFilter" class="form-select form-select-sm">
          <option value="">ทั้งหมด</option>
          <option value="สายหลัก">สายหลัก</option>
          <option value="สายย่อย">สายย่อย</option>
        </select>
      </div>

    </div>

    <!-- ขวา: ปุ่ม Add / Import / Export -->
    <div class="d-flex align-items-center gap-1">
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addRouteModal" title="เพิ่มสายรถ">
        <i class="bi bi-plus-lg"></i> เพิ่มสายรถ
      </button>
      <button class="btn btn-primary btn-sm" id="exportRoutes" title="ส่งออก CSV">
        <i class="bi bi-download"></i> ส่งออก
      </button>
    </div>

  </div>
</div>

<!-- Content -->
<div class="content-wrapper collapsed p-0" id="mainContent">
  <div id="tableWrapper" class="table-responsive p-0">
    <table class="table table-bordered table-striped table-sm">
      <thead class="table-dark">
        <tr class="text-center">
          <th>ลำดับ</th>
          <th>เลขสาย</th>
          <th>ชื่อสายเดินรถ</th>
          <th>ประเภท</th>
          <th>ระยะทาง</th>
          <th>จัดการ</th>
        </tr>
      </thead>
      <tbody>
<?php $i = 1; ?>
<?php foreach($routes as $route_number => $group): ?>
<?php $main = $group['main']; ?>
<tr class="route-row" data-name="<?= htmlspecialchars($main['route_name_th']) ?>">
  <td><?= $i ?></td>
  <td>
    <span class="badge bg-primary"><?= htmlspecialchars($main['route_number']) ?></span>
</td>

  <td><?= htmlspecialchars($main['route_name_th']) ?></td>
  <td><span class="badge bg-success">สายหลัก</span></td>
  <td><?= htmlspecialchars($main['distance_km']) ?> กม.</td>
  <td>
    <button class="btn btn-info btn-sm" onclick="viewSubRoute(<?= $main['route_id'] ?>)">
        <i class="bi bi-eye"></i> ดูสายย่อย
    </button>
    <button class="btn btn-warning btn-sm" onclick="editRoute(<?= $main['route_id'] ?>)">
        <i class="bi bi-pencil"></i> แก้ไข
    </button>
</td>

</tr>
<?php $i++; endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Panel ด้านขวา -->
<div id="routePanel" class="d-flex flex-column bg-light shadow" style="
    width: 0; 
    overflow: hidden;
    transition: width 0.3s;
    border-left: 1px solid #ccc;
    position: fixed;
    top: 50px; /* เริ่มใต้ filter bar */
    right: 0;
    height: calc(100vh - 50px);
    z-index: 1051;
">
  <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
    <h5 class="mb-0" id="panelTitle">จัดการสายเดินรถ</h5>
    <button class="btn-close" onclick="closeRoutePanel()"></button>
  </div>
  <div class="p-3" id="panelContent">
    <p class="text-muted">กำลังโหลด...</p>
  </div>
</div>

<!-- Modal เพิ่มสายย่อย -->
<div class="modal fade" id="addSubRouteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addSubRouteForm">
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มสายย่อย</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="main_id" id="mainIdForSub">
          <div class="mb-2">
            <label class="form-label">ชื่อสายย่อย</label>
            <input type="text" class="form-control" name="route_name_th" required>
          </div>
          <div class="mb-2">
            <label class="form-label">ระยะทาง (กม.)</label>
            <input type="number" class="form-control" name="distance_km">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">บันทึกสายย่อย</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Modal เพิ่มสายรถ -->
<div class="modal fade" id="addRouteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addRouteForm">
        <div class="modal-header">
          <h5 class="modal-title">เพิ่มสายรถ (สายหลัก)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">เลขสาย</label>
            <input type="text" class="form-control" name="route_number" required>
          </div>
          <div class="mb-2">
            <label class="form-label">ชื่อสายเดินรถ</label>
            <input type="text" class="form-control" name="route_name_th" required>
          </div>
          <div class="mb-2">
            <label class="form-label">ระยะทาง (กม.)</label>
            <input type="number" class="form-control" name="distance_km" required>
          </div>
          <div class="mb-2">
            <label class="form-label">เวลา (นาที)</label>
            <input type="number" class="form-control" name="total_time">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">บันทึกสายรถ</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
document.getElementById('toggleSidebar').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('collapsed');
  document.getElementById('mainContent').classList.toggle('collapsed');
  document.getElementById('dateFilterBar').classList.toggle('collapsed');
});

// ฟังก์ชันเปิด/ปิด panel ด้านขวา
function openRoutePanel() {
  document.getElementById('routePanel').style.width = '420px';
  document.getElementById('tableWrapper').classList.add('shrink');
}
function closeRoutePanel() {
  document.getElementById('routePanel').style.width = '0';
  document.getElementById('tableWrapper').classList.remove('shrink');
}

// ดูสายย่อย
function viewSubRoute(routeId){
  fetch('get_sub_routes.php?main_id=' + routeId)
    .then(res => res.text())
    .then(html => {
      document.getElementById('panelTitle').innerText = 'สายย่อย';
      document.getElementById('panelContent').innerHTML = html;
      const mainIdInput = document.getElementById('mainIdForSub');
      if(mainIdInput) mainIdInput.value = routeId;
      openRoutePanel();
    });
}

// แก้ไขสายหลัก
function editRoute(routeId){
  fetch('get_route.php?route_id=' + routeId)
    .then(res => res.json())
    .then(data => {
      if(data.error){ alert(data.error); return; }

      document.getElementById('panelTitle').innerText = 'แก้ไขสายเดินรถ';
      document.getElementById('panelContent').innerHTML = `
        <form id="editRouteForm">
          <input type="hidden" name="route_id" value="${data.route_id}">
          <div class="mb-2">
            <label>เลขสาย</label>
            <input class="form-control" name="route_number" value="${data.route_number}" readonly>
          </div>
          <div class="mb-2">
            <label>ชื่อสายเดินรถ</label>
            <input class="form-control" name="route_name_th" value="${data.route_name_th}" required>
          </div>
          <div class="mb-2">
            <label>ประเภท</label>
            <select class="form-select" name="type_route" required>
              <option value="สายหลัก" ${data.type_route=='สายหลัก'?'selected':''}>สายหลัก</option>
              <option value="สายย่อย" ${data.type_route=='สายย่อย'?'selected':''}>สายย่อย</option>
            </select>
          </div>
          <div class="mb-2">
            <label>ระยะทาง (กม.)</label>
            <input class="form-control" name="distance_km" value="${data.distance_km}">
          </div>
          <button type="submit" class="btn btn-success btn-sm mt-2">บันทึก</button>
        </form>
      `;
      openRoutePanel();

      // Submit edit form ผ่าน AJAX
      document.getElementById('editRouteForm').addEventListener('submit', function(e){
        e.preventDefault();
        const fd = new FormData(this);
        fetch('update_route.php', { method:'POST', body:fd })
          .then(res=>res.json())
          .then(resp=>{
            if(resp.success){
              alert('แก้ไขเรียบร้อย');
              closeRoutePanel();
              location.reload();
            } else {
              alert('เกิดข้อผิดพลาด: ' + resp.error);
            }
          });
      });
    });
}

// เพิ่มสายรถหลักผ่าน AJAX
document.getElementById('addRouteForm').addEventListener('submit', function(e){
  e.preventDefault();
  const fd = new FormData(this);
  fetch('add_route.php', { method:'POST', body:fd })
    .then(res => res.json())
    .then(data => {
      if(data.success){
        alert('เพิ่มสายรถเรียบร้อย');
        bootstrap.Modal.getInstance(document.getElementById('addRouteModal')).hide();
        location.reload();
      } else {
        alert('เกิดข้อผิดพลาด: ' + data.error);
      }
    });
});

// เพิ่มสายย่อยผ่าน Modal
document.getElementById('addSubRouteForm').addEventListener('submit', function(e){
  e.preventDefault();
  const fd = new FormData(this);
  fetch('add_sub_route.php',{method:'POST', body:fd})
    .then(res=>res.json())
    .then(data=>{
      if(data.success){
        alert('เพิ่มสายย่อยเรียบร้อย');
        bootstrap.Modal.getInstance(document.getElementById('addSubRouteModal')).hide();
        viewSubRoute(fd.get('main_id'));
      } else alert('เกิดข้อผิดพลาด:'+data.error);
    });
});

// DOMContentLoaded: search, filter, import/export
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('routeSearch');

  // ค้นหาสาย
  searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.route-row').forEach(row => {
      const name = row.dataset.name.toLowerCase();
      row.style.display = name.includes(query) ? '' : 'none';
    });
  });

  // ล้างค้นหา
  document.getElementById('clearSearch').addEventListener('click', function(){
    searchInput.value = '';
    searchInput.dispatchEvent(new Event('input'));
  });

  // กรองประเภทสาย
  document.getElementById('routeTypeFilter').addEventListener('change', function(){
    const type = this.value;
    document.querySelectorAll('.route-row').forEach(row => {
      const rowType = row.dataset.type;
      row.style.display = (type === '' || type === rowType) ? '' : 'none';
    });
  });

  // ส่งออก
  document.getElementById('exportRoutes').addEventListener('click', () => {
    window.location.href = 'export_routes.php';
  });
});
</script>

</body>
</html>
