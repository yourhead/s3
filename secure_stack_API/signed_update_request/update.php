<?php

require_once "YHStacksUpdate.php";

$update = new YHStacksUpdate();
if ($update->verify()) {
	header("Location:appcast.xml");
}
else {
	header("HTTP/1.0 403 Forbidden");
}

/*
	instead of a simple redirect you can use the update
	info to return a specific appcast
	since this info is signed, you know that it's a request
	sent directly from the Stacks updater

	Example:  StackUpdateInfo
	{
		"StackAPIVersion" : "9",
		"StackID" : "com.yourhead.stacks.paths.cds",
		"StacksVersion" : "3.3.0",
		"StacksBuild" : "3936",
		"RapidWeaverVersion" : "7.4",
		"RapidWeaverBuild" : "18672b"
	}

	$stackUpdateInfo    = $update->info();

	$stackID            = $stackUpdateInfo->StackID;
	$stackAPIVersion    = $stackUpdateInfo->StackAPIVersion;

	$stacksVersion      = $stackUpdateInfo->StacksVersion;
	$stacksBuild        = $stackUpdateInfo->StacksBuild;

	$rapidWeaverVersion = $stackUpdateInfo->RapidWeaverVersion;
	$rapidweaverBuild   = $stackUpdateInfo->RapidWeaverBuild;
*/