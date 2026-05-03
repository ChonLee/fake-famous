# fake-famous — CLAUDE.md

## Project Overview

A fake YouTube livestream PWA for kids. Each "stream" is a themed experience — currently just **airport** (Dan's plane-spotting at MCO), with hotwheels, baseball, bowling etc. planned. All streams share one SQLite database and a common frontend template.

The app simulates a real YouTube live stream with:
- A live camera feed (rear camera preferred)
- Fake scrolling chat messages, auto-starting on page load
- Super Chat donations popping up randomly
- A live viewer count that fluctuates
- A scrolling themed ticker at the bottom of the video
- Floating reaction emojis
- PWA support — save to iPhone home screen

---

## File Structure

```
fake-famous/
├── index.php               Root menu — auto-discovers streams from */config.json
├── .gitignore              Excludes db/fake-famous.db
├── db/
│   ├── .gitkeep
│   └── fake-famous.db      Shared SQLite database (git-ignored, created by init.php)
├── airport/                Dan — MCO plane spotting
│   ├── index.php           Stream frontend (config-driven template)
│   ├── config.json         Stream identity + theme: icon, tickerLabel, reactions[]
│   ├── init.php            Run once to seed DB — delete after use
│   └── api/
│       └── messages.php    Returns substituted messages as JSON
└── beamng/                 LegoEaston — BeamNG Drive
    ├── index.php           Stream frontend (same generic template as airport)
    ├── overlay.php         OBS Browser Source overlay — transparent chat only, no controls
    ├── config.json         Stream identity + BeamNG fields: game, vehicle, map
    ├── init.php            Run once to seed DB — delete after use
    └── api/
        └── messages.php    BeamNG substitutions: {game} {vehicle} {map}
```

Adding a new stream = new folder with `config.json`, `index.php`, `api/messages.php`, `init.php`.
The root `index.php` menu picks it up automatically — no edits needed.

---

## Server Setup (SWAG — nginx + PHP Docker)

1. Deploy files to the nginx document root (e.g. `/config/www/`)
2. Visit `/{stream}/init.php` for each stream to seed the database
3. **Delete or restrict each `init.php`** after running
4. Protect the shared database in nginx:

```nginx
location /db {
    deny all;
    return 404;
}
```

---

## Database Schema

```sql
streams    (id PK, slug UNIQUE)                         -- 'airport', 'hotwheels', etc.
messages   (id PK, stream_id FK, category, text,
            username, is_mod)                           -- category: generic|aviation|superchat|opener|ticker
usernames  (id PK, stream_id FK, name)                  -- per-stream username pool
characters (id PK, username UNIQUE, weight)             -- global; high weight = appears more often
cities     (id PK, name UNIQUE)                         -- shared across all streams
```

- `stream_id` is auto-detected from the folder name (`basename(dirname(__DIR__))`) — no config needed
- `characters` has no `stream_id` — global across all streams (friends/family names go here)
- Cities use `INSERT OR IGNORE` so any stream's init.php can seed them without duplicating

---

## Stream config.json

All streams share these base fields:

```json
{
  "playerName": "Dan",
  "nicknames": ["Dan The Man", "Danny"],
  "channelName": "Dan The Man's MCO Live",
  "description": "MCO Plane Spotting ✈️ | B-Side Long Term Parking | Runway 17R/35L",
  "icon": "✈️",
  "tickerLabel": "✈ MCO OPS",
  "reactions": ["✈️","🛫","🛬","❤️","🔥","👏"],
  "subCount": 128000,
  "likeCount": 14700,
  "viewerCount": 2341,
  "chatMinDelay": 3000,
  "chatMaxDelay": 9000
}
```

- `description` — used for root menu card AND stream title in the header
- `icon` — emoji for PWA icon, channel avatar, react button, StreamBot, own messages
- `tickerLabel` — text on the ticker badge (e.g. `"✈ MCO OPS"`, `"💥 BEAMNG"`)
- `reactions` — array of emojis for floating reactions; repeat entries for higher frequency
- `chatMinDelay`/`chatMaxDelay` — milliseconds; defaults 800/2800 if omitted

Stream-specific extra fields (airport): `airport`, `runway`, `arrivalRunway`, `departureRunway`, `viewerLocation`
Stream-specific extra fields (beamng): `game`, `vehicle`, `map`

---

## Message Placeholders

Substitution happens **server-side in PHP** (`api/messages.php`) before JSON is returned. No substitution in JS.

**Airport** (`airport/api/messages.php`):

| Placeholder    | Value from config       |
|---|---|
| `{player}`     | playerName (random from playerName + nicknames) |
| `{channel}`    | channelName             |
| `{airport}`    | airport                 |
| `{runway}`     | runway                  |
| `{arr_runway}` | arrivalRunway           |
| `{dep_runway}` | departureRunway         |
| `{location}`   | viewerLocation          |
| `{city}`       | Random city from DB     |

**BeamNG** (`beamng/api/messages.php`):

| Placeholder    | Value from config       |
|---|---|
| `{player}`     | playerName (random from playerName + nicknames) |
| `{channel}`    | channelName             |
| `{city}`       | Random city from DB     |
| `{game}`       | game                    |
| `{vehicle}`    | vehicle                 |
| `{map}`        | map                     |

---

## How a Stream Boots

1. `window.load` → `loadData()` fetches `config.json` + `api/messages.php` in parallel
2. `applyConfig()` writes player name, channel name, counts, stream title, PWA manifest into DOM
3. `startTicker()`, `startChat()`, `startViewerTick()`, `startHeartLoop()` all kick off

Chat starts immediately — no camera required.

---

## Known Characters

Friends/family go in the `characters` table via `airport/init.php`:

```php
$characters = [
  ['username' => 'FriendName', 'weight' => 5],  // weight = how often vs random users
];
```

Higher weight = more frequent appearances. Global — same people appear in all streams.

---

## Chat Message Counts

**Airport**: ~320 unique messages (150 generic, 130 aviation, 40 superchat).
At 9s/message over 2 hours (~800 messages fired) → ~2.5x repeat rate.

**BeamNG**: ~308 unique messages (154 generic, 119 game-specific, 35 superchat).
At 4s/message over 2 hours (~1800 messages fired) → ~5x repeat rate. Faster chat, shorter messages, acceptable.

---

## OBS Browser Source Overlay (BeamNG)

`beamng/overlay.php` is a transparent chat-only overlay for OBS:

- In OBS: Add **Browser Source** → URL pointing to `https://yourserver/fake-famous/beamng/overlay.php`
- Set width to **380**, height to **1080** (or match stream resolution height)
- Check **"Allow transparency"** in the Browser Source settings
- Position it in the bottom-right corner of the game capture
- The overlay shows only chat messages — no controls, no video section
- Chat runs the same simulation as the main stream page

---

## Key JavaScript Functions

| Function | What it does |
|---|---|
| `loadData()` | Fetches config.json + api/messages.php, stores in `config` / `MESSAGES` |
| `applyConfig()` | Writes config values into DOM, rebuilds PWA manifest |
| `startCamera()` | Requests rear camera, falls back to any camera |
| `startTicker()` | Cycles through `MESSAGES.ticker` every 30s |
| `startChat()` | Fires openers, then continuous random messages |
| `addMsg(...)` | Creates and appends a chat message DOM element |
| `pickMsg()` | Returns a random message — 60% generic, 40% aviation |
| `startViewerTick()` | Adjusts viewerCount ±random every 4 seconds |
| `spawnReact()` | Creates a floating emoji that animates upward |
| `toggleLike/Subscribe()` | Toggle state, update counts, fire StreamBot message |
| `sendOwnMessage()` | Reads chat input, posts as MOD message from the player |
| `ri(arr)` | Utility: random item from array |
| `fmt(n)` | Utility: formats numbers as 1.2K / 3.4M |

---

## Update Server

When the user says **"update server"** or **"update the server"**, run:

```bash
ssh -i ~/.ssh/id_ed25519 root@192.168.7.110 "cd /mnt/user/appdata/swag/www/fake-famous && git pull"
```

---

## Things to Watch Out For

- `scroll-behavior: smooth` + `overflow-y: scroll` + `min-height: 0` on `.chat-messages` are all required — do not remove any of them
- Do NOT add `overflow: hidden` to `.chat-section` — breaks chat scroll
- Camera only works in Safari on iOS
- Placeholder substitution is PHP-side only — no `fill()` function exists in the JS
- The shared DB lives at `db/fake-famous.db` (root level), not inside each stream folder
- `init.php` path for the DB: `dirname(__DIR__) . '/db/fake-famous.db'`
- `api/messages.php` path for the DB: `dirname(dirname(__DIR__)) . '/db/fake-famous.db'`
