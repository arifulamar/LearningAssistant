<?php
session_start();

$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['email'])) {
    $_SESSION['user'] = [
        'name' => $input['name'],
        'email' => $input['email'],
        'photoURL' => $input['photoURL']
    ];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
