<?php
$lang = $_SESSION['language'] ?? 'fa';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    
    if ($stmt->rowCount() > 0) {
        $error = "شماره موبایل قبلا ثبت شده است";
    } else {
        $code = generateUniqueCode($pdo);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, password, code) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $hashedPassword, $code]);
        
        $success = "ثبت نام موفقیت آمیز بود! کد دائمی شما: <strong>$code</strong>";
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <title>ثبت نام</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-light">
    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;">ثبت نام در پلتفرم چت</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>نام کامل:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>شماره موبایل:</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>رمز عبور:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">ثبت نام</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="/login" style="color: var(--primary-color);">قبلا ثبت نام کرده‌اید؟ وارد شوید</a>
            </div>
        </div>
    </div>
</body>
</html>
