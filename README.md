# Setup
* To run on an ubuntu server, run:
* `sudo apt-get install php-mysql`
* That will allow PHP to communicate with the MySQL db
* You will also need to create a file in /var/www/inc/ called dbinfo.inc: `sudo nano /var/www/inc/dbinfo.inc`
* Open the file and populate it with:
```
<?php
define('DB_SERVER', 'instancename.eu-west-1.rds.amazonaws.com');
define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_DATABASE', 'databasename');

?>
```
* Ensure flushblacklist.php is executable: `sudo chmod +x flushblacklist.php`
* Set up cronjob to run php files: `crontab -e`
```
0 4 * * * php -q /path_to_playlist/flushblacklist.php
```