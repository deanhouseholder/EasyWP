EasyWP (WordPress Installer)
============

Description:
------------------
Easy WordPress Installer is a simple script that can help you install your self-hosted WordPress easily. Just upload it to your server, browse to it, and you're off.


Instructions:
-------------------
1. Using a SFTP/FTP program, create a directory on your webserver where you would like your WordPress site to be installed such as http://yoursite.com/blog or perhaps just http://yoursite.com/.
2. Using a SFTP/FTP program, upload this script: easywp.php
3. Using a web browser, go to the newly uploaded script, such as: http://yoursite.com/easywp.php
4. Enter a valid username and password for your MySQL database.
5. Select the hostname which most of the time is "localhost".
6. Click "Check available databases" to see a list of databases in a pulldown menu.
7. Either select an existing database or type in the name of a new database you want to create and click "Create Database".
8. Finally click "Install WordPress".  You will be redirected into the WordPress installer.


Note:
----------------------
This script automatically creates complex, random 64-character secret keys for usage in the install so each WordPress install created with this script has this automatically.


Requirements:
----------------------
1. Web Hosting
2. PHP
3. MySQL


Credits:
----------------------
This is a fork from the EasyWP WordPress Installer found on: http://www.funscripts.net/php-scripts/


License:
----------------------
Freeware