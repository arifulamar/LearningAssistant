<?php

header("Content-Type: application/json; charset=UTF-8");

// API key harus disimpan di environment variable pada server.
// Jangan pernah menaruh secret di source code atau Git.
$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    http_response_code(503);
    echo json_encode(["type" => "text", "reply" => "Layanan Gemini belum dikonfigurasi. Silakan hubungi administrator."]);
    exit;
}

$model = "gemini-3.5-flash";
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
$message = trim($data['message'] ?? "");

if ($message === "") {
    echo json_encode(["type" => "text", "reply" => "Silakan ketik pertanyaan Anda."]);
    exit;
}

if (preg_match('/^tabel\\s*(.*)$/i', $message)) {
    $html = "<table border='1' cellpadding='5' cellspacing='0'><tr><th>Nama</th><th>Nilai</th></tr><tr><td>Ani</td><td>90</td></tr><tr><td>Budi</td><td>80</td></tr></table>";
    echo json_encode(["type" => "table", "html" => $html]);
    exit;
}

if (preg_match('/^grafik\\s*(.*)$/i', $message, $m)) {
    $pairs = preg_split('/\\s+/', trim($m[1]));
    $labels = [];
    $values = [];
    foreach ($pairs as $pair) {
        if (strpos($pair, ":") === false) continue;
        [$name, $val] = explode(":", $pair, 2);
        $labels[] = ucfirst(trim($name));
        $values[] = (int)trim($val);
    }
    if (count($labels) > 0) {
        echo json_encode(["type" => "chart", "config" => [
            "type" => "bar",
            "data" => ["labels" => $labels, "datasets" => [["label" => "Nilai", "data" => $values]]],
            "options" => ["responsive" => true, "plugins" => ["legend" => ["display" => true], "title" => ["display" => true, "text" => "Grafik Nilai"]]]
        ]]);
        exit;
    }
}

$payload = ["contents" => [["role" => "user", "parts" => [["text" => $message]]]]];
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . rawurlencode($model) . ":generateContent";
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json", "x-goog-api-key: " . $apiKey],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 60,
]);
$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    error_log("Gemini cURL error: " . $curlError);
    echo json_encode(["type" => "text", "reply" => "Gagal menghubungi server Gemini."]);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code(502);
    $errorData = json_decode($response, true);
    $errorMessage = $errorData['error']['message'] ?? "Gemini API mengembalikan HTTP $httpCode.";
    echo json_encode(["type" => "text", "reply" => "Gemini API Error: " . $errorMessage]);
    exit;
}

$result = json_decode($response, true);
if (!is_array($result)) {
    http_response_code(502);
    echo json_encode(["type" => "text", "reply" => "Respons Gemini tidak valid."]);
    exit;
}

$reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
if (!$reply) {
    echo json_encode(["type" => "text", "reply" => "Gemini tidak memberikan jawaban."]);
    exit;
}

if (preg_match('/!\\[.*?\\]\\((.*?)\\)/', $reply, $matches)) {
    echo json_encode(["type" => "image", "url" => $matches[1], "caption" => "Gambar dari Gemini."]);
    exit;
}

echo json_encode(["type" => "text", "reply" => $reply]);
exit;
