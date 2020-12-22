<?php include "/var/www/inc/dbinfo.inc"; ?>
<?php include("./head.html"); ?>

<!--<script src="jquery-3.3.1.min.js"></script>-->
<?php
$id = "0";
if (isset($_COOKIE["userID"])) {
    $id = $_COOKIE["userID"];
}
function getTitle($url)
{
    $json = file_get_contents('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $url . '&format=json'); //get JSON video details
    $details = json_decode($json, true); //parse the JSON into an array
    return $details['title']; //return the video title
}

/* Connect to MySQL and select the database. */
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

$database = mysqli_select_db($connection, DB_DATABASE);
?>

<?php
$roomid = $_GET["room"];
$sql = "SELECT id FROM rooms WHERE id='$roomid'";
$badid = false;
preg_match("/[^0-9]/i", $roomid, $falseArray);
if ($falseArray) {
    $badid = true;
}
if ($result = mysqli_query($connection, $sql)) {
    if (mysqli_num_rows($result) <= 0) {
        $badid = true;
    }
}
if ($badid) {
    echo "<meta http-equiv='refresh' content='0; url=./index.php?badid=true'>";
}

if (!$badid) {
    $cookie_name = "lastRoomID";
    $cookie_value = $roomid;
    setcookie($cookie_name, $cookie_value, time() + (86400), "/"); // 86400 = 1 day

    $sql = "INSERT INTO `room-activeusers` (roomid, userid)
		VALUES ('$roomid','$id')";
    if ($connection->query($sql) === TRUE) {
        //"Table created successfully";
    }
}

?>

<title><?php if ($_GET["room"]) {
            echo "Room " . $_GET["room"];
        } ?>
</title>
</head>

<body>
    <div class="content">
        <?php if ($_GET["room"]) {
            echo "<h1>Room " . $_GET["room"] . "</h1>";
        } ?>
        <table class="guesttable">
            <tr>
                <td colspan="3">
                    <h2>Search for a YouTube video: </h2>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <input type="text" id="urlBox" value="" width="100%">
                </td>
            </tr>
            <tr>
                <td>
                    <?php
                    if ($_GET["submitted"] == "true") {
                        echo "<h3 style='color:red'>Video submitted.</h3>";
                    } else if ($_GET["submitted"] == "false") {
                        echo "<h3 style='color:red'>False URL attempt. Pls no hack.</h3>";
                    } else if ($_GET["submitted"] == "error") {
                        echo "<h3 style='color:red'>SQL error, try again later or talk to Max.</h3>";
                    } else if ($_GET["submitted"] == "duplicate") {
                        echo "<h3 style='color:red'>This video is already in the playlist.</h3>";
                    }
                    ?>
                    <h3 style='color:red' id="urlError"></h3>
                </td>
            </tr>
            <tr>
                <td>
                    <button id="submitURL" class="submit-button">Search</button></td>
                <td>
                    <button id="homeURL2" class="submit-button">Home</button></td>
                <td>
                    <button id="voteskip" class="submit-button">Vote Skip</button>
                </td>
            </tr>
        </table>
        <?php if ($_GET['voteskip']) {
            echo "<h3 class='red'>Voteskip submitted</h3>";
        }
        ?>
        <?php
        $position = 0;
        $vidposition = 0;
        $sql = "SELECT currentvideoid FROM `room-position` WHERE roomid='$roomid'";
        if ($result2 = mysqli_query($connection, $sql)) {
            while ($row2 = mysqli_fetch_row($result2)) {
                $position = $row2[0];
                $vidposition = $position;
            }
        }
        ?>
        <div id="queue">
        </div>
        <script>
            function update_queue(data) {
                data = JSON.parse(data);

                var rows = data.length / 2;
                var content = '<h3>Your Upcoming Videos:</h3><table class="border"><tr><th class="border"><h3>Name</h3></th><th class="border"><h3>Position</h3></th></tr>';
                for (i = 0; i < rows; i++) {
                    content += '<tr class="border"><td><p>' + data[2 * i] + '</p></td><td><p>' + data[2 * i + 1] + '</p></td></tr>';
                }
                content += '</table>';

                if (rows > 0) {
                    $('#queue').html(content);
                } else {
                    $('#queue').html("");
                }
            }

            var ajax_call = function() {
                $.ajax({
                    type: "POST",
                    url: "/playlist/updatequeue.php?room=<?php echo $roomid; ?>",
                    datatype: "json",
                    success: function(data) {
                        update_queue(data);
                    }
                });
            }
            var interval = 3000; //3 seconds
            setInterval(ajax_call, interval);

            $(document).ready(function() {
                $.ajax({
                    type: "POST",
                    url: "/playlist/updatequeue.php?room=<?php echo $roomid; ?>",
                    datatype: "json",
                    success: function(data) {
                        update_queue(data);
                    }
                });
            });
        </script>

        <?php if ($_GET['search']) {
            echo "<h3>Search Results:</h3>";
        }
        ?>
        <script src="./ytembed.js"></script>
        <div id="ytThumbs"></div>

        <script>
            function GetId(input) {
                var roomid = <?php echo $_GET['room']; ?>;
                var encodedInput = encodeURIComponent(input);
                if (input == null || input == "") {
                    document.getElementById("urlError").innerHTML = "Invalid URL, try again";
                } else {
                    location.href = "./submit.php?url=" + encodedInput + "&room=" + roomid;
                }
            }
            <?php if ($_GET['search']) {
                echo "
			ytEmbed.init({'block':'ytThumbs','key':'AIzaSyCG_tMCePfKeih85nhUXkNhB6sP0svfhsw','q':'" . $_GET['search'] . "','type':'search','results':5,'meta':false,'player':'link','layout':'full'});";
            }
            ?>
        </script>
        <script>
            document.getElementById("submitURL").onclick = function() {
                var input = document.getElementById("urlBox").value;
                location.href = "./guest.php?search=" + input + "&room=" + <?php echo $_GET['room']; ?>;
            }

            document.getElementById("homeURL2").onclick = function() {
                location.href = "./deleteactive.php?room=" + <?php echo $_GET['room']; ?>;
            }

            document.getElementById("voteskip").onclick = function() {
                location.href = "./voteskip.php?room=" + <?php echo $_GET['room']; ?>;
            }

            // When enter is pressed in search box, perform a search
            var input = document.getElementById("urlBox");
            // Execute a function when the user releases a key on the keyboard
            input.addEventListener("keyup", function(event) {
                // Number 13 is the "Enter" key on the keyboard
                if (event.keyCode === 13) {
                    // Cancel the default action, if needed
                    event.preventDefault();
                    // Trigger the button element with a click
                    document.getElementById("submitURL").click();
                }
            });
        </script>
    </div>
</body>

</html>