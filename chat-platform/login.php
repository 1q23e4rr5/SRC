<?php
$lang = $_SESSION['language'] ?? 'fa';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['theme'] = $user['theme'];
        $_SESSION['font_size'] = $user['font_size'];
        $_SESSION['language'] = $user['language'];
        
        header("Location: /dashboard");
        exit();
    } else {
        $error = "شماره موبایل یا رمز عبور اشتباه است";
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <title>ورود</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-light">
    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;">ورود به پلتفرم چت</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>شماره موبایل:</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>رمز عبور:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">ورود</button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="/register" style="color: var(--primary-color);">حساب کاربری ندارید؟ ثبت نام کنید</a>
            </div>
            
            <div style="text-align: center; margin-top: 10px;">
                <a href="/admin" style="color: var(--secondary-color); font-size: 12px;">پنل مدیریت</a>
            </div>
        </div>
    </div>
</body>
</html>
