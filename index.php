<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>GAKKO.EDU</title>
		<link rel="stylesheet" href="adds/style/standard.css" type="text/css" />
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	</head>
	<body>
		<script type="text/javascript">
		$(function()
		{
			if (location.hash=='')
			{
				loadContent("main");
			}
			else
			{
				loadContent(location.hash.substring(1));
			}
			
			$("#banner_toggle").click(function(evt)
			{
				$("#header").slideToggle(300, function() {
					resize_child();
				});
			});

			$("#button_admin").click(function(evt)
			{
				$("#dropmenu").fadeIn(100);
			});

			$("#dropmenu").mouseleave(function(evt)
			{
				$("#dropmenu").fadeOut(100);
			});

			$("#header").click(function(evt)
			{
				window.location = "https://edux.pjwstk.edu.pl/";
			});
		});
		</script>

		<script type="text/javascript">

		function resize_child()
		{
		    var newsize = ($(window).height() - $("#header").height() - $("#menuBar").height() - 7); // 7 is menuBar padding

		    if (!($("#header").is(':visible')))
		    	newsize += $("#header").height();

		    $("#contNoMenu").css('height', newsize);
		}

		window.onresize = resize_child;

		$(function() {
		    resize_child();
		});

		function loadContent(target)
		{
			if (target=="main")
				$("#contNoMenu").load(target + ".html"); //need iframe-resizing javascript
			else
				$("#contNoMenu").load(target + ".html #child_content");
			location.hash = "#" + target;
		}

		</script>

		<div id="container">
			<div id="header"></div>
			<div id="menuBar">
				<div style="float:right;">
					<ul id="dropdown">
						<li>
							<a id="button_admin">Administrator</a>
							<div id="dropmenu">
								<?php

									include 'connect.php';
									$link = mysqli_connect("127.0.0.1", $user, $password, "edux");

									if (!$link)
									{
										die("Can't connect to MySQL: " . mysqli_connect_error());
									}

									if ($result = mysqli_query($link, "SELECT * FROM MenuOptions ORDER BY Position"))
									{
										while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) 
										{
									    	printf("<a onclick='loadContent(\"%s\")' >%s</a>", $row["Target"], $row["Title"]);
										}
									}
									mysqli_close($link);
								?>
							</div>
						</li>
						<li>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</li>
						<li>
							<a id="button_help">Pomoc</a>
						</li>
						<li>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						</li>
						<li>
							<a id="banner_toggle">Wyloguj</a>
						</li>
					</ul>
				</div>
			</div>				
			<div id="contNoMenu">

			</div>
		</div>
	</body>
</html>