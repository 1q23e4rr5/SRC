<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    
    // بررسی وجود کاربر
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

$lang = $_SESSION['language'] ?? 'fa';
?>
<!DOCTYPE html>
<html lang="fa" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo getLanguageText('register', $lang); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-light">
    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; margin-bottom: 30px;"><?php echo getLanguageText('register', $lang); ?></h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label><?php echo getLanguageText('name', $lang); ?>:</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label><?php echo getLanguageText('phone', $lang); ?>:</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label><?php echo getLanguageText('password', $lang); ?>:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <?php echo getLanguageText('register', $lang); ?>
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="color: var(--primary-color);">
                    <?php echo getLanguageText('login', $lang); ?>
                </a>
            </div>
        </div>
    </div>
</body>
</html>