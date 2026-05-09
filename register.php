<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectBasedOnRole();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $type = $_POST['type']; // 'donor' or 'beneficiary'
    
    // Simple validation
    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = "جميع الحقول مطلوبة";
    } elseif (strlen($password) < 6) {
        $error = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "البريد الإلكتروني مسجل مسبقاً";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, type) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $hashedPassword, $type])) {
                $success = "تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.";
            } else {
                $error = "حدث خطأ أثناء إنشاء الحساب";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - يداً بيد</title>
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
        <a href="login.php">تسجيل الدخول</a>
        <a href="register.php" class="active">إنشاء حساب</a>
    </div>

    <?php if ($error): ?>
        <div style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color: green; text-align: center; margin-bottom: 15px;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="input-box">
            <i class="fa fa-user"></i>
            <input type="text" name="name" placeholder="الاسم الكامل" required>
        </div>
        <div class="input-box">
            <i class="fa fa-envelope"></i>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        </div>
        <div class="input-box">
            <i class="fa fa-phone"></i>
            <input type="text" name="phone" placeholder="رقم الجوال" required>
        </div>
        <div class="input-box">
            <i class="fa fa-lock"></i>
            <input type="password" name="password" id="regPass" placeholder="كلمة المرور" required>
            <span style="position: absolute; left: 15px; top: 14px; cursor: pointer;" onclick="togglePassword('regPass')">
                <i class="fa fa-eye"></i>
            </span>
        </div>

        <div class="radio-group">
            <p>نوع المستخدم</p>
            <label><input type="radio" name="type" value="beneficiary" checked> مستفيد</label>
            <label><input type="radio" name="type" value="donor"> متبرع</label>
        </div>

        <button type="submit" class="submit-btn">إنشاء حساب</button>
    </form>
</div>

<script>
    function togglePassword(id) {
        let input = document.getElementById(id);
        input.type = input.type === "password" ? "text" : "password";
    }
</script>

</body>
</html>
