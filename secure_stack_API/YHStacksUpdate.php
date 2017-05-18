<?php




/*
 * Every stack update coming from Stacks 3.5+ is signed and can be verified as coming from Stacks.
 * To verify an update use the you'll need to pass the info from two of the HTTP headers to the 
 * verify funciton below.
 */
class YHStacksUpdate {

	public $stacksPublicKeyFilename;
	public $developerPrivateKeyFilename;
	public $developerPrivateKeyPassphrase;





  	// the URL where the stacks public key can be downloaded
	const STACKS_PUBLIC_KEY_URL = "http://yourhead.com/appcast/RW6/Stacks3/stack_public_key.pem";

	// the stack update request
	private $stacksSignature;
	private $stacksInfo;
	private $stacksInfoJSON;

	// the developer update info
	private $developerSignature;
	private $developerInfo;
	private $developerInfoJSON;





	public function __construct() {

		$this->stacksPublicKeyFilename = "stack_public_key.pem";
		$this->developerPrivateKeyFilename = "Private.pem";
		$this->developerPrivateKeyPassphrase = "";

		$headers = apache_request_headers();
		$headers = array_change_key_case($headers,CASE_LOWER);

		if ($headers) $this->setStacksInfo ($headers);
		if ($this->stacksInfo) $this->setDeveloperInfo ($this->stacksInfo);
	}





	/*
	 * Verified data
	 * Verify the signatures and return the data only if the signature is valid.
	 */	
	public function stacks_update_request () {
		if (!$this->verify_stacks_update_request ()) return false;
		return $this->stacksInfo;
	}

	public function devloper_update_info () {
		if (!$this->verify_devloper_update_info ()) return false;
		return $this->developerInfo;
	}





	/*
	 * Verify signatues
	 * To verify we compare the decrypted signature with the hashed data. If the hash
	 * matches the decrypted signature the signature is valid and the data can be trusted.
	 */
	public function verify_stacks_update_request () {
		$hash = $this->public_decrypt ($this->stacksSignature, $this->stack_public_key ());
		$digest = $this->digest ($this->stacksInfoJSON);
		if ($hash !== $digest) throw new Exception("Invalid Stacks signature");
		return true;
	}

	public function verify_devloper_update_info () {
		$hash = $this->private_decrypt ($this->developerSignature, $this->developer_private_key ());
		$digest = $this->digest ($this->developerInfoJSON);
		if ($hash !== $digest) throw new Exception("Invalid developer signature");

		return true;
	}





	/* Extract HTTP header info
	 * The normal request is delivered from Stacks for backward compatibility, 
	 * but the signed data is contained in the HTTP headers. When the signatures
	 * are verified this data can be trusted to be valid.
	 * 
	 * Here we extract the data from the headers. The JSON objects are preserved to
	 * be used by the signature verification.
	 */
	private function setStacksInfo ($headers) {
		if (array_key_exists ('signature', $headers)) $this->stacksSignature = $headers['signature'];
		if (array_key_exists ('stackupdateinfo', $headers)) $this->stacksInfoJSON = $headers['stackupdateinfo'];

		if ($this->stacksInfoJSON) {
			$this->stacksInfo = json_decode($this->stacksInfoJSON);
		}
	}

	private function setDeveloperInfo ($stacksInfo) {
		if (property_exists ($stacksInfo, 'DeveloperSignature')) $this->developerSignature = $stacksInfo->DeveloperSignature;
		if (property_exists ($stacksInfo, 'DeveloperInfo')) $this->developerInfoJSON = $stacksInfo->DeveloperInfo;

		if ($this->developerInfoJSON) {
			$this->developerInfo = json_decode($this->developerInfoJSON);
		}
	}





	/* MD5 digest hash
	 * To verify the signatures we create an md5 hash of the JSON objects.
	 * Each signature, once decrypted, should match its corresponding hash.
	 */
	private function digest($data) {
		$digest = openssl_digest($data, "md5", true);
		if (!$digest) throw new Exception("Could not create digest.");
		return $digest;
	}





	/* RSA keys
	 * We'll need two keys to perform the signature verification. The stack public key
	 * and the developer private key. The stack public key can be found on the Stacks
	 * github repository. The developer private key should be created with the generate_keys
	 * ruby script. Your private key should not be shared with anyone else.
	 */
	private function stack_public_online_key() {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, self::STACKS_PUBLIC_KEY_URL);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($ch, CURLOPT_HEADER, false);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$results = curl_exec($ch);
		$httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);
		return $httpCode !== 200 ? false : $results;
	}

	private function stack_public_key() {
		$publicKey = false;
		if (file_exists($this->stacksPublicKeyFilename)) {
			$publicKey = file_get_contents($this->stacksPublicKeyFilename);
		}
		else {
			$publicKey = $this->online_key();
			if ($publicKey) file_put_contents($this->stacksPublicKeyFilename, $publicKey);
		}

		if (!$publicKey) throw new Exception ("Stacks public key could not be read.");
		return $publicKey;
	}

	private function developer_private_key() {
		$privateKey = false;
		if (file_exists($this->developerPrivateKeyFilename)) {
			$privateKey = openssl_get_privatekey('file://'.$this->developerPrivateKeyFilename, $this->developerPrivateKeyPassphrase);
		}

		if (!$privateKey) throw new Exception ("Developer private key could not be read.");
		return $privateKey;
	}





	/* Decrypt signatures
	 * Signatures are encrypted with a public/private key-pair and can be decrypted with
	 * the corresponding key.
	 *
	 * The entire update request JSON object is signed with the stack private key. You will
	 * use the stack public key to decrypt the signature. Once decrypted, it should match
	 * an MD5 digest hash of the request JSON object.
	 *
	 * The developer update info JSON object, provided in the stack by the developer is
	 * signed with your public key, which should also reside in the stack. We will decrypt
	 * this signature with your private key and it should match an MD5 hash of the JSON
	 * object.
	 */
	private function public_decrypt($data64, $key) {
		$success = openssl_public_decrypt(base64_decode($data64), $decrypted, $key);
		if (!$success) throw new Exception("Public decrypt: Bad signature");
		return $decrypted;
	}

	private function private_decrypt($data64, $key) {
		$enc = base64_decode($data64);
		$success = openssl_private_decrypt(base64_decode($data64), $decrypted, $key);

		if (!$success) throw new Exception("Private decrypt: Bad signature");
		return $decrypted;
	}


}
