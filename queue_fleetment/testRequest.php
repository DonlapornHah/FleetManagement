<?php 
    include 'config.php';
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $route = [2,3,4];
    $sql_request = "SELECT * FROM `queue_request` WHERE br_id IN (" . implode(',', $route) . ") ORDER BY br_id";
    $result_request = mysqli_query($conn, $sql_request);

    $request = [];
    while ($row = mysqli_fetch_assoc($result_request)) {
        $qr_request = json_decode($row['qr_request'], true);
        $request[$row['br_id']]['request'] = $qr_request['request'];
        $request[$row['br_id']]['reserve'] = $qr_request['reserve'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Request & Reserve</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .request-button {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 1050;
      border-radius: 50px;
      padding: 10px 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body class="bg-light">

<!-- Floating Button -->
<button class="btn btn-danger btn-lg request-button" onclick="showRequestModal()">
  <i class="bi bi-send-fill me-1"></i> Request & Reserve
</button>

<!-- Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="requestModalLabel">จัดการ Request & Reserve</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="request-tables"></div>
      </div>
    </div>
  </div>
</div>

<script>
const request = <?php echo json_encode($request); ?>;

function getAllCodeOptions(request) {
    const groupMap = {};
    Object.entries(request).forEach(([br_id, obj]) => {
        groupMap[br_id] = [];
        (obj.request || []).forEach((_, i, arr) => {
            let code = (i === arr.length - 1) ? `${br_id}-3-last` : `${br_id}-3-${i+1}`;
            groupMap[br_id].push({ value: code, label: code });
        });
        (obj.reserve || []).forEach((_, i) => {
            groupMap[br_id].push({ value: `${br_id}-1-${i+1}`, label: `${br_id}-1-${i+1}` });
        });
    });
    groupMap['อื่นๆ'] = [ { value: '0', label: '0' }, { value: '1', label: '1' }, { value: '2', label: '2' } ];
    return groupMap;
}

function createSelect(name, selected, routeOptions, br_id, type, idx, isDup) {
    let html = `<select name="${name}" class="form-select${isDup ? ' is-invalid' : ''}" onchange="onQueueChange('${br_id}','${type}',${idx},this)">`;
    Object.entries(routeOptions).forEach(([group, opts]) => {
        html += `<optgroup label="${group}">`;
        opts.forEach(opt => {
            html += `<option value="${opt.value}" ${opt.value === selected ? 'selected' : ''}>${opt.label}</option>`;
        });
        html += '</optgroup>';
    });
    html += '</select>';
    if (isDup && selected !== '0') html += '<div class="invalid-feedback">ซ้ำ</div>';
    return html;
}

function onQueueChange(br_id, type, idx, selectElem) {
    request[br_id][type][idx] = selectElem.value;
    renderTables();
}

function renderTables() {
  const container = document.getElementById('request-tables');
  const routeOptions = getAllCodeOptions(request);
  let allSelected = [], codeLocation = {}, seen = new Set(), duplicateCodes = new Set();

  Object.entries(request).forEach(([br_id, obj]) => {
    ['request', 'reserve'].forEach(type => {
      (obj[type] || []).forEach((val, idx) => {
        if (val && val !== '0') {
          allSelected.push(val);
          codeLocation[val] = codeLocation[val] || [];
          codeLocation[val].push(`Route ${br_id} - ${type} ลำดับ ${idx + 1}`);
        }
      });
    });
  });

  allSelected.forEach(code => {
    if (code === '1' || code === '2') return;
    if (seen.has(code)) duplicateCodes.add(code);
    else seen.add(code);
  });

  let html = `<div id="all-route-form">`;

  Object.entries(request).forEach(([br_id, obj]) => {
    html += `
    <div class="card mb-4 shadow-sm">
      <div class="card-header bg-secondary text-white"><h5 class="mb-0">Route: ${br_id}</h5></div>
      <div class="card-body">
        <div class="row">
          <!-- Request Column -->
          <div class="col-md-6">
            <b>Request</b>
            <table class="table table-bordered table-sm align-middle">
              <thead class="table-light"><tr><th>#</th><th>Code</th><th>Queue</th><th>Action</th></tr></thead>
              <tbody>`;
              
    (obj.request || []).forEach((val, idx, arr) => {
      const code = (idx === arr.length - 1) ? `${br_id}-3-last` : `${br_id}-3-${idx + 1}`;
      const isDup = duplicateCodes.has(val);
      html += `<tr><td>${idx+1}</td><td>${code}</td><td>${createSelect(`request[${br_id}][]`, val, routeOptions, br_id, 'request', idx, isDup)}</td>
        <td>
          <div class="btn-group btn-group-sm" role="group">
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'before')">แทรกก่อน</button>
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','request',${idx},'after')">แทรกหลัง</button>
            <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','request',${idx})">ลบ</button>
          </div>
        </td></tr>`;
    });

    html += `<tr><td>ใหม่</td><td>${br_id}-3-ใหม่</td><td>${createSelect('', '0', routeOptions, br_id, 'request', obj.request.length, false)}</td>
        <td><button type='button' class="btn btn-success btn-sm" onclick="insertRow('${br_id}','request',${obj.request.length-1},'after')">เพิ่ม</button></td></tr>`;

    html += `
              </tbody>
            </table>
          </div>

          <!-- Reserve Column -->
          <div class="col-md-6">
            <b>Reserve</b>
            <table class="table table-bordered table-sm align-middle">
              <thead class="table-light"><tr><th>#</th><th>Code</th><th>Queue</th><th>Action</th></tr></thead>
              <tbody>`;

    (obj.reserve || []).forEach((val, idx) => {
      const code = `${br_id}-1-${idx + 1}`;
      const isDup = duplicateCodes.has(val);
      html += `<tr><td>${idx+1}</td><td>${code}</td><td>${createSelect(`reserve[${br_id}][]`, val, routeOptions, br_id, 'reserve', idx, isDup)}</td>
        <td>
          <div class="btn-group btn-group-sm" role="group">
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','reserve',${idx},'before')">แทรกก่อน</button>
            <button type='button' class="btn btn-outline-secondary" onclick="insertRow('${br_id}','reserve',${idx},'after')">แทรกหลัง</button>
            <button type='button' class="btn btn-outline-danger" onclick="removeRow('${br_id}','reserve',${idx})">ลบ</button>
          </div>
        </td></tr>`;
    });

    html += `<tr><td>ใหม่</td><td>${br_id}-1-ใหม่</td><td>${createSelect('', '0', routeOptions, br_id, 'reserve', obj.reserve.length, false)}</td>
        <td><button type='button' class="btn btn-success btn-sm" onclick="insertRow('${br_id}','reserve',${obj.reserve.length-1},'after')">เพิ่ม</button></td></tr>`;

    html += `
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>`;
  });

  html += `<div class='my-3'><button type='button' class="btn btn-primary w-100" onclick="submitQueueData()">บันทึกทั้งหมด</button></div></div>`;
  container.innerHTML = html;
}

function removeRow(br_id, type, idx) {
    request[br_id][type].splice(idx, 1);
    renderTables();
}

function insertRow(br_id, type, idx, pos) {
    let insertIdx = pos === 'before' ? idx : idx + 1;
    request[br_id][type].splice(insertIdx, 0, '0');
    renderTables();
    setTimeout(() => {
        const row = document.getElementById(`tbody-${br_id}-${type}`)?.children[insertIdx];
        if (row) row.scrollIntoView({ behavior: 'smooth' });
    }, 100);
}

function showRequestModal() {
  renderTables();
  new bootstrap.Modal(document.getElementById('requestModal')).show();
}

function submitQueueData() {
  // รวบรวมข้อมูลจาก request object
  const data = new FormData();
  for (const [br_id, obj] of Object.entries(request)) {
    if (obj.request) {
      obj.request.forEach(val => data.append(`request[${br_id}][]`, val));
    }
    if (obj.reserve) {
      obj.reserve.forEach(val => data.append(`reserve[${br_id}][]`, val));
    }
  }

  // ส่งข้อมูลด้วย fetch POST ไปยัง request_db.php
  fetch('request_db.php', {
    method: 'POST',
    body: data
  })
  .then(response => response.text())
  .then(result => {
    alert("บันทึกข้อมูลสำเร็จ");
    // สามารถปิด modal ได้หากต้องการ:
    const modal = bootstrap.Modal.getInstance(document.getElementById('requestModal'));
    if (modal) modal.hide();
  })
  .catch(error => {
    console.error('Error:', error);
    alert("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
  });
}

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>