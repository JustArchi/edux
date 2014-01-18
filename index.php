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

				$("#footer").slideToggle(200);
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
		    var newsize = ($(window).height() - $("#header").height() - $("#menuBar").height() - $("#footer").height() - 7); // 7 is menuBar padding

		    if (!($("#header").is(':visible')))
		    	newsize += $("#header").height();

		    if (!($("#footer").is(':visible')))
		    	newsize += $("#footer").height();

		    $("#contNoMenu").css('height', newsize);
		}

		window.onresize = resize_child;

		$(function() {
		    resize_child();
		});

		function loadContent(target)
		{
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
								<a onclick='loadContent("main")'>Main</a>
								<a onclick='loadContent("docs")'>Google Docs</a>
								<a onclick='loadContent("calendar")'>Kalendarz</a>
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
			<div id="footer">Edu@Gakko PJWSTK 2011-2013</div>
		</div>
	</body>
</html>