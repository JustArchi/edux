<?php
require 'check_session.php';
?>

<html>
	<head>
		<link rel="stylesheet" href="admin.css" type="text/css" />
	</head>
	<body>
		<div class="container">
			<a class="button" href="manage_docs.php">Manage docs</a>
			<a class="button" href="logs.php">Logs (raw)</a>
			<a class="button" href="logs3.php">View logs</a>
			<a class="button" href="git_test.php">Git</a>
			<a class="button" href="backend.php">Backend</a>
			<br><br><br>
			<a class="button-red" href="login.php?logout">Logout</a>
		</div>
	</body>
</html>
