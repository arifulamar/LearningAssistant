<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data login tidak valid.']);
    exit;
}

$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$name = trim((string)($input['name'] ?? ''));
$photoURL = trim((string)($input['photoURL'] ?? ''));

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email pengguna tidak valid.']);
    exit;
}

// Hindari nilai URL profil berbahaya; foto hanya dipakai sebagai sumber gambar.
if ($photoURL !== '' && !filter_var($photoURL, FILTER_VALIDATE_URL)) {
    $photoURL = '';
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'name' => $name !== '' ? $name : $email,
    'email' => $email,
    'photoURL' => $photoURL
];

echo json_encode(['success' => true]);
