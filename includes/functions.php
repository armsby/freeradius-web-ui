<?php

if ( preg_match( "/functions.php/", $_SERVER['SCRIPT_NAME'] ) ) {
	header( "Location: ../" );
	exit;
}

/**
 * @return bool
 */

function NTLMHash($Input) {
        // Convert the password from UTF8 to UTF16 (little endian)
        $Input=iconv('UTF-8','UTF-16LE',$Input);

        // Encrypt it with the MD4 hash
        $MD4Hash=bin2hex(mhash(MHASH_MD4,$Input));

        // You could use this instead, but mhash works on PHP 4 and 5 or above
        // The hash function only works on 5 or above
        //$MD4Hash=hash('md4',$Input);

        // Make it uppercase, not necessary, but it's common to do so with NTLM hashes
        $NTLMHash=strtoupper($MD4Hash);

        // Return the result
        return($NTLMHash);
}

function getSetting($vkey) {
	require_once 'config.php';
	try {
		$link = new PDO( RAD_DB_DRIVER . ':host=' . RAD_DB_HOST . ';dbname=' . RAD_DB_NAME, RAD_DB_USER, RAD_DB_PASS );
	} catch(PDOException $Exception) {
		die($Exception->getMessage());
	}

	$chkSetting = $link->prepare("SELECT data FROM rmsettings WHERE vkey = :setting");
	$chkSetting->bindParam(':setting',$vkey);
	$chkSetting->execute();

	if ($chkSetting->rowCount() == 1) {

		$Result = $chkSetting->fetch(PDO::FETCH_ASSOC);
		return $Result['data'];
	} else {
		return false;
	}
}


function StripTrailingSlash($_URL)
{
	if (substr($_URL, -1, 1) == "/") {
		return substr($_URL, 0, strlen($_URL) - 1);
	}

	return $_URL;
}

function GetProductURL($currentDir = '')
{
	$_isHTTPS = false;
	if(isset($_SERVER['HTTPS']) && (intval($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'on'))
	{
		$_isHTTPS = true;
	}

	$_selfURL = sprintf('http%s://%s%s',(isset($_SERVER['HTTPS']) && (intval($_SERVER['HTTPS']) != 0 || strtolower($_SERVER['HTTPS']) == 'on')? 's': ''),$_SERVER['HTTP_HOST'],$_SERVER['REQUEST_URI']);
	$_domainData = parse_url($_selfURL);
	$_finalPort = '';
	if (isset($_domainData['port'])) {
		$_finalPort = ':' . $_domainData['port'];
	}

	$_swiftPath = '';
	if (!empty($_domainData["user"])) {
		$_swiftPath = $_domainData["scheme"]."://".$_domainData["user"].":".$_domainData["password"]."@".$_domainData["host"].$_finalPort.'/'.substr($_domainData["path"], 1, strrpos($_domainData["path"],"/"));
	} else if (isset($_domainData['scheme'], $_domainData['host'], $_domainData['path'])) {
		$_swiftPath = $_domainData["scheme"]."://".$_domainData["host"].$_finalPort.'/'.substr($_domainData["path"], 1, strrpos($_domainData["path"],"/"));
	} else {
		$_swiftPath = "http" . IIF($_isHTTPS, 's', ''). "://" . $_SERVER["SERVER_NAME"]."/" . substr($_SERVER["PHP_SELF"], 1, strrpos($_SERVER["PHP_SELF"],"/"));
	}

	// Cook up the product URL
	$_setupLocation = $_swiftPath;
	if (isset($_POST["producturl"]) && trim($_POST['producturl']) != "")
	{
		$_setupLocation = $_POST["producturl"];
	} else {
		$_setupLocation = substr($_setupLocation, 0, strlen($_setupLocation)-strlen("$currentDir/"));
	}

	$_setupLocation = StripTrailingSlash($_setupLocation) . '/';

	return $_setupLocation;
}
