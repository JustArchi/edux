<?php
require 'check_session.php';
?>

<?php
	if (isset($_GET["data"]))
	{
		$results = array();
		exec('tac ~/log/error.log', $results);



		$count = 6;
		$start = 0;

		if (isset($_GET['page']))
		{
			$start = $count * $_GET['page'];
		}
		
		

		$counter = 0;
		$i = 0;
		while ($counter < $start)
		{
			$line = $results[$i];

			if (strlen($line > 20) && $line{20} == '[')
			{
				$counter++;
			}

			$i++;
		}

		if ($i > 0)
			$i--;

		$oneshot = FALSE;
		$counter = 0;

		$buffer = array();

		while ($counter < $count)
		{
			$line = $results[$i];

			if (strlen($line > 20) && $line{20} == '[')
			{
				$buffer[] = $line;
				array_reverse($buffer);

				$counter++;
				if ($counter > 1)
					echo '</td></tr>';

				if ($counter & 1)
					echo '<tr><td>';
				else
					echo '<tr class=\'alt\'><td>';

				$final = '';
				foreach ($buffer as $num => $this_line) 
				{
					if (strlen($this_line > 20) && $this_line{20} == '[')
					{
						$date = substr($this_line, 0, 19);
						$message = substr($this_line, 19);
						$message = substr($message, strpos($message, ': ') + 2); // cut out the ' [error] 23882#0: ' part
						$message = substr($message, strpos($message, ' ') + 1); // cut out ' *1234 '
					}
					else
					{
						$message = $this_line;
					}


					$separator = 'PHP message: ';
					$pos = strpos($message, $separator);

					if ($pos !== FALSE)
					{
						$message = substr($message, $pos + strlen($separator));
						$message = '<b>' . substr($message, 0, strpos($message, ':')) . '</b>' . substr($message, strpos($message, ':')) ;
					}


					$details_start = strpos($message, ', client: ');

					if ($details_start !== FALSE)
					{
						$message = substr($message, 0, $details_start) . "<div class='hidden' id='details_{$i}'>" . str_replace(', ', '<br>', substr($message, $details_start + 2)) . '</div>';
					}

					$message = str_replace('" while reading response header from upstream', '', $message);
					$message = str_replace('" while reading upstream', '', $message);
					$final .= ($message . '<br>');
				}

				echo "<center>$date</center></td><td>$final";
				echo "<br><a href='javascript:' onclick=\"$('#details_{$i}').toggle();\">Details</a>";

				$buffer = array();
			}
			else
			{
				$buffer[] = $line;
				/*if (!$oneshot)
				{
					echo '<tr><td></td><td>';
					$oneshot = TRUE;
				}	

				$message = $line;
				$separator = 'PHP message: ';
				$pos = strpos($message, $separator);

				if ($pos !== FALSE)
				{
					$message = substr($message, $pos + strlen($separator));
					$message = '<b>' . substr($message, 0, strpos($message, ':')) . '</b>' . substr($message, strpos($message, ':')) ;
				}


				$details_start = strpos($message, ', client: ');

				if ($details_start !== FALSE)
				{
					$message = substr($message, 0, $details_start) . "<div class='hidden' id='details_{$i}'>" . str_replace(', ', '<br>', substr($message, $details_start + 2)) . '</div>';
				}

				$message = str_replace('" while reading response header from upstream', '', $message);
				$message = str_replace('" while reading upstream', '', $message);

				echo '<br>' . $message;
				echo "<br><a href='javascript:' onclick=\"$('#details_{$i}').toggle();\">Details</a>";*/
			}

			$i++;
		
			echo '</td></tr>';
		}
		

		die();
	}
?>


<html>
	<head>
		<script src="http://code.jquery.com/jquery-2.1.0.min.js"></script>
		
		<style type="text/css">
			td.date
			{
				width: 100px;
				text-align: center;
				line-height: 140%;
			}

			.hidden
			{
				display: none;
				font-size: 80%;
			}
		</style>

		<script type="text/javascript">
			$(function() {
				loadMore();
			});

			function loadMore()
			{
				$("#load_more").html("Loading...");
				if (loadMore.page == undefined)
					loadMore.page = 0;
				else
					loadMore.page++;

				$.get("logs3.php?data&page=" + loadMore.page, function(data) {
					$("#table").append(data); 
					$("#load_more").html("Moar!");
				});
			}
		</script>
		<link rel="stylesheet" href="admin.css" type="text/css" />
	</head>
	<body>
		<div class="container">
			<table id="table">
			</table>
			<a class="button" href="index.php">Return</a>
			<div id="load_more" class="button" onclick="loadMore();">Load more...</div>
		</div>
	</body>
</html>