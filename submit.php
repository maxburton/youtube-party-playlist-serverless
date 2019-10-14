<?php include "/var/www/inc/dbinfo.inc"; ?>
<html>
    <head>
	
    <?php
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);
    
    $rawurl = $_GET['url'];
    $url = urldecode($rawurl);
    $roomid = $_GET['room'];
    $name = "";
    $userid = "";
    if( $_COOKIE["userID"]){
        $userid = $_COOKIE["userID"];
        $sql = "SELECT id, name FROM users WHERE id='$userid'";
        $result = $connection->query($sql);
        if ($result->num_rows > 0) {
        // output data of each row
        while($row = $result->fetch_assoc()) {
            $name = $row["name"];
        }
    }
    }
	
	//Check Duplicate
	$duplicate = false;
	$currentposition = 0;
	$sql = "SELECT currentvideoid FROM `room-position` WHERE roomid='$roomid'";
	if ($result=mysqli_query($connection,$sql)){
        while ($row=mysqli_fetch_row($result)){
			$currentposition = $row[0];
		}
	}
	$sql = "SELECT url, id FROM `room-user` WHERE roomid='$roomid'";
	if ($result=mysqli_query($connection,$sql)){
        while ($row2=mysqli_fetch_row($result)){
			if ($url == $row2[0] && $row2[1] >= $currentposition){
				$duplicate = true;
			}
		}
	}
    
    function getTitle($url){
        $json = file_get_contents('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $url . '&format=json'); //get JSON video details
        $details = json_decode($json, true); //parse the JSON into an array
        return $details['title']; //return the video title
	}
    
    $sql = "INSERT INTO `room-user` (roomid, userid, url)
    VALUES ('$roomid','$userid', '$url')";
    if(strlen($url) < 12){
		if($duplicate){
			echo "<meta http-equiv='refresh' content='0; url=./guest.php?room=" . $roomid . "&submitted=duplicate'>";
		}
        else if ($connection->query($sql) === TRUE) {
            //"Table created successfully";
            echo "<meta http-equiv='refresh' content='0; url=./guest.php?room=" . $roomid . "&submitted=true'>";
            } else {
        echo "Error creating table: " . $connection->error;
        echo "<meta http-equiv='refresh' content='0; url=./guest.php?room=" . $roomid . "&submitted=error'>";
        }
    }else{
        echo "<meta http-equiv='refresh' content='0; url=./guest.php?room=" . $roomid . "&submitted=false'>";
    }
    $connection->close();
    ?>

    </head>
</html>