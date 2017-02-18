<?php

// Resource Objects and Helper Functions
// --------------------------------------------------------------------------------

// User data object
class User implements JsonSerializable {
    public $steamid;
    public $song_number;

    public function __construct($steamid, $song_number) {
        $this->steamid = $steamid;
        $this->song_number = $song_number;
    }

    public function jsonSerialize() {
        return [['steamid' => $this->steamid, 'song_number' => $this->song_number]];
    }
}

// --------------------------------------------------------------------------------

// SITE CORE
// ================================================================================

error_reporting(0);
@set_time_limit(3);

// SONGS AND PICUTRES
// ------------------------------------------------
$authors = array(
    1 => 'Yamajet - Recollections',
    2 => 'Blood-C OST - Cafe Guimauve',
    3 => 'You Reposted in the Wrong TTT Server',
    4 => 'Pink Panther Theme Song',
    5 => 'Foster The People - Pumped up Kicks'
);
$pictures = array(1,2,3);
// ------------------------------------------------

$plname  = 'Player';
$map     = '';
$avatar  = 'img/nouser.png';
$r = mt_rand(1,count($authors));
$user_data_file = 'user_data.json';

// display loading pictures
shuffle($pictures);

// display map data
if (isset($_GET['mapname']))
    $map = '<br>You will play the map: '.$_GET['mapname'];

// get steam user data
$current_user = new User($_GET['steamid'] == null ? "invalid_steam_id" : $_GET['steamid'], $r);

if (isset($_GET['steamid'])) {
    // get data from steam id profile
    $data = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=DC435F37A7FABFF8ADFE7AB0EA5700D2&steamids='.$_GET['steamid'];
    $f = file_get_contents($data);
    $arr = json_decode($f, true);

    // set player name and avatar from steam profile
    if (isset($arr['response']['players'][0]['personaname']))
        $plname = $arr['response']['players'][0]['personaname'];
    if (isset($arr['response']['players'][0]['avatar']))
        $avatar = $arr['response']['players'][0]['avatar'];
}

function update_user_data($user_data_file, $current_user, $authors) {
    
    $user_data_json = json_decode(file_get_contents($user_data_file), true);
   
    // update song selection for current user
    $existing_user = false;
    foreach ($user_data_json as &$user) {
        echo var_dump($user);
        if ($user['steamid'] === $current_user->steamid) {
            $user["song_number"] = $user["song_number"] % count($authors) + 1;
            $current_user->song_number = $user["song_number"];
            $existing_user = true;
        }
    }
    echo var_dump($current_user);

    // add new user if user not in database
    if(!$existing_user) {
        echo "adding new user";
        array_push($user_data_json, $current_user);
    }

    // write json file
    $user_data = fopen($user_data_file,"w");
    fwrite($user_data, json_encode($user_data_json));
    fclose($user_data);
}

// load from stored data or create new json entry
if(file_exists($user_data_file)) {
    update_user_data($user_data_file, $current_user, $authors);
} else {
    // create new json file for current user
    $user_data = fopen($user_data_file,"w");
    fwrite($user_data, json_encode($current_user));
    fclose($user_data);
}

// ============================================================================?>
<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Source+Sans+Pro">

    <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
</head>
<body>
    <audio autoplay id="player">
        <source src="music/<?php echo $current_user->song_number?>.ogg" type="audio/ogg"></source>
    </audio>
    <script>
    var audio = document.getElementById("player");
    audio.addEventListener("ended", function() {
        audio.src = "music/1.ogg";
        audio.play();
    });
    </script>
    <div class="container">
        <div class="jumbotron" style="margin-top: 50px;">
            <div class="pull-right cycle-slideshow" data-cycle-fx="none">
                <?php foreach ($pictures as $pic) {
                    echo '<img src="img/'.$pic.'.jpg" alt="Picture '.$pic.'" class="imgtop img-rounded">';
                }?>
            </div>
            <h1 id="title" class="bigEntrance" style="font-size: 50px;">Turnipcraft</h1>
	    <p class="lead">
                Welcome to our TTT-Server. Have fun!<br>
                <small>
                    <ul style="line-height: 1.6;">
                        <li>Be friendly.</li>
                        <li>No Random Killing; However, Traitors Can Gift Innocents Weapons on Community Pool Revamped to Join Their Force.</li>
                        <li>No Ghosting!</li>
                        <li>Only English (Taylor) or American Allowed.</li>
                        <li> Server Management Team: techdude154, R. </li>
                    </ul>
                    All used Workshop items can be found here:
                    <br>
                    <code><a href="http://steamcommunity.com/sharedfiles/filedetails/?id=469332812">
		    	     http://steamcommunity.com/sharedfiles/filedetails/?id=469332812</a>
		    </code><br>→ TTT-Servercontent DL (Link)
                </small>
            </p>

        </div>
    </div>
    <div style="position: absolute;bottom: 0px;left: 20px;font-size: 12px;min-width: 260px;" class="well well-sm">
        <img src="<?php echo $avatar?>" alt="" class="pull-right img-circle">
        Hello, <b><?php echo $plname ?></b><?php echo $map ?><br>
        Music: "<?php echo $authors[$current_user->song_number];?>"
    </div>
    <script src="js/vendor/jquery-1.10.1.min.js"></script>
    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/jquery.cycle2.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
