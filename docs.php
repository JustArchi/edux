<?php
if ($_SERVER["HTTP_X_REQUESTED_WITH"] != "XMLHttpRequest" )
{
	header('Location: index.php#' . basename(__FILE__, ".php"));
}
?>

<script type="text/javascript">
	var docsSet = new Object();
	function show(id)
	{
		$(".docs_frame").hide();
		if (docsSet[id] == undefined)
		{
			$("#docs_body").append("<iframe id='docs_frame_" + id + "' src='https://docs.google.com/document/d/" + id + "' class='docs_frame' frameBorder='0' scrolling='no' style='width: 100%; height: 100%;'></iframe>");
			
			setTimeout(function() {
				$("#docs_button_" + id).append("<span class='doc_status'>OTWARTY</span>");
			}, 2000);

			docsSet[id] = true;
		}
		else
		{
			$("#docs_frame_" + id).show();
		}

		$(".selected").removeClass("selected");
		$("#docs_button_" + id).addClass("selected");
	}

	function addDoc()
	{
		$("#dialog-modal-state1").show();
		$("#dialog-modal-state2").hide();
		$( "#dialog-modal" ).dialog({
			modal: true
		});
	}

	function doAddGoogleDoc()
	{
		$("#dialog-modal-state1").hide(100);
		$("#dialog-modal-state2").show(100);

		var name = $("#new_doc_name").val();
		$.get("add_doc.php", { name : name }).done(function(data) {
			$("#dialog-modal").dialog("close");
			document.location.reload();
		})		
	}

	$(function() {
		$("#new_doc_name").keyup(function (event) {
			if (event.which == 13)
			{
				event.preventDefault();
				doAddGoogleDoc();
			}
		});
	});

</script>

<div id="dialog-modal" class="dialog" style='display:none' title="Utwórz nowy dokument">
	<div id="dialog-modal-state1">
		<p>Podaj tytuł dokumentu:</p>
		<form onsubmit="return false;">
			<input id="new_doc_name" type="text"></input>
			<input onclick="doAddGoogleDoc();" type="button" value="   OK   "></input>
		</form>
	</div>
	<div id="dialog-modal-state2" style="display:none">
		Zaczekaj...
	</div>
</div>

<div id="child_content">
	<div id="docs_list" class="maxheight">

		<div id="docs_list_header">Wybierz dokument:</div>
		<div id="docs_list_content">
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
			    	printf("<a id='docs_button_%s' onclick=show('%s')>%s</a>", $row["Target"], $row["Target"], $row["Title"]);
				}
			}
			mysqli_close($link);
		?>
		</div>

		<a style='margin-left:60px;' id='add_new_doc' onclick='addDoc()'>Utwórz nowy...</a>

	</div>
	<div id="docs_body" class="maxheight">

	</div>

</div>