<?php
session_start();

// تنظیمات دیتابیس برای Render
$db_config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'dbname' => getenv('DB_NAME') ?: 'chat_platform',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: ''
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch(PDOException $e) {
    // اگر دیتابیس وجود ندارد، ایجادش کن
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        die("دیتابیس وجود ندارد. لطفا دیتابیس 'chat_platform' را در MySQL ایجاد کنید.");
    } else {
        die("Connection failed: " . $e->getMessage());
    }
}

// ایجاد خودکار جداول
$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(11) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        code CHAR(7) UNIQUE NOT NULL,
        theme VARCHAR(10) DEFAULT 'light',
        font_size VARCHAR(10) DEFAULT 'medium',
        language VARCHAR(10) DEFAULT 'fa',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        contact_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (contact_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_contact (user_id, contact_id)
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT,
        receiver_id INT,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )
");

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
            'login' => 'ورود',
            'register' => 'ثبت نام',
            'phone' => 'شماره موبایل',
            'password' => 'رمز عبور',
            'name' => 'نام کامل',
            'welcome' => 'خوش آمدید',
            'logout' => 'خروج',
            'contacts' => 'مخاطبین',
            'settings' => 'تنظیمات',
            'add_contact' => 'اضافه کردن مخاطب',
            'send' => 'ارسال',
            'type_message' => 'پیام خود را بنویسید...'
        ],
        'en' => [
            'login' => 'Login',
            'register' => 'Register',
            'phone' => 'Phone Number',
            'password' => 'Password',
            'name' => 'Full Name',
            'welcome' => 'Welcome',
            'logout' => 'Logout',
            'contacts' => 'Contacts',
            'settings' => 'Settings',
            'add_contact' => 'Add Contact',
            'send' => 'Send',
            'type_message' => 'Type your message...'
        ],
        'ar' => [
            'login' => 'تسجيل الدخول',
            'register' => 'تسجيل',
            'phone' => 'رقم الهاتف',
            'password' => 'كلمة المرور',
            'name' => 'الاسم الكامل',
            'welcome' => 'أهلا بك',
            'logout' => 'تسجيل الخروج',
            'contacts' => 'جهات الاتصال',
            'settings' => 'الإعدادات',
            'add_contact' => 'إضافة جهة اتصال',
            'send' => 'إرسال',
            'type_message' => 'اكتب رسالتك...'
        ]
    ];
    
    return $translations[$lang][$key] ?? $key;
}
?>