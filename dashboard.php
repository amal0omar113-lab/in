<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

checkLogin();
$user = getLoggedInUser();

if ($user['type'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Stats
$user_count = $pdo->query("SELECT COUNT(*) FROM users WHERE type != 'admin'")->fetchColumn();
$product_pending = $pdo->query("SELECT COUNT(*) FROM products WHERE approval_status = 'pending'")->fetchColumn();
$request_count = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - مدير النظام</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px; }
        .stat-card { background: #fff; border: 2px solid var(--dark-green); padding: 30px; border-radius: 20px; text-align: center; }
        .stat-card i { font-size: 40px; color: var(--dark-green); margin-bottom: 15px; }
        .stat-card h3 { font-size: 24px; margin-bottom: 10px; }
        .stat-card p { font-size: 30px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <?php include '../includes/header.php'; ?>
    
    <h2>مرحباً يا <?php echo $user['name']; ?> 👋</h2>
    <p>إليك ملخص لنشاط المنصة اليوم:</p>

    <div class="admin-grid">
        <a href="users.php" style="text-decoration:none; color:inherit;">
            <div class="stat-card">
                <i class="fa fa-users"></i>
                <h3>المستخدمين</h3>
                <p><?php echo $user_count; ?></p>
            </div>
        </a>
        <a href="products.php" style="text-decoration:none; color:inherit;">
            <div class="stat-card">
                <i class="fa fa-box"></i>
                <h3>تبرعات قيد المراجعة</h3>
                <p><?php echo $product_pending; ?></p>
            </div>
        </a>
        <a href="requests.php" style="text-decoration:none; color:inherit;">
            <div class="stat-card">
                <i class="fa fa-hand-holding-heart"></i>
                <h3>إجمالي الطلبات</h3>
                <p><?php echo $request_count; ?></p>
            </div>
        </a>
    </div>

    <div style="margin-top: 40px; display: flex; gap: 20px;">
        <a href="reports.php" class="btn-primary" style="padding: 15px 30px; text-decoration:none;">
            <i class="fa fa-file-invoice"></i> إصدار التقارير والإحصائيات
        </a>
        <a href="../logout.php" class="btn-primary" style="background: #cc0000; padding: 15px 30px; text-decoration:none;">
            <i class="fa fa-right-from-bracket"></i> تسجيل الخروج
        </a>
    </div>
</div>
</body>
</html>
