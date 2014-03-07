<?php
require 'check_session.php';
?>

<?php

	$results = array();
	exec('tac ~/log/error.log', $results);

	$i = 0;
	$counter = 0;
	foreach ($results as $line)
	{
		

		echo $line;

		if (strlen($line > 20) && $line{20} == '[')
		{
			echo '<hr>';
		}
		$i++;
	}
?>
