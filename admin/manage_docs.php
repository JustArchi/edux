<?php
require 'check_session.php';
?>

<?php
require_once '../connect.php';
require_once "../google-api-php-client/src/Google_Client.php";
require_once "../google-api-php-client/src/contrib/Google_DriveService.php";
require_once "../google-api-php-client/src/contrib/Google_Oauth2Service.php";

$auth = new Google_AssertionCredentials(
    $service_account_email,
    $drive_scope,
    file_get_contents( "../" . $privatekey_filename )
);

$client = new Google_Client();
$client->setUseObjects( true );
$client->setAssertionCredentials( $auth );
$service = new Google_DriveService( $client );

if (isset($_GET["delete"]))
{
	$service->files->delete($_GET["delete"]);
	$link = mysqli_connect("127.0.0.1", $user, $password, $database);

	if (!$link)
	{
		die("Can't connect to MySQL: " . mysqli_connect_error());
	}

	$sql = sprintf("DELETE FROM DocumentList WHERE Target = '%s'", $_GET["delete"]);
	mysqli_query($link, $sql);
	//echo $sql;
	mysqli_close($link);

	header( 'Location: manage_docs.php' ) ;

	sleep(3);
	return;
}

$list = $service->files->listFiles();

if (isset($_GET["parse"]) && $_GET["parse"] == "false")
{
	printf("<pre>%s</pre>", print_r($list, true));
	return;
}
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="admin.css" type="text/css" />
</head>
<body>
		<div class="container">

<?php


echo "Files: <br>";
$files = $list->items;

echo "<table>";
$alt = false;

echo "<tr class='header'><td>TITLE</td><td>OPEN</td><td>CREATED</td><td>DELETE</td></tr>";

foreach ($files as $file)
{
	if ($alt)
		echo "<tr class='alt'>";
	else
		echo "<tr>";

	$alt = !$alt;

	printf("<td>%s</td><td><a href='%s'>OPEN</a></td><td>%s</td><td><a href='manage_docs.php?delete=%s'>DELETE</a></td>", $file->title, $file->alternateLink, str_replace("T", " ", $file->createdDate), $file->id);
	echo "</tr>";
}
echo "</table>";


?>
<br><br>
<a class="button" href="index.php">Return</a>
<a class="button-blue" href='manage_docs.php?parse=false'>Show raw data</a>

</div>
</body>
</html>