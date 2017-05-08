<?php



//
// Every stack update coming from Stacks 3.5+ is signed and can be verified as coming from Stacks.
// To verify an update use the you'll need to pass the info from two of the HTTP headers to the 
// verify funciton below.
//
class YHStacksUpdate {

	public function __construct() {			    
	}
	
	// compute an md5 hash of the data
	// this will be compared with the decrypted signature
	public function digest ($data) {
		$digest = openssl_digest($data, "md5", true);
		if (!$digest) throw new Exception("Could not create digest.");
		return $digest;
	}

	// the stacks public key
	// we'll retrieve it from the yourhead site, but it can be embedded on your server
	// to improve speed 
	public function key () {
		$stackPublicKeyURL = "https://raw.githubusercontent.com/yourhead/s3/master/secure_update_API/stack_public_key.pem";
		$fp = fopen ($stackPublicKeyURL, "r");
		//$fp = fopen ("stack_public_key.pem", "r");
		if (!$fp) throw new Exception ("Could not retrieve the stack public key.");
		$publicKey = fread($fp, 8192);
		fclose($fp);
		if (!$publicKey) throw new Exception ("Stacks public key could not be read.");
		return $publicKey;
	}

	// use the public key  to decrypt the signature.
	// the result should be the same as the digest md5 hash
	public function decrypt ($signature) {
		$success = openssl_public_decrypt(base64_decode($signature), $decrypted, $this->key());
		if (!$success) throw new Exception("Bad signature");
		return $decrypted;
	}

	// compare the digest md5 hash to the decrypted signature
	public function verify ($signature, $data) {
		try {
			$hash = $this->decrypt($signature);
			$digest = $this->digest($data);
			if ($hash !== $digest) throw new Exception("Invalid signature");
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

}

$headers = apache_request_headers();
$signature = $headers['Signature'];
$stackUpdateInfoJSON = $headers['StackUpdateInfo'];


$update = new YHStacksUpdate ();
if ($update->verify ($signature, $stackUpdateInfoJSON)) {
   header("Location:appcast.xml");
} else {
	header("HTTP/1.0 403 Forbidden");
}


   	// instead of a simple redirect you can use the update
    // info to return a specific appcast
    // since this info is signed, you know that it's a request
    // sent directly from the Stacks updater
    //
	// 	Example:  StackUpdateInfo
	//  {
	//    "StackAPIVersion" : "9",
	//    "StackID" : "com.yourhead.stacks.paths.cds",
	//    "StacksVersion" : "3.3.0",
	//    "StacksBuild" : "3936",
	//    "RapidWeaverVersion" : "7.4",
	//    "RapidWeaverBuild" : "18672b"
	//  }

	// $stackUpdateInfo = json_decode ($stackUpdateInfoJSON);

	// $stackID = $stackUpdateInfo['StackID'];
	// $stackAPIVersion = $stackUpdateInfo['StackAPIVersion'];

	// $stacksVersion = $stackUpdateInfo['StacksVersion'];
	// $stacksBuild = $stackUpdateInfo['StacksBuild'];

	// $rapidWeaverVersion = $stackUpdateInfo['RapidWeaverVersion'];
	// $rapidweaverBuild = $stackUpdateInfo['RapidWeaverBuild'];


