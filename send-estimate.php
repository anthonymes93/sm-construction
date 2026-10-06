<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond(int $status, bool $ok): void {
    http_response_code($status);
    echo json_encode(['ok' => $ok]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, false);
}
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 24000) respond(413, false);

// Store this configuration ABOVE public_html, never in the public website.
$configPath = dirname(__DIR__) . '/sm-resend-config.php';
if (!is_file($configPath) || !function_exists('curl_init')) respond(503, false);
$config = require $configPath;
if (!is_array($config) || empty($config['api_key']) || empty($config['from']) || empty($config['to'])) respond(503, false);

$limits = ['name' => 150, 'phone' => 60, 'email' => 254, 'city' => 150, 'project' => 80, 'contact' => 30, 'details' => 6000, 'website' => 200];
$data = [];
foreach ($limits as $field => $limit) {
    $value = $_POST[$field] ?? '';
    if (!is_string($value) || strlen($value) > $limit) respond(422, false);
    $data[$field] = trim($value);
}
// Honeypot: ordinary visitors leave this field empty.
if ($data['website'] !== '') respond(200, true);
foreach (['name', 'phone', 'details'] as $field) {
    if ($data[$field] === '') respond(422, false);
}
if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) respond(422, false);
if (!in_array($data['project'], ['Bathroom Renovation', 'Kitchen Renovation', 'Basement Renovation', 'Interior Renovation / Repair', 'Other'], true)) respond(422, false);
if (!in_array($data['contact'], ['Phone call', 'Text message', 'Email'], true)) respond(422, false);
if ($data['contact'] === 'Email' && $data['email'] === '') respond(422, false);

// A bounded, locked file outside the web root limits each IP to five attempts per ten minutes.
$ratePath = sys_get_temp_dir() . '/sm-estimate-' . hash('sha256', __DIR__) . '.json';
$rateFile = @fopen($ratePath, 'c+');
if (!$rateFile || !flock($rateFile, LOCK_EX)) respond(503, false);
$rates = json_decode(stream_get_contents($rateFile), true) ?: [];
$now = time();
foreach ($rates as $key => $entry) {
    if ($entry['until'] <= $now) unset($rates[$key]);
}
$ip = hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown', $config['api_key']);
$entry = $rates[$ip] ?? ['until' => $now + 600, 'count' => 0];
if ($entry['count'] >= 5 || count($rates) >= 10000) {
    flock($rateFile, LOCK_UN);
    fclose($rateFile);
    header('Retry-After: 600');
    respond(429, false);
}
$entry['count']++;
$rates[$ip] = $entry;
rewind($rateFile);
ftruncate($rateFile, 0);
fwrite($rateFile, json_encode($rates));
fflush($rateFile);
flock($rateFile, LOCK_UN);
fclose($rateFile);

$text = "New website estimate request\n\n";
foreach (['name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'city' => 'City / Project Area', 'project' => 'Project Type', 'contact' => 'Preferred Contact', 'details' => 'Project Details'] as $field => $label) {
    $text .= $label . ': ' . ($data[$field] ?: '(not provided)') . "\n\n";
}
$payload = [
    'from' => $config['from'],
    'to' => [$config['to']],
    'subject' => 'Website estimate request - ' . $data['project'],
    'text' => $text,
];
if ($data['email'] !== '') $payload['reply_to'] = $data['email'];
$json = json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);
$curl = curl_init('https://api.resend.com/emails');
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 25,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $config['api_key'],
        'Content-Type: application/json',
        // Resend deduplicates identical submissions for 24 hours, including uncertain retries.
        'Idempotency-Key: estimate-' . hash('sha256', $json),
    ],
    CURLOPT_POSTFIELDS => $json,
]);
$response = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
$result = is_string($response) ? json_decode($response, true) : null;
if ($status < 200 || $status >= 300 || empty($result['id'])) {
    error_log('SM Construction: Resend submission failed; HTTP status ' . $status);
    respond(502, false);
}
respond(200, true);
