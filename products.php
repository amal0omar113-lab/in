<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

checkLogin();
$user = getLoggedInUser();

if ($user['type'] !== 'donor') {
    header("Location: ../beneficiary/home.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND donor_id = ?");
    $stmt->execute([$id, $user['id']]);
    header("Location: products.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE donor_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قطعي المضافة - يداً بيد</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <?php include '../includes/header.php'; ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>إدارة قطع الملابس الخاصة بي</h2>
        <a href="add.php" class="btn-primary">+ إضافة جديد</a>
    </div>
    <main class="products-grid">
        <?php if (empty($products)): ?>
            <p style="text-align:center; grid-column: 1 / -1;">لم تقم بإضافة أي قطع بعد.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="card">
                    <div class="img-container"><img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="p"></div>
                    <div class="card-footer" style="flex-direction: column; align-items: stretch; gap: 10px;">
                        <p style="text-align: center; margin-bottom: 10px;"><?php echo htmlspecialchars($product['name']); ?></p>
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-primary" style="background: var(--dark-green); text-align: center; text-decoration: none;"><i class="fa fa-edit"></i> تعديل البيانات</a>
                            <div style="display: flex; gap: 5px;">
                                <a href="?delete=<?php echo $product['id']; ?>" class="btn-primary" style="background: #cc0000; flex: 1; text-align: center; text-decoration: none;" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
                                <span class="btn-primary" style="background: <?php echo $product['status'] == 'available' ? '#28a745' : '#666'; ?>; flex: 1; text-align: center;">
                                    <?php $st_ar = ['available' => 'متاح', 'requested' => 'مطلوب', 'donated' => 'تم وهبها']; echo $st_ar[$product['status']]; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
