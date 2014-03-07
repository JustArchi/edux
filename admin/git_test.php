<?php
require 'check_session.php';
?>

<?php

if (isset($_GET["oper"]))
{
	$oper = $_GET["oper"];
	$out = array();

	if ($oper == 'status')
	{
		exec('cd .. && git status 2>&1', $out);
	}
	else if ($oper == 'pull')
	{
		exec('cd .. && git pull origin master 2>&1', $out);
	}
	else if ($oper == 'reset')
	{
		exec('cd .. && git reset --hard 2>&1', $out);
	}
	else if ($oper == 'log')
	{
		$head = exec('cd .. && git rev-parse HEAD');
		echo "Current HEAD: $head\n\n";
		exec('cd .. && git fetch origin master');
		exec("cd .. && git --no-pager log ${head}..origin/master 2>&1", $out);
	}
	else
	{
		$result = ('Unknown operation ' . $oper);
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
		function load(a)
		{
			$('#results').html('Please wait...');
			$('#results').load(a);
		}
		</script>
	</head>
	<body>
		<div class="container">

		<a class="button" href="index.php">Return</a>
		<a class="button-blue" onclick="load('git_test.php?oper=status');" href="#">STATUS</a>
		<a class="button-blue" onclick="load('git_test.php?oper=log');" href="#">LOG</a>
		<a class="button-red" onclick="load('git_test.php?oper=reset');" href="#">RESET</a>
		<a class="button-green" onclick="load('git_test.php?oper=pull');" href="#">PULL</a>

		<br><br><hr>
		<xmp id="results" style="text-align:left">
		</xmp>
		</div>
	</body>
</html>
