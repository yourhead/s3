<?php
    ob_start ();


    //
    // You will need to configure these constants to match your
    // stack and your server setup.
    //


    // when this is true Stacks versions before v3.5 will still be allowed to
    // update. when set to false, we'll reject those updates.
    define ("ALLOW_OLD_STACK_VERSIONS",                   true);



    // the stack ID to verify
    define ("DEVELOPER_INFO_STACK_ID",                    "com.yourhead.stack.foundry");

    // the path to the appcast file to deliver
    define ("APPCAST_PATH",                               "../appcast.xml");



    // the filename of the localally cached copy of the stacks public key
    define ("STACKS_PUBLIC_KEY_FILENAME",                 "stack_public_key.pem");

    // the filename of the developers private key
    define ("DEVELOPER_PRIVATE_KEY_FILENAME",             "Private.pem");

    // the passphrase used to secure the developers private key
    define ("DEVELOPER_PRIVATE_KEY_PASSPHRASE",           "Stacks");



    // the passphrase used to secure the developers private key
    define ("SECURE_STACK_VERSION",                       3942);






    require_once "YHStacksUpdate.php";

    try {


        /*
         * Get the Stacks version info
         */
        if (!array_key_exists ('HTTP_USER_AGENT' , $_SERVER)) throw new Exception("Bad User Agent");
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        if (empty($userAgent)) throw new Exception("Empty User Agent");
        if (!strpos($userAgent, 'Sparkle')) throw new Exception("Not a Sparkle update");

        $exploded = explode("/", $userAgent);
        $stacksBuildString = end ($exploded);
        $stacksBuild = intval ($stacksBuildString);
    

    
    
        /*
         * Validate the signatures
         */
        if ($stacksBuild >= SECURE_STACK_VERSION) {
        
        
        
            $update = new YHStacksUpdate();
            $update->stacksPublicKeyFilename = STACKS_PUBLIC_KEY_FILENAME;
            $update->developerPrivateKeyFilename = DEVELOPER_PRIVATE_KEY_FILENAME;
            $update->developerPrivateKeyPassphrase = DEVELOPER_PRIVATE_KEY_PASSPHRASE;


            /*
             * get the verified stacks update request
             * this only returns info that has been verified to be from Stacks
             * this will return false when the request is insecure or verification fails
             *
             * here we're rejecting all other types of spoofed requests. since only 
             * requests signed by stacks will pass
             */
            $stacksUpdateRequest = $update->stacks_update_request();
            if (!$stacksUpdateRequest) throw new Exception("Bad Request");
    
    
            /*
             * get the verified developer update info
             * this only returns info that was verified signed with your keys
             * this will return false in all other cases
             *
             * here we're rejecting all attempts to modify the stacks info to
             * pose as a different product or remove security info. only data
             * that hs been verified by stacks, and resigned with your public
             * key will pass
             */
            $developerUpdateInfo = $update->devloper_update_info ();
            if (!$developerUpdateInfo) throw new Exception("Bad Update Info");
    
    
            /*
             * now that we can trust the data is is valid, we check
             * to make sure it's the data we expect
             */
            $stackID = $developerUpdateInfo->id;    
            if ($stackID !== DEVELOPER_INFO_STACK_ID) {
                 throw new Exception("Bad Stack ID: '" . $stackID ."'");
            }
        
            /*  
             * We could place more checks here for other info that we place
             * into the developer info JSON.
             */
        




        } else {
                  
            /* Handle insecure requests from older versions of Stacks < v3.5
             * 
             * until most users have updated to Stacks 3.5 we will allow insecure
             * update requests.  in a few months (at your descretion)
             * set ALLOW_OLD_STACK_VERSIONS to false
             * and regect these old insecure requests.
             */
            if (ALLOW_OLD_STACK_VERSIONS) {
                throw new Exception("No updates allowed for out of date Stacks.");                
            } else {
                error_log ("Old Insecure Stack -- allowed for now.");
            }

        }





        /*
         * Deliver the appcast
         *
         * Instead of redirecting to the appcast file we deliver the content of the file
         * this ensures we never expose the actual location of the appcast file.
         *
         * The file itself can be placed anywhere on your system where PHP has access. 
         * Placing it outside of the public web root directory is recommended.
         */

        $filename = APPCAST_PATH;
        if (!file_exists ($filename)) throw new Exception("File not found.");
    
        // Set the content type to xml
        header ("Content-Type: text/xml");
        header ("Content-Disposition: attachment; filename=" . basename ($filename));
        header ("Content-Length: " . filesize ($filename));
    
        // Tell the user-agent (Stacks) not to cache the file.
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
    
        // flush the output buffer
        ob_clean();
        flush();

        // read the data and send the file
        readfile ($filename);
        
        
        exit;
        
        
        
        
    }
    
    catch (Exception $e) {

        // whenever there is a bad request or any other problem
        // send a 403 error messaage -- and log to our php error log
        ob_clean ();

        $message = ($e->getMessage ()) ?: "";
        header("HTTP/1.0 403 Forbidden" . $message);
        error_log("\n" . $message . "\n\n", 0);
    
        ob_end_flush();
    }


        
