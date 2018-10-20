<?php
//////////////////////////////////////////////
//                                          //
// EasyWP WordPress Installer v1.2          //
// Copyright 2008 - 2010 Michael VanDeMar   //
// http://www.funscripts.net/               //
// All rights reserved.                     //
//                                          //
//////////////////////////////////////////////

//////////////////////////////////////////////
//                                          //
// EasyWP WordPress Installer v2.0          //
// Copyright 2018 - Dean Householder        //
// https://www.deanhouseholder.com/         //
// Freeware                                 //
//                                          //
//////////////////////////////////////////////

function rkg() { // random key generator
    $secret_ar = str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()-=+[]{};:<>,.?");
    $secret = "";
    for($i=0;$i<66;$i++) {
        $secret .= $secret_ar[rand(0,85)];
    }
    return substr($secret,0,64);
}

function getLatestWP() {
    file_put_contents("wordpress.zip", file_get_contents("https://wordpress.org/latest.zip"));
}

function dbConnect($host, $user, $pass, $dbname = false) {
    $host = filter_var(trim($host), FILTER_SANITIZE_STRING);
    $user = filter_var(trim($user), FILTER_SANITIZE_STRING);
    $pass = trim($pass);
    $dbname = $dbname === true ? ";dbname=" . filter_var(trim($dbname), FILTER_SANITIZE_STRING) : "";

    // Try to connect
    try {
        $db = new PDO('mysql:host=' . $host . $dbname, $user, $pass);
    } catch (PDOException $e) {
        $err_code = $e->getCode();
        if ($err_code == 2002) {
            $msg = "Incorrect Server Hostname or IP";
        } elseif ($err_code == 1045) {
            $msg = "Incorrect Username or Password";
        } else {
            $msg = $e->getMessage();
        }
        return [null, false, $msg];
    }
    return [$db, true, "Connection successful"];
}

function dbQuery($db, $query) {
    $query = filter_var(trim($query), FILTER_SANITIZE_STRING);

    $stmt = $db->query($query);
    if ($stmt === false) {
        return [false, "Query Error"];
    }

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!is_array($data)) {
        return [false, "Couldn't fetch data"];
        die;
    }
    return [$data, "Success"];
}

$dir = dirname(__FILE__);
$error_prefix = '<p style="color: red; font: italic 10pt verdana, sans-serif;"><strong>* Error:</strong> &nbsp;';
$message_prefix = "<p style='font: 11pt arial; line-height: 1.5em;'>";

