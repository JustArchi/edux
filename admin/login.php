<?php
require "../connect.php";

if (isset($_GET["logout"]))
{
	session_start();
	$_SESSION = array();

	if (isset($_COOKIE[session_name()])) 
	{ 
  		setcookie(session_name(), '', time()-42000, '/'); 
  	}

	session_destroy();
	header('Location: login.php');
	die;
}
else if (isset($_POST["pass"]) && isset($_POST["user"]))
{
	if ($_POST["user"] === $admin_user &&
		$_POST["pass"] === $admin_pass)
	{
		session_start();
		$_SESSION["auth"] = true;

		if (isset($_GET["next"]))
		{
			header("Location: ${_GET['next']}");
		}
		else
		{
			header('Location: index.php');
		}
		die;
	}
	else
	{
		$message = "Invalid login. ";
	}
}
?>

<html>
	<head>
		<link rel="stylesheet" href="admin.css" type="text/css" />
	</head>
	<body>
		<div class="container">
			<form method="post" >
			<input placeholder="User" class="textbox" type="text" name="user"/><br>
			<input placeholder="Password" class="textbox" type="password" name="pass"/><br>
			<input type="submit" class="button" value="Login"/>
			</form>
			<?php
				if (isset($message))
				{
					echo $message;
				}
			?>
		</div>
	</body>
</html>
