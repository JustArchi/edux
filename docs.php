<?php
if ($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" )
{
	header('Location: index.php#' . basename(__FILE__, ".php"));
}
?>

<script type="text/javascript">
	function show(id, a)
	{
		$(".selected").removeClass("selected");
		$("#docs_frame").attr('src', "https://docs.google.com/document/d/" + a);
		$("#" + id).addClass("selected");
	}
</script>

<div id="child_content">
	<div id="docs_list" class="maxheight">

		<div id="docs_list_header">Wybierz dokument:</div>
		<?php
			include 'connect.php';
			$link = mysqli_connect("127.0.0.1", $user, $password, "edux");

			if (!$link)
			{
				die("Can't connect to MySQL: " . mysqli_connect_error());
			}

			if ($result = mysqli_query($link, "SELECT * FROM DocumentList"))
			{
				while ($row = mysqli_fetch_array($result, MYSQL_ASSOC)) 
				{
			    	printf("<a id='%s' onclick=show('%s','%s')>%s</a>", $row["ID"], $row["ID"], $row["Target"], $row["Title"]);
				}
			}
			mysqli_close($link);
		?>

	</div>
	<div id="docs_body">
		<iframe id="docs_frame" 
				src="" 
				frameBorder="0" 
				scrolling="no" 
				style="width: 100%; height: 100%;"></iframe>
	</div>

</div>