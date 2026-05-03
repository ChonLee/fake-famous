<?php
define('STREAM_SLUG', basename(__DIR__));
define('DB_PATH',     dirname(__DIR__) . '/db/fake-famous.db');

$db = new SQLite3(DB_PATH);
$db->exec("PRAGMA journal_mode=WAL");

$db->exec("CREATE TABLE IF NOT EXISTS streams   (id INTEGER PRIMARY KEY, slug TEXT UNIQUE)");
$db->exec("CREATE TABLE IF NOT EXISTS messages  (id INTEGER PRIMARY KEY, stream_id INTEGER, category TEXT, text TEXT, username TEXT, is_mod INTEGER DEFAULT 0)");
$db->exec("CREATE TABLE IF NOT EXISTS usernames (id INTEGER PRIMARY KEY, stream_id INTEGER, name TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS characters(id INTEGER PRIMARY KEY, username TEXT UNIQUE, weight INTEGER DEFAULT 1)");
$db->exec("CREATE TABLE IF NOT EXISTS cities    (id INTEGER PRIMARY KEY, name TEXT UNIQUE)");

$db->exec("INSERT OR IGNORE INTO streams (slug) VALUES ('" . STREAM_SLUG . "')");
$streamId = $db->querySingle("SELECT id FROM streams WHERE slug = '" . STREAM_SLUG . "'");

$existing = $db->querySingle("SELECT COUNT(*) FROM messages WHERE stream_id = $streamId");
if ($existing > 0) {
    echo "Stream already seeded ($existing messages). To reseed: DELETE FROM messages WHERE stream_id = $streamId; then DELETE FROM usernames WHERE stream_id = $streamId; then re-run.\n";
    exit;
}

// ── CITIES (shared) ───────────────────────────────────────────────────────────
$cities = [
    'New York','Los Angeles','Chicago','Houston','Phoenix','Philadelphia','San Antonio','San Diego',
    'Dallas','San Jose','Austin','Jacksonville','Fort Worth','Columbus','Charlotte','Indianapolis',
    'San Francisco','Seattle','Denver','Nashville','Oklahoma City','El Paso','Boston','Portland',
    'Las Vegas','Memphis','Louisville','Baltimore','Milwaukee','Albuquerque','Tucson','Fresno',
    'Sacramento','Mesa','Kansas City','Atlanta','Omaha','Colorado Springs','Raleigh','Virginia Beach',
    'Long Beach','Minneapolis','Tampa','New Orleans','Arlington','Bakersfield','Honolulu','Anaheim',
    'Aurora','Santa Ana','Corpus Christi','Riverside','St. Louis','Lexington','Pittsburgh','Anchorage',
    'Stockton','Cincinnati','St. Paul','Greensboro','Toledo','Newark','Plano','Henderson','Lincoln',
    'Buffalo','Fort Wayne','Jersey City','Chula Vista','Orlando','St. Petersburg','Norfolk','Chandler',
    'Laredo','Madison','Durham','Lubbock','Winston-Salem','Garland','Glendale','Hialeah','Reno',
    'Baton Rouge','Irvine','Chesapeake','Scottsdale','North Las Vegas','Fremont','Gilbert','Birmingham',
    'Rochester','Richmond','Spokane','Des Moines','Montgomery','Modesto','Fayetteville','Tacoma',
    'Toronto','Vancouver','London','Sydney','Melbourne','Auckland','Dublin','Edinburgh','Tokyo',
    'Seoul','Singapore','Mumbai','Cape Town','Amsterdam','Berlin','Paris','Rome','Madrid','Stockholm',
];
$cityStmt = $db->prepare("INSERT OR IGNORE INTO cities (name) VALUES (:n)");
foreach ($cities as $c) { $cityStmt->bindValue(':n', $c); $cityStmt->execute(); }

