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
	
	$count = 0;
	$sql = "SELECT COUNT(*) FROM `voteskip` WHERE roomid='$roomid'";
	if ($result=mysqli_query($connection,$sql)){
        while ($row=mysqli_fetch_row($result)){
			$count = $row[0];
		}
	}
	
	$active = 0;
	$sql = "SELECT COUNT(*) FROM `room-activeusers` WHERE roomid='$roomid'";
	if ($result=mysqli_query($connection,$sql)){
        while ($row=mysqli_fetch_row($result)){
			$active = $row[0];
		}
	}
	
	array_push($out,$count,$active);
	
    $connection->close();
	echo json_encode($out);
    ?>