<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>เข้าสู่ระบบ</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body {
            height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            padding: 50px 35px 50px 35px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .system-title {
            font-weight: 700;
            font-size: 1.4rem;
            color: #4b3c97;
            letter-spacing: 2px;
            margin-bottom: 25px;
            user-select: none;
        }
        .logo {
            max-width: 180px;
            margin-bottom: 25px;
            filter: drop-shadow(0 0 5px rgba(0,0,0,0.15));
        }
        h5 {
            margin-bottom: 25px;
            color: #000000;
        }
        label {
            display: block;
            text-align: left;
            font-weight: 600;
            margin-bottom: 10px;
            color: #555;
            font-size: 17px;
            letter-spacing: 0.03em;
        }
        input[type="date"] {
            width: 100%;
            padding: 14px 18px;
            border-radius: 14px;
            border: 1.8px solid #ccc;
            font-size: 16px;
            font-weight: 400;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: inherit;
        }
        input[type="date"]:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 12px #6c5ce7;
        }
        button[type="submit"] {
            margin-top: 35px;
            width: 100%;
            padding: 16px;
            background: #6c5ce7;
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 1px;
            transition: background 0.3s ease, box-shadow 0.3s ease;
            font-family: inherit;
        }
        button[type="submit"]:hover {
            background: #4834d4;
            box-shadow: 0 8px 25px rgba(72,52,212,0.6);
        }
        .footer-text {
            margin-top: 22px;
            font-size: 14px;
            color: #999;
            letter-spacing: 0.05em;
        }
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 25px 40px 25px;
            }
            .system-title {
                font-size: 1.2rem;
                margin-bottom: 18px;
            }
            h2 {
                font-size: 1.6rem;
            }
            button[type="submit"] {
                font-size: 18px;
            }
            label {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <main class="login-card" role="main" aria-label="หน้าเข้าสู่ระบบ">
        <div class="system-title">ระบบประกาศผลการออดิชั่น Higher Level Audition 2025</div>

        <img src="https://i.ibb.co/6JmdbQ8k/image.png" alt="โลโก้ Higher Level" class="logo" />

        <h5>เข้าสู่ระบบด้วยวันเกิด</h5>

        <form action="process_login.php" method="POST" novalidate>
            <label for="birthday">วันเกิด (dd-mm-yyyy) :</label>
            <input type="date" name="birthday" id="birthday" required aria-required="true" />
            <button type="submit">เข้าสู่ระบบ</button>
        </form>

        <p class="footer-text">© 2025 Higher Level Audition</p>
    </main>
</body>
</html>
