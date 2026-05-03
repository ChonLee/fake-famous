<?php
/**
 * Seeds the airport stream into the shared database.
 * Safe to run alongside other streams — only touches rows for this stream.
 * Visit /airport/init.php in a browser, or: php airport/init.php
 * DELETE or restrict this file after running.
 */

define('STREAM_SLUG', basename(__DIR__));
define('DB_DIR',      dirname(__DIR__) . '/db');
define('DB_PATH',     DB_DIR . '/fake-famous.db');

if (!is_dir(DB_DIR)) mkdir(DB_DIR, 0755, true);

$db = new SQLite3(DB_PATH);

// ── SHARED SCHEMA ─────────────────────────────────────────────────────────────
$db->exec("
  CREATE TABLE IF NOT EXISTS streams (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE
  );
  CREATE TABLE IF NOT EXISTS messages (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    stream_id INTEGER NOT NULL REFERENCES streams(id),
    category  TEXT NOT NULL,
    text      TEXT NOT NULL,
    username  TEXT,
    is_mod    INTEGER DEFAULT 0
  );
  CREATE TABLE IF NOT EXISTS usernames (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    stream_id INTEGER NOT NULL REFERENCES streams(id),
    name      TEXT NOT NULL
  );
  CREATE TABLE IF NOT EXISTS cities (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
  );
  CREATE TABLE IF NOT EXISTS characters (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    weight   INTEGER NOT NULL DEFAULT 3
  );
");

// ── GUARD ─────────────────────────────────────────────────────────────────────
$existing = $db->querySingle("SELECT id FROM streams WHERE slug = '" . STREAM_SLUG . "'");
if ($existing) {
    echo "Stream '" . STREAM_SLUG . "' is already seeded.\n";
    echo "To reseed: DELETE FROM messages WHERE stream_id = $existing; DELETE FROM usernames WHERE stream_id = $existing; DELETE FROM streams WHERE id = $existing; then re-run.\n";
    exit;
}

// ── REGISTER STREAM ───────────────────────────────────────────────────────────
$db->exec("INSERT INTO streams (slug) VALUES ('" . STREAM_SLUG . "')");
$streamId = $db->lastInsertRowID();

// ── KNOWN CHARACTERS ──────────────────────────────────────────────────────────
// These appear in chat more often than random users (weight = times more likely).
// Global — shared across all streams. INSERT OR IGNORE so adding a second stream
// won't duplicate them. Edit usernames and weights to match real friends/family.
$characters = [
  // ['username' => 'FriendName1', 'weight' => 5],  // ← replace with real names
  // ['username' => 'FriendName2', 'weight' => 4],
  // ['username' => 'GrandmaName', 'weight' => 4],
  // ['username' => 'CoachName',   'weight' => 3],
];

$stmt = $db->prepare("INSERT OR IGNORE INTO characters (username, weight) VALUES (:username, :weight)");
foreach ($characters as $c) {
  $stmt->bindValue(':username', $c['username']);
  $stmt->bindValue(':weight',   $c['weight']);
  $stmt->execute(); $stmt->reset();
}

// ── OPENERS ───────────────────────────────────────────────────────────────────
$openers = [
  ['StreamBot ✈️',    '🔴 {channel} is LIVE! Welcome spotters! ✈️',                                     1],
  ['StreamBot ✈️',    'Keep chat kind and respectful — let\'s enjoy some planes! 🛫',                     1],
  ['MCO_Spotter',     'LETS GOOOO {player} is live!! 🙌🙌',                                               0],
  ['AvGeek_Orlando',  'Finally!! Been waiting for this all day 😍',                                       0],
  ['RunwayRat305',    '{airport} looking gorgeous today — perfect spotting weather ☀️',                   0],
  ['TarmacTom',       'Already seeing great traffic on {runway} — this is going to be an amazing stream', 0],
  ['FlightDeckDave',  'Good afternoon everyone! Let\'s spot some planes ✈️',                              0],
];

$stmt = $db->prepare("INSERT INTO messages (stream_id, category, text, username, is_mod) VALUES (:sid, 'opener', :text, :username, :is_mod)");
foreach ($openers as [$username, $text, $is_mod]) {
  $stmt->bindValue(':sid',      $streamId);
  $stmt->bindValue(':text',     $text);
  $stmt->bindValue(':username', $username);
  $stmt->bindValue(':is_mod',   $is_mod);
  $stmt->execute(); $stmt->reset();
}

// ── GENERIC MESSAGES ───────────────────────────────────────────────────���──────
$generic = [
  'This stream is AMAZING 🔥',
  'Love this channel so much ❤️',
  '{player} you are literally the best!!',
  'Been watching for 2 hours and can\'t stop 😂',
  'This is exactly what I needed today',
  'You never disappoint {player}! 🙌',
  'Best live stream on YouTube right now, no cap',
  'Hello from {city}!! 👋',
  'Just showed my dad this and now he\'s hooked too lol',
  'The quality of this stream is unreal',
  '{player} living up to the name!! 🏆',
  '{airport} is my favourite airport to watch and this is why',
  'Obsessed with this channel fr',
  'This is so relaxing to have on in the background ☁️',
  'You deserve WAY more subs',
  'First time here and already subscribed ✅',
  'Literally can\'t look away from the screen',
  'You make every flight look beautiful ✈️',
  'Hello everyone! Great to be here 🙌',
  'This stream always cheers me up 💛',
  'God I love planes so much 😭',
  '{airport} never sleeps and neither does {player} lol',
  'Dropping a like — this deserves millions of views',
  'My kids are watching too, they LOVE this',
  'Camera quality is incredible today!',
  'So peaceful watching the runway 😌',
  'You\'re doing amazing {player} 💖',
  'Shoutout to everyone in chat ✈️✈️',
  'Another banger stream from the absolute GOAT',
  'Can\'t believe this is free to watch, thank you {player}!!',
  'I\'ve watched every single stream you\'ve ever done 💯',
  'Telling all my friends about this channel',
  'This is better than anything on TV right now',
  '{player} you\'re a legend in the aviation community ✊',
  'Live from my couch pretending I\'m at the airport 😂',
  'The vibes today are immaculate ✨',
  'You got me through a rough week, thank you ❤️',
  'Honestly therapeutic watching planes land 😌',
  'Every time I watch this I feel calm. Thank you {player}!',
  'This is my happy place 🛫',
  'Wow just got here and already love this ❤️',
  'Following from {city} 🙌',
  'This is the most peaceful thing on the internet',
  'Just subscribed — where has this channel been all my life?',
  'Honestly I watch this while working from home lol',
  'My cat is ALSO watching the screen 😂',
  'This never gets old no matter how many times I watch',
  'The audio is so good today — you can hear every engine!',
  'Good morning from {city}! ✈️',
  'Good evening from {city} 🌙✈️',
  'Family tradition — watching {player}\'s stream ❤️',
  'I could literally watch this forever',
  'Why is watching planes so satisfying? Science can\'t explain it',
  '{player} you should do this every weekend',
  'The commentary in chat is half the fun lol',
  'Just made a coffee, settling in for the long haul ☕✈️',
  'Screen-sharing this with my buddy right now 🙌',
  'You\'re my favourite content creator, no contest',
  'Day off + {player}\'s stream = perfect day',
  'Already seen 3 of my favourite aircraft types today!',
  'The sun hitting those wings on approach 😍',
  'Chat is so wholesome, I love this community ❤️',
  'Never thought I\'d spend hours watching planes but here we are',
  'This is 100x better than whatever\'s on Netflix',
  'Big shoutout to everyone watching from {city}!',
  'Grab some snacks, this stream is too good to leave 🍿',
  'Following from work… shhh don\'t tell my boss 😅',
  'The runway lighting is gorgeous today',
  'I feel like I\'m right there at the airport 🛫',
  'Stream quality is flawless today {player} 💯',
  'This is therapy honestly 🧘',
  'Hello from the other side of the world! 🌏✈️',
  'First time catching a stream live — worth every second',
  'Came for the planes, stayed for the community 💛',
  'Every stream feels like an event ✈️',
  'I have a long flight tomorrow and this is my warm-up lol',
  'The patience of a plane spotter is unmatched',
  'Planes have always been my thing since I was a little kid ✈️',
  'So proud of how far this channel has come',
  'Just woke up and this is the first thing I put on 😂 good morning!',
  'The detail you put into these streams is unreal',
  'Worth waking up early for every single time ⏰✈️',
  'Volume up, coffee hot, ready for whatever {airport} brings today ☕',
  'I\'ve recommended this channel to literally everyone I know',
  'Perfect background stream while I do chores 🧹✈️',
  'International viewer here — so cool to see this from my couch!',
  'This community is genuinely the nicest on all of YouTube',
  '3 hours in and I\'m not even close to bored',
  'The sound of jet engines is literally music to my ears',
  'Certified plane nerd signing in ✈️',
  'I found this channel late one night and now I\'m hooked 😂',
  'Low clouds burning off — perfect spotting conditions incoming ☀️',
  '{player} never misses. Consistent excellence right here 🏆',
  'Okay I was just going to watch for 10 minutes… that was an hour ago',
  'The comment section here is 10/10, everyone is so friendly',
  'Sent this stream to my aviation group chat, everyone loves it',
  'Rain or shine, {player} delivers every single time ✈️',
  'Something about watching planes land just hits different on a weekend 😌',
  'My whole family is gathered around the screen right now lol',
  'Not all heroes wear capes — some stand in parking lots with cameras ✈️',
  'I used to think plane spotting was niche. This community says otherwise!',
  'Quality content. No drama. Just planes. This is what YouTube was made for.',
  'New viewer here — instantly understand why everyone loves this channel 🙌',
  'I pause whatever I\'m doing whenever {player} goes live. Priority 1.',
  'Chat is going OFF today 🔥',
  'This is what Saturday mornings are for ✈️☕',
  'Saving this stream to rewatch later. Already.',
  'You make plane spotting feel like the most exciting thing in the world',
  'Shoutout to the regulars in chat, you know who you are 💛',
  'The whole vibe of this stream is just *chef\'s kiss* 🤌',
  'Some people have spa days. I have this. Equally relaxing 😂',
  'Watching from {city} and loving every second ✈️',
  'Why does the internet not talk about this channel more?? Underrated gem.',
  'Already planning my trip to {airport} because of this stream 🛫',
  'The amount of planes today is insane — {airport} is popping off',
  'I genuinely smile every time I see {player} go live. Thank you for this.',
  'Okay that last landing was genuinely perfect. I rewound it twice.',
  'My wife said I watch this too much. She\'s now also watching. 😂',
  'Tuning in from {city} on my lunch break — best decision I made today',
  'I\'ve learned more about aviation from this chat than anywhere else',
  'The regulars in this chat are like family at this point ❤️',
  'Hit that like button everyone — {player} deserves it 👍',
  'Zero ads, zero drama, pure planes. Perfection.',
  'Just got my kid to watch and they\'re completely hooked 😂✈️',
  'Watching from the airport lounge waiting for my flight — full circle moment ✈️',
  'The effort {player} puts into every stream is so obvious. Respect. 🙏',
  'Been a subscriber since nearly the beginning — so cool to watch this grow',
  'Sunday streams hit different. Thank you {player} ☀️',
  'I set an alarm for this stream. Worth it every time.',
  '{player} out here living my dream life at the airport 😍',
  'First comment of the day! Hello from {city} ✈️',
  'Still can\'t believe how good the camera quality is',
  'Five stars. Would recommend to every aviation fan I know. ⭐⭐⭐⭐⭐',
  'My dog is also watching. He seems to enjoy it. 🐕✈️',
  'Commenting from my back porch — perfect afternoon stream ☀️',
  'The chat moving fast means the planes are coming fast 🔥',
  'Every time I close the tab I end up opening it again within 5 minutes lol',
  'Honestly the best way to kill a Sunday afternoon ✈️',
  '{player} I hope you know how much this means to all of us in chat 💛',
  'Just hit subscribe for the third time on different accounts 😂 love this channel',
  'Watching the approach lights come on as the sun goes down 🌅✈️',
  'This stream is better than therapy and cheaper too 😂',
  'Tag yourself — I\'m the person who said "just one more plane" 3 hours ago ✈️',
];

// ── AVIATION MESSAGES ─────────────────────────────────────────────────────────
$aviation = [
  'Is that a 737 MAX on final?? Those split winglets give it away 👀',
  'Boeing 777 widebody coming in HOT 🔥 look at that wingspan',
  'That\'s a 787 Dreamliner — notice the raked wingtips!',
  'A321neo!! Love those CFM LEAP engines, so much quieter',
  'Airbus A320 family spotted — beautiful sharklet winglets',
  'Ooh an A350 XWB — that carbon fibre fuselage looks gorgeous in the sun',
  'Is that a 767 freighter? UPS or FedEx?',
  'Classic 737-800 on short final — the workhorse of aviation ✈️',
  'E175 regional jet!! Love the Embraer family',
  'That\'s a CRJ-900 landing — regional jets are underrated',
  'A220 spotted!! Such an underrated aircraft',
  'That engine noise sounds like a CFM56 — classic narrowbody sound',
  'GEnx engines on that 787 sound so much smoother',
  'Look at those Pratt & Whitney GTF engines on the A320neo — so quiet!',
  'Southwest ramp is BUSY today 🟡🟡',
  'Delta mainline looking clean as always 🔵',
  'United\'s new livery looks really sharp in the sunshine',
  'American Airlines 777 widebody!! Love seeing those at {airport}',
  'JetBlue always has the best tail designs 💙',
  'Spirit keeping it bright yellow as always 💛',
  'Frontier with the animal tail!! Which animal is it??',
  'Air Canada narrowbody visiting today ❄️ → ☀️',
  'British Airways transatlantic into {airport} — love it 🇬🇧',
  'Lufthansa direct!! German engineering touching down ✈️',
  'Virgin Atlantic — that red livery is iconic 🔴',
  'WestJet coming down from Canada 🇨🇦',
  '{airport} has two parallel runways right? {runway} is the one to watch',
  'The view from {location} onto {arr_runway} is unreal, best spot at {airport}',
  'Holding short of {arr_runway} — ATC must be sequencing arrivals',
  'Beautiful ILS approach in that sunshine 🌞',
  'That crosswind landing was SMOOTH — pilot really greased it',
  'Wake turbulence separation after that heavy — good airmanship',
  'Listen to those engines spool up for departure — gives me chills 😍',
  '{airport} sees over 50 million passengers a year, traffic is wild',
  'Love watching the ground crews work — unsung heroes 🙏',
  'That go-around was exciting!! Pilot made the right call',
  '{player} can you pan to the cargo ramp? Love seeing the freighters',
  'Gear up right after rotation — pilot flying smooth today 👏',
  'Speed brakes on touchdown — textbook arrival',
  'Is that nose gear steering kicking in? Love the ground handling',
  'That descent profile looks really stable — fully configured on final',
  'Heavy on final — nose up attitude, gear down, flaps full 🛬',
  'You can really see the ground effect just before touchdown',
  'The contrails today are incredible — high moisture at altitude',
  'Any ATR-72s today? Love those turboprops',
  'That 737 reversed thrust HARD on landing — must have needed a short stop',
  'Love seeing the nose-up rotation on departure — V1, VR, V2 babyyy ✈️',
  'Airside bus just picking up the crew — ground ops in full swing',
  'I counted more than 10 aircraft on the apron just now, so busy!',
  'The density altitude really affects performance in Florida summers',
  'Look at the lift coming off those wingtips — induced drag visualised',
  'Final approach fix looks like it\'s about 5-6 miles out',
  'Those wingflex shots on the 787 never get old 😍',
  'An avgeek in the wild — we are everywhere 🤓',
  'That paint scheme looks brand new — fresh delivery?',
  'Crosswind component looks manageable today — maybe 8 knots off the nose',
  'Full flaps, gear down, on glide slope — textbook visual approach',
  'The sheer size of a 747 never stops being impressive',
  'A380 at {airport}?! That would literally make my day 🙏',
  'Love hearing the flap retraction sound after rotation 🛫',
  'You can see the heat shimmer off the runway in Florida heat 🌡️',
  'Line up and wait… and they\'re rolling! Beautiful rotation ✈️',
  'Reverse thrust + spoilers — textbook landing',
  'Ground speed on final looks about 140-145 knots — right in the window',
  'The glide slope for an ILS is 3 degrees — so precise every time',
  'That\'s a full freight configuration — cargo door is massive',
  'The flap track fairings on that A320 are so distinctive',
  'I used to work ramp at {airport} — this brings back so many memories 🙏',
  'One engine taxi to the gate — conserving fuel, love to see it',
  'Left base turn — they\'re number 2 for the runway behind that widebody',
  'Beautiful crab angle correction just before touchdown — classic crosswind technique',
  'The terminal radar approach controller hands off to tower about 10 miles out',
  'Notice how the gear doors open and then close after extension — pure engineering',
  'Dispatch must have built in extra fuel for those afternoon storms ⛈️',
  'Anti-collision lights — red beacon on the fuselage, white strobes on wingtips',
  'Night approaches at {airport} are going to be gorgeous later 🌃✈️',
  'Standard instrument departure from {dep_runway} — climbing straight out',
  'I can ID aircraft by engine sound at this point 😂 total avgeek',
  'The spoilers deploying on touchdown kill lift immediately — smart design',
  'Thrust reversers and the roar that comes with them 😍',
  'Wingtip vortices are invisible but VERY real — that\'s why we have wake separation',
  'That\'s a max gross weight departure — long roll before rotation',
  'You can see the APU exhaust at the tail — main engines not yet started',
  'The tug pushing back that 777 makes it look like a toy 😂',
  'Southwest has the highest aircraft utilisation in the world — always moving',
  'That\'s a visual approach without ILS guidance — pilot flying by hand',
  'Love seeing the airline ground coordinators giving marshalling signals',
  'The main gear touches first, THEN the nose gear — every time, by design',
  'Smoke from the tyres on touchdown — friction heating the rubber 🔥',
  'The auto-brake system kicks in before the crew even touches the pedals',
  'Air traffic control at busy airports handles one landing every 90 seconds at peak',
  'PIREP just came in: smooth ride all the way down — no turbulence today 👌',
  'The first officer is probably flying this one — building hours ✈️',
  'That\'s a Category III ILS approach — can land in near-zero visibility',
  'Love watching the nose wheel lift clear last on departure',
  'High bypass turbofan — most of the thrust is from the fan, not the core exhaust',
  'Fuel trucks out on the apron — turnaround is tight, they\'re on the clock',
  'That aircraft did a TOGA go-around — thrust levers all the way forward, nose up!',
  'Anyone else tracking this flight on Flightradar right now?? 👀',
  'Florida weather = pop-up afternoon thunderstorms — hoping skies stay clear ⛅',
  'Short final and gear down and locked — textbook 👍',
  'That\'s a 757 — narrow body but still carries a full load of passengers ✈️',
  'The 737 family has been flying since 1968 — most produced airliner in history',
  'Sun Country, Allegiant, Avelo — the low cost carriers filling every gate',
  'MORA on the approach plate keeps the crew clear of terrain in IMC — love the detail in aviation',
  'Brake temps on that landing were probably spicy — glad they have fans on the ground 🌡️',
  'The FMC calculates the optimal descent point from cruise — pilots barely touch it 😂',
  'V-speeds are recalculated for every single departure based on weight and conditions',
  'That\'s a wet lease operation — aircraft AND crew from another carrier',
  'SELCAL chime just went off on the HF radio — crew getting a company message ✈️',
  'The wingtip strobe lights are visible for miles — important for night operations',
  'Hearing the gear horn test before pushback — crew running through the checklist',
  'That outbound flight will be at FL380 in about 20 minutes — incredible when you think about it',
  'The TAF for {airport} looks clear all afternoon — perfect spotting conditions 📋',
  'Love watching the passengers at the gate windows watching the planes too 😄',
  'That\'s a deadhead crew in the cabin — positioning for their next flight',
  'The aircraft registration starts with N — so it\'s US registered ✈️',
  'Final flap setting selected — you can see them extend to the full position',
  'The crew just checked in on 121.5 — emergency frequency is always monitored',
  'Weight and balance has to be calculated to the pound before every departure',
  'That\'s a slot-controlled departure — they\'re waiting for their EDCT time',
  'The PAPI lights showing white over white — slightly above glidepath, correcting now',
  'Full reverse and max braking — short runway or fast touchdown speed',
  'Love how the jetbridge operator lines it up perfectly every single time 🎯',
];

// ── SUPER CHAT MESSAGES ───────────────────────────────────────────────────────
$superchat = [
  '{player} you\'ve made me fall in love with plane spotting!! Keep it up! ✈️❤️',
  'Watching from the UK — {airport} is my favourite US airport! Thank you for this!',
  'My son is obsessed with planes because of your channel. You\'re a hero {player} 🙏',
  'Best aviation content on all of YouTube. Not even close. Love you {player}!! 🔥',
  'Just got my PPL!! Celebrating by watching the big boys land 😂 Thanks for the inspiration!',
  'Retired airline mechanic here — your passion for planes warms my heart 💛',
  'Watching from {city}! You inspired me to visit {airport} just to plane spot!!',
  'Two years of watching and still the highlight of my week every time ✈️',
  'My whole family watches every stream. You\'re our favourite!! 🏆',
  'Thank you for making aviation fun and accessible for everyone!!! Legend 🌟',
  '{airport} Live forever!! {player} you are the king of airport streams 👑',
  'Sending this to my pilot friend — he is going to love it. Keep going!!',
  'From a pilot myself — you capture the magic of flight perfectly. Thank you {player}! 🛫',
  'I\'m visually impaired and this stream is how I experience airports. Thank you ✈️',
  'Just got home from a 14-hour flight. First thing I do is join this stream 💛',
  'Your stream played at my daughter\'s birthday party — the kids were glued to the screen 😂',
  'You turned my retired dad into a plane spotter. He watches every stream ❤️',
  'Watching from the hospital waiting room. {player} you\'re keeping me sane. Thank you ❤️',
  'I\'ve flown through {airport} hundreds of times for work. Now I SEE it from outside. Amazing!',
  'My son loves planes and this is his comfort stream. More good than you know 💙',
  'Celebrating 25 years as an A&P mechanic today. This stream is my reward 🔧✈️',
  'Watching from {city} because I found this channel and literally could not stop. Worth it!!',
  'First YouTube channel I\'ve ever donated to. That tells you everything. Keep going {player}!! 🏆',
  'My grandson calls this "the plane show" and demands it every weekend. Fan for life 👦✈️',
  'Was going to quit my dream of becoming a pilot. This reminded me why I started. Back on track 🛫',
  'I watch from {city} every week. Better than any travel I\'ve ever done. Thank you {player}!',
  'Just booked my first trip to {airport} BECAUSE of your streams. Can\'t wait!! 🙌',
  'Retired ATC here from {airport}. Watching you spot aircraft I used to sequence. Full circle 🥹',
  'Honestly {player} you have no idea the joy you bring to people. Keep doing this. 💛✈️',
  'My flight was just cancelled and this stream is the only thing keeping me sane right now 😂✈️',
  'I\'ve spotted planes at 30 airports around the world. {airport} is still my favourite. Cheers {player}!',
  'My daughter just said she wants to be a pilot because of this stream. You changed her life 🛫💖',
  'Donating because this stream got me through a really hard month. Thank you from the bottom of my heart.',
  'First time superchatting ever. {player} you absolutely deserve it. Keep going forever. 🏆✈️',
  'Watching from {city} where it\'s -20 outside. This Florida sunshine is medicine right now ☀️✈️',
  'You inspired me to get my student pilot certificate. Solo flight next month!! 🛫🎉',
  'This channel is proof that passion is contagious. You love planes and now so do we all. 💛',
  'Watching with my 4-year-old who now knows what a 737 MAX looks like. Thanks {player} 😂✈️',
  'Been following since you had under 1000 subs. So proud to see where you\'ve come. Keep going!! 🙌',
  'Just landed at {airport} and immediately opened the stream from the terminal. Meta. ✈️😂',
];

// ── TICKER ITEMS ───────────────────────────────────────────────────────────��──
$ticker = [
  'VIEWING POSITION: {location}  ·  RUNWAY {runway}',
  'ACTIVE: {arr_runway} ARRIVALS  ·  {dep_runway} DEPARTURES — WIND 170 AT 12KT',
  'METAR K{airport}: 17012KT 10SM FEW040 SCT250 30/18 A2994 — VFR',
  'RUNWAY {runway} — 10,000 FT — ILS PRECISION APPROACH ACTIVE',
  'TRAFFIC: AAL · DAL · UAL · WN · B6 · NK · F9 · G4 · AS · AC',
  '{location} VIEW: STRAIGHT-ON FINALS TO {arr_runway} — BEST SEAT IN THE HOUSE',
  '{airport} TOWER {arr_runway}: 124.3  ·  GROUND: 121.9  ·  ATIS: 132.35',
  'OAT +31°C · DENSITY ALTITUDE ~2,100 FT · WINDS FAVOUR {arr_runway} TODAY',
  'NOTAM: TWY F CLOSED 0800–1600 LOCAL — EXPECT EXTENDED TAXI TIMES',
  'NEXT HEAVIES EXPECTED: UAL 777 · DAL 767 · AAL 787 ON FINAL {arr_runway}',
];

$stmt = $db->prepare("INSERT INTO messages (stream_id, category, text) VALUES (:sid, :cat, :text)");
foreach ([
  'generic'   => $generic,
  'aviation'  => $aviation,
  'superchat' => $superchat,
  'ticker'    => $ticker,
] as $cat => $msgs) {
  foreach ($msgs as $text) {
    $stmt->bindValue(':sid',  $streamId);
    $stmt->bindValue(':cat',  $cat);
    $stmt->bindValue(':text', $text);
    $stmt->execute(); $stmt->reset();
  }
}

// ── USERNAMES ─────────────────────────────────────────────────────────────────
$usernames = [
  'AvGeek_Orlando','MCO_Spotter','RunwayRat305','PlaneNerd77','JetBlastFan',
  'TarmacTom','FlightDeckDave','AviationKing','SpotterSam','GateAgent99',
  'N1AvGeek','WidebodyWatcher','TailfinTerry','ApronApex','FinalApproach_FL',
  'OrlandoAirHead','MCO_Daily','TakeoffTina','ClearanceClive','SquawkBox',
  'HoldShortHannah','IFRFlier','LandingLarry','SkyTracker_US','JetAge_Jo',
  'AircraftNut','WingletWendy','TaxiwayTed','AltimeterAl','FlapsFullFrank',
  'EngineEric','PushbackPat','GlideSlopeGary','ThrottleUp42','BlueskiesBen',
  'TurbofanTina','CrosswindCarl','AtcListener','SpotterLife','FL350Crew',
  'PlaneSpotUSA','AviationDaily','JetPhotog','RunwayChaser','GearDownGus',
  'FinalFive','ApproachBandit','SpeedBrakeSue','ClearedToLand','RotateRick',
  'VSI_Watcher','StabilisedApproach','VFR_Victor','DeicingDave','FlightLevel99',
  'TailwindTerry','HeadwindHenry','ThrustReversalRob','GearUpGina','FlapsOne',
  'TaxiwayKing','GroundSpeedGreg','RunwayClearance','AircraftNoise','WakeTurbulence',
  'CabinAltitude','FuelDumpFrank','APU_Andy','CompassRose','MagneticNorth',
  'GreaseIt_Gary','SmokeOnThe17','SpotterFam','AvnerdsUnite','BigJetTV_Fan',
];

$stmt = $db->prepare("INSERT INTO usernames (stream_id, name) VALUES (:sid, :name)");
foreach ($usernames as $name) {
  $stmt->bindValue(':sid',  $streamId);
  $stmt->bindValue(':name', $name);
  $stmt->execute(); $stmt->reset();
}

// ── CITIES (shared — INSERT OR IGNORE) ───────────────────────────────────────
$cities = [
  'New York','London','Sydney','Chicago','Toronto','Dallas','Atlanta',
  'Denver','Seattle','Miami','Boston','Tokyo','Berlin','Dubai','Phoenix',
  'Nashville','Portland','Minneapolis','Cleveland','Houston','Las Vegas',
  'San Francisco','Amsterdam','Singapore','Melbourne','Auckland',
  'Los Angeles','Paris','Madrid','Rome','Dublin','Edinburgh','Manchester',
  'Vancouver','Montreal','Calgary','Mexico City','São Paulo','Buenos Aires',
  'Cape Town','Nairobi','Mumbai','Bangkok','Seoul','Hong Kong','Taipei',
  'Oslo','Stockholm','Copenhagen','Helsinki','Vienna','Zurich','Brussels',
];

$stmt = $db->prepare("INSERT OR IGNORE INTO cities (name) VALUES (:name)");
foreach ($cities as $name) {
  $stmt->bindValue(':name', $name);
  $stmt->execute(); $stmt->reset();
}

$db->close();

echo "✅ Stream '" . STREAM_SLUG . "' seeded (stream_id = $streamId)\n";
echo "   · " . count($generic)    . " generic messages\n";
echo "   · " . count($aviation)   . " aviation messages\n";
echo "   · " . count($superchat)  . " super chat messages\n";
echo "   · " . count($ticker)     . " ticker items\n";
echo "   · " . count($usernames)  . " usernames\n";
echo "   · " . count($characters) . " known characters\n";
echo "   · " . count($cities)     . " cities (shared)\n";
echo "\n⚠️  Fill in real names in the \$characters array, then delete airport/init.php.\n";
