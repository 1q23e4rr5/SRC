<?php
require_once 'config.php';

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
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "شماره موبایل یا رمز عبور اشتباه است";
    }
}

$lang = $_SESSION['language'] ?? 'fa';
?>
<!DOCTYPE html>
<html lang="fa" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo getLanguageText('login', $lang); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-light">
    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;"><?php echo getLanguageText('login', $lang); ?></h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label><?php echo getLanguageText('phone', $lang); ?>:</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label><?php echo getLanguageText('password', $lang); ?>:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <?php echo getLanguageText('login', $lang); ?>
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="register.php" style="color: var(--primary-color);">
                    <?php echo getLanguageText('register', $lang); ?>
                </a>
            </div>
            
            <div style="text-align: center; margin-top: 10px;">
                <a href="admin.php" style="color: var(--secondary-color); font-size: 12px;">
                    پنل مدیریت
                </a>
            </div>
        </div>
    </div>
</body>
</html>