<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

checkLogin();
$user = getLoggedInUser();

if ($user['type'] !== 'donor') {
    header("Location: ../beneficiary/home.php");
    exit();
}

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE donor_id = ?");
$stmt->execute([$user['id']]);
$total_products = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM requests r JOIN products p ON r.product_id = p.id WHERE p.donor_id = ? AND r.status = 'pending'");
$stmt->execute([$user['id']]);
$pending_requests = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة المتبرع - يداً بيد</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: #fff; padding: 30px; border-radius: 20px; border: 2px solid var(--dark-green); text-align: center; }
        .stat-card h3 { font-size: 32px; color: var(--dark-green); }
        .stat-card p { color: #666; font-weight: bold; }
        .donor-actions { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; }
        .donor-actions a { padding: 15px 30px; text-decoration: none; font-size: 18px; }
    </style>
</head>
<body>
<div class="container">
    <?php include '../includes/header.php'; ?>
    <h2 style="margin-bottom: 30px; text-align: center;">مرحباً بك يا <?php echo htmlspecialchars($user['name']); ?></h2>
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $total_products; ?></h3>
            <p>إجمالي القطع المضافة</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $pending_requests; ?></h3>
            <p>طلبات بانتظار الموافقة</p>
        </div>
    </div>
    <div class="donor-actions">
        <a href="add.php" class="btn-primary"><i class="fa fa-plus"></i> إضافة قطعة جديدة</a>
        <a href="products.php" class="btn-primary"><i class="fa fa-list"></i> إدارة قطعي</a>
        <a href="requests.php" class="btn-primary"><i class="fa fa-hand-holding-heart"></i> عرض الطلبات</a>
        <a href="../messages.php" class="btn-primary" style="background: #333;"><i class="fa fa-envelope"></i> عرض رسائلي</a>
        <a href="../logout.php" class="btn-primary" style="background: #cc0000;"><i class="fa fa-right-from-bracket"></i> تسجيل الخروج</a>
    </div>
</div>
</body>
</html>
