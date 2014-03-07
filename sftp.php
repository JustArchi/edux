<?php
if ($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" )
{
	header('Location: index.php#' . basename(__FILE__, ".php"));
}
?>

<iframe id="child_content" 
        src="http://edux.justarchi.net/sftp/"
        style="width: 100%; height: 100%; position: relative;" 
        frameborder="0" 
        />