<?php include "/var/www/inc/dbinfo.inc"; ?>
<?php include("./head.html"); ?>
<?php

function getTitle($url)
{
	$json = file_get_contents('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $url . '&format=json'); //get JSON video details
	$details = json_decode($json, true); //parse the JSON into an array
	return $details['title']; //return the video title
}

$roomid = "0";
if (isset($_COOKIE["hostID"])) {
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

<title><?php if (isset($_COOKIE["hostID"])) {
			echo "Room " . $_COOKIE["hostID"] . " - " . $_COOKIE["username"] . "'s Room";
		} ?>
</title>
</head>

<body>
	<div class="container-fluid content">
		<div class="row">
			<div class="col">
				<?php
				session_start();
				if (isset($_COOKIE["hostID"])) {
					echo "<h1> Room " . $_COOKIE["hostID"] . "</h1>";
				}
				$videoid = 0;
				$videourl = "null";
				$position = 0;
				if (isset($_COOKIE["videoID"])) {
					$videoid = $_COOKIE["videoID"];
				}
				$sql = "SELECT url,id FROM `room-user` WHERE roomid='$roomid' ORDER BY id ASC";
				$found = false;
				if ($result = mysqli_query($connection, $sql)) {
					while ($row = mysqli_fetch_row($result)) {
						if ($row[1] > $videoid && $found == false) {
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
				if (!$found) {
					if (!$_SESSION["defaultorder"]) {
						$sql = "SELECT COUNT(*) FROM `defaultplaylist`";
						if ($result = mysqli_query($connection, $sql)) {
							while ($row = mysqli_fetch_row($result)) {
								$totaldefault = $row[0];
							}
						}
						for ($i = 0; $i < $totaldefault; $i++) {
							array_push($indexarray, $i);
						}
						$sql = "SELECT id,videoid FROM `defaultplaylist`";
						if ($result = mysqli_query($connection, $sql)) {
							while ($row = mysqli_fetch_row($result)) {
								$pick = rand(0, sizeof($indexarray) - 1);
								$entry = array($indexarray[$pick], $row[1]);
								array_splice($indexarray, $pick, 1);
								array_push($defaultorder, $entry);
							}
							sort($defaultorder);
							$_SESSION["defaultorder"] = json_encode($defaultorder);
							$defaultvideos = json_decode($_SESSION["defaultorder"]);
							$videourl = $defaultvideos[0][1];
							array_splice($defaultvideos, 0, 1);
							$_SESSION["defaultorder"] = json_encode($defaultvideos);
						}
					} else {
						$defaultvideos = json_decode($_SESSION["defaultorder"]);
						$videourl = $defaultvideos[0][1];
						array_splice($defaultvideos, 0, 1);
						if (sizeof($defaultvideos) <= 0) {
							unset($_SESSION['defaultorder']);
						} else {
							$_SESSION["defaultorder"] = json_encode($defaultvideos);
						}
					}
				}
				?>
			</div>
		</div>


		<div class="row content">
			<div class="col">
				<h3>
					<?php
					$defaulton = false;
					$videotitle = getTitle($videourl);
					if (strlen($videotitle) > 75) {
						$videotitleComingUp = substr($videotitleComingUp, 0, 75) . "...";
					}
					if ($found == false || $videourl == "null") {
						echo "Now playing default playlist. Add some songs to resume.";
						$defaulton = true;
					} else {
						$defaulton = false;
						echo $videotitle;
					} ?>
				</h3>
			</div>
		</div>
		<div class="row">
			<div class="col-8 video-and-buttons">
				<div class="row">
					<div class="col video">
						<div class="row">
							<div class="col">
								<div class="iframe-container">
									<div id="player">
										<script src="http://www.youtube.com/player_api"></script>
										<script>
											<?php


											if (!$defaulton) {
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
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row hostButtons">
					<div class="col">
						<?php if ($found == false || $videourl == "null") {
							echo '
				<button id="homeURL" class="submit-button" >Home</button>
				</div><div class="col">
				<button id="skipbutton" class="submit-button" >Skip</button>
				</div><div class="col">
				<button id="restart" class="submit-button">From The Top</button>';
						} else {
							echo '
				<button id="homeURL" class="submit-button" >Home</button>
				</div><div class="col">
				<button id="skipbutton" class="submit-button" >Skip</button>
				</div><div class="col">
				<strong><p class="red" id="voteskip_counter"></p></strong>';
						} ?>
					</div>
				</div>
			</div>
			<div class="col-1">
				<div class="row">
				</div>
			</div>
			<div class="col-3 comingup">
				<div class="row">
					<div class="col" valign="top">
						<h3>Coming Up:</h3>
						<?php
						$sql = "SELECT url,id,userid FROM `room-user` WHERE roomid='$roomid' ORDER BY id ASC";
						$videourlComingUp = "null";
						$foundComingUp = 0;
						$foundFirst = false;
						if (isset($_COOKIE["videoID"])) {
							$videoid = $_COOKIE["videoID"];
						}
						if ($result = mysqli_query($connection, $sql)) {
							while ($row = mysqli_fetch_row($result)) {
								if ($row[1] > $videoid) {
									if ($foundComingUp >= 3) {
										$foundComingUp = $foundComingUp + 1;
									} else {
										if ($foundFirst == false) {
											$foundFirst = true;
										} else {
											$userid = $row[2];
											$username = "anonymous";
											$sql2 = "SELECT name FROM `users` WHERE id='$userid'";
											if ($result2 = mysqli_query($connection, $sql2)) {
												while ($row2 = mysqli_fetch_row($result2)) {
													$username = $row2[0];
												}
											}
											$videourlComingUp = $row[0];
											$foundComingUp = $foundComingUp + 1;
											$videotitleComingUp = getTitle($videourlComingUp);
											if (strlen($videotitleComingUp) > 55) {
												$videotitleComingUp = substr($videotitleComingUp, 0, 55) . "...";
											}
											echo '
					<strong><p id="title' . $foundComingUp . '">' . $videotitleComingUp . '</p></strong>
					<img class="thumbnail" id="thumb' . $foundComingUp . '" src="https://img.youtube.com/vi/' . $videourlComingUp . '/mqdefault.jpg">
					</div>
					</div>
					<div class="row">
					<div class="col">
					
					<p id="submitted' . $foundComingUp . '">submitted by ' . $username . '</p>';
										}
									}
								}
								//printf ("%s \n",$row[0]);
							}
							mysqli_free_result($result);
							mysqli_free_result($result2);
						}
							echo '
		<strong><p id="title1"></p></strong>
		<img class="thumbnail" id="thumb1">
		</div>
		</div>
		<div class="row">
		<div class="col">
		<p id="submitted1"></p>
		<strong><p id="title2"></p></strong>
		<img class="thumbnail" id="thumb2">
		</div>
		</div>
		<div class="row">
		<div class="col">
		<p id="submitted2"></p>
		<strong><p id="title3"></p></strong>
		<img class="thumbnail" id="thumb3">
		</div>
		</div>
		<div class="row">
		<div class="col">
		<p id="submitted3"></p>
		<strong><p id="morevideos"></p></strong>';
						?>
					</div>
				</div>
				<script>
					function update_comingup(data) {
						data = JSON.parse(data);

						var numvideos = data.length / 3;
						if (numvideos > 0) {
							for (var i = 0; i < numvideos; i++) {
								if (i < 3) {
									document.getElementById("title" + (i + 1)).innerHTML = data[3 * i];
									document.getElementById("thumb" + (i + 1)).setAttribute("src", "https://img.youtube.com/vi/" + data[(3 * i) + 1] + "/mqdefault.jpg");
									document.getElementById("submitted" + (i + 1)).innerHTML = "submitted by " + data[(3 * i) + 2];
								}
							}
						} else {
							document.getElementById("title1").innerHTML = "Default Playlist (You\'ve run out of songs!)";
							document.getElementById("thumb1").setAttribute("src", "./default.png");
						}
						if (numvideos > 3) {
							document.getElementById("morevideos").innerHTML = "+" + (numvideos - 3) + " more videos";
						}
					}
					var ajax_call_comingup = function() {
						$.ajax({
							type: "POST",
							url: "/playlist/comingup.php",
							datatype: "json",
							success: function(data) {
								update_comingup(data);
							}
						});
					}
					var interval_comingup = 1000; //1 second
					setInterval(ajax_call_comingup, interval_comingup);

					function update_voteskip(data) {
						data = JSON.parse(data);

						var voteskips = data[0];
						var users = data[1];
						var skips_needed = Math.floor(users / 2) + 1;

						if (voteskips >= skips_needed) {
							location.reload();
						}

						if (voteskips > 0) {
							document.getElementById("voteskip_counter").innerHTML = voteskips + " user(s) want to skip this video (" + (skips_needed - voteskips) + " more needed)";
						}
					}

					var ajax_call_voteskip = function() {
						$.ajax({
							type: "POST",
							url: "/playlist/getvoteskip.php",
							datatype: "json",
							success: function(data) {
								update_voteskip(data);
							}
						});
					}
					interval_voteskip = 2000; //2 seconds
					setInterval(ajax_call_voteskip, interval_voteskip);
				</script>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		function setCookie(cname, cvalue) {
			var d = new Date();
			d.setTime(d.getTime() + (24 * 60 * 60 * 1000)); //1 day
			var expires = "expires=" + d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}

		document.getElementById("homeURL").onclick = function() {
			location.href = "./";
		}
		document.getElementById("skipbutton").onclick = function() {
			location.reload();
		}
		<?php
		if ($found == false || $videourl == "null") {
			echo '
            document.getElementById("restart").onclick = function () {
                setCookie("videoID",0);
                location.reload();
        }';
		}
		?>
	</script>
</body>

</html>