if (isset($_POST["check"])) { // Clicked "Check available databases" button

    list($db, $status, $msg) = dbConnect($_POST['host'], $_POST['user'], $_POST['pass']);
    if ($status === false) {
        echo $error_prefix . $msg . "</p>";
        die;
    }

    list($data, $msg) = dbQuery($db, "SHOW DATABASES");
    if ($data === false) {
        echo $error_prefix . $msg . "</p>";
        die;
    }

    $options = "";
    foreach($data as $row) {
        $options .= "<option value=\"" . $row['Database'] . "\">" . $row['Database'] . "</option>\n";
    }
    print "<p><select name=\"DB_NAME\">\n$options\n</select></p>\n<hr>\n";
    print "<p>Or create a new database: <input type=\"text\" name=\"createnew\" id=\"createnew\">";
    print "\n<button onclick=\"create_database(user.value,pass.value,host.value,createnew.value); return false;\">Create Database</button>";
    print "\n<br><span id=\"create_status\"></span></p>";

} elseif (isset($_POST["create"])) { // Filled out a new database name and clicked "Create Database" button

    list($db, $status, $msg) = dbConnect($_POST['host'], $_POST['user'], $_POST['pass']);
    if ($status === false) {
        echo $error_prefix . $msg . "</p>";
        die;
    }

    list($data, $msg) = dbQuery($db, "CREATE DATABASE " . $_POST['database']);
    if ($data === false) {
        echo $error_prefix . "Failed to create database.</p>\n";
        echo "<p>Does it already exist?</p>\n";
        echo "<p>Click the <u>Check available database</u> button again and check.</p>\n";
        die;
    }

    echo "\n<p><strong>Success: </strong>Database " . $_POST['database'] . " created successfully!</p>";
    echo "\n<p>Click the <u>Check available database</u> button again and select the new database.</p>";
    exit();

} elseif (isset($_POST["process"]) && $_POST["process"] == "true") { // Clicked "Install WordPress" button

    list($db, $status, $msg) = dbConnect($_POST['DB_HOST'], $_POST['DB_USER'], $_POST['DB_PASSWORD'], $_POST['DB_NAME']);

    // If "doanyway" is not set and there's a DB problem, show DB Error page
    if (!isset($_POST["doanyway"]) && $status === false) {
?>
<html>
<head>
<title>EasyWP WordPress Installer</title>
<style>
.container {
    width: 700px;
    margin: 0px auto;
    font-family: Verdana;
    font-size: 12px;
}
.container input {
    margin-top: 5px;
}
H2 {
    font-family: Verdana;
    text-align: center;
    margin: 26px;
}
.gray {
    background-color: #e9ecf3;
}
.white {
    background-color: #f4f4f4;
}
div {
    padding: 10px;
}
</style>
</head>
<body>
<h2>EasyWP WordPress Installer</h1>
<div class="container">
<br><br>
The connection to the database failed with the settings you entered.<br><br>
<form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
<input type="hidden" name="doanyway" value="true">
<input type="hidden" name="wpfile" value="<?=$_POST["wpfile"]?>">
<input type="hidden" name="DB_NAME" value="<?=$_POST["DB_NAME"]?>">
<input type="hidden" name="DB_USER" value="<?=$_POST["DB_USER"]?>">
<input type="hidden" name="DB_PASSWORD" value="<?=$_POST["DB_PASSWORD"]?>">
<input type="hidden" name="DB_HOST" value="localhost" value="<?=$_POST["DB_HOST"]?>">
<input type="hidden" name="SECRET_KEY" value="<?=$_POST["SECRET_KEY"]?>">
<input type="hidden" name="AUTH_KEY" value="<?=$_POST["AUTH_KEY"]?>">
<input type="hidden" name="SECURE_AUTH_KEY" value="<?=$_POST["SECURE_AUTH_KEY"]?>">
<input type="hidden" name="LOGGED_IN_KEY" value="<?=$_POST["LOGGED_IN_KEY"]?>">
<input type="hidden" name="NONCE_KEY" value="<?=$_POST["NONCE_KEY"]?>">
<input type="hidden" name="AUTH_SALT" value="<?=$_POST["AUTH_SALT"]?>">
<input type="hidden" name="SECURE_AUTH_SALT" value="<?=$_POST["SECURE_AUTH_SALT"]?>">
<input type="hidden" name="LOGGED_IN_SALT" value="<?=$_POST["LOGGED_IN_SALT"]?>">
<input type="hidden" name="NONCE_SALT" value="<?=$_POST["NONCE_SALT"]?>">
<input type="hidden" name="table_prefix" value="<?=$_POST["table_prefix"]?>">
<input id="edit_values" type="radio" name="process" value="false" checked><label for="edit_values">Let me edit the values.</label><br>
<input id="proceed_anyway" type="radio" name="process" value="true"><label for="proceed_anyway">That's ok, proceed anyway.</label><br><br>
<input type="submit" value="Continue">
</form>
<?php
    } else {
        if (empty($_POST["wpfile"])) {
            $_POST["wpfile"] = "wordpress.zip";
        }
        exec("unzip " . $_POST["wpfile"]);
        exec("mv -f " . $dir . "/wordpress/* " . $dir);
        exec("rm -rf wordpress");
        exec("rm " . $_POST["wpfile"]);
        if (!file_exists(dirname(__FILE__) . "/wp-config-sample.php")) {
            echo "Operation appears to have failed.<br>\n";
        } else {
            $config = file_get_contents(dirname(__FILE__) . "/wp-config-sample.php");

            $config = str_replace("database_name_here", $_POST["DB_NAME"], $config);
            $config = str_replace("username_here",      $_POST["DB_USER"], $config);
            $config = str_replace("password_here",      $_POST["DB_PASSWORD"], $config);
            $config = str_replace("localhost",          $_POST["DB_HOST"], $config);
            $config = str_replace("'AUTH_KEY',         'put your unique phrase here'", "'AUTH_KEY',         '" . $_POST["AUTH_KEY"] . "'", $config);
            $config = str_replace("'SECURE_AUTH_KEY',  'put your unique phrase here'", "'SECURE_AUTH_KEY',  '" . $_POST["SECURE_AUTH_KEY"] . "'", $config);
            $config = str_replace("'LOGGED_IN_KEY',    'put your unique phrase here'", "'LOGGED_IN_KEY',    '" . $_POST["LOGGED_IN_KEY"] . "'", $config);
            $config = str_replace("'NONCE_KEY',        'put your unique phrase here'", "'NONCE_KEY',        '" . $_POST["NONCE_KEY"] . "'", $config);
            $config = str_replace("'AUTH_SALT',        'put your unique phrase here'", "'AUTH_SALT',        '" . $_POST["AUTH_SALT"] . "'", $config);
            $config = str_replace("'SECURE_AUTH_SALT', 'put your unique phrase here'", "'SECURE_AUTH_SALT', '" . $_POST["SECURE_AUTH_SALT"] . "'", $config);
            $config = str_replace("'LOGGED_IN_SALT',   'put your unique phrase here'", "'LOGGED_IN_SALT',   '" . $_POST["LOGGED_IN_SALT"] . "'", $config);
            $config = str_replace("'NONCE_SALT',       'put your unique phrase here'", "'NONCE_SALT',       '" . $_POST["NONCE_SALT"] . "'", $config);
            if (substr($_POST["table_prefix"], strlen($_POST["table_prefix"])-1) == "_") {
                $config = str_replace("\$table_prefix  = 'wp_';", "\$table_prefix  = '" . $_POST["table_prefix"] . "';", $config);
            } else {
                $config = str_replace("\$table_prefix  = 'wp_';", "\$table_prefix  = '" . $_POST["table_prefix"] . "_';", $config);
            }
            $fp = fopen(dirname(__FILE__) . "/wp-config-sample.php", "w+");
            fwrite($fp, $config);
            fclose($fp);
        }
        rename(dirname(__FILE__) . "/wp-config-sample.php", dirname(__FILE__) . "/wp-config.php");
        unlink('easywp.php');
        header("Location: wp-admin/install.php");
        die;
    }
} else {
    // Show Main Form

    if (@filetype($dir . "/wp-config-sample.php") == "file" || @filetype($dir . "/wp-config.php") == "file") {
        echo $error_prefix . "</p>\n";
        echo $message_prefix . "It appears that Wordpress has already been already uploaded and/or installed.<br>\n";
        echo "This utility is designed for clean installs only.</p>";
        die;
    }

    if (is_writable($dir) === false) {
        echo $error_prefix . "</p>\n";
        echo $message_prefix . "It appears that the current directory is not writable.<br>\n";
        echo "Please correct and re-run this script.</p>\n";
        die;
    }

    $availfiles = array();

    if ($dh = opendir($dir)) {
        while(($file = readdir($dh)) !== false) {
            if (filetype($dir . "/" . $file) == "file" && substr($file, 0, 9) == "wordpress") {
                if (substr($file, strlen($file) - 3) == ".gz" || substr($file, strlen($file) - 4) == ".zip") {
                    $availfiles[] = $file;
                }
            }
        }
        closedir($dh);
    }

    if (count($availfiles) == 0) {
        $availfiles[] = getLatestWP();
    } elseif (count($availfiles) > 1) {
        echo $error_prefix . "</p>\n";
        echo $message_prefix . "Multiple verions of Wordpress archives detected.<br>\n";
        echo "Please delete all but the one you wish to install, or delete all of them and allow this script to<br>\n";
        echo "download the latest version available from Wordpress.org.</p>\n";
        die;
    }

    // Nothing above has failed so show the form

    // If re-editing
    if (isset($_POST["process"]) && $_POST["process"] == "false") {
        $db_name = $_POST["DB_NAME"];
        $db_user = $_POST["DB_USER"];
        $db_password = $_POST["DB_PASSWORD"];
        $db_host = $_POST["DB_HOST"];
        $secret_key = $_POST["SECRET_KEY"];
        $auth_key = $_POST["AUTH_KEY"];
        $secure_auth_key = $_POST["SECURE_AUTH_KEY"];
        $logged_in_key = $_POST["LOGGED_IN_KEY"];
        $nonce_key = $_POST["NONCE_KEY"];
        $auth_salt = $_POST["AUTH_SALT"];
        $secure_auth_salt = $_POST["SECURE_AUTH_SALT"];
        $logged_in_salt = $_POST["LOGGED_IN_SALT"];
        $nonce_salt = $_POST["NONCE_SALT"];
        $table_prefix = $_POST["table_prefix"];
    } else {
        $db_name = "";
        $db_user = "";
        $db_password = "";
        $db_host = "localhost";
        $secret_key = rkg();
        $auth_key = rkg();
        $secure_auth_key = rkg();
        $logged_in_key = rkg();
        $nonce_key = rkg();
        $auth_salt = rkg();
        $secure_auth_salt = rkg();
        $logged_in_salt = rkg();
        $nonce_salt = rkg();
        $table_prefix = "wp_";
    }
?>
<html>
<head>
<title>EasyWP WordPress Installer</title>
<style>
.container {
    width: 700px;
    margin: 0px auto;
    font-family: Verdana;
    font-size: 12px;
}
.container input {
    margin-top: 5px;
}
H2 {
    font-family: Verdana;
    text-align: center;
    margin: 26px;
}
.gray {
    background-color: #e9ecf3;
}
.white {
    background-color: #f4f4f4;
}
div {
    padding: 10px;
}
</style>
<script type="text/javascript">
function check_avail_dbs(user, pass, host) {
    if (user=="") {
        document.getElementById("available_dbs").innerHTML="";
        return;
    }
    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    }
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("available_dbs").innerHTML=xmlhttp.responseText;
        }
    }
    xmlhttp.open("POST","<?=$_SERVER['PHP_SELF']?>",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    thedata="check=1&user="+user+"&pass="+pass+"&host="+host
    xmlhttp.send(thedata);
}