// ── OPENERS ───────────────────────────────────────────────────────────────────
$openers = [
    ['StreamBot 🚗', "Welcome to {channel}! {player} is LIVE — BeamNG chaos starts now! 💥", true],
    ['StreamBot 🚗', "Crash counter starting at zero... let's see how long that lasts 😂", true],
    ['StreamBot 🚗', "Like the stream if you want to see something absolutely destroyed today 🚗💨", true],
    ['JoyRider_Gaming', "Let's gooo {player}!! Been waiting all week for this!"],
    ['CrashTester99', "First! Ready to watch some cars get absolutely wrecked"],
    ['SpeedDemon_Real', "here we gooooo 🔥🔥🔥"],
    ['BeamNGFan', "yooo {player} is live! dropping everything"],
    ['OffRoadAdventures', "I called in sick for this stream lol (worth it)"],
    ['TurboBoostGamer', "{player}!! Can you start with the Pessima?? please??"],
    ['DriftMaster2024', "hi hi hi {player} hope you crash something amazing today"],
    ['GamingKid_2012', "omg he's live!! hi {player}!!"],
    ['PhysicsIsReal', "the physics engine is about to get absolutely destroyed 😂"],
    ['XxDriftKingxX', "ready for maximum destruction, let's get it"],
    ['RealCarPhysics', "good morning from {city}! {player} you're the best"],
    ['BeamNG_Forever', "I told my whole class about this stream lol"],
];

