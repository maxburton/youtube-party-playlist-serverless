<?php include "/var/www/inc/dbinfo.inc"; ?>
<html>
    <head>
	
    <?php
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);
    
   
    
    $name = "";
    if(!empty($_POST["name"])){
        $name = $_POST["name"];
    }
    
    $default = false;
    if(!empty($_POST["def"])){
        $default = true;
    }
    
    $code = 0;
    if(!empty($_POST["captchacode"])){
        $code = $_POST["captchacode"];
    }
    
    $captchafail = false;
    if($code > 0){
        if($code != $_POST["captcha"]){
            $captchafail = true;
        }
    }
    
    $falseName = true;
    preg_match("/[^A-Za-z0-9-_]/i", $name, $falseArray);
    if(!$falseArray){
        $falseName = false;
    }
    
    $sql = "INSERT INTO users (name, date)
    VALUES ('$name', NOW())";
    if(!$falseName && !$captchafail && $default){
        if ($connection->query($sql) === TRUE) {
            //"Table created successfully";
            $last_id = $connection->insert_id;
            $cookie_name = "userID";
            $cookie_value = $last_id;
            setcookie($cookie_name, $cookie_value, time() + (86400), "/"); // 86400 = 1 day
            setcookie("username", $name, time() + (86400), "/"); // 86400 = 1 day
            
            $clientip = $_SERVER['REMOTE_ADDR'];
            $sql = "INSERT INTO `blacklist` (ip)
                VALUES ('$clientip')";
                if ($connection->query($sql) === TRUE) {
                    //"Table created successfully";
                }
            echo '<meta http-equiv="refresh" content="0; url=./index.php?joined=true">';
            } else {
        echo "Error creating table: " . $connection->error;
        }
    }else if($falsename){
        echo '<meta http-equiv="refresh" content="0; url=./index.php?joined=false">';
    }else if($captchafail){
        echo '<meta http-equiv="refresh" content="0; url=./index.php?joined=captcha">';
    }else if(!$default){
        echo '<meta http-equiv="refresh" content="0; url=./index.php?joined=miss">';
    }else{
		echo '<meta http-equiv="refresh" content="0; url=./index.php?joined=false">';
	}
    $connection->close();
    ?>
    </head>
</html>