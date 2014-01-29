<?php
if ($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" )
{
	header('Location: index.php#' . basename(__FILE__, ".php"));
}
?>

<iframe id="child_content" 
		src="https://docs.google.com/a/pjwstk.edu.pl/document/d/16yBaYbAPYDOpUth7c8tUiRexMrcXq7_3tcTTuk1FTLM/" 
		frameBorder="0" 
		scrolling="no" 
		style="width: 100%; height: 100%; position: relative;"/>
