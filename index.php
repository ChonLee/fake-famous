<?php
// Auto-discover streams — any subdirectory containing a config.json
$streams = [];
foreach (glob(__DIR__ . '/*/config.json') as $cfgFile) {
    $cfg = json_decode(file_get_contents($cfgFile), true);
    $streams[] = [
        'dir'    => basename(dirname($cfgFile)),
        'name'   => $cfg['channelName']   ?? 'Untitled Stream',
        'desc'   => $cfg['description']   ?? '',
        'player' => $cfg['playerName']    ?? '',
        'icon'   => $cfg['icon']          ?? '🔴',
    ];
}
usort($streams, fn($a, $b) => strcmp($a['name'], $b['name']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fake Famous — Choose Your Stream</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
  * { box-sizing: border-box; margin: 0; padding: 0; }
  :root { --red: #FF0000; --dark: #0f0f0f; --card: #212121; --border: #303030; --text: #f1f1f1; --sub: #aaaaaa; }
  body { background: var(--dark); color: var(--text); font-family: 'Roboto', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }

  .topbar {
    background: #212121; padding: 12px 20px;
    display: flex; align-items: center; gap: 10px;
    border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 10;
  }
  .yt-logo { display: flex; align-items: center; gap: 6px; font-size: 20px; font-weight: 700; }
  .topbar-title { font-size: 15px; color: var(--sub); margin-left: 4px; }

  .hero { padding: 48px 24px 32px; text-align: center; }
  .hero h1 { font-size: 28px; font-weight: 700; margin-bottom: 10px; }
  .hero p { color: var(--sub); font-size: 15px; }

  .grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
    padding: 0 24px 48px;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
  }

  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    color: var(--text);
    display: flex;
    flex-direction: column;
    transition: border-color .2s, transform .15s;
  }
  .card:hover { border-color: #555; transform: translateY(-2px); }

  .card-thumb {
    background: linear-gradient(160deg, #0a1628 0%, #0d2137 60%, #1a3a5c 100%);
    aspect-ratio: 16/9;
    display: flex; align-items: center; justify-content: center;
    font-size: 64px;
    position: relative;
  }
  .live-pill {
    position: absolute; top: 10px; left: 10px;
    background: var(--red); color: #fff;
    font-size: 11px; font-weight: 700;
    padding: 3px 8px; border-radius: 4px; letter-spacing: .5px;
  }

  .card-body { padding: 14px 16px 18px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
  .card-name { font-size: 15px; font-weight: 600; line-height: 1.3; }
  .card-desc { font-size: 13px; color: var(--sub); line-height: 1.4; flex: 1; }
  .card-go {
    margin-top: 14px;
    background: var(--red); color: #fff; border: none;
    padding: 9px 0; border-radius: 20px;
    font-size: 14px; font-weight: 600;
    cursor: pointer; font-family: 'Roboto', sans-serif;
    width: 100%; text-align: center;
    transition: background .2s;
  }
  .card:hover .card-go { background: #cc0000; }

  .empty { text-align: center; color: var(--sub); padding: 60px 24px; grid-column: 1/-1; }
</style>
</head>
<body>

<div class="topbar">
  <div class="yt-logo">
    <svg width="28" height="20" viewBox="0 0 90 20" fill="none">
      <path d="M27.97 3.12C27.64 1.89 26.68.93 25.45.6 23.22 0 14.3 0 14.3 0S5.38 0 3.15.6C1.92.93.96 1.89.63 3.12.03 5.35.03 10 .03 10s0 4.65.6 6.88c.33 1.23 1.29 2.2 2.52 2.52C5.38 20 14.3 20 14.3 20s8.92 0 11.15-.6c1.23-.33 2.19-1.29 2.52-2.52.6-2.23.6-6.88.6-6.88s0-4.65-.6-6.88z" fill="#FF0000"/>
      <path d="M11.42 14.29L18.85 10l-7.43-4.29v8.58z" fill="white"/>
    </svg>
    <span style="color:#fff">YouTube</span>
  </div>
  <span class="topbar-title">/ Fake Famous</span>
</div>

<div class="hero">
  <h1>🔴 Choose Your Stream</h1>
  <p>Pick a live experience to launch</p>
</div>

<div class="grid">
  <?php if (empty($streams)): ?>
    <div class="empty">No streams found. Add a subfolder with a config.json to get started.</div>
  <?php else: ?>
    <?php foreach ($streams as $s): ?>
      <a class="card" href="<?= htmlspecialchars($s['dir']) ?>/">
        <div class="card-thumb">
          <span><?= htmlspecialchars($s['icon']) ?></span>
          <div class="live-pill">LIVE</div>
        </div>
        <div class="card-body">
          <div class="card-name"><?= htmlspecialchars($s['name']) ?></div>
          <?php if ($s['desc']): ?>
            <div class="card-desc"><?= htmlspecialchars($s['desc']) ?></div>
          <?php endif ?>
          <div class="card-go">▶ Go Live</div>
        </div>
      </a>
    <?php endforeach ?>
  <?php endif ?>
</div>

</body>
</html>
