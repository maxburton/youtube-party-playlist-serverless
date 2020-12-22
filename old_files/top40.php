
	

<html>
    <head>
	
<?php include "/var/www/inc/dbinfo.inc"; ?>

    <?php
    
    /* Connect to MySQL and select the database. */
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

    $database = mysqli_select_db($connection, DB_DATABASE);
	
	$sql = "DELETE FROM `top40`";
	if ($connection->query($sql) === TRUE) {
            //"Table created successfully";
        } else {
        echo "Top40 Not Deleted: " . $connection->error;
		}
    

    
    ?>

    </head>
	
	<body>
	
	<?php

require_once 'Google/autoload.php';
require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';

$client = new Google_Client();
$client->setDeveloperKey('AIzaSyCG_tMCePfKeih85nhUXkNhB6sP0svfhsw');
$youtube = new Google_Service_YouTube($client);

$nextPageToken = '';
$htmlBody = '<ul>';

echo 'IN';

do {
	echo 'IN2';
    $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
    'playlistId' => 'PL2vrmw2gup2Jre1MK2FL72rQkzbQzFnFM',
    'maxResults' => 40,
    'pageToken' => $nextPageToken));

    foreach ($playlistItemsResponse['items'] as $playlistItem) {
        $htmlBody .= sprintf('<li>%s (%s)</li>', $playlistItem['snippet']['title'], $playlistItem['snippet']['resourceId']['videoId']);
		$id = $playlistItem['videoId'];
		$sql = "INSERT INTO `top40` (videoid) VALUES ('$id')";
		if ($connection->query($sql) === TRUE) {
				//"Table created successfully";
			} else {
			echo "Top40 Not Updated: " . $connection->error;
			}
		}

    $nextPageToken = $playlistItemsResponse['nextPageToken'];
} while ($nextPageToken <> '');

$htmlBody .= '</ul>';
$connection->close();
?>
  </body>
</html>