// ── GENERIC MESSAGES ─────────────────────────────────────────────────────────
$generic = [
    "let's go {player}!!!",
    "LETS GOOOOO",
    "{player} you're actually insane at this game",
    "first time catching {player} live 🔥",
    "came from your YouTube, love your content",
    "hi from {city}! 👋",
    "greetings from {city}!",
    "anyone else watching from {city}?",
    "{player} say hi to {city}!",
    "{player} how long have you been playing {game}?",
    "I play {game} too but I'm nowhere near as good",
    "I downloaded {game} because of your videos",
    "is {game} on console?",
    "{player} what PC do you have?",
    "what specs do you need for {game}?",
    "the graphics in {game} are so realistic",
    "{player} have you tried any mods?",
    "the physics in this game are actually insane",
    "the damage model is so good",
    "this game just keeps getting better with updates",
    "the devs are so dedicated to {game}",
    "{player} you're my fav streamer",
    "this is the best gaming channel on YouTube",
    "{player} I've been watching you for like a year",
    "{player} can you do a shoutout for my channel",
    "omg I can't believe I caught a live stream",
    "just subbed!",
    "already subbed and liked ✅",
    "new sub here!",
    "{player} you should do a 24 hour stream",
    "wish I could play games all day",
    "I showed my brother your channel and now he's obsessed",
    "my little brother loves {game} too",
    "bro I told all my friends about this stream",
    "hi hi hi hi hi",
    "hiiiiiii {player} 😄",
    "can you see my message {player}",
    "let's see how long I last in this stream lol",
    "I've been watching for like 3 hours straight",
    "bruh I have school tomorrow but I can't stop watching",
    "this is more entertaining than Netflix",
    "clip it clip it clip it",
    "that was insane",
    "W streamer fr fr",
    "not me watching this at midnight lol",
    "I'm doing homework rn and still watching lol",
    "{player} my mom said I can watch for one more hour",
    "bro I love this stream so much",
    "{player} you should stream more often",
    "this stream is actually so entertaining",
    "I love the energy in this stream 🔥",
    "chat is so hype rn lol",
    "this is the most fun stream I've watched in a while",
    "how does {player} make this look so easy",
    "{player} are you going to post this on YouTube?",
    "save the replay for a video please!!",
    "this would make such a good YouTube clip",
    "I shared this stream with 5 people already",
    "everyone I know watches {player}",
    "{player} you deserve way more subscribers",
    "underrated streamer honestly",
    "the algorithm finally showed me this stream and I'm not leaving",
    "bro this community is actually so wholesome",
    "chat is the best part of a {player} stream 😂",
    "this is giving me ideas for when I get {game}",
    "adding {game} to my wishlist rn",
    "my parents won't let me get {game} yet :((",
    "{player} can you recommend a good gaming PC?",
    "how much does {game} cost?",
    "is {game} worth it?",
    "YES it's worth it I have it",
    "100% worth getting, so many hours of fun",
    "gg {player} gg",
    "{player} what's your favorite car in the game?",
    "chat what's your fav vehicle in {game}?",
    "team Pessima in the chat 🙋",
    "team ETK let's gooo",
    "nah Moonhawk is the GOAT",
    "the Burnside is criminally underrated",
    "love your reaction vids {player}",
    "dude you need to do a collab with another BeamNG streamer",
    "{player} when's your next video coming out",
    "can you do a face reveal {player} 👀",
    "{player} this is giving me anxiety in the best way",
    "my heart is racing watching this lol",
    "I literally yelled at my screen just now",
    "bro my roommates keep asking why I'm yelling",
    "that gave me secondhand adrenaline",
    "{player} your content gets better every week",
    "been here since the beginning, so proud of how far you've come",
    "the growth on this channel is insane",
    "one day you'll hit a million subs and I'll be like I was there",
    "keep going {player}, you're doing amazing",
    "{player} is genuinely one of the best gaming channels out there",
    "the thumbnail for today's stream 🔥🔥🔥",
    "bro the notifications never fail to come through right when I'm busy",
    "dropped everything to tune in lol",
    "cancelled my plans for this stream (no regrets)",
    "eating dinner rn and watching, perfect combo",
    "this is my after-school routine at this point",
    "every day I check if {player} is live",
    "set a reminder and still almost missed it lol",
    "love how consistent {player} is with streaming",
    "reliable upload schedule is so underrated",
    "can we hit 1000 likes on this stream?",
    "let's get this stream to trending",
    "smashing the like button rn",
    "liked and turned notifications on 🔔",
    "telling all my friends to tune in right now",
    "ngl this is the most fun stream I've found in months",
    "the chat is actually hilarious rn 😂",
    "I love how everyone in chat is as hyped as I am",
    "who else is screaming at their screen rn",
    "bro I just spilled my drink reacting to that",
    "I literally jumped out of my chair",
    "my dog came in to check why I was yelling lol",
    "{player} you're genuinely so entertaining to watch",
    "the commentary on this stream is top tier",
    "I love how chill {player} is even when everything is chaos",
    "best streaming setup ever tbh",
    "the reaction to that crash was priceless 😂",
    "I want {player} to react to my crashes in {game}",
    "should I stream {game} too? {player} inspired me",
    "you made me want to be a streamer {player}",
    "the career mode in {game} is actually underrated btw",
    "I unlocked the Hirochi Prasu in career today finally",
    "career mode grind is real but worth it",
    "{player} have you done all the missions in career mode?",
    "the tutorial missions in {game} are actually helpful ngl",
    "anyone else spend more time in the garage than actually driving",
    "I spent 2 hours tuning a car and then crashed it immediately",
    "the tuning system in {game} is genuinely deep",
    "differential tuning changed my life in {game}",
    "spring rate nerd in the chat 👋",
    "lowering the ride height and then immediately scraping everything lol",
    "the anti-roll bar settings matter way more than you think",
    "anyone else drive like a normal person for 10 minutes then go full chaos",
    "I love the zen of just cruising on {map} before the chaos starts",
    "{player} try just normal driving for 60 seconds... then chaos",
    "peaceful drive through {map} incoming... just kidding floor it",
    "the scenic routes in {game} actually go hard",
    "the sunset lighting on {map} is genuinely beautiful",
    "who knew a crash sim would be so pretty",
    "the environmental details in {game} are 🤌",
    "bro the tire smoke looks so good in slow motion",
    "the sparks when metal hits the road are so satisfying",
    "I could watch the debris physics forever",
    "glass breaking in slow motion in {game} is art",
    "hood flying off in slow motion hits different",
    "the way the seats eject in crashes is both funny and sad",
    "{player} have you tried the cargo trailer? it adds so much chaos",
    "hitching a trailer to anything and seeing what happens is peak {game}",
    "boat trailer + sports car = absolute disaster",
    "what's the most cargo you can stack on one vehicle?",
];

