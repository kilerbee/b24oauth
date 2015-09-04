<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 04.09.15
 */

session_start();

if(isset($_REQUEST["clear"]))
{
	unset($_SESSION["auth"]);
	unset($_SESSION["portal"]);
}


if(isset($_REQUEST["portal"]))
{
	$_SESSION["portal"] = $_REQUEST["portal"];
}

if(isset($_SESSION["portal"]))
{
	$portal = $_SESSION["portal"];

	require_once("class/client.php");
	$client = new \Bitrix24\OAuthClient($portal, "local.55e983e2c63c50.41307294", "dc7c1d992ff0ed0997be3b7ffa4b3dd1", array("user", "log"));

	if(!$_SESSION["auth"])
	{
		if(!$client->isAuthCodeSent())
		{
			$url = $client->getAuthorizeUrl();

			header("HTTP 302 Found");
			header("Location: " . $url);
		}
		else
		{
			$auth = $client->getAuth();
			$_SESSION["auth"] = $auth;

			header("HTTP 302 Found");
			header("Location: ?finish");
		}
	}
	else
	{
		if(isset($_REQUEST["refresh"]))
		{
			$auth = $client->getAuth($_SESSION["auth"]["refresh_token"]);
			$_SESSION["auth"] = $auth;

			header("HTTP 302 Found");
			header("Location: ?finish");
		}
		else
		{

?>
		<pre><?print_r($_SESSION["auth"]);?></pre>
		<a href="?clear=1">Clear sesstion</a>
		<a href="?refresh=1">Refresh token</a>
<?
		}
	}
}
else
{
?>
<form method="POST">Portal address: <input type="text" name="portal"> <input type="submit" value="Authorize"></form>
<?
}
