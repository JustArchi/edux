<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>GAKKO.EDU</title>
		<link rel="stylesheet" href="adds/style/standard.css" type="text/css" />
		<script src="http://code.jquery.com/jquery-2.1.0.min.js"></script>
		<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
		<link rel="stylesheet" href="adds/style/jquery-ui.css" type="text/css" >
	</head>
	<body>
		<script type="text/javascript">

		var hideDropdownInterval;

		function hideDropdown()
		{
				hideDropdownInterval = setInterval(function() {
					$("#dropmenu").fadeOut(100);
				}, 400);
		}

		function showDropdown()
		{
			$("#dropmenu").fadeIn(100);
			clearInterval(hideDropdownInterval);
		}

		$(function()
		{
			window.onhashchange = function()
			{
				if (location.hash=='')
				{
					loadContent("main");
				}
				else
				{
					loadContent(location.hash.substring(1));
				}
			};

			window.onhashchange();
			
			$("#banner_toggle").click(function(evt)
			{
				$("#header").slideToggle(300, function() {
					resize_child();
				});
			});

			$("#button_admin").mouseenter(showDropdown);

			$("#dropmenu").mouseenter(showDropdown);

			$("#button_admin").mouseleave(hideDropdown);

			$("#dropmenu").mouseleave(hideDropdown);

			$("#header").click(function(evt)
			{
				loadContent("main");
			});
		});
		</script>

		<script type="text/javascript">

		function resize_child()
		{
		    var newsize = ($(window).height() - $("#header").height() - $("#menuBar").outerHeight() + 3); // 3 is the negative margin

		    if (!($("#header").is(':visible')))
		    	newsize += $("#header").height();

		    $(".maxheight").css('height', newsize);
		}

		window.onresize = resize_child;

		$(function() {
		    resize_child();
		});

		var pageSet = new Object;

		function loadContent(target)
		{
			if (loadContent.previous_target == target)
				return;

			loadContent.previous_target = target

			if (pageSet[target] == undefined)
				$("#loading").show(0);
			else
				$("#loading").hide(0);

			$("#contNoMenu").fadeOut(100, function() {
				if (pageSet[target] == undefined)
				{
					$(".subpage").hide(0);
					$("#contNoMenu").append("<div class='subpage' id='" + target + "'></div>");
					pageSet[target] = true;
					
					target_url = target + ".php";

					$("#" + target).load(target_url, function() {
						$("#contNoMenu").fadeIn(100);
						resize_child();
					});
				}
				else
				{
					$(".subpage").hide(0);
					$("#" + target).show(0);
					$("#contNoMenu").fadeIn(100);
					resize_child();
				}

			});
			location.hash = "#" + target;

			$("#dropmenu").fadeOut(100);
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

								<a onclick="$('#embedux').attr('src', 'https://edux.pjwstk.edu.pl/logout.aspx'); loadContent('main');">Wyloguj PJWSTK</a>

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
			<div id="page_container">
				<div id="contNoMenu" class="maxheight">

				</div>
				<div id="loading">
					Loading...
				</div>	
			</div>
		</div>
	</body>
</html>