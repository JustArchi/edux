<?php
if ($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" )
{
	header('Location: index.php#' . basename(__FILE__, ".php"));
}
?>

<iframe id="child_content" 
        src="https://www.google.com/calendar/embed?showTitle=0&amp;showPrint=0&amp;height=800&amp;showCalendars=0&amp;wkst=2&amp;bgcolor=%23FFFFFF&amp;src=pjwstktop1%40gmail.com&amp;color=%23853104&amp;ctz=Europe%2FWarsaw" 
        style="width: 100%; height: 100%; position: relative;" 
        frameborder="0" 
        scrolling="no"/>