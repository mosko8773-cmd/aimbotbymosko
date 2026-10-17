```php
<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'استخدم POST'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$key = strtoupper(trim($input['key'] ?? ''));

if ($key === '') {
    echo json_encode([
        'success' => false,
        'message' => 'اكتب المفتاح أولا'
    ]);
    exit;
}

if (!preg_match('/^MOSKO-[A-Z0-9]{12}$/', $key)) {
    echo json_encode([
        'success' => false,
        'message' => 'المفتاح غير مكتمل أو تنسيقه خاطئ'
    ]);
    exit;
}

/*
 * ضع المفاتيح في ملف keys.json
 * لا تضع هذا الملف في مجلد عام يمكن للزوار قراءته.
 */
$database = __DIR__ . '/private_keys.json';

if (!is_file($database)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'قاعدة المفاتيح غير موجودة'
    ]);
    exit;
}

$keys = json_decode(file_get_contents($database), true);

if (!is_array($keys) || !isset($keys[$key])) {
    echo json_encode([
        'success' => false,
        'message' => 'المفتاح خاطئ'
    ]);
    exit;
}

$expiresAt = $keys[$key]['expiresAt'] ?? null;

if ($expiresAt !== null && time() > (int)$expiresAt) {
    echo json_encode([
        'success' => false,
        'message' => 'انتهت صلاحية المفتاح'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'valid' => true,
    'message' => 'المفتاح صحيح',
    'expiresAt' => $expiresAt
]);
```