// ── GAME-SPECIFIC MESSAGES (stored in 'aviation' category slot) ───────────────
$gameSpecific = [
    "CRASH!! 💥💥💥",
    "omg that rollover was perfect",
    "that car is NOT surviving that 😂",
    "the damage physics are so good",
    "try the {vehicle} next!",
    "drive to the top of the mountain and let it roll",
    "{map} has the best roads for crashing",
    "do a barrel roll",
    "ram it into the wall full speed",
    "GO FASTER",
    "more boost more boost more boost",
    "can you do a jump off the bridge?",
    "I tried this exact thing and destroyed my car in 2 seconds",
    "the suspension is holding up better than expected",
    "that should NOT still be driveable 😂",
    "bro how is the engine still running after that",
    "full speed into the traffic 👀",
    "do the figure 8 course",
    "try the demolition derby next",
    "the Pigeon is so underrated for crashes",
    "the Pessima is built different",
    "the ETK is so fun off road",
    "use the Moonhawk it's a classic",
    "the Barstow has such a good crash sound",
    "try the Bolide it's actually insane",
    "the Wigeon holds up surprisingly well lol",
    "use a garbage truck 😂",
    "the bus is SO satisfying to crash",
    "all wheel drive makes a huge difference on {map}",
    "go to the dirt roads on {map}",
    "Italy map is the most beautiful",
    "Small Island is perfect for stunts",
    "Gridmap lets you set up anything",
    "WCUSA has the best city driving",
    "go to the industrial area on {map}",
    "the highway on {map} goes hard",
    "do the long jump",
    "try to flip it exactly 3 times",
    "soften the suspension and hit the jump again",
    "brake check the AI cars",
    "set the AI to aggressive mode",
    "get 10 AI cars and have a race",
    "traffic AI is surprisingly good in {game}",
    "the sound design in this game is 10/10",
    "I love how every car sounds different",
    "that engine swap sounds SO good",
    "put the V8 in the Pigeon and go",
    "turbo + nos = chaos",
    "watch out for that turn!!",
    "TOO FAST TOO FAST 😱",
    "he's not slowing down LOL",
    "there is no way that car still drives",
    "the car is basically a pile of metal now 😭",
    "save the replay for YouTube!!",
    "clip that whole run for a video",
    "that's going in the highlights for sure",
    "do the scenario where you have to deliver fragile cargo",
    "try the police escape scenario",
    "the time trials on {map} are so hard",
    "beat my record of 0 damage in 10 seconds 😂",
    "can you outrun the police in {game}?",
    "go down the mountain with no brakes",
    "close your eyes for 5 seconds while driving lol",
    "turn off all the stability assists",
    "drift mode is so fun on the mountain roads",
    "oversteer all the way",
    "the AWD vs RWD debate is real in {game}",
    "truck drag race when??",
    "bus vs bus collision test pls",
    "can a car survive being run over by a bus? test it",
    "bro the slow motion replay is always so satisfying",
    "the way the doors fly off 😂",
    "I love watching the wheels fold under the car",
    "the crumple zones in this game are actually accurate",
    "real automotive engineers use {game} for testing btw",
    "this is basically a physics simulator at this point",
    "the jello car mod is absolutely wild if you haven't tried it",
    "there's a mod that makes the car out of glass try it",
    "the falling car from space mod is incredible",
    "do the vs 1000 tons challenge",
    "try the spike strip scenario",
    "what's the fastest you've gone in {game}?",
    "I got my car to 400mph with mods and the game just gave up",
    "land speed record attempt when?",
    "can you beat the track time without crashing once",
    "no crash challenge? impossible? maybe?",
    "do a gentle Sunday drive... for 30 seconds then chaos",
    "carefully drive to the gas station... then explode",
    "police chase but the police car is a bus",
    "use only the handbrake for the whole run",
    "drive with the camera facing backwards",
    "that landing was actually clean tho",
    "no way you stuck that",
    "STICK THE LANDING STICK THE LANDING",
    "nailed it!!",
    "I've been trying that jump for hours lol",
    "the {vehicle} handles so different after the physics update",
    "new update changed everything about how this car feels",
    "I feel like the suspension is bouncier than it used to be",
    "anyone else notice the improved tire physics?",
    "the road surface deformation in this game is crazy",
    "how does it handle the rain physics?",
    "night driving in {game} is underrated",
    "the headlights in this game look so real",
    "fog mode on {map} is actually terrifying",
    "the sand dunes on {map} are perfect for jumps",
    "the quarry on {map} is so fun for off-roading",
    "has anyone tried doing laps of the whole map?",
    "I mapped out the longest possible drive on {map} lol",
    "what's the hidden shortcut on {map}?",
    "{player} have you found all the secret areas on {map}?",
    "there's a hidden ramp behind the warehouse btw",
    "the toll booth area on WCUSA is perfect for chaos",
    "canyon road drift session? lets go",
    "mountain pass drift at night hits different",
    "I love when the car goes completely airborne",
    "hang time!! hang time!!",
    "that was like 3 full seconds of air time",
    "the car is orbiting at this point lol",
];

