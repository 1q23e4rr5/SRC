<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: /login");
    exit();
}

$user_id = $_SESSION['user_id'];
$lang = $_SESSION['language'] ?? 'fa';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// به‌روزرسانی تنظیمات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $theme = $_POST['theme'];
    $font_size = $_POST['font_size'];
    $language = $_POST['language'];
    
    $stmt = $pdo->prepare("UPDATE users SET theme = ?, font_size = ?, language = ? WHERE id = ?");
    $stmt->execute([$theme, $font_size, $language, $user_id]);
    
    $_SESSION['theme'] = $theme;
    $_SESSION['font_size'] = $font_size;
    $_SESSION['language'] = $language;
    
    header("Location: /dashboard");
    exit();
}

// اضافه کردن مخاطب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {
    $contact_code = $_POST['contact_code'];
    
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE code = ? AND id != ?");
    $stmt->execute([$contact_code, $user_id]);
    $contact = $stmt->fetch();
    
    if ($contact) {
        $stmt = $pdo->prepare("SELECT id FROM contacts WHERE user_id = ? AND contact_id = ?");
        $stmt->execute([$user_id, $contact['id']]);
        
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO contacts (user_id, contact_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $contact['id']]);
            $contact_success = "مخاطب با موفقیت اضافه شد";
        } else {
            $contact_error = "این مخاطب قبلا اضافه شده است";
        }
    } else {
        $contact_error = "کد مخاطب یافت نشد";
    }
}

// ارسال پیام
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $receiver_id, $message]);
    
    header("Location: /dashboard?chat=" . $receiver_id);
    exit();
}

// دریافت مخاطبین
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.code 
    FROM contacts c 
    JOIN users u ON c.contact_id = u.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$contacts = $stmt->fetchAll();

// دریافت پیام‌ها
$chat_messages = [];
if (isset($_GET['chat'])) {
    $receiver_id = $_GET['chat'];
    
    $stmt = $pdo->prepare("
        SELECT m.*, u.name as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) 
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
    $chat_messages = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $current_chat = $stmt->fetch();
}

// دریافت کد کاربر
$stmt = $pdo->prepare("SELECT code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_code = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fa" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <title>پنل کاربری</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-<?= $_SESSION['theme'] ?> font-<?= $_SESSION['font_size'] ?>">
    <div class="dashboard">
        <div class="sidebar">
            <div style="margin-bottom: 30px;">
                <h3>👋 <?= $_SESSION['user_name'] ?></h3>
                <p style="font-size: 12px; color: var(--secondary-color);">
                    کد شما: <?= $user_code['code'] ?>
                </p>
            </div>

            <nav style="flex: 1;">
                <h4>مخاطبین</h4>
                <?php foreach ($contacts as $contact): ?>
                    <a href="?chat=<?= $contact['id'] ?>" 
                       style="display: block; padding: 10px; margin: 5px 0; 
                              background: <?= isset($_GET['chat']) && $_GET['chat'] == $contact['id'] ? 'var(--primary-color)' : 'transparent' ?>;
                              color: <?= isset($_GET['chat']) && $_GET['chat'] == $contact['id'] ? 'white' : 'var(--text-color)' ?>;
                              text-decoration: none; border-radius: 5px;">
                        📞 <?= $contact['name'] ?>
                    </a>
                <?php endforeach; ?>
                
                <hr style="margin: 20px 0;">
                
                <h4>تنظیمات</h4>
                <form method="POST" style="margin-top: 10px;">
                    <div class="form-group">
                        <label>تم:</label>
                        <select name="theme" class="form-control">
                            <option value="light" <?= $_SESSION['theme'] == 'light' ? 'selected' : '' ?>>روشن</option>
                            <option value="dark" <?= $_SESSION['theme'] == 'dark' ? 'selected' : '' ?>>تیره</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>سایز فونت:</label>
                        <select name="font_size" class="form-control">
                            <option value="small" <?= $_SESSION['font_size'] == 'small' ? 'selected' : '' ?>>کوچک</option>
                            <option value="medium" <?= $_SESSION['font_size'] == 'medium' ? 'selected' : '' ?>>متوسط</option>
                            <option value="large" <?= $_SESSION['font_size'] == 'large' ? 'selected' : '' ?>>بزرگ</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>زبان:</label>
                        <select name="language" class="form-control">
                            <option value="fa" <?= $_SESSION['language'] == 'fa' ? 'selected' : '' ?>>فارسی</option>
                            <option value="en" <?= $_SESSION['language'] == 'en' ? 'selected' : '' ?>>English</option>
                            <option value="ar" <?= $_SESSION['language'] == 'ar' ? 'selected' : '' ?>>العربية</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update_settings" class="btn btn-primary btn-block">
                        ذخیره تنظیمات
                    </button>
                </form>
                
                <hr style="margin: 20px 0;">
                
                <h4>اضافه کردن مخاطب</h4>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="contact_code" class="form-control" 
                               placeholder="کد 7 رقمی مخاطب" required>
                    </div>
                    <button type="submit" name="add_contact" class="btn btn-primary btn-block">
                        اضافه کردن مخاطب
                    </button>
                </form>
                
                <?php if (isset($contact_success)): ?>
                    <div class="alert alert-success" style="margin-top: 10px; font-size: 12px;">
                        <?= $contact_success ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($contact_error)): ?>
                    <div class="alert alert-error" style="margin-top: 10px; font-size: 12px;">
                        <?= $contact_error ?>
                    </div>
                <?php endif; ?>
            </nav>

            <a href="/logout" class="btn" 
               style="background-color: #dc3545; color: white; text-align: center; text-decoration: none;">
                خروج
            </a>
        </div>

        <div class="content">
            <?php if (isset($_GET['chat']) && $current_chat): ?>
                <div class="chat-container">
                    <div style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <h3>چت با <?= $current_chat['name'] ?></h3>
                    </div>

                    <div class="messages">
                        <?php foreach ($chat_messages as $message): ?>
                            <div class="message <?= $message['sender_id'] == $user_id ? 'message-sent' : 'message-received' ?>">
                                <?= htmlspecialchars($message['message']) ?>
                                <div style="font-size: 10px; margin-top: 5px;">
                                    <?= date('H:i', strtotime($message['created_at'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form method="POST" class="message-input">
                        <input type="hidden" name="receiver_id" value="<?= $_GET['chat'] ?>">
                        <input type="text" name="message" class="form-control" 
                               placeholder="پیام خود را بنویسید..." required>
                        <button type="submit" name="send_message" class="btn btn-primary">ارسال</button>
                    </form>
                </div>
            <?php else: ?>
                <h2>خوش آمدید <?= $_SESSION['user_name'] ?>!</h2>
                <p>برای شروع چت، یکی از مخاطبین خود را انتخاب کنید یا مخاطب جدید اضافه کنید.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
