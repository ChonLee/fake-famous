<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat Overlay</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body {
    height: 100%;
    background: transparent;
    font-family: 'Roboto', sans-serif;
    overflow: hidden;
  }

  .chat-wrap {
    position: fixed;
    right: 0; bottom: 0;
    width: 380px;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    pointer-events: none;
  }

  .chat-messages {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px 10px;
    overflow-y: scroll;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
  }
  .chat-messages::-webkit-scrollbar { display: none; }

  .chat-msg {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    padding: 5px 8px;
    border-radius: 8px;
    background: rgba(0,0,0,0.65);
    backdrop-filter: blur(4px);
    animation: msg-in .3s ease-out;
  }
  @keyframes msg-in { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

  .msg-avatar {
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; flex-shrink: 0; margin-top: 1px;
  }
  .msg-body { flex: 1; min-width: 0; }
  .msg-name { font-size: 12px; font-weight: 700; margin-bottom: 1px; }
  .msg-text { font-size: 13px; line-height: 1.35; color: #f1f1f1; word-break: break-word; }
  .msg-badge { font-size: 9px; padding: 1px 4px; border-radius: 3px; margin-left: 4px; vertical-align: middle; }
  .badge-mod { background: #2d7a2d; color: #fff; }
  .badge-member { background: #065fd4; color: #fff; }

  .superchat {
    background: rgba(255,180,0,0.18);
    border-left: 3px solid #ffd700;
  }
  .superchat .msg-name { color: #ffd700; }
  .superchat-amount { font-size: 11px; font-weight: 700; color: #ffd700; margin-bottom: 2px; }
</style>
</head>
<body>
<div class="chat-wrap">
  <div class="chat-messages" id="chat-messages"></div>
</div>

<script>
const AVATAR_COLORS = [
  '#ef5350','#ec407a','#ab47bc','#7e57c2','#42a5f5',
  '#26c6da','#26a69a','#66bb6a','#ffa726','#ff7043',
  '#29b6f6','#9ccc65','#ffca28','#8d6e63','#78909c',
];

let config   = {};
let MESSAGES = {};
let likeCount = 0, viewerCount = 0;

function ri(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

async function loadData() {
  const [cfgRes, msgRes] = await Promise.all([
    fetch('config.json'),
    fetch('api/messages.php'),
  ]);
  config   = await cfgRes.json();
  MESSAGES = await msgRes.json();
  likeCount   = config.likeCount;
  viewerCount = config.viewerCount;
}

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
  while (container.children.length > 80) container.removeChild(container.firstChild);
  container.scrollTop = container.scrollHeight;
}

function pickMsg() {
  return ri(Math.random() < 0.60 ? MESSAGES.generic : MESSAGES.aviation);
}

function startChat() {
  const openers = MESSAGES.openers.map(o => [o.username, o.text, false, null, o.is_mod]);
  openers.forEach(([u,m,sc,amt,mod], i) =>
    setTimeout(() => addMsg(u, m, sc, amt, mod), i * 600)
  );

  function next() {
    const minD = config.chatMinDelay ?? 800;
    const delay = minD + Math.random() * ((config.chatMaxDelay ?? 2800) - minD);
    setTimeout(() => {
      const r = Math.random();
      const user = ri(MESSAGES.usernames);
      if (r < 0.06) {
        const amt = ri([2,5,5,10,10,20,50,100]);
        addMsg(user, ri(MESSAGES.superchat), true, amt);
      } else {
        addMsg(user, pickMsg());
      }
      next();
    }, delay);
  }
  setTimeout(next, openers.length * 600 + 500);
}

window.addEventListener('load', async () => {
  await loadData();
  startChat();
});
</script>
</body>
</html>