// ── SUPERCHAT MESSAGES ────────────────────────────────────────────────────────
$superchat = [
    "{player} absolutely smashing it as always 💛",
    "keep up the amazing content, this stream is gold",
    "{player} you inspired me to get {game}, best decision ever",
    "happy birthday if it's your birthday 🎂 love the channel",
    "hi {player}! can you do a shoutout for my little brother Tyler?",
    "my son watches you every day, you're genuinely his favorite streamer",
    "{player} you make my day every time you stream",
    "just bought {game} because of watching this stream today!",
    "thank you for making such great content {player} ❤️",
    "{player} you're genuinely one of the best gaming channels out there",
    "love from {city}! my whole family watches your channel",
    "first superchat ever and it's going to {player} — you deserve it",
    "been watching since the beginning, so proud of how far you've come",
    "you make gaming look so fun {player} ❤️",
    "this stream is the highlight of my week fr",
    "can you say hi to my friend Jake who just discovered your channel?",
    "we watch every stream, never miss it! keep it up",
    "{player} your content helped me through a rough few weeks, thank you",
    "you're so talented at {game} {player}, keep it up!",
    "one day I'll be as good at {game} as you {player} 🙏",
    "love the energy on this stream 🔥",
    "hi from {city}! we love you {player}!",
    "please never stop streaming {player}",
    "shoutout to {player} for always being so entertaining",
    "you're genuinely one of the best streamers for kids, keep going",
    "keep smashing those crash tests {player} 💥",
    "my little brother just discovered your channel and he's completely hooked",
    "we drove through {city} this weekend and it made me think of your stream lol",
    "{player} can you crash test the Pigeon please? for science 🔬",
    "massive W streamer, love the community here",
    "{player} you're going to hit 100K soon I can feel it",
    "I've never enjoyed a gaming stream this much, you're hilarious",
    "just got my allowance and spent it on a superchat lol worth it",
    "this is the best $10 I've ever spent, {player} is the GOAT",
    "my whole friend group watches {player} now, you brought us together 😂",
];

