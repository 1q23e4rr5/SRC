<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$lang = $_SESSION['language'] ?? 'fa';

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
    
    header("Location: dashboard.php");
    exit();
}

// اضافه کردن مخاطب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {
    $contact_code = $_POST['contact_code'];
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE code = ? AND id != ?");
    $stmt->execute([$contact_code, $user_id]);
    $contact = $stmt->fetch();
    
    if ($contact) {
        // بررسی وجود قبلی مخاطب
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
    
    header("Location: dashboard.php?chat=" . $receiver_id);
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
?>
<!DOCTYPE html>
<html lang="fa" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <title>پنل کاربری</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-<?php echo $_SESSION['theme']; ?> font-<?php echo $_SESSION['font_size']; ?>">
    <div class="dashboard">
        <!-- سایدبار -->
        <div class="sidebar">
            <div style="margin-bottom: 30px;">
                <h3>👋 <?php echo $_SESSION['user_name']; ?></h3>
                <p style="font-size: 12px; color: var(--secondary-color);">
                    کد شما: <?php 
                    $stmt = $pdo->prepare("SELECT code FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_code = $stmt->fetch();
                    echo $user_code['code'];
                    ?>
                </p>
            </div>

            <nav style="flex: 1;">
                <h4><?php echo getLanguageText('contacts', $lang); ?></h4>
                <?php foreach ($contacts as $contact): ?>
                    <a href="?chat=<?php echo $contact['id']; ?>" 
                       style="display: block; padding: 10px; margin: 5px 0; 
                              background: <?php echo isset($_GET['chat']) && $_GET['chat'] == $contact['id'] ? 'var(--primary-color)' : 'transparent'; ?>;
                              color: <?php echo isset($_GET['chat']) && $_GET['chat'] == $contact['id'] ? 'white' : 'var(--text-color)'; ?>;
                              text-decoration: none; border-radius: 5px;">
                        📞 <?php echo $contact['name']; ?>
                    </a>
                <?php endforeach; ?>
                
                <hr style="margin: 20px 0;">
                
                <h4><?php echo getLanguageText('settings', $lang); ?></h4>
                <form method="POST" style="margin-top: 10px;">
                    <div class="form-group">
                        <label>تم:</label>
                        <select name="theme" class="form-control">
                            <option value="light" <?php echo $_SESSION['theme'] == 'light' ? 'selected' : ''; ?>>روشن</option>
                            <option value="dark" <?php echo $_SESSION['theme'] == 'dark' ? 'selected' : ''; ?>>تیره</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>سایز فونت:</label>
                        <select name="font_size" class="form-control">
                            <option value="small" <?php echo $_SESSION['font_size'] == 'small' ? 'selected' : ''; ?>>کوچک</option>
                            <option value="medium" <?php echo $_SESSION['font_size'] == 'medium' ? 'selected' : ''; ?>>متوسط</option>
                            <option value="large" <?php echo $_SESSION['font_size'] == 'large' ? 'selected' : ''; ?>>بزرگ</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>زبان:</label>
                        <select name="language" class="form-control">
                            <option value="fa" <?php echo $_SESSION['language'] == 'fa' ? 'selected' : ''; ?>>فارسی</option>
                            <option value="en" <?php echo $_SESSION['language'] == 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="ar" <?php echo $_SESSION['language'] == 'ar' ? 'selected' : ''; ?>>العربية</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update_settings" class="btn btn-primary btn-block">
                        ذخیره تنظیمات
                    </button>
                </form>
                
                <hr style="margin: 20px 0;">
                
                <h4><?php echo getLanguageText('add_contact', $lang); ?></h4>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="contact_code" class="form-control" 
                               placeholder="کد 7 رقمی مخاطب" required>
                    </div>
                    <button type="submit" name="add_contact" class="btn btn-primary btn-block">
                        <?php echo getLanguageText('add_contact', $lang); ?>
                    </button>
                </form>
                
                <?php if (isset($contact_success)): ?>
                    <div class="alert alert-success" style="margin-top: 10px; font-size: 12px;">
                        <?php echo $contact_success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($contact_error)): ?>
                    <div class="alert alert-error" style="margin-top: 10px; font-size: 12px;">
                        <?php echo $contact_error; ?>
                    </div>
                <?php endif; ?>
            </nav>

            <a href="logout.php" class="btn" 
               style="background-color: #dc3545; color: white; text-align: center; text-decoration: none;">
                <?php echo getLanguageText('logout', $lang); ?>
            </a>
        </div>

        <!-- محتوای اصلی -->
        <div class="content">
            <?php if (isset($_GET['chat']) && $current_chat): ?>
                <!-- صفحه چت -->
                <div class="chat-container">
                    <div style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <h3>چت با <?php echo $current_chat['name']; ?></h3>
                    </div>

                    <div class="messages">
                        <?php foreach ($chat_messages as $message): ?>
                            <div class="message <?php echo $message['sender_id'] == $user_id ? 'message-sent' : 'message-received'; ?>">
                                <?php echo htmlspecialchars($message['message']); ?>
                                <div style="font-size: 10px; margin-top: 5px;">
                                    <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form method="POST" class="message-input">
                        <input type="hidden" name="receiver_id" value="<?php echo $_GET['chat']; ?>">
                        <input type="text" name="message" class="form-control" 
                               placeholder="<?php echo getLanguageText('type_message', $lang); ?>" required>
                        <button type="submit" name="send_message" class="btn btn-primary">
                            <?php echo getLanguageText('send', $lang); ?>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <!-- صفحه اصلی دشبورد -->
                <h2><?php echo getLanguageText('welcome', $lang); ?> <?php echo $_SESSION['user_name']; ?>!</h2>
                <p>برای شروع چت، یکی از مخاطبین خود را انتخاب کنید یا مخاطب جدید اضافه کنید.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>