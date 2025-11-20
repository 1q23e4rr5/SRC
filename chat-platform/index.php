<?php
session_start();

// تنظیمات دیتابیس
$db_config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'dbname' => getenv('DB_NAME') ?: 'chat_platform',
    'username' => getenv('DB_USERNAME') ?: 'root', 
    'password' => getenv('DB_PASSWORD') ?: ''
];

// اتصال به دیتابیس
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}

// ایجاد خودکار جداول
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(11) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    code CHAR(7) UNIQUE NOT NULL,
    theme VARCHAR(10) DEFAULT 'light',
    font_size VARCHAR(10) DEFAULT 'medium', 
    language VARCHAR(10) DEFAULT 'fa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    contact_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_contact (user_id, contact_id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// توابع کمکی
function generateUniqueCode($pdo) {
    do {
        $code = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE code = ?");
        $stmt->execute([$code]);
    } while ($stmt->rowCount() > 0);
    return $code;
}

function getLanguageText($key, $lang = 'fa') {
    $translations = [
        'fa' => [
            'login' => 'ورود', 'register' => 'ثبت نام', 'phone' => 'شماره موبایل',
            'password' => 'رمز عبور', 'name' => 'نام کامل', 'welcome' => 'خوش آمدید',
            'logout' => 'خروج', 'contacts' => 'مخاطبین', 'settings' => 'تنظیمات',
            'add_contact' => 'اضافه کردن مخاطب', 'send' => 'ارسال', 
            'type_message' => 'پیام خود را بنویسید...', 'chat_with' => 'چت با'
        ],
        'en' => [
            'login' => 'Login', 'register' => 'Register', 'phone' => 'Phone Number',
            'password' => 'Password', 'name' => 'Full Name', 'welcome' => 'Welcome',
            'logout' => 'Logout', 'contacts' => 'Contacts', 'settings' => 'Settings',
            'add_contact' => 'Add Contact', 'send' => 'Send',
            'type_message' => 'Type your message...', 'chat_with' => 'Chat with'
        ],
        'ar' => [
            'login' => 'تسجيل الدخول', 'register' => 'تسجيل', 'phone' => 'رقم الهاتف',
            'password' => 'كلمة المرور', 'name' => 'الاسم الكامل', 'welcome' => 'أهلا بك',
            'logout' => 'تسجيل الخروج', 'contacts' => 'جهات الاتصال', 'settings' => 'الإعدادات',
            'add_contact' => 'إضافة جهة اتصال', 'send' => 'إرسال',
            'type_message' => 'اكتب رسالتك...', 'chat_with' => 'دردشة مع'
        ]
    ];
    return $translations[$lang][$key] ?? $key;
}

// مسیریابی
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

if ($path === '/' || $path === '/login') {
    include 'login.php';
} elseif ($path === '/register') {
    include 'register.php';
} elseif ($path === '/dashboard') {
    include 'dashboard.php';
} elseif ($path === '/admin') {
    include 'admin.php';
} elseif ($path === '/logout') {
    include 'logout.php';
} else {
    http_response_code(404);
    echo "صفحه یافت نشد";
}
?>
