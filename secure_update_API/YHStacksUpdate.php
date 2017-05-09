<?php
//
// Every stack update coming from Stacks 3.5+ is signed and can be verified as coming from Stacks.
// To verify an update use the you'll need to pass the info from two of the HTTP headers to the 
// verify funciton below.
//
class YHStacksUpdate {

	private $signature;
	private $stackUpdateInfoJSON;
	private $publicKey;

	public function __construct()
	{
		$headers = apache_request_headers();
		$headers = array_change_key_case($headers,CASE_LOWER);
		$this->signature = $headers['signature'];
		$this->stackUpdateInfoJSON = $headers['stackupdateinfo'];
	}

	// compute an md5 hash of the data
	// this will be compared with the decrypted signature
	private function digest($data)
	{
		$digest = openssl_digest($data, "md5", true);
		if (!$digest) throw new Exception("Could not create digest.");
		return $digest;
	}

	// retrieve key from the yourhead site
	private function online_key()
	{
		$stackPublicKeyURL = "https://raw.githubusercontent.com/yourhead/s3/master/secure_update_API/stack_public_key.pem";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$stackPublicKeyURL);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
		$results = curl_exec($ch);
		$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $httpCode !== 200 ? false : $results;
	}

	// the stacks public key
	private function key()
	{
		$publicKey = false;
		$keyFile = "stack_public_key.pem";
		if (file_exists($keyFile)) {
			$publicKey = file_get_contents($keyFile);
		}
		else {
			$publicKey = $this->online_key();
			if ($publicKey) file_put_contents($keyFile,$publicKey);
		}
		if (!$publicKey) throw new Exception ("Stacks public key could not be read.");
		return $publicKey;
	}

	// use the public key  to decrypt the signature.
	// the result should be the same as the digest md5 hash
	private function decrypt($signature)
	{
		$success = openssl_public_decrypt(base64_decode($signature), $decrypted, $this->key());
		if (!$success) throw new Exception("Bad signature");
		return $decrypted;
	}

	// compare the digest md5 hash to the decrypted signature
	public function verify_signature()
	{
		$hash = $this->decrypt($this->signature);
		$digest = $this->digest($this->stackUpdateInfoJSON);
		if ($hash !== $digest) throw new Exception("Invalid signature");
		return true;
	}

	// runs verify_signature but catches all exceptions
	public function verify()
	{
		try {
			$this->verify_signature();
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}

	// return update object
	public function info()
	{
		return json_decode($this->stackUpdateInfoJSON);
	}
}
