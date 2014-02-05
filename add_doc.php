<?php
require_once "connect.php";
require_once "google-api-php-client/src/Google_Client.php";
require_once "google-api-php-client/src/contrib/Google_DriveService.php";
require_once "google-api-php-client/src/contrib/Google_Oauth2Service.php";

$title = $_GET["name"];

$auth = new Google_AssertionCredentials(
    $service_account_email,
    $drive_scope,
    file_get_contents( $privatekey_filename );
);

$client = new Google_Client();
$client->setUseObjects(true);
$client->setAssertionCredentials($auth);
$service = new Google_DriveService($client);

$file = new Google_DriveFile();
$file->setTitle($title);
$file->setMimeType('application/vnd.google-apps.document');
$file = $service->files->insert($file);

$permission = new Google_Permission();
$permission->setRole('writer');
$permission->setType('anyone');
$permission->setValue('me');
$permission->setWithLink(true);
$service->permissions->insert($file->getId(), $permission);

$link = mysqli_connect("127.0.0.1", $user, $password, "edux");

if (!$link)
{
	die("Can't connect to MySQL: " . mysqli_connect_error());
}

$sql = sprintf("INSERT INTO DocumentList (ID, Title, Target) VALUES (NULL, '%s', '%s');", $title, $file->id);
mysqli_query($link, $sql);
mysqli_close($link);
?>