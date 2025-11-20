<?php
// بررسی دسترسی ادمین
$admin_phone = '09120000000';
if (!isset($_SESSION['user_phone']) || $_SESSION['user_phone'] !== $admin_phone) {
    header("Location: /login");
    exit();
}

// دریافت تمام کاربران
$stmt = $pdo->query("SELECT id, name, phone, code, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// دریافت تمام پیام‌ها
$stmt = $pdo->query("
    SELECT m.*, u1.name as sender_name, u2.name as receiver_name 
    FROM messages m 
    JOIN users u1 ON m.sender_id = u1.id 
    JOIN users u2 ON m.receiver_id = u2.id 
    ORDER BY m.created_at DESC
");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پنل مدیریت</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-dark">
    <div class="container">
        <h1>پنل مدیریت سیستم چت</h1>
        
        <div style="margin: 20px 0;">
            <a href="/dashboard" class="btn btn-primary">بازگشت به پنل کاربری</a>
            <a href="/logout" class="btn" style="background-color: #dc3545; color: white;">خروج</a>
        </div>

        <h2>لیست کاربران (<?= count($users) ?> نفر)</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr style="background-color: var(--primary-color); color: white;">
                        <th style="padding: 10px; border: 1px solid var(--border-color);">نام</th>
                        <th style="padding: 10px; border: 1px solid var(--border-color);">شماره موبایل</th>
                        <th style="padding: 10px; border: 1px solid var(--border-color);">کد دائمی</th>
                        <th style="padding: 10px; border: 1px solid var(--border-color);">تاریخ ثبت‌نام</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= htmlspecialchars($user['name']) ?></td>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= $user['phone'] ?></td>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= $user['code'] ?></td>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= $user['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2>تاریخچه چت‌ها (<?= count($messages) ?> پیام)</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr style="background-color: var(--primary-color); color: white;">
                        <th style="padding: 10px; border: 1px solid var(--border-color);">فرستنده</th>
                        <th style="padding: 10px; border: 1px solid var(--border-color);">گیرنده</th>
                        <th style="padding: 10px; border: 1px solid var(--border-color);">پیام</th>
                        <th style="padding: 10px; border: 1px solid var(--border-color);">زمان</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $message): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= htmlspecialchars($message['sender_name']) ?></td>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= htmlspecialchars($message['receiver_name']) ?></td>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= htmlspecialchars($message['message']) ?></td>
                        <td style="padding: 10px; border: 1px solid var(--border-color);"><?= $message['created_at'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
