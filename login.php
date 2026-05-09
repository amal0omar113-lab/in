<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectBasedOnRole();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $country = $_POST['country'] ?? '';
    $region = $_POST['region'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "يرجى إدخال البريد الإلكتروني وكلمة المرور";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'suspended') {
                $error = "عذراً، تم إيقاف حسابك من قبل الإدارة.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_type'] = $user['type'];

                if ($user['type'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($user['type'] === 'donor') {
                    header("Location: donor/home.php");
                } else {
                    header("Location: beneficiary/home.php");
                }
                exit();
            }
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - يداً بيد</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">

<div class="auth-container">
    <div class="auth-logo">
        <img src="images/logo.png.jpeg" alt="Logo">
    </div>

    <div class="auth-title">يداً بيد</div>

    <div class="tabs">
        <a href="login.php" class="active">تسجيل الدخول</a>
        <a href="register.php">إنشاء حساب</a>
    </div>

    <?php if ($error): ?>
        <div style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="input-box">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        </div>

        <div class="input-box">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" id="loginPass" placeholder="كلمة المرور" required>
            <span style="position: absolute; left: 15px; top: 14px; cursor: pointer;" onclick="togglePassword('loginPass')">
                <i class="fa fa-eye"></i>
            </span>
        </div>

        <div class="input-box">
            <select name="country" id="login_country" onchange="updateRegions('login_country', 'login_region')">
                <option value="">اختر الدولة (اختياري)</option>
                <option value="saudi">السعودية</option>
                <option value="uae">الإمارات</option>
                <option value="kuwait">الكويت</option>
                <option value="qatar">قطر</option>
            </select>
        </div>

        <div class="input-box">
            <select name="region" id="login_region">
                <option value="">اختر المنطقة</option>
            </select>
        </div>

        <button type="submit" class="submit-btn">دخول</button>
    </form>
</div>

<script>
    function togglePassword(id) {
        let input = document.getElementById(id);
        input.type = input.type === "password" ? "text" : "password";
    }

    const regions = {
        saudi: ["الرياض", "جدة", "مكة", "المدينة", "الدمام"],
        uae: ["دبي", "أبوظبي", "الشارقة"],
        kuwait: ["العاصمة", "حولي"],
        qatar: ["الدوحة", "الريان"]
    };

    function updateRegions(countryId, regionId) {
        let country = document.getElementById(countryId).value;
        let region = document.getElementById(regionId);
        region.innerHTML = '<option value="">اختر المنطقة</option>';
        if (regions[country]) {
            regions[country].forEach(r => {
                let option = document.createElement("option");
                option.value = r;
                option.textContent = r;
                region.appendChild(option);
            });
        }
    }
</script>

</body>
</html>
