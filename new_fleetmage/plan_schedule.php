<?php
include 'config.php';

// ---------- JSON สำหรับ FullCalendar ----------
if(isset($_GET['action']) && $_GET['action'] === 'json') {
    header('Content-Type: application/json');

    $filter_route = isset($_GET['route_id']) ? $_GET['route_id'] : '';

    $sql = "SELECT schedule_id, plan_name, route_number, quetime, plan_type, 
                   junction1, junction2, total_distance, total_time, plan_date
            FROM plan_schedule";

    if($filter_route != '') {
        $sql .= " WHERE route_number = '".$conn->real_escape_string($filter_route)."'";
    }

    $sql .= " ORDER BY plan_date, quetime";

    $result = $conn->query($sql);

    $events = [];

    while($row = $result->fetch_assoc()){
        $events[] = [
            'id' => $row['schedule_id'],
            'title' => $row['plan_name'], 
            'start' => $row['plan_date'].'T'.$row['quetime'], 
            'allDay' => false,
            'extendedProps' => [
                'route_number' => $row['route_number'],
                'plan_type' => $row['plan_type'],
                'junction1' => $row['junction1'],
                'junction2' => $row['junction2'],
                'total_distance' => $row['total_distance'],
                'total_time' => $row['total_time']
            ]
        ];
    }

    echo json_encode($events);
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แผนรถประจำวัน</title>

<!-- FullCalendar CSS/JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&display=swap" rel="stylesheet">

<style>
  body { 
      background-color: #f4f6f9; 
      margin: 0; 
      padding: 20px; 
  }

  h3 { text-align: center; margin-bottom: 20px; color: #333; }

  #calendar { 
      width: 100%;
      margin: 0 auto; 
      background: #fff; 
      border-radius: 12px; 
      box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
      padding: 20px; 
  }

  .fc-toolbar-title {
      font-weight: 600;
      font-size: 22px;
      color: #444;
  }

  .fc .fc-button {
      background-color: #333;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-weight: 500;
  }

  .fc .fc-button:hover {
      background-color: #555;
  }

  .fc-event {
      font-size: 14px;
      padding: 4px 6px;
      border-radius: 6px;
      color: #fff;
      box-shadow: 0 1px 4px rgba(0,0,0,0.15);
  }

  .filter-bar {
      max-width: 1200px;
      margin: 0 auto 15px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
  }

  @media (max-width: 768px) {
      #calendar { padding: 10px; }
      .filter-bar { flex-direction: column; align-items: flex-start; }
  }
</style>
</head>
<body>

<h3>Plan Schedule Calendar</h3>

<div class="filter-bar">
    <select id="routeFilter" class="form-select form-select-sm" style="width: 300px;">
        <option value="">ทุกสาย</option>
        <?php
        // ดึงสายรถ join กับ location
        $sql_routes = "
            SELECT r.route_id, 
                   s.locat_name_th AS start_name, 
                   e.locat_name_th AS end_name
            FROM route r
            LEFT JOIN location s ON r.start_location_id = s.locat_id
            LEFT JOIN location e ON r.end_location_id = e.locat_id
            ORDER BY s.locat_name_th, e.locat_name_th
        ";
        $res = $conn->query($sql_routes);
        while($r = $res->fetch_assoc()) {
            $name = $r['start_name'] . " - " . $r['end_name'];
            echo "<option value='".htmlspecialchars($r['route_id'])."'>".htmlspecialchars($name)."</option>";
        }
        ?>
    </select>
</div>

<div id='calendar'></div>

<!-- Modal แสดงรายละเอียด -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalBody"></div>
    </div>
  </div>
</div>

<script>
var calendar;
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var routeFilterEl = document.getElementById('routeFilter');

    function renderCalendar(routeFilter = '') {
        if(calendar) {
            calendar.destroy();
        }

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridDay',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridDay,listDay'
            },
            slotDuration: '00:30:00',
            slotLabelInterval: '01:00',
            slotMinTime: "06:00:00",
            slotMaxTime: "22:00:00",
            events: '?action=json&route_id=' + routeFilter,
            eventOverlap: true,
            nowIndicator: true,
            displayEventTime: true,
            displayEventEnd: true,
            eventClick: function(info) {
                var e = info.event.extendedProps;

                document.getElementById('modalTitle').innerText = info.event.title;

                // แสดงชื่อสายต้นทาง-ปลายทาง
                var routeName = routeFilterEl.options[routeFilterEl.selectedIndex].text;

                document.getElementById('modalBody').innerHTML = `
                    <p><strong>สายรถ:</strong> ${routeName}</p>
                    <p><strong>ประเภทแผน:</strong> ${e.plan_type}</p>
                    <p><strong>ต้นทาง:</strong> ${e.junction1}</p>
                    <p><strong>ปลายทาง:</strong> ${e.junction2}</p>
                    <p><strong>ระยะทางรวม:</strong> ${e.total_distance} กม.</p>
                    <p><strong>เวลาเดินทาง:</strong> ${e.total_time}</p>
                `;

                var modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();
            },
            eventDidMount: function(info) {
                info.el.style.backgroundColor = '#333';
                info.el.style.color = 'white';
                info.el.style.borderRadius = '6px';
            }
        });

        calendar.render();
    }

    renderCalendar();

    routeFilterEl.addEventListener('change', function(){
        renderCalendar(this.value);
    });
});
</script>

</body>
</html>
