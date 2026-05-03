<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>🔴 LIVE – Loading…</title>

<!-- PWA manifest — rewritten after config loads -->
<script>
  function buildManifest(name, icon) {
    const manifest = {
      name, short_name: name,
      start_url: '.', display: 'standalone',
      background_color: '#0f0f0f', theme_color: '#FF0000', orientation: 'any',
      icons: [{ src: `data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><rect width='512' height='512' rx='80' fill='%23FF0000'/><text y='340' x='50%' text-anchor='middle' font-size='300' font-family='serif'>${icon}</text></svg>`, sizes: '512x512', type: 'image/svg+xml', purpose: 'any maskable' }]
    };
    const old = document.querySelector('link[rel="manifest"]');
    if (old) { URL.revokeObjectURL(old.href); old.remove(); }
    const link = document.createElement('link');
    link.rel = 'manifest';
    link.href = URL.createObjectURL(new Blob([JSON.stringify(manifest)], {type: 'application/manifest+json'}));
    document.head.appendChild(link);
  }
</script>

<!-- iOS / Safari PWA support -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="Live">
<meta name="theme-color" content="#FF0000">

<!-- iOS home screen icon (canvas-generated, called from applyConfig) -->
<script>
  function buildAppleTouchIcon(icon) {
    const canvas = document.createElement('canvas');
    canvas.width = 180; canvas.height = 180;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#FF0000';
    const r = 36;
    ctx.beginPath();
    ctx.moveTo(r,0); ctx.lineTo(180-r,0); ctx.quadraticCurveTo(180,0,180,r);
    ctx.lineTo(180,180-r); ctx.quadraticCurveTo(180,180,180-r,180);
    ctx.lineTo(r,180); ctx.quadraticCurveTo(0,180,0,180-r);
    ctx.lineTo(0,r); ctx.quadraticCurveTo(0,0,r,0);
    ctx.closePath(); ctx.fill();
    ctx.font = '110px serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
    ctx.fillText(icon, 90, 95);
    ctx.fillStyle = '#ffffff'; ctx.font = 'bold 28px sans-serif';
    ctx.fillText('LIVE', 90, 155);
    const existing = document.querySelector('link[rel="apple-touch-icon"]');
    if (existing) existing.remove();
    const link = document.createElement('link');
    link.rel = 'apple-touch-icon'; link.href = canvas.toDataURL('image/png');
    document.head.appendChild(link);
  }
