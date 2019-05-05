<?php
session_start();

ini_set("date.timezone", "Europe/Ljubljana");

$BASEPATH = dirname(__DIR__, 1) . DIRECTORY_SEPARATOR;
require $BASEPATH . 'lib/functions.php';
require $BASEPATH . 'env.php';
include $BASEPATH . "public/api/class.Lang.php";

$l = new Lang($LANGUAGE);

if(isset($_POST['input-username']) && !empty($_POST['input-username']) && isset($_POST['input-password']) && !empty($_POST['input-password'])) {
    $DB = getDB($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS);
    $q = "SELECT userid, username, passwrd, userlevel, firstname, lastname FROM hccusers WHERE username=:username LIMIT 1";
    $stmt = $DB->prepare($q);
    try {
        $stmt->execute([':username' => trim($_POST['input-username'])]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(password_verify($_POST['input-password'], $row['passwrd'])) {
            $_SESSION['user'] = [
                'username'  => $row['username'],
                'firstname' => $row['firstname'],
                'lastname'  => $row['lastname'],
                'userlevel' => $row['userlevel'],
                'loggedin'  => time()
            ];
            $_POST = [];
            header('location: main.php');
            exit();
        } else {
            $_POST = [];
            session_destroy();
        }
    } catch (PDOException $e) {
        $_POST = [];
        session_destroy();
        throw new PDOException($e->getMessage(), (int)$e->getCode());
        error_log("Error logging in user {$_POST['input-username']}: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Home control</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="screen" href="css/login.css">
</head>
<body>

<div class="container">

<form class="form-signin" method="post" action="index.php">
<h2 class="form-signin-heading"><?php echo $l->Get("login_title");?></h2>
<label for="input-username" class="sr-only"><?php echo $l->Get("username");?></label>
<input type="text" name="input-username" id="input-username" class="form-control" placeholder="<?php echo $l->Get("username");?>" required autofocus>
<label for="input-password" class="sr-only"><?php echo $l->Get("password");?></label>
<input type="password" name="input-password" id="input-password" class="form-control" placeholder="<?php echo $l->Get("password");?>" required>

<button class="btn btn-lg btn-primary btn-block" type="submit"><?php echo $l->Get("login_button");?></button>
</form>

</div> <!-- /container -->

</body>
</html>