<?php
header('Content-Type: application/json');

// Cấu hình
$api_token = '6836efcfcdece32a0a1e56b8'; // Token của bạn
$key_secret = 'phuocan_secret';         // Chuỗi bí mật để mã hóa

// Hàm tạo key: PAP-XXXXXX
function generate_key($length = 6) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $rand = '';
    for ($i = 0; $i < $length; $i++) {
        $rand .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return 'PAP-' . $rand;
}

// XOR mã hóa
function xor_encrypt($data, $key) {
    $out = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $out .= chr(ord($data[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return bin2hex($out);
}

// Tạo key và mã hóa
$raw_key = generate_key();
$encrypted_key = xor_encrypt($raw_key, $key_secret);

// Link chứa key mã hóa
$long_url = "https://www.webkey.x10.mx/?ma=" . urlencode($encrypted_key);

// Gọi API rút gọn
$api_url = "https://link2m.net/api-shorten/v2?api={$api_token}&url=" . urlencode($long_url);
$response = @file_get_contents($api_url);

// Kiểm tra lỗi gọi API
if ($response === FALSE) {
    echo json_encode([
        "status" => "error",
        "message" => "Không thể kết nối đến máy chủ rút gọn link"
    ]);
    exit;
}

$result = json_decode($response, true);
if (!isset($result["status"]) || strtolower($result["status"]) !== 'thành công') {
    echo json_encode([
        "status" => "error",
        "message" => $result["message"] ?? "API trả lỗi không xác định"
    ]);
    exit;
}

// Lưu vào keys.json
$data_file = 'keys.json';
$all = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : [];
$all[] = [
    "raw_key" => $raw_key,
    "created_at" => time(),
    "used" => false
];
file_put_contents($data_file, json_encode($all, JSON_PRETTY_PRINT));

// ✅ Trả về JSON KHÔNG chứa raw_key (ma)
echo json_encode([
    "status" => "success",
    "encrypted_key" => $encrypted_key,
    "link" => $result["shortenedUrl"],
    "message" => "Tạo key thành công"
]);
?>