</script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
  * { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --red: #FF0000; --dark: #0f0f0f; --card: #212121;
    --border: #303030; --text: #f1f1f1; --sub: #aaaaaa;
  }
  html, body { height: 100%; }
  body { background: var(--dark); color: var(--text); font-family: 'Roboto', sans-serif; height: 100%; display: flex; flex-direction: column; }
  @media (max-width: 899px) { html, body { height: auto; min-height: 100%; } }

  .topbar {
    background: #212121; padding: 10px 16px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 100;
  }
  .yt-logo { display: flex; align-items: center; gap: 6px; font-size: 20px; font-weight: 700; }
  .avatar-btn {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, #4fc3f7, #0288d1);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 11px; border: 2px solid var(--border);
  }

  .main { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; }
  @media (max-width: 899px) { .main { overflow: visible; } }
  @media (min-width: 900px) {
    .main { flex-direction: row; }
    .video-section { flex: 1; overflow-y: auto; }
    .chat-section { width: 380px; height: 100%; }
  }
  .chat-section { display: flex; flex-direction: column; background: var(--dark); border-left: 1px solid var(--border); height: 480px; flex-shrink: 0; }
  @media (min-width: 900px) { .chat-section { height: 100%; flex-shrink: unset; } }

  .video-wrapper { position: relative; width: 100%; aspect-ratio: 16/9; background: #000; overflow: hidden; }
  #camera-feed { width: 100%; height: 100%; object-fit: cover; display: none; }

  .camera-placeholder {
    width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    background: linear-gradient(160deg, #0a1628 0%, #0d2137 60%, #1a3a5c 100%);
    gap: 14px;
  }
  .plane-icon { font-size: 56px; animation: taxi 3s ease-in-out infinite alternate; }
  @keyframes taxi { from{transform:translateX(-12px)} to{transform:translateX(12px)} }
  .placeholder-title { font-size: 15px; color: #90caf9; text-align: center; padding: 0 20px; }
  .start-btn {
    background: var(--red); color: #fff; border: none;
    padding: 12px 28px; border-radius: 24px;
    font-size: 15px; font-weight: 600; cursor: pointer;
    font-family: 'Roboto', sans-serif; transition: background .2s, transform .1s;
  }
  .start-btn:hover { background: #cc0000; transform: scale(1.03); }

  .live-badge {
    position: absolute; top: 12px; left: 12px;
    display: flex; align-items: center; gap: 6px;
    background: var(--red); color: #fff;
    font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 4px; letter-spacing: .5px;
    animation: pulse-badge 2s infinite;
  }
  .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #fff; animation: blink 1.2s infinite; }
  @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }
  @keyframes pulse-badge { 0%,100%{box-shadow:0 0 0 0 rgba(255,0,0,.5)} 50%{box-shadow:0 0 0 6px rgba(255,0,0,0)} }

  .viewer-count {
    position: absolute; top: 12px; right: 12px;
    display: flex; align-items: center; gap: 5px;
    background: rgba(0,0,0,.72); color: #fff;
    font-size: 12px; font-weight: 500; padding: 4px 10px; border-radius: 4px;
  }

  .runway-ticker {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(0,0,0,.7); color: #90caf9;
    font-size: 12px; font-weight: 500; padding: 5px 12px;
    display: flex; align-items: center; gap: 8px; overflow: hidden;
  }
  .ticker-label { background: #0288d1; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 3px; white-space: nowrap; flex-shrink: 0; }
  .ticker-scroll { white-space: nowrap; color: #e3f2fd; animation: scroll-left 30s linear infinite; }
  @keyframes scroll-left { 0%{transform:translateX(100vw)} 100%{transform:translateX(-100%)} }

  .reactions-overlay { position: absolute; bottom: 30px; right: 10px; height: 80%; width: 44px; pointer-events: none; overflow: hidden; }
  .floating-react { position: absolute; bottom: 0; right: 0; font-size: 22px; animation: float-up 3s ease-out forwards; }
  @keyframes float-up { 0%{transform:translateY(0) scale(1);opacity:1} 80%{opacity:1} 100%{transform:translateY(-280px) scale(.5);opacity:0} }

  .video-info { padding: 14px 16px 8px; background: var(--dark); }
  .stream-title { font-size: 17px; font-weight: 600; line-height: 1.3; margin-bottom: 10px; }
  .channel-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
  .channel-info { display: flex; align-items: center; gap: 10px; }
  .channel-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0288d1, #26c6da); display: flex; align-items: center; justify-content: center; font-size: 20px; }
  .channel-name { font-size: 15px; font-weight: 600; }
  .sub-count { font-size: 12px; color: var(--sub); }
  .subscribe-btn { background: #fff; color: #000; border: none; padding: 8px 18px; border-radius: 20px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'Roboto', sans-serif; transition: background .2s; }
  .subscribe-btn.subscribed { background: var(--card); color: var(--text); }

  .actions { display: flex; gap: 8px; padding: 10px 16px; border-bottom: 1px solid var(--border); overflow-x: auto; }
  .action-pill { background: var(--card); border: none; color: var(--text); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; white-space: nowrap; font-family: 'Roboto', sans-serif; transition: background .15s; }
  .action-pill:hover { background: #383838; }
  .action-pill.liked { background: #065fd4; }

  .chat-header { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
  .chat-live-indicator { display: flex; align-items: center; gap: 6px; font-size: 13px; }
  .chat-live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--red); animation: blink 1.2s infinite; }

  .chat-messages { flex: 1; overflow-y: scroll; min-height: 0; padding: 10px 12px; display: flex; flex-direction: column; gap: 2px; scroll-behavior: smooth; }
  .chat-messages::-webkit-scrollbar { width: 4px; }
  .chat-messages::-webkit-scrollbar-thumb { background: #444; border-radius: 2px; }

  .chat-msg { display: flex; align-items: flex-start; gap: 8px; padding: 5px 6px; border-radius: 6px; animation: msg-in .25s ease-out; transition: background .15s; }
  .chat-msg:hover { background: rgba(255,255,255,.04); }
  @keyframes msg-in { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }

  .msg-avatar { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; margin-top: 1px; }
  .msg-body { flex: 1; min-width: 0; }
  .msg-name { font-size: 12px; font-weight: 600; margin-bottom: 1px; }
  .msg-text { font-size: 13px; line-height: 1.4; color: var(--text); word-break: break-word; }
  .msg-badge { font-size: 10px; padding: 1px 5px; border-radius: 3px; margin-left: 4px; vertical-align: middle; }
  .badge-mod { background: #2d7a2d; color: #fff; }
  .badge-member { background: #065fd4; color: #fff; }

  .superchat { background: linear-gradient(135deg,rgba(255,200,0,.13),rgba(255,120,0,.08)); border-left: 3px solid #ffd700; padding: 8px 10px; border-radius: 6px; margin: 4px 0; }
  .superchat .msg-name { color: #ffd700; }
  .superchat-amount { font-size: 11px; font-weight: 700; color: #ffd700; margin-bottom: 3px; }

  .chat-input-area { padding: 10px 12px; border-top: 1px solid var(--border); display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
  .chat-input { flex: 1; background: var(--card); border: 1px solid var(--border); border-radius: 20px; color: var(--text); font-size: 13px; padding: 8px 14px; outline: none; font-family: 'Roboto', sans-serif; }
  .chat-input:focus { border-color: #555; }
  .chat-input::placeholder { color: #666; }
  .send-btn { background: none; border: none; color: #3ea6ff; cursor: pointer; font-size: 20px; padding: 4px; display: flex; align-items: center; transition: transform .15s; }
  .send-btn:hover { transform: scale(1.15); }

  .camera-flip-btn {
    position: absolute; bottom: 36px; right: 12px;
    background: rgba(0,0,0,.72); color: #fff; border: none;
    width: 36px; height: 36px; border-radius: 50%;
    font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s; z-index: 10;
  }
  .camera-flip-btn:hover { background: rgba(255,255,255,.15); }
  .camera-flip-btn:active { transform: rotate(180deg); }

  .camera-switch-btn {
    position: absolute; bottom: 36px; left: 12px;
    background: rgba(0,0,0,.72); color: #fff; border: none;
    padding: 5px 10px; border-radius: 20px;
    font-size: 12px; font-weight: 500; cursor: pointer;
    display: flex; align-items: center; gap: 5px;
    transition: background .15s; z-index: 10;
  }
  .camera-switch-btn:hover { background: rgba(255,255,255,.15); }

  .camera-picker-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.88);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 10px; z-index: 20; padding: 20px;
  }
  .camera-picker-title { color: #fff; font-size: 15px; font-weight: 600; margin-bottom: 4px; }
  .camera-option-btn {
    background: #212121; color: var(--text); border: 1px solid #444;
    padding: 10px 20px; border-radius: 8px;
    font-size: 13px; font-weight: 500; cursor: pointer; width: 100%; max-width: 300px;
    font-family: 'Roboto', sans-serif; text-align: left; transition: background .15s, border-color .15s;
  }
  .camera-option-btn:hover { background: #383838; border-color: #666; }
  .camera-option-btn.active { border-color: var(--red); }
  .camera-cancel-btn {
    background: none; color: var(--sub); border: none;
    font-size: 13px; cursor: pointer; padding: 6px; margin-top: 4px;
    font-family: 'Roboto', sans-serif;
  }
  .camera-cancel-btn:hover { color: var(--text); }
</style>
</head>
<body>

<div class="topbar">
  <div class="yt-logo">
    <svg width="32" height="22" viewBox="0 0 90 20" fill="none">
      <path d="M27.97 3.12C27.64 1.89 26.68.93 25.45.6 23.22 0 14.3 0 14.3 0S5.38 0 3.15.6C1.92.93.96 1.89.63 3.12.03 5.35.03 10 .03 10s0 4.65.6 6.88c.33 1.23 1.29 2.2 2.52 2.52C5.38 20 14.3 20 14.3 20s8.92 0 11.15-.6c1.23-.33 2.19-1.29 2.52-2.52.6-2.23.6-6.88.6-6.88s0-4.65-.6-6.88z" fill="#FF0000"/>
      <path d="M11.42 14.29L18.85 10l-7.43-4.29v8.58z" fill="white"/>
    </svg>
    <span style="color:#fff">YouTube</span>
  </div>
  <div class="avatar-btn" id="avatar-btn">…</div>
</div>

<div class="main">
  <div class="video-section">
    <div class="video-wrapper">
      <video id="camera-feed" autoplay playsinline muted></video>

      <div class="camera-placeholder" id="camera-placeholder">
        <div class="plane-icon" id="plane-icon"></div>
        <p class="placeholder-title" id="placeholder-title">Loading…</p>
        <button class="start-btn" onclick="startCamera()">📷 Show Camera</button>
      </div>

      <div class="live-badge"><div class="live-dot"></div>LIVE</div>
      <div class="viewer-count">👁 <span id="viewer-num">0</span></div>
      <div class="runway-ticker">
        <span class="ticker-label" id="ticker-label"></span>
        <span class="ticker-scroll" id="ticker-text"></span>
      </div>
      <div class="reactions-overlay" id="reactions-overlay"></div>
    </div>

    <div class="video-info">
      <div class="stream-title" id="stream-title">🔴 LIVE…</div>
      <div class="channel-row">
        <div class="channel-info">
          <div class="channel-avatar" id="channel-avatar"></div>
          <div>
            <div class="channel-name" id="channel-name">Loading…</div>
            <div class="sub-count" id="sub-count">…</div>
          </div>
        </div>
        <button class="subscribe-btn" id="sub-btn" onclick="toggleSubscribe()">Subscribe</button>
      </div>
    </div>

    <div class="actions">
      <button class="action-pill" id="like-btn" onclick="toggleLike()">👍 <span id="like-count">…</span></button>
      <button class="action-pill" id="react-btn" onclick="addHeart()"></button>
      <button class="action-pill">📤 Share</button>
      <button class="action-pill">⬇️ Save</button>
    </div>
  </div>

  <div class="chat-section">
    <div class="chat-header">
      <span>Live chat</span>
      <div class="chat-live-indicator">
        <div class="chat-live-dot"></div>
        <span style="color:var(--sub);font-weight:400">Live</span>
      </div>
    </div>
    <div class="chat-messages" id="chat-messages"></div>
    <div class="chat-input-area">
      <input class="chat-input" id="chat-input" placeholder="Say something…" onkeydown="handleChatKey(event)">
      <button class="send-btn" onclick="sendOwnMessage()">➤</button>
    </div>
  </div>
</div>

<script>
const AVATAR_COLORS = [
  '#ef5350','#ec407a','#ab47bc','#7e57c2','#42a5f5',
  '#26c6da','#26a69a','#66bb6a','#ffa726','#ff7043',
  '#29b6f6','#9ccc65','#ffca28','#8d6e63','#78909c',
];
let REACTION_EMOJIS = ['❤️','🔥','👏','😍','🙌','💛','🏆'];

let config   = {};
let MESSAGES = {};
let liked = false, subscribed = false;
let likeCount = 0, viewerCount = 0, subCount = 0;
let cameraOn = false;
let currentStream = null;
let currentFacingMode = 'environment';
let savedDeviceId = localStorage.getItem('ff_cameraId');

function ri(arr)  { return arr[Math.floor(Math.random() * arr.length)]; }
function fmt(n)   { if (n>=1e6) return (n/1e6).toFixed(1)+'M'; if (n>=1e3) return (n/1e3).toFixed(1)+'K'; return String(n); }


// ── DATA LOADING ─────────────────────────────────────────────────────────────
async function loadData() {
  try {
    const [cfgRes, msgRes] = await Promise.all([
      fetch('config.json'),
      fetch('api/messages.php'),
    ]);
    if (!cfgRes.ok || !msgRes.ok) throw new Error('fetch failed');
    config   = await cfgRes.json();
    MESSAGES = await msgRes.json();
    likeCount   = config.likeCount;
    viewerCount = config.viewerCount;
    subCount    = config.subCount;
  } catch (e) {
    document.body.innerHTML = '<div style="color:#fff;text-align:center;padding:60px 20px;font-family:sans-serif"><h2>⚠️ Setup needed</h2><p style="margin-top:12px;color:#aaa">Visit <a href="init.php" style="color:#90caf9;text-decoration:underline">init.php</a> to initialise the database, then refresh.</p></div>';
    throw e;
  }
}

function applyConfig() {
  const p = config.playerName;
  const initials = p.slice(0, 3).toUpperCase();
  const icon = config.icon ?? '🔴';

  document.title = `🔴 LIVE – ${config.channelName}`;
  document.getElementById('avatar-btn').textContent          = initials;
  document.getElementById('stream-title').textContent        = `🔴 LIVE: ${config.channelName} — ${config.description}`;
  document.getElementById('channel-name').textContent        = `${config.channelName} ✓`;
  document.getElementById('sub-count').textContent           = `${fmt(subCount)} subscribers`;
  document.getElementById('placeholder-title').innerHTML     = `${config.channelName}<br>Tap to show your camera!`;
  document.getElementById('viewer-num').textContent          = fmt(viewerCount);
  document.getElementById('like-count').textContent          = fmt(likeCount);
  document.getElementById('ticker-label').textContent        = config.tickerLabel ?? (icon + ' OPS');
  document.getElementById('plane-icon').textContent          = icon;
  document.getElementById('channel-avatar').textContent      = icon;
  document.getElementById('react-btn').textContent           = icon + ' React';

  REACTION_EMOJIS = config.reactions ?? [icon, '❤️', '🔥', '👏', '😍', '🙌', icon, icon];

  const meta = document.querySelector('meta[name="apple-mobile-web-app-title"]');
  if (meta) meta.content = config.channelName;

  buildAppleTouchIcon(icon);
  buildManifest(config.channelName, icon);
}

// ── CAMERA ───────────────────────────────────────────────────────────────────
async function startCamera() {
  const preferred = savedDeviceId
    ? { video: { deviceId: { exact: savedDeviceId }, width: { ideal: 1280 } }, audio: false }
    : { video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } }, audio: false };

  const ok = await applyCamera(preferred) || await applyCamera({ video: true, audio: false });
  if (!ok) { alert('Camera not available — the stream will continue without it!'); return; }

  const devices = await navigator.mediaDevices.enumerateDevices();
  const cams = devices.filter(d => d.kind === 'videoinput');
  if (cams.length > 1) buildFlipButton();
  if (cams.length > 2) buildCameraSwitch(cams);
}

async function applyCamera(constraints) {
  try {
    if (currentStream) currentStream.getTracks().forEach(t => t.stop());
    const stream = await navigator.mediaDevices.getUserMedia(constraints);
    currentStream = stream;
    const settings = stream.getVideoTracks()[0]?.getSettings?.();
    if (settings?.facingMode) currentFacingMode = settings.facingMode;
    if (settings?.deviceId) { savedDeviceId = settings.deviceId; localStorage.setItem('ff_cameraId', settings.deviceId); }
    const v = document.getElementById('camera-feed');
    v.srcObject = stream;
    v.style.display = 'block';
    document.getElementById('camera-placeholder').style.display = 'none';
    cameraOn = true;
    return true;
  } catch { return false; }
}

function buildFlipButton() {
  if (document.getElementById('camera-flip-btn')) return;
  const btn = document.createElement('button');
  btn.id = 'camera-flip-btn';
  btn.className = 'camera-flip-btn';
  btn.title = 'Flip camera';
  btn.textContent = '🔄';
  btn.onclick = flipCamera;
  document.querySelector('.video-wrapper').appendChild(btn);
}

async function flipCamera() {
  const next = currentFacingMode === 'environment' ? 'user' : 'environment';
  // Try facingMode (reliable on iPhone); fall back to cycling deviceIds on desktop
  const ok = await applyCamera({ video: { facingMode: { exact: next }, width: { ideal: 1280 } }, audio: false })
          || await applyCamera({ video: { facingMode: next,             width: { ideal: 1280 } }, audio: false });
  if (!ok) {
    const cams = (await navigator.mediaDevices.enumerateDevices()).filter(d => d.kind === 'videoinput');
    const idx  = cams.findIndex(c => c.deviceId === savedDeviceId);
    const nextCam = cams[(idx + 1) % cams.length];
    await applyCamera({ video: { deviceId: { exact: nextCam.deviceId }, width: { ideal: 1280 } }, audio: false });
  }
}

function buildCameraSwitch(cams) {
  const existing = document.getElementById('camera-switch-btn');
  if (existing) existing.remove();
  const btn = document.createElement('button');
  btn.id = 'camera-switch-btn';
  btn.className = 'camera-switch-btn';
  btn.textContent = '📷 Switch';
  btn.onclick = () => showCameraPicker(cams);
  document.querySelector('.video-wrapper').appendChild(btn);
}

function showCameraPicker(cams) {
  const existing = document.getElementById('camera-picker-overlay');
  if (existing) { existing.remove(); return; }

  const overlay = document.createElement('div');
  overlay.id = 'camera-picker-overlay';
  overlay.className = 'camera-picker-overlay';

  const title = document.createElement('div');
  title.className = 'camera-picker-title';
  title.textContent = 'Select Camera';
  overlay.appendChild(title);

  cams.forEach((cam, i) => {
    const btn = document.createElement('button');
    btn.className = 'camera-option-btn' + (cam.deviceId === savedDeviceId ? ' active' : '');
    btn.textContent = cam.label || ('Camera ' + (i + 1));
    btn.onclick = async () => {
      overlay.remove();
      await applyCamera({ video: { deviceId: { exact: cam.deviceId }, width: { ideal: 1280 } }, audio: false });
      const devices = await navigator.mediaDevices.enumerateDevices();
      buildCameraSwitch(devices.filter(d => d.kind === 'videoinput'));
    };
    overlay.appendChild(btn);
  });

  const cancel = document.createElement('button');
  cancel.className = 'camera-cancel-btn';
  cancel.textContent = 'Cancel';
  cancel.onclick = () => overlay.remove();
  overlay.appendChild(cancel);

  document.querySelector('.video-wrapper').appendChild(overlay);
}

// ── TICKER ───────────────────────────────────────────────────────────────────
function startTicker() {
  let i = 0;
  const el = document.getElementById('ticker-text');
  function next() {
    el.textContent = MESSAGES.ticker[i++ % MESSAGES.ticker.length];
    el.style.animation = 'none'; el.offsetHeight; el.style.animation = '';
  }
  next(); setInterval(next, 30000);
}

// ── CHAT ─────────────────────────────────────────────────────────────────────
function addMsg(username, text, isSC=false, amount=null, isMod=false) {
  const container = document.getElementById('chat-messages');
  const color = AVATAR_COLORS[Math.floor(Math.random() * AVATAR_COLORS.length)];
  const initial = username[0].toUpperCase();
  const el = document.createElement('div');
  el.className = 'chat-msg' + (isSC ? ' superchat' : '');

  if (isSC && amount) {
    el.innerHTML = `
      <div class="msg-avatar" style="background:${color}33;color:${color}">${initial}</div>
      <div class="msg-body">
        <div class="superchat-amount">💛 $${amount} Super Chat</div>
        <div class="msg-name" style="color:${color}">${username}</div>
        <div class="msg-text">${text}</div>
      </div>`;
  } else {
    const modB = isMod ? '<span class="msg-badge badge-mod">MOD</span>' : '';
    const memB = !isMod && Math.random() < 0.06 ? '<span class="msg-badge badge-member">MEMBER</span>' : '';
    el.innerHTML = `
      <div class="msg-avatar" style="background:${color}33;color:${color}">${initial}</div>
      <div class="msg-body">
        <div class="msg-name" style="color:${color}">${username}${modB}${memB}</div>
        <div class="msg-text">${text}</div>
      </div>`;
  }
  container.appendChild(el);
  while (container.children.length > 120) container.removeChild(container.firstChild);
  container.scrollTop = container.scrollHeight;
}

function pickMsg() {
  return ri(Math.random() < 0.60 ? MESSAGES.generic : MESSAGES.aviation);
}

function startChat() {
  const openers = MESSAGES.openers.map(o => [o.username, o.text, false, null, o.is_mod]);
  openers.forEach(([u,m,sc,amt,mod], i) =>
    setTimeout(() => addMsg(u, m, sc, amt, mod), i * 800)
  );

  function next() {
    const minD = config.chatMinDelay ?? 800;
    const maxD = config.chatMaxDelay ?? 2800;
    const r1 = Math.random(), r2 = Math.random();
    const t  = Math.random() < 0.5 ? Math.min(r1, r2) : Math.max(r1, r2);
    const delay = minD + t * (maxD - minD);
    setTimeout(() => {
      const r = Math.random();
      const user = ri(MESSAGES.usernames);
      if (r < 0.06) {
        const amt = ri([2,5,5,10,10,20,50,100]);
        addMsg(user, ri(MESSAGES.superchat), true, amt);
        likeCount += Math.floor(Math.random()*100+30);
        document.getElementById('like-count').textContent = fmt(likeCount);
        spawnReact(); spawnReact(); spawnReact();
      } else {
        addMsg(user, pickMsg());
      }
      next();
    }, delay);
  }
  setTimeout(next, openers.length * 800 + 500);
}

// ── VIEWER COUNT ─────────────────────────────────────────────────────────────
function startViewerTick() {
  setInterval(() => {
    viewerCount = Math.max(1400, viewerCount + Math.floor(Math.random()*60-20));
    document.getElementById('viewer-num').textContent = fmt(viewerCount);
  }, 4000);
}

// ── REACTIONS ────────────────────────────────────────────────────────────────
function spawnReact() {
  const overlay = document.getElementById('reactions-overlay');
  if (!overlay) return;
  const el = document.createElement('div');
  el.className = 'floating-react';
  el.textContent = ri(REACTION_EMOJIS);
  el.style.right = (Math.random()*18)+'px';
  overlay.appendChild(el);
  setTimeout(() => el.remove(), 3200);
}
function addHeart() { spawnReact(); spawnReact(); }
function startHeartLoop() {
  function go() { setTimeout(() => { spawnReact(); go(); }, 3000 + Math.random()*5000); }
  go();
}

// ── LIKE / SUBSCRIBE ─────────────────────────────────────────────────────────
function toggleLike() {
  liked = !liked;
  likeCount += liked ? 1 : -1;
  const btn = document.getElementById('like-btn');
  btn.className = 'action-pill'+(liked?' liked':'');
  btn.innerHTML = '👍 <span id="like-count">'+fmt(likeCount)+'</span>';
}
function toggleSubscribe() {
  subscribed = !subscribed;
  subCount += subscribed ? 1 : -1;
  document.getElementById('sub-btn').textContent  = subscribed ? 'Subscribed ✓' : 'Subscribe';
  document.getElementById('sub-btn').className    = 'subscribe-btn'+(subscribed?' subscribed':'');
  document.getElementById('sub-count').textContent = fmt(subCount)+' subscribers';
  if (subscribed) addMsg('StreamBot ' + config.icon, `🎉 A new member just joined the crew! Welcome to ${config.channelName}! ${config.icon}`, false, null, true);
}

function sendOwnMessage() {
  const input = document.getElementById('chat-input');
  const text = input.value.trim();
  if (!text) return;
  addMsg(`${config.playerName} ${config.icon}`, text, false, null, true);
  input.value = '';
}
function handleChatKey(e) { if (e.key === 'Enter') sendOwnMessage(); }

// ── MOBILE CHAT HEIGHT ───────────────────────────────────────────────────────
function updateMobileChatHeight() {
  if (window.innerWidth >= 900) return;
  const chat = document.querySelector('.chat-section');
  if (!chat) return;
  const maxH = Math.round(window.innerHeight * 0.78);
  const minH = 300;
  const chatTop = chat.getBoundingClientRect().top;
  const fill = window.innerHeight - Math.max(chatTop, 0);
  chat.style.height = Math.max(minH, Math.min(maxH, fill)) + 'px';
}
window.addEventListener('scroll', updateMobileChatHeight, { passive: true });
window.addEventListener('resize', updateMobileChatHeight);

// ── BOOT ─────────────────────────────────────────────────────────────────────
window.addEventListener('load', async () => {
  await loadData();
  applyConfig();
  updateMobileChatHeight();
  startTicker();
  startChat();
  startViewerTick();
  startHeartLoop();
});
</script>
</body>
</html>
