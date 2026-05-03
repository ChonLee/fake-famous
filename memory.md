# Memory — Dan The Man's MCO Live Project

## About the User
- Parent of a special needs son named **Dan**
- Built this project as a fun, immersive experience for Dan — he loves planes
- They visit **Orlando International Airport (MCO)** to watch planes from the **B-side long term parking lot**
- Their specific vantage point looks directly at **Runway 17R/35L** (the center runway at MCO)
- The goal is to make Dan feel like a real YouTube livestreamer while plane spotting

## The App
- Single HTML file: `DanTheManMCOLive.html`
- Fake YouTube-style livestream page
- Channel name: **Dan The Man's MCO Live**
- Themed around MCO airport plane spotting from B-side long term parking, Runway 17R/35L
- Saved as a **PWA on iPhone** via Safari → Add to Home Screen
- Icon: red rounded square with ✈️ and "LIVE"

## Key Technical Decisions Made During This Session
- Chat starts on page load — **no camera required** — so Dan sees messages immediately
- Camera is rear-facing by default (points at the runway), optional
- Chat is **60% generic appreciation**, **40% aviation/plane-spotting content**
- Super Chats fire ~6% of the time with random dollar amounts
- Chat scroll fixed by using `overflow-y: scroll` + `min-height: 0` on the flex chat container
- PWA manifest and Apple touch icon generated at runtime via canvas — no external files needed
- Everything is one self-contained HTML file, no build tools, no server needed

## Files in Project
| File | Purpose |
|---|---|
| `DanTheManMCOLive.html` | The main app — everything in one file |
| `CLAUDE.md` | Claude Code context file — full technical reference |
| `memory.md` | This file — personal context and session history |

## Session History
- Started by building a generic fake YouTube livestream with camera + fake chat
- Refined to MCO plane-spotting theme with aviation-specific chat messages
- Channel renamed to "Dan The Man's MCO Live"
- Fixed chat scrolling (was expanding the page instead of scrolling in place)
- Corrected runway from 18R/36L → **17R/35L** based on user's real-world knowledge of the B-side lot view
- Added PWA support with generated icon for iPhone home screen saving
- Created `CLAUDE.md` for Claude Code
- Created this memory file
