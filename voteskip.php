<?php include "/var/www/inc/dbinfo.inc"; ?>
<html>
    <head>
	
    <?php
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);
    
    $userid = 0;
    if(isset($_COOKIE["userID"])) {
        $userid = $_COOKIE["userID"];
    }
	$roomid = 0;
	if($_GET["room"]){
        $roomid = $_GET["room"];
    }
    
    $sql = "INSERT INTO `voteskip` (roomid, userid)
    VALUES ('$roomid','$userid')";
    if ($connection->query($sql) === TRUE) {
		//"Table created successfully";
		echo '<meta http-equiv="refresh" content="0; url=./guest.php?room=' . $roomid . '&voteskip=true">';
    }else{
        echo '<meta http-equiv="refresh" content="0; url=./guest.php?room=' . $roomid . '">';
    }
    $connection->close();
    ?>
    </head>
</html>