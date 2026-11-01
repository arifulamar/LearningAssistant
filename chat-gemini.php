<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// ======================================================
// GEMINI API KEY
// ======================================================

$apiKey = "AQ.Ab8RN6K_HkKAYCI4QiinATxZFHejrGBNzro5PWWQDRBPoP6wKw";

// Model yang masih aktif
$model = "gemini-3.5-flash";

// ======================================================
// AMBIL INPUT
// ======================================================

$raw = file_get_contents("php://input");

$data = json_decode($raw, true);

$message = trim($data['message'] ?? "");

if ($message === "") {
    echo json_encode([
        "type" => "text",
        "reply" => "Silakan ketik pertanyaan Anda."
    ]);
    exit;
}

// ======================================================
// ROUTING MANUAL - TABEL
// ======================================================

if (preg_match('/^tabel\s*(.*)$/i', $message, $m)) {

    $html = "
        <table border='1' cellpadding='5' cellspacing='0'>
            <tr>
                <th>Nama</th>
                <th>Nilai</th>
            </tr>
            <tr>
                <td>Ani</td>
                <td>90</td>
            </tr>
            <tr>
                <td>Budi</td>
                <td>80</td>
            </tr>
        </table>
    ";

    echo json_encode([
        "type" => "table",
        "html" => $html
    ]);

    exit;
}

// ======================================================
// ROUTING MANUAL - GRAFIK
// ======================================================

if (preg_match('/^grafik\s*(.*)$/i', $message, $m)) {

    $pairs = preg_split('/\s+/', trim($m[1]));

    $labels = [];
    $values = [];

    foreach ($pairs as $pair) {

        if (strpos($pair, ":") === false) {
            continue;
        }

        [$name, $val] = explode(":", $pair, 2);

        $labels[] = ucfirst(trim($name));
        $values[] = (int)trim($val);
    }

    if (count($labels) > 0) {

        echo json_encode([
            "type" => "chart",
            "config" => [
                "type" => "bar",
                "data" => [
                    "labels" => $labels,
                    "datasets" => [[
                        "label" => "Nilai",
                        "data" => $values
                    ]]
                ],
                "options" => [
                    "responsive" => true,
                    "plugins" => [
                        "legend" => [
                            "display" => true
                        ],
                        "title" => [
                            "display" => true,
                            "text" => "Grafik Nilai"
                        ]
                    ]
                ]
            ]
        ]);

        exit;
    }
}

// ======================================================
// GEMINI REQUEST
// ======================================================

$payload = [
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                [
                    "text" => $message
                ]
            ]
        ]
    ]
];

$url = "https://generativelanguage.googleapis.com/v1beta/models/"
     . $model
     . ":generateContent?key="
     . urlencode($apiKey);

// ======================================================
// CURL
// ======================================================

$ch = curl_init();

curl_setopt_array($ch, [

    CURLOPT_URL => $url,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_POST => true,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],

    CURLOPT_POSTFIELDS => json_encode($payload),

    CURLOPT_TIMEOUT => 60,

]);

$response = curl_exec($ch);

$curlError = curl_error($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// ======================================================
// CURL ERROR
// ======================================================

if ($response === false) {

    http_response_code(500);

    echo json_encode([
        "type" => "text",
        "reply" => "Gagal menghubungi server Gemini.",
        "error" => $curlError
    ]);

    exit;
}

// ======================================================
// HTTP ERROR
// ======================================================

if ($httpCode < 200 || $httpCode >= 300) {

    http_response_code(500);

    $errorData = json_decode($response, true);

    $errorMessage =
        $errorData['error']['message']
        ?? "Gemini API mengembalikan HTTP $httpCode.";

    echo json_encode([
        "type" => "text",
        "reply" => "⚠️ Gemini API Error: " . $errorMessage,
        "http_code" => $httpCode
    ]);

    exit;
}

// ======================================================
// PARSE RESPONSE
// ======================================================

$result = json_decode($response, true);

if (!$result) {

    http_response_code(500);

    echo json_encode([
        "type" => "text",
        "reply" => "Respons Gemini tidak valid.",
        "raw_response" => $response
    ]);

    exit;
}

// ======================================================
// AMBIL JAWABAN
// ======================================================

$reply =
    $result['candidates'][0]['content']['parts'][0]['text']
    ?? null;

if (!$reply) {

    echo json_encode([
        "type" => "text",
        "reply" => "Gemini tidak memberikan jawaban.",
        "debug" => $result
    ]);

    exit;
}

// ======================================================
// CEK MARKDOWN IMAGE
// ======================================================

$imagePattern = '/!\[.*?\]\((.*?)\)/';

if (preg_match($imagePattern, $reply, $matches)) {

    $imageUrl = $matches[1];

    echo json_encode([
        "type" => "image",
        "url" => $imageUrl,
        "caption" => "Gambar dari Gemini."
    ]);

    exit;
}

// ======================================================
// DEFAULT TEXT
// ======================================================

echo json_encode([
    "type" => "text",
    "reply" => $reply
]);

exit;
?>