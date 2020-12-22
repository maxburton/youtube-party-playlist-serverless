<?php include "/var/www/inc/dbinfo.inc"; ?>
    <?php
	
	function getTitle($url){
		$json = file_get_contents('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $url . '&format=json'); //get JSON video details
		$details = json_decode($json, true); //parse the JSON into an array
		return $details['title']; //return the video title
	}
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    $database = mysqli_select_db($connection, DB_DATABASE);

	$out = array();
	
	$roomid = $_GET["room"];
	$userid = $_COOKIE["userID"];
	
	$position = 0;
	$vidposition = 0;
	$sql = "SELECT currentvideoid FROM `room-position` WHERE roomid='$roomid'";
	if ($result=mysqli_query($connection,$sql)){
        while ($row=mysqli_fetch_row($result)){
			$position = $row[0];
		}
	}
	$sql="SELECT url,id,userid FROM `room-user` WHERE roomid='$roomid' ORDER BY id ASC";
	if ($result=mysqli_query($connection,$sql)){
		while ($row=mysqli_fetch_row($result)){
			if ($row[1] > $position){
				$videourl = $row[0];
				$videotitle = getTitle($videourl);
				if(strlen($videotitle) > 75){
					$videotitle = substr($videotitle,0,75) . "...";
				}
				if ($row[2] == $userid){
					$vidposition = $vidposition + 1;
					array_push($out,$videotitle,$vidposition);
				}else{
					$vidposition = $vidposition + 1;
					}
				}
			}
		}
	mysqli_free_result($result);
    $connection->close();
	echo json_encode($out);
    ?>