<?php include "/var/www/inc/dbinfo.inc"; ?>
<html>
    <head>
	<?php include("./head.html");?>
    <?php
	
	function getTitle($url){
	$json = file_get_contents('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $url . '&format=json'); //get JSON video details
	$details = json_decode($json, true); //parse the JSON into an array
	return $details['title']; //return the video title
	}
	
    $roomid = "0";
    if(isset($_COOKIE["hostID"])) {
        $roomid = $_COOKIE["hostID"];
    }
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);
	
	$sql = "DELETE FROM `voteskip` WHERE roomid='$roomid'";
    if ($connection->query($sql) === TRUE) {
		//"Table created successfully";
	}
    ?>
    
    <title><?php if(isset($_COOKIE["hostID"])) {
        echo "Room " . $_COOKIE["hostID"] . " - " . $_COOKIE["username"] . "'s Room";} ?>
    </title>
    </head>
    
    <body>
        <?php 
		session_start();
		if(isset($_COOKIE["hostID"])) {
        echo "<h1> Room " . $_COOKIE["hostID"] . "</h1>";}
		$videoid = 0;
		$videourl = "null";
        $position = 0;
		if(isset($_COOKIE["videoID"])) {
			$videoid = $_COOKIE["videoID"];
		}
        $sql="SELECT url,id FROM `room-user` WHERE roomid='$roomid' ORDER BY id ASC";
		$found = false;
        if ($result=mysqli_query($connection,$sql)){
            while ($row=mysqli_fetch_row($result)){
				if ($row[1] > $videoid && $found == false){
					setcookie("videoID", $row[1], time() + (86400 * 30), "/"); // 86400 = 1 day
					$videourl = $row[0];
                    $position = $row[1];
					$found = true;
				}
            }
        mysqli_free_result($result);
        }
		$defaultorder = array();
		$totaldefault = 0;
		$indexarray = array();
		if(!$found){
			if(!$_SESSION["defaultorder"]){
				$sql="SELECT COUNT(*) FROM `defaultplaylist`";
				if ($result=mysqli_query($connection,$sql)){
					while ($row=mysqli_fetch_row($result)){
						$totaldefault = $row[0];
					}
				}
				for($i = 0; $i<$totaldefault; $i++){
					array_push($indexarray, $i);
				}				
				$sql="SELECT id,videoid FROM `defaultplaylist`";
				if ($result=mysqli_query($connection,$sql)){
					while ($row=mysqli_fetch_row($result)){
						$pick = rand(0,sizeof($indexarray)-1);
						$entry = array($indexarray[$pick],$row[1]);
						array_splice($indexarray, $pick, 1);
						array_push($defaultorder,$entry);
					}
					sort($defaultorder);
					$_SESSION["defaultorder"] = json_encode($defaultorder);
					$defaultvideos = json_decode($_SESSION["defaultorder"]);
					$videourl = $defaultvideos[0][1];
					array_splice($defaultvideos, 0, 1);
					$_SESSION["defaultorder"] = json_encode($defaultvideos);
				}
			}else{
				$defaultvideos = json_decode($_SESSION["defaultorder"]);
				$videourl = $defaultvideos[0][1];
				array_splice($defaultvideos, 0, 1);
				if(sizeof($defaultvideos) <= 0){
					unset($_SESSION['defaultorder']);
				}else{
					$_SESSION["defaultorder"] = json_encode($defaultvideos);
				}
			}
		}
        ?>
	<h3>
	<?php 
	$defaulton = false;
	$videotitle = getTitle($videourl);
	if(strlen($videotitle) > 75){
		$videotitleComingUp = substr($videotitleComingUp,0,75) . "...";
	}
	if ($found == false || $videourl == "null"){
		echo "Now playing default playlist. Add some songs to resume.";
		$defaulton = true;
	}else{
		$defaulton = false;
		echo $videotitle;
	} ?>
	</h3>
	<table class="hosttable"><tr><td width="100%">
	</td>
	<td class="comingup" rowspan="10">
	<table>
	<tr><td valign="top"><h3>Coming Up:</h3>
	<?php 
	$sql="SELECT url,id,userid FROM `room-user` WHERE roomid='$roomid' ORDER BY id ASC";
	$videourlComingUp = "null";
	$foundComingUp = 0;
	$foundFirst = false;
	if(isset($_COOKIE["videoID"])) {
		$videoid = $_COOKIE["videoID"];
	}
    if ($result=mysqli_query($connection,$sql)){
        while ($row=mysqli_fetch_row($result)){
			if ($row[1] > $videoid){
				if ($foundComingUp >= 3){
					$foundComingUp = $foundComingUp + 1;
				} else{
				if ($foundFirst == false){
					$foundFirst = true;
				} else{
				$userid = $row[2];
				$username = "anonymous";
				$sql2="SELECT name FROM `users` WHERE id='$userid'";
				if ($result2=mysqli_query($connection,$sql2)){
					while ($row2=mysqli_fetch_row($result2)){
						$username = $row2[0];
					}
				}
				$videourlComingUp = $row[0];
				$foundComingUp = $foundComingUp + 1;
				$videotitleComingUp = getTitle($videourlComingUp);
				if(strlen($videotitleComingUp) > 55){
					$videotitleComingUp = substr($videotitleComingUp,0,55) . "...";
				}
				echo '
					<strong><p id="title' . $foundComingUp . '">' . $videotitleComingUp . '</p></strong>
					<img id="thumb' . $foundComingUp . '" src="https://img.youtube.com/vi/' . $videourlComingUp . '/mqdefault.jpg">
					</td>
					</tr>
					<tr>
					<td>
					
					<p id="submitted' . $foundComingUp . '">submitted by ' . $username . '</p>';
				}
				}
			}
        //printf ("%s \n",$row[0]);
        }
    mysqli_free_result($result);
	mysqli_free_result($result2);
    }
	if($foundComingUp > 3){
		echo '<strong><p id="morevideos">+' . ($foundComingUp - 3) . ' more videos</p></strong>';
	} else if($foundComingUp <= 0){
		echo '
		<strong><p id="title1">Default Playlist [You\'ve run out of songs!]</p></strong>
		<img id="thumb1" src="./default.png">
		</td>
		</tr>
		<tr>
		<td>
		<p id="submitted1"></p>
		<strong><p id="title2"></p></strong>
		<img id="thumb2">
		</td>
		</tr>
		<tr>
		<td>
		<p id="submitted2"></p>
		<strong><p id="title3"></p></strong>
		<img id="thumb3">
		</td>
		</tr>
		<tr>
		<td>
		<p id="submitted3"></p>
		<strong><p id="morevideos"></p></strong>';
	}else if($foundComingUp == 1){
		echo '
		<strong><p id="title2"></p></strong>
		<img id="thumb2">
		</td>
		</tr>
		<tr>
		<td>
		<p id="submitted2"></p>
		<strong><p id="title3"></p></strong>
		<img id="thumb3">
		</td>
		</tr>
		<tr>
		<td>
		<p id="submitted3"></p>
		<strong><p id="morevideos"></p></strong>';
	}else if($foundComingUp == 2){
		echo '
		<strong><p id="title3"></p></strong>
		<img id="thumb3">
		</td>
		</tr>
		<tr>
		<td>
		<p id="submitted3"></p>
		<strong><p id="morevideos"></p></strong>';
	}
	?>
	</td></tr></table>
	<script>
	function update_comingup(data){
		data = JSON.parse(data);
		
		var numvideos = data.length / 3;
		if(numvideos > 0 && numvideos <= 3){
			for (var i = 0; i < numvideos; i++) { 
				document.getElementById("title" + (i+1)).innerHTML = data[3*i];
				document.getElementById("thumb" + (i+1)).setAttribute("src", "https://img.youtube.com/vi/" + data[(3*i)+1] + "/mqdefault.jpg"); 
				document.getElementById("submitted" + (i+1)).innerHTML = "submitted by " + data[(3*i)+2];
			}
		}
		if(numvideos > 3){
			document.getElementById("morevideos").innerHTML = "+" + (numvideos - 3) + " more videos";
		}
	}
	var ajax_call_comingup = function(){
		$.ajax({
			type: "POST",
			url: "/playlist/comingup.php",
			datatype:"json",
			success: function(data){
				update_comingup(data);
			}
		});
	}
	var interval_comingup = 1000; //1 second
	setInterval(ajax_call_comingup, interval_comingup);
	
	function update_voteskip(data){
		data = JSON.parse(data);
		
		var voteskips = data[0];
		var users = data[1];
		var skips_needed = Math.floor(users/2) + 1;
		
		if(voteskips >= skips_needed){
			location.reload();
		}
		
		if(voteskips > 0){
			document.getElementById("voteskip_counter").innerHTML = voteskips + " user(s) want to skip this video (" + (skips_needed - voteskips) + " more needed)";
		}
	}
	
	var ajax_call_voteskip = function(){
		$.ajax({
			type: "POST",
			url: "/playlist/getvoteskip.php",
			datatype:"json",
			success: function(data){
				update_voteskip(data);
			}
		});
	}
	interval_voteskip = 2000; //2 seconds
	setInterval(ajax_call_voteskip, interval_voteskip);
	</script>
	</td>
	</tr>
	<tr><td valign="top">
	<table width="100%">
	<tr><td>
	<div id="player"></div>
    <script src="http://www.youtube.com/player_api"></script>
    <script>
	<?php
	
	
	if (!$defaulton){
		$sql = "INSERT INTO `room-position` (roomid, currentvideoid)
		VALUES ('$roomid','$position') ON DUPLICATE KEY UPDATE currentvideoid='$position'";
		
		if ($connection->query($sql) === TRUE) {
			//"Table created successfully";
			} else {
			echo "Error creating table: " . $connection->error;
		}
		$connection->close();
	}
	
	echo "
        //create youtube player
        var player;
        function onYouTubePlayerAPIReady() {
            player = new YT.Player('player', {
              width: '804',
              height: '490',
              videoId: '" . $videourl . "',
              events: {
                onReady: onPlayerReady,
                onStateChange: onPlayerStateChange
              }
            });
        }

        // autoplay video
        function onPlayerReady(event) {
            event.target.playVideo();
        }

        // when video ends
        function onPlayerStateChange(event) {        
            if(event.data === 0) {          
                location.reload();
            }
        }
		";
	?>
    </script>
	</td>
	<td width="100%">
		<table class="hostButtons"><tr><td>
			<?php if ($found == false || $videourl == "null"){
				echo '
				<button id="homeURL" class="submit-button" >Home</button>
				</td></tr><tr><td>
				<button id="skipbutton" class="submit-button" >Skip</button>
				</td></tr><tr><td>
				<button id="restart" class="submit-button">From The Top</button>
				</td>';
			}else{
				echo '
				<button id="homeURL" class="submit-button" >Home</button>
				</td></tr><tr><td>
				<button id="skipbutton" class="submit-button" >Skip</button>
				</td></tr><tr><td>
				<strong><p class="red" id="voteskip_counter"></p></strong>
				</td>';
			}?>
		</tr></table>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	<script type="text/javascript">
        function setCookie(cname, cvalue) {
            var d = new Date();
            d.setTime(d.getTime() + (24*60*60*1000)); //1 day
            var expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }
    
		document.getElementById("homeURL").onclick = function () {
			location.href = "./";
		}
		document.getElementById("skipbutton").onclick = function () {
			location.reload();
		}
        <?php 
        if ($found == false || $videourl == "null"){
        echo '
            document.getElementById("restart").onclick = function () {
                setCookie("videoID",0);
                location.reload();
        }';}
        ?>
    </script>
	</td></tr>
	</table>
    </body>
</html>