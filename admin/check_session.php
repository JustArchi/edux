<?php
session_start();
if (!isset($_SESSION["auth"]) || $_SESSION["auth"] !== true)
{
	header("Location: login.php?next=" . basename($_SERVER["PHP_SELF"]));
}
?>