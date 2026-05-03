<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('STREAM_SLUG', basename(dirname(__DIR__)));
define('DB_PATH',     dirname(dirname(__DIR__)) . '/db/fake-famous.db');
define('CFG_PATH',    dirname(__DIR__) . '/config.json');

if (!file_exists(DB_PATH)) {
    http_response_code(503);
    echo json_encode(['error' => 'Database not initialised. Run ' . STREAM_SLUG . '/init.php first.']);
    exit;
}

$cfg = json_decode(file_get_contents(CFG_PATH), true);
$db  = new SQLite3(DB_PATH, SQLITE3_OPEN_READONLY);

// Resolve stream id from slug
$stmt = $db->prepare("SELECT id FROM streams WHERE slug = :slug");
$stmt->bindValue(':slug', STREAM_SLUG);
$row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Stream "' . STREAM_SLUG . '" not found. Run init.php.']);
    exit;
}
$streamId = $row['id'];

// Load cities (shared across all streams)
$cities = [];
$res = $db->query("SELECT name FROM cities");
while ($r = $res->fetchArray(SQLITE3_ASSOC)) { $cities[] = $r['name']; }

function sub(string $text, array $cfg, array $cities): string {
    $names = array_merge([$cfg['playerName']], $cfg['nicknames'] ?? []);
    return str_replace(
        ['{player}', '{channel}', '{airport}', '{runway}', '{arr_runway}', '{dep_runway}', '{location}', '{city}'],
        [$names[array_rand($names)], $cfg['channelName'], $cfg['airport'], $cfg['runway'], $cfg['arrivalRunway'], $cfg['departureRunway'], $cfg['viewerLocation'], $cities[array_rand($cities)]],
        $text
    );
}

$out = ['generic' => [], 'aviation' => [], 'superchat' => [], 'openers' => [], 'ticker' => [], 'usernames' => []];

// Openers in insertion order
$stmt = $db->prepare("SELECT username, text, is_mod FROM messages WHERE stream_id = :sid AND category = 'opener' ORDER BY id ASC");
$stmt->bindValue(':sid', $streamId);
$res = $stmt->execute();
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    $out['openers'][] = ['username' => $r['username'], 'text' => sub($r['text'], $cfg, $cities), 'is_mod' => (bool)$r['is_mod']];
}

// All other messages shuffled
$stmt = $db->prepare("SELECT category, text FROM messages WHERE stream_id = :sid AND category != 'opener' ORDER BY RANDOM()");
$stmt->bindValue(':sid', $streamId);
$res = $stmt->execute();
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    $cat = $r['category'];
    if (isset($out[$cat])) $out[$cat][] = sub($r['text'], $cfg, $cities);
}

// Usernames for this stream
$stmt = $db->prepare("SELECT name FROM usernames WHERE stream_id = :sid ORDER BY RANDOM()");
$stmt->bindValue(':sid', $streamId);
$res = $stmt->execute();
while ($r = $res->fetchArray(SQLITE3_ASSOC)) { $out['usernames'][] = $r['name']; }

// Known characters — global, added weight times each so ri() picks them more often
$res = $db->query("SELECT username, weight FROM characters ORDER BY RANDOM()");
while ($r = $res->fetchArray(SQLITE3_ASSOC)) {
    for ($i = 0; $i < (int)$r['weight']; $i++) $out['usernames'][] = $r['username'];
}
shuffle($out['usernames']);

$db->close();
echo json_encode($out);
