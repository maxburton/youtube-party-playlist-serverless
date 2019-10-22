<?php include "/var/www/inc/dbinfo.inc"; ?>
<?php include("./head.html"); ?>
<title>YPP</title>

<?php

/* Connect to MySQL and select the database. */
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

$database = mysqli_select_db($connection, DB_DATABASE);

$clientip = $_SERVER['REMOTE_ADDR'];


$count = 0;
$sql = "SELECT COUNT(*) FROM `blacklist` WHERE ip='$clientip'";
if ($result = mysqli_query($connection, $sql)) {
    while ($row = mysqli_fetch_row($result)) {
        $count = $row[0];
    }
}
?>

</head>

<body>
    <div class="content">

        <h1>Youtube Party Playlist</h1>
        <h2>Let your friends add music or videos to a shared playlist in a fair way</h2>
        <?php
        if (isset($_COOKIE["userID"])) {
            echo "<h3>Hi there, " . $_COOKIE['username'] . "!</h3>";
        } else {
            echo "<h3>Enter Your Name:</h3>";
        }
        ?>
        <table class="indextable">
            <tr>
                <td>
                    <?php
                    if (isset($_COOKIE["userID"])) {
                        echo "
                <button id='rename' class='submit-button'>Change Name</button>";
                    } else {
                        echo '<form id="nameform" action="newuser.php" method="post">';
                        echo '<h3> <input type="text" id="nameBox" name="name"></h3>';
                        echo '<input type="hidden" name="def" value="default" />';
                        if ($count >= 10) {
                            $a = rand(2, 9);
                            $b = rand(2, 9);
                            $c = $a + $b;
                            echo '<input type="hidden" name="captchacode" value="' . $c . '">';
                            echo '<h3>Prove you\'re not a robot: ' . $a . ' + ' . $b . ' = </h3><input class="textBox" type="text" name="captcha" />';
                        }
                        echo '</form>';
                        echo '<button id="rename" class="submit-button" >Submit Name</button>';
                        if ($_GET["joined"] == "false") {
                            echo '<h3 style="color:red" id="nameError">Invalid name, please enter a name with 20 or fewer alphanumeric characters.</h3>';
                        } else if ($_GET["joined"] == "captcha") {
                            echo '<h3 style="color:red" id="nameError">Incorrent answer, try again</h3>';
                        } else if ($_GET["joined"] == "miss") {
                            echo '<h3 style="color:red" id="nameError">Please use the form</h3>';
                        } else {
                            echo '<h3 style="color:red" id="nameError"></h3>';
                        }
                    }
                    ?>
                    <?php
                    if ($_GET["badid"]) {
                        echo "<p style='color:red'>Room doesn't exist, try again</p>";
                    }
                    ?>

                    <?php
                    if (isset($_COOKIE["userID"])) {
                        echo '<form id="roomform" action="./makeroom.php" method="post">';
                        echo '<input type="hidden" name="def" value="default" />';
                        if ($count >= 10) {
                            $a = rand(2, 9);
                            $b = rand(2, 9);
                            $c = $a + $b;
                            echo '</td></tr>
                    <tr><td>';
                            echo '<input type="hidden" name="captchacode" value="' . $c . '">';
                            echo '<h3>Prove you\'re not a robot: ' . $a . ' + ' . $b . ' = </h3><input class="textBox" type="text" name="captcha" />';
                        }
                        if ($_GET["newroom"] == "captcha") {
                            echo '<h3 style="color:red" id="newroomError">Incorrent answer, try again</h3>';
                        } else if ($_GET["newroom"] == "miss") {
                            echo '<h3 style="color:red" id="nameError">Please use the form</h3>';
                        }
                        echo '</form>
			</td></tr>
			<tr><td>
			<button form="roomform" id="makeRoom" class="submit-button" >Make New Room</button>';
                    }
                    ?>

                    <?php
                    if (isset($_COOKIE["hostID"])) {
                        echo "
		</td></tr>
		<tr><td>
		<button id='rehostRoom' class='submit-button' >Rehost Room " . $_COOKIE["hostID"] . "</button>";
                    }
                    ?>

                    <?php
                    if (isset($_COOKIE["userID"])) {
                        echo '
			</td></tr>
			<tr><td>
			<button id="joinRoom" class="submit-button" >Join Room</button>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php
                    if (isset($_COOKIE["lastRoomID"]) && isset($_COOKIE["userID"])) {
                        echo "<button id='rejoinRoom' class='submit-button' >Rejoin Room " . $_COOKIE["lastRoomID"] . "</button>";
                    }
                    ?>
                </td>
            </tr>
        </table>

        <script type="text/javascript">
            function setCookie(cname, cvalue) {
                var d = new Date();
                d.setTime(d.getTime() + (24 * 60 * 60 * 1000)); //1 day
                var expires = "expires=" + d.toUTCString();
                document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            }

            function getCookie(cname) {
                var name = cname + "=";
                var decodedCookie = decodeURIComponent(document.cookie);
                var ca = decodedCookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }

            function deleteCookie(cname) {
                document.cookie = cname + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            }
            <?php
            if (isset($_COOKIE["hostID"])) {
                echo '
    document.getElementById("rehostRoom").onclick = function () {
        location.href = "./host.php";
    };';
            } ?>
            <?php
            if (isset($_COOKIE["userID"])) {
                echo '
        document.getElementById("joinRoom").onclick = function () {
            if (getCookie("userID") != ""){
                var input = prompt("Please enter a room number:");
                if (input == null || input == "" || input.length > 5) {
                    alert("Invalid room format, enter a room with 5 digits or less");
                } else {
                    location.href = "./guest.php?room=" + input;
                }
            }
        };';
            } ?>


            <?php
            if (isset($_COOKIE["lastRoomID"]) && isset($_COOKIE["userID"])) {
                echo '
    document.getElementById("rejoinRoom").onclick = function () {
        location.href = "./guest.php?room=" + getCookie("lastRoomID");
    };';
            } ?>


            $(document).ready(function() {
                $("#rename").click(function() {
                    if (Cookies.get('userID')) {
                        Cookies.remove('username');
                        Cookies.remove('userID');
                        location.href = "./deleteactive.php";
                    } else {
                        var $input = $("#nameBox").val();
                        if ($input == null || $input == "" || $input.length > 20) {
                            $("#nameError").html("Invalid name, please enter a name with 20 or fewer alphanumeric characters.");
                        } else {
                            $("#nameform").submit();
                        }
                    }
                });
            });
        </script>
    </div>
</body>

</html>