function create_database(user, pass, host, database) {
    if (user=="") {
        document.getElementById("create_status").innerHTML="";
        return;
    }
    if (window.XMLHttpRequest) {
        xmlhttp=new XMLHttpRequest();
    }
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("create_status").innerHTML=xmlhttp.responseText;
        }
    }
    xmlhttp.open("POST","<?=$_SERVER['PHP_SELF']?>",true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    thedata="create=1&user="+user+"&pass="+pass+"&host="+host+"&database="+database
    xmlhttp.send(thedata);
}
</script>
</head>
<body>
<h2>EasyWP WordPress Installer</h1>
<div class="container">
    <form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
    <input type="hidden" name="process" value="true">
    <input type="hidden" name="wpfile" value="<?=$availfiles[0]?>">
    <div class="gray">
        The MySQL username (<em>must be a valid user for the database entered above</em>):<br>
        <input type="text" name="DB_USER" size="25" value="<?=$db_user?>" id="user"><br>
    </div>
    <div class="white">
        The MySQL password (<em>this is the password for the database user entered above</em>):<br >
        <input type="text" name="DB_PASSWORD" size="25" value="<?=$db_password?>" id="pass"><br>
    </div>
    <div class="gray">
        The host MySQL runs on (<em>99% chance you won't need to change this value</em>):<br>
        <input type="text" name="DB_HOST" value="localhost" size="25" value="<?=$db_host?>" id="host"><br>
        <input type="hidden" name="SECRET_KEY" value="<?=$secret_key?>">
        <input type="hidden" name="AUTH_KEY" value="<?=$auth_key?>">
        <input type="hidden" name="SECURE_AUTH_KEY" value="<?=$secure_auth_key?>">
        <input type="hidden" name="LOGGED_IN_KEY" value="<?=$logged_in_key?>">
        <input type="hidden" name="NONCE_KEY" value="<?=$nonce_key?>">
        <input type="hidden" name="AUTH_SALT" value="<?=$auth_salt?>">
        <input type="hidden" name="SECURE_AUTH_SALT" value="<?=$secure_auth_salt?>">
        <input type="hidden" name="LOGGED_IN_SALT" value="<?=$logged_in_salt?>">
        <input type="hidden" name="NONCE_SALT" value="<?=$nonce_salt?>">
    </div>
    <div class="white">
        Fill in the above fields and then click:<br>
        <button onClick="check_avail_dbs(user.value,pass.value,host.value); return false;">Check available databases</button>
    </div>
    <div class="gray">
        Select the database you wish to install WordPress into:<br>
        <span id="available_dbs"></span>
    </div>
    <div class="white">
        You can have multiple WordPress installations in one database if you give each a unique prefix:<br>
        <input type="text" name="table_prefix" value="<?=$table_prefix?>" size="25"> (Only numbers, letters, and underscores please!)<br>
    </div>
    <input type="submit" value="Install WordPress">
    </form>
<?php
}
?>
</div>
</body>
</html>
