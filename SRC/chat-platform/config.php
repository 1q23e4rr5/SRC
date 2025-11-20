<?php
session_start();

$host = 'localhost';
$dbname = 'chat_platform';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

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