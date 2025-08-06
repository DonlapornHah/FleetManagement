<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$fullname = $_SESSION['user'];

$sql = "SELECT * FROM users WHERE fullname = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $fullname);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$isPass = ($data['result_status'] === "ผ่าน");
$result_color = $isPass ? "success" : "danger";
$result_icon = $isPass ? "bi-check-circle-fill" : "bi-x-circle-fill";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ผลการออดิชั่น</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Sarabun', sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }
        .main-card {
            max-width: 650px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .result-banner {
            padding: 1.5rem;
            font-size: 1.6rem;
            font-weight: 600;
            text-align: center;
            color: #fff;
        }
        .result-banner.success {
            background-color: #28a745;
        }
        .result-banner.danger {
            background-color: #dc3545;
        }
        .result-banner i {
            font-size: 1.8rem;
            margin-right: 10px;
        }
        .card-content {
            padding: 2rem;
        }
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        .info-group {
            margin-bottom: 1rem;
        }
        .info-label {
            font-weight: 500;
            color: #555;
            min-width: 120px;
        }
        .info-value {
            color: #222;
        }
        .comment-box {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            white-space: pre-line;
        }
        .logout-btn {
            text-align: right;
            margin-top: 2rem;
        }
        @media (max-width: 576px) {
            .card-content {
                padding: 1.2rem;
            }
            .result-banner {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-card">
        <!-- แถบผลลัพธ์ -->
        <div class="result-banner <?php echo $isPass ? 'success' : 'danger'; ?>">
            <i class="bi <?php echo $result_icon; ?>"></i>
            ผลการออดิชั่น: <?php echo $data['result_status']; ?>
        </div>

        <!-- เนื้อหา -->
        <div class="card-content">
            <div class="section-title">ข้อมูลผู้สมัคร</div>

            <div class="row info-group">
                <div class="col-5 info-label">ชื่อ:</div>
                <div class="col-7 info-value"><?php echo htmlspecialchars($data['fullname']); ?></div>
            </div>
            <div class="row info-group">
                <div class="col-5 info-label">วันเกิด:</div>
                <div class="col-7 info-value"><?php echo $data['birthday']; ?></div>
            </div>

            <div class="section-title mt-4">รายละเอียดผล</div>

            <div class="row info-group">
                <div class="col-5 info-label">คะแนนที่ได้รับ:</div>
                <div class="col-7 info-value fw-bold text-primary"><?php echo $data['score']; ?></div>
            </div>

            <div class="row info-group">
                <div class="col-5 info-label">ผลการพิจารณา:</div>
                <div class="col-7 info-value fw-bold text-<?php echo $result_color; ?>"><?php echo $data['result_status']; ?></div>
            </div>

<div class="row info-group">
    <div class="col-5 info-label">ความคิดเห็นจากกรรมการ:</div>
    <div class="col-7 info-value">
        <?php echo nl2br(htmlspecialchars($data['comment'])); ?>
    </div>
</div>



            <div class="logout-btn">
                <a href="logout.php" class="btn btn-sm btn-outline-dark">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</body>
</html>
