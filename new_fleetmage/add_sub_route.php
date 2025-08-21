<?php
include 'config.php';

$response = ['status'=>'error','message'=>''];

if($_SERVER['REQUEST_METHOD']==='POST'){
    // รับค่าจากฟอร์ม
    $br_id = intval($_POST['br_id']); // route_id ของสาย
    $bus_number = $conn->real_escape_string($_POST['bus_number']);
    $full_bus_number = $conn->real_escape_string($_POST['full_bus_number']);
    $license_plate = $conn->real_escape_string($_POST['license_plate']);
    $engine_number = $conn->real_escape_string($_POST['engine_number']);
    $chassis_number = $conn->real_escape_string($_POST['chassis_number']);
    $notes = $conn->real_escape_string($_POST['notes']);
    $in_service = isset($_POST['in_service']) ? intval($_POST['in_service']) : 0;

    // ตรวจสอบว่า br_id มีจริงในตาราง route หรือไม่
    $check = $conn->query("SELECT route_name_th FROM route WHERE route_id=$br_id");
    if($check && $check->num_rows>0){
        $route = $check->fetch_assoc();
        $route_name_th = $route['route_name_th'];

        // insert ลง bus_info
        $sql = "INSERT INTO bus_info
            (br_id, bus_number, full_bus_number, license_plate, engine_number, chassis_number, notes, in_service)
            VALUES
            ($br_id, '$bus_number', '$full_bus_number', '$license_plate', '$engine_number', '$chassis_number', '$notes', $in_service)";
        
        if($conn->query($sql)){
            $bus_id = $conn->insert_id;

            // ดึงข้อมูลเต็มเพื่อตอบกลับให้ JS update ตาราง
            $bus_info = $conn->query("
                SELECT b.*, r.route_name_th, t.bt_name
                FROM bus_info b
                LEFT JOIN route r ON b.br_id = r.route_id
                LEFT JOIN bus_type t ON b.bus_type_id = t.bt_id
                WHERE b.bus_id=$bus_id
            ")->fetch_assoc();

            $response = [
                'status'=>'success',
                'bus_id'=>$bus_id,
                'route_name_th'=>$bus_info['route_name_th'],
                'bus_number'=>$bus_info['bus_number'],
                'full_bus_number'=>$bus_info['full_bus_number'],
                'license_plate'=>$bus_info['license_plate'],
                'engine_number'=>$bus_info['engine_number'],
                'chassis_number'=>$bus_info['chassis_number'],
                'bt_name'=>$bus_info['bt_name'],
                'main_driver_name'=>'', // default ว่าง
                'notes'=>$bus_info['notes'],
                'in_service'=>$bus_info['in_service']
            ];
        } else {
            $response['message'] = "เพิ่มรถไม่สำเร็จ: ".$conn->error;
        }
    } else {
        $response['message'] = "สายเดินรถไม่ถูกต้อง";
    }

} else {
    $response['message'] = "Invalid request";
}

echo json_encode($response);
