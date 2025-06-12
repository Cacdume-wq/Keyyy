<?php
// Tạo key ngẫu nhiên dài 32 ký tự
function generateKey($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Tạo key mới
$key = generateKey();

// Trả về JSON
header('Content-Type: application/json');
echo json_encode([
    "status" => "success",
    "key" => $key,
    "created_at" => date("Y-m-d H:i:s")
]);
