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
	
	$roomid = $_COOKIE["hostID"];
	
	$currentposition = 0;
	$sql = "SELECT currentvideoid FROM `room-position` WHERE roomid='$roomid'";
	if ($result=mysqli_query($connection,$sql)){
        while ($row=mysqli_fetch_row($result)){
			$currentposition = $row[0];
		}
	}
	$sql="SELECT url,id,userid FROM `room-user` WHERE roomid='$roomid' ORDER BY id ASC";
    if ($result=mysqli_query($connection,$sql)){
        while ($row=mysqli_fetch_row($result)){
			if ($row[1] > $currentposition){
				$videourlComingUp = "null";
				$videotitleComingUp = "null";
				$userid = $row[2];
				$username = "anonymous";
				$sql2="SELECT name FROM `users` WHERE id='$userid'";
				if ($result2=mysqli_query($connection,$sql2)){
					while ($row2=mysqli_fetch_row($result2)){
						$username = $row2[0];
					}
				}
				$videourlComingUp = $row[0];
				$videotitleComingUp = getTitle($videourlComingUp);
				if(strlen($videotitleComingUp) > 55){
					$videotitleComingUp = substr($videotitleComingUp,0,55) . "...";
				}
				
				array_push($out,$videotitleComingUp,$videourlComingUp,$username);
			}
		}
	}
    $connection->close();
	echo json_encode($out);
    ?>