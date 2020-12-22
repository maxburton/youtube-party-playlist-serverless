<?php include "/var/www/inc/dbinfo.inc"; ?>
<html>
    <head>
	
    <?php
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);
    
    function RoomExists($id, $connection, $dbName) {
        $tableName = "rooms";
        $t = mysqli_real_escape_string($connection, $tableName);
        $d = mysqli_real_escape_string($connection, $dbName);

        $checktable = mysqli_query($connection, 
            "SELECT id FROM rooms WHERE id=" . $id);

        if(mysqli_num_rows($checktable) > 0) return true;

        return false;
    }
    
    function checkRoom($id){
        if(!RoomExists($id, $connection, $dbName)) {
            return false;
        }else{
            return true;
        }
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
    
    $roomExists = true;
    $id = rand(1,99999);
    while($roomExists){
        $id = rand(1,99999);
        $roomExists = checkRoom($id);
    }
    
    $sql = "INSERT INTO rooms (id, date)
    VALUES (" . $id . ", NOW())";
 
    if(!$captchafail && $default){
        if ($connection->query($sql) === TRUE) {
            //"Table created successfully";
            $cookie_name = "hostID";
            $cookie_value = $id;
            setcookie($cookie_name, $cookie_value, time() + (86400), "/"); // 86400 = 1 day
            $clientip = $_SERVER['REMOTE_ADDR'];
                $sql = "INSERT INTO `blacklist` (ip)
                    VALUES ('$clientip')";
                    if ($connection->query($sql) === TRUE) {
                        //"Table created successfully";
                    }
            } else {
        echo "Error creating table: " . $connection->error;
        }
        echo '<meta http-equiv="refresh" content="0; url=./host.php">';
    }else if($captchafail){
        echo '<meta http-equiv="refresh" content="0; url=./index.php?newroom=captcha">';
    }else if(!$default){
        echo '<meta http-equiv="refresh" content="0; url=./index.php?newroom=miss">';
    }
    $connection->close();
    ?>
    </head>
</html>