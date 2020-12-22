<?php include "/var/www/inc/dbinfo.inc"; ?>
<html>
    <head>
	
    <?php
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);
    
    $sql = "DELETE FROM `blacklist`";
        if ($connection->query($sql) === TRUE) {
            //"Table created successfully";
        } else {
        echo "Error deleting blacklist: " . $connection->error;
		}
    ?>

    </head>
</html>