// ── TICKER ITEMS ──────────────────────────────────────────────────────────────
$ticker = [
    "NOW CRASHING: {vehicle} ON {map} — BEAMNG DRIVE LIVE WITH LEGOEASTON",
    "CRASH COUNT: LOST TRACK — CURRENT VEHICLE: SOMEHOW STILL MOVING",
    "TODAY'S CHALLENGE: CAN {vehicle} SURVIVE? SPOILER: PROBABLY NOT",
    "SPEED RECORD ATTEMPT IN PROGRESS — VIEWER SAFETY NOT GUARANTEED",
    "CURRENT STATUS: SOMEHOW STILL DRIVING — NEXT CRASH: IMMINENT",
    "ATTEMPTING WORLD'S MOST RIDICULOUS JUMP — RESULTS TBD",
    "VEHICLE CONDITION: CRITICAL — ENGINE: INEXPLICABLY STILL RUNNING",
    "LEGOEASTON'S BEAMNG DRIVE — WHERE CARS GO TO DIE BEAUTIFULLY",
    "TODAY'S FEATURED MAP: {map} — DESTRUCTION IS INEVITABLE",
    "CURRENT WORLD RECORD ATTEMPT: 0 TO COMPLETELY TOTALED IN RECORD TIME",
    "WARNINGS: EXCESSIVE CRASHES, ZERO BRAKE USAGE, MAXIMUM CHAOS",
    "PHYSICS ENGINE: CURRENTLY BEING PUSHED TO ITS ABSOLUTE LIMITS",
    "VEHICLE FLEET TODAY: EVERY AVAILABLE CAR — ALL WILL CRASH GLORIOUSLY",
    "DAMAGE MODEL RATING: 10/10 — REALISM RATING: TERRIFYINGLY ACCURATE",
    "STUNTS, CRASHES, AND CHAOS — LEGOEASTON'S BEAMNG LIVE STREAM",
    "CURRENT GOAL: FLIP THE CAR EXACTLY 12 TIMES — PROGRESS: WORKING ON IT",
    "BEAMNG LIVE: CRASH TESTING {vehicle} ON EVERY POSSIBLE ROAD SURFACE",
    "TUNE IN EVERY WEEKEND FOR MAXIMUM DESTRUCTION AND ZERO SAFETY STANDARDS",
    "LIVE CRASH TESTING: PROFESSIONAL RESULTS, AMATEUR SAFETY PRACTICES",
    "TODAY ON LEGOEASTON: CAN {vehicle} SURVIVE {map}? LET'S FIND OUT",
    "BEAMNG UPDATE: NEW PHYSICS — NEW WAYS TO DESTROY EVERYTHING",
    "CURRENT SPEED: TOO FAST — CURRENT BRAKING: NONE — CURRENT VIBE: PERFECT",
    "EPISODE 1 OF CAN I SURVIVE WITHOUT BRAKES: IT IS NOT GOING WELL",
    "CRASH COMPILATION COMING SOON — CURRENT FOOTAGE: INCREDIBLE",
    "LIVE FROM THE DRIVER SEAT OF TOTAL CHAOS — LEGOEASTON BEAMNG",
    "I mapped out the longest possible crash route on {map} lol",
    "the train on {map} is actually a weapon and I love it",
    "I hit the train in {game} and didn't survive, can you?",
    "hotdog run: can you stay on the rails without derailing",
    "the forklift handling model is surprisingly accurate",
    "construction vehicles only challenge — {map} wouldn't survive",
    "race a forklift vs a bus, I need this content",
    "shopping cart mod exists and it is incredibly important",
    "the unicycle mod makes me genuinely angry",
    "{player} please try the unicycle mod",
    "the skateboard handling is terrifyingly accurate",
    "the tanker truck chaos on {map} is unmatched",
    "double-decker bus on the mountain roads please",
    "can a helicopter land on a moving vehicle in {game}?",
    "what's the largest vehicle you've driven at top speed?",
    "max weight + max speed = {map} cannot handle this",
    "the weight class system in {game} is actually realistic",
    "have you done the 18-wheeler on the track yet",
    "18-wheeler drift is a genre and it slaps",
    "full send the semi down the mountain, chat needs this",
    "one time I drove the semi off the cliff and time stopped",
    "the game froze for a second because the physics couldn't cope 😂",
    "true story: I once crashed so hard the game apologized",
    "my rig can run {game} smoothly until there are 20 AI cars",
    "AI traffic with aggressive settings is genuinely terrifying",
    "I got rear-ended by an AI going 90 in a school zone 💀",
    "the AI behavior after the update is chef's kiss",
    "police pursuit mode when?? this game needs it as a full mode",
    "custom scenarios editor is so underrated in {game}",
    "I spent 4 hours building a custom track and then immediately crashed on it",
    "{map} MAP: LOADING — CARS: READY — CRASHES: INEVITABLE",
    "NO GUARDRAILS WERE HARMED IN THIS STREAM... WAIT YES THEY WERE",
    "PHYSICS SIMULATION ACTIVE — COMMON SENSE SIMULATION: OFFLINE",
    "BEAMNG DRIVE: THE ONLY GAME WHERE CRASHING IS THE POINT",
    "CHAT IS CONTROLLING THE CHAOS TODAY — GOOD LUCK EVERYONE",
];

