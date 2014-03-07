<?php
require 'check_session.php';
?>

<?php

if (isset($_GET["oper"]))
{
	$oper = $_GET["oper"];
	$out = array();

	if ($oper == 'stop')
	{
		exec('cd ~/private/backend && touch STOP', $out);
	}
	else if ($oper == 'restart')
	{
		exec('cd ~/private/backend && touch RESTART', $out);
	}
	
	$result = implode("\n", $out);
	echo $result;
	return;
}

?>

<html>
	<head>
		<script src="http://code.jquery.com/jquery-2.1.0.min.js"></script>
		<link rel="stylesheet" href="admin.css" type="text/css" />
		<script type="text/javascript">
		function backend_stop()
		{
			$.get("backend.php?oper=stop");
		}

		function backend_restart()
		{
			$.get("backend.php?oper=restart");
		}
		</script>
	</head>
	<body>
		<div class="container">
		<a class="button" href="index.php">Return</a>
		<a class="button-red" onclick="backend_stop();" href="#">STOP</a>
		<a class="button-red" onclick="backend_restart();" href="#">RESTART</a>
		</div>
	</body>
</html>
