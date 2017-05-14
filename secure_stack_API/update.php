<?php

require_once "YHStacksUpdate.php";

try {

	$update = new YHStacksUpdate();

	// get the verified stacks update request
	$stacksUpdateRequest = $update->stacks_update_request();
	if (!$stacksUpdateRequest) throw new Exception("Bad Request");


	// get the verified developer update info
	$developerUpdateInfo = $update->devloper_update_info ();
	if (!$developerUpdateInfo) throw new Exception("Bad Update Info");

	// if everything went well, we redirect to the appropriate appcast file
	$stackID = $developerUpdateInfo->id;
	$stack = "";

	if ($stackID === 'com.yourhead.stack.columns')	$stack = 'columns';
	if ($stackID === 'com.yourhead.stack.grid')		$stack = 'grid';
	if ($stackID === 'com.yourhead.stack.quote')	$stack = 'quote';

	if (empty ($stack)) {
		header("HTTP/1.0 404 Not Found -- Unknown Stack ID");
		echo "Not Found -- Unknown Stack ID\n";
		var_dump($developerUpdateInfo);
		var_dump($stackID);
		exit (404);
	}
	
	header('Location:' . $stack . '/appcast.xml');
	echo $stack . "\n";
		exit;

}
catch (Exception $e) {
	// something went wrong, reject the update
	//$message = ($e->getMessage ()) ? " -- " . $e->getMessage () : "";
	$message = ($e->getMessage ()) ?: "";
	header("HTTP/1.0 403 Forbidden" . $message);
	//echo "\n" . $message . "\n\n";
}