// ── USERNAMES ─────────────────────────────────────────────────────────────────
$usernames = [
    'GamingKid_2012','CrashTester99','BeamNGFan','SpeedDemon_Real','XxDriftKingxX',
    'CarCrashKing','PhysicsIsReal','TurboBoostGamer','OffRoadAdventures','DriftMaster2024',
    'CrashAndBurn_TV','RealCarPhysics','BeamNG_Forever','VehicleDestroyer','SpeedrunnerBoy',
    'GamingWithKids','PixelRacerKid','CarEnthusiast12','JumpAndCrash','StuntDriverPro',
    'FullSendGaming','NitroGamer_X','QuickShiftDrifter','CrashTestDummy','BeamNG_Addict',
    'SpeedFreak99','VehicleSandbox','OffRoadKing_YT','DrivingSimFan','CrashKing_LIVE',
    'PhysicsEngine_Fan','SuperCrashBros','BeamNG_Believer','TurboTimeTrial','GravityDefier',
    'NoBrakesNeeded','WildRider_YT','CrashAndSurvive','MaxDamageGamer','PixelCrashTV',
    'RacingFanatic_YT','DesertRacer2024','MountainCrasher','MotorheadKid','Rev_LimiterPro',
    'DriftQueenYT','JoyRider_Gaming','LooseCannonGamer','TopGearFan_YT','CarPhysicsNerd',
    'EnduranceRacer','FuelInjectedFan','SlipstreamRacer','CrashTestBros','BeamNG_Wizard',
    'SuspensionTuner','TireKicker_YT','OversteerObsessed','ManualGearGrinder','FlatOutFan',
    'SimRacingKid','VroomVroomBros','TotalLossInsurance','GarageKingYT','BoostPressure99',
    'NightRacerXX','CanyonDrifter','WheelsOfChaos','ImpactZoneGaming','FlipAndSpin_YT',
    'AutobelloFan','BurnsidesRule','Pigeon_Power','ETK_Everything','MoonhawkMafia',
    'PessimaGang','BolideOrBust','RollbarNotIncluded','AirTimeAddict','ZeroAirbagsYT',
    'FullThrottleKid','HandbrakeHero','EmergencyBrakeWho','SafetyFirstNOT','OneStarCrashTest',
    'NerfedPhysics','FrameRateDropper','UltraGraphicsGamer','HighFPSChaos','LowPingDestroyer',
    'ModdedToTheTeath','MoreCarsMoreFun','RandomSpawnGamer','SandboxKing_YT','ScenarioKiller',
    'CareerModeHater','FreeRoamForever','MissionFailed_Again','CheckpointMissed','FullSendBros',
    'JustOneMoreRun','LastOneIPromise','OkActuallyLastOne','FiveMoreMinutesKid','GoodAtCrashes',
    'BadAtSurviving','SuspensionWhisperer','TireWearObsessed','AlignmentNerd','CamberKing',
    'DifferentialDave','TransmissionTom','CrumpleZoneChris','ABSisOverrated','TractionControlOff',
    'ESCdisabled','RevMatchingPro','HealAndToe_YT','LaunchControlGamer','QuickestLap_YT',
    'Understeer_King','OversteerQueen','MidCornerChaos','LateApexAddict','EarlyBrakeNerd',
];

