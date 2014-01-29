<?php
if ($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" )
{
	header('Location: index.php#' . basename(__FILE__, ".php"));
}
?>


<script type="text/javascript">
function fix_iframe_height()
{
	var newsize = $("#contNoMenu").height();
	newsize += 167;
	$("#embedux").css('height', newsize);
}
</script>

<iframe id="embedux" onload="fix_iframe_height();" frameborder="0" src="http://edux.pjwstk.edu.pl"></iframe>