// ── KNOWN CHARACTERS (friends/family — add real names here) ──────────────────
$characters = [
    // ['username' => 'FriendName', 'weight' => 5],
];

// ── INSERT EVERYTHING ─────────────────────────────────────────────────────────
$msgStmt = $db->prepare("INSERT INTO messages (stream_id, category, text, username, is_mod) VALUES (:sid, :cat, :text, :user, :mod)");

foreach ($openers as [$user, $text, $isMod]) {
    $msgStmt->bindValue(':sid',  $streamId);
    $msgStmt->bindValue(':cat',  'opener');
    $msgStmt->bindValue(':text', $text);
    $msgStmt->bindValue(':user', $user);
    $msgStmt->bindValue(':mod',  $isMod ? 1 : 0);
    $msgStmt->execute();
}

foreach ($generic as $text) {
    $msgStmt->bindValue(':sid',  $streamId);
    $msgStmt->bindValue(':cat',  'generic');
    $msgStmt->bindValue(':text', $text);
    $msgStmt->bindValue(':user', '');
    $msgStmt->bindValue(':mod',  0);
    $msgStmt->execute();
}

foreach ($gameSpecific as $text) {
    $msgStmt->bindValue(':sid',  $streamId);
    $msgStmt->bindValue(':cat',  'aviation');
    $msgStmt->bindValue(':text', $text);
    $msgStmt->bindValue(':user', '');
    $msgStmt->bindValue(':mod',  0);
    $msgStmt->execute();
}

foreach ($superchat as $text) {
    $msgStmt->bindValue(':sid',  $streamId);
    $msgStmt->bindValue(':cat',  'superchat');
    $msgStmt->bindValue(':text', $text);
    $msgStmt->bindValue(':user', '');
    $msgStmt->bindValue(':mod',  0);
    $msgStmt->execute();
}

foreach ($ticker as $text) {
    $msgStmt->bindValue(':sid',  $streamId);
    $msgStmt->bindValue(':cat',  'ticker');
    $msgStmt->bindValue(':text', $text);
    $msgStmt->bindValue(':user', '');
    $msgStmt->bindValue(':mod',  0);
    $msgStmt->execute();
}

$uStmt = $db->prepare("INSERT INTO usernames (stream_id, name) VALUES (:sid, :name)");
foreach ($usernames as $name) {
    $uStmt->bindValue(':sid',  $streamId);
    $uStmt->bindValue(':name', $name);
    $uStmt->execute();
}

$cStmt = $db->prepare("INSERT OR IGNORE INTO characters (username, weight) VALUES (:u, :w)");
foreach ($characters as $c) {
    $cStmt->bindValue(':u', $c['username']);
    $cStmt->bindValue(':w', $c['weight']);
    $cStmt->execute();
}

$db->close();

$total = count($generic) + count($gameSpecific) + count($superchat);
echo "BeamNG stream seeded!\n";
echo "  Openers:  " . count($openers) . "\n";
echo "  Generic:  " . count($generic) . "\n";
echo "  Game:     " . count($gameSpecific) . "\n";
echo "  Superchat:" . count($superchat) . "\n";
echo "  Ticker:   " . count($ticker) . "\n";
echo "  Usernames:" . count($usernames) . "\n";
echo "  Total chat messages: $total\n";
echo "\nDelete this file when done.\n";
