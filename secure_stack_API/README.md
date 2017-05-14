
## Secure Updates

In order to ensure that only correct updates are delivered Stacks 3.5 can sign and verify each update request.

To ensure that the stack comes directly from the developer and makes the round-trip to your
update server without being hacked we need to validate it at each step. We use RSA public-key encryption to ensure the validation process.

 - Before the update begins Stacks validates the developer info inside the stack
 - Your server validates that Stacks sent the update request
 - Your server validates the developer info inside of the request

All of the three steps above can be performed to ensure that updates are absolutely secure. Or you can perform only some of the steps for improved security, or perform none and rely on obfuscation alone as we've done in the past.




### Backward compatible and Optional

Securing updates is optional and backward compatible. If you do nothing updates will continue to be delivered as they always have.

Stacks will sign all requests going forward, but your server can choose to ignore that information.

If your stack provides update-info in the plist it will be returned to your server.

If your stack provides signed update-info, the signature will be validated and signed by Stacks.




### Mix and Match

##### Ignore security
If you're just getting started, or your stacks are free, ignoring all security might be a good option. Keep it simple and deliver updates in the standard sparkle fashion.

##### Verify request
You can choose to simply verify that stacks is making the request. This will prevent people from hacking the server and just downloading your files or appcasts.

##### Provide update info
Your stack can provide a JSON object with update info that you'd like returned to your server to help your server return the correct update/appcast.

##### Sign the update info
Your stack can also sign the JSON object.  When signed Stacks will verify the signature before making an update request.

##### Verify the update info
When you provide signed update info the update info is re-signed by stacks before it is returned. Your update server can verify this info before it delivers the update.




### One More Thing - Encrypted Data

In addition to securing updates Stacks will also read encrypted plist data. This allows you to hide the data from customers or anyone else that opens the plist.  Once encrypted with the stacks public key only Stacks 3.5 will be able to read the data.  When the stack is opened it will behave normally.





### Goals

The goals of the Secure Update API is to provide a flexible set of tools to ensure that updates can be delivered with as much security as each developer needs -- while maintaining backward compatibility to unsecured updates.





### Example php

There is a complete example to securing updates provided. It validates both the request and the data before redirecting to the update.




## Ruby scripts

### Key generation
There is a ruby script for generating public/private key-pairs to be used in signing your stacks. Run the script on the command line. It will provide usage info.

#### Example: Generate public/private key-pair
```
% generate_keys Stacks

```
> Results  two key files are created:  `Public.pem` and `Private.pem`




### Sign an update
There is a ruby script that, given a text file containing data and private key, will sign the data. 

#### Example: Sign a JSON object

###### Create text file `json.dat` with contents:
```
{
    "id": "com.yourhead.stack.columns",
    "title": "2 Columns",
    "version": "3.6.5",
    "build": "12345"
}
```
 
###### Create a public/private key-pair
`Private.pem` and `Public.pem` (see example above).
Copy the `Public.pem` file to your stack's Resources directory.

###### Sign the data using the ruby script:
```
% stack_sign_update json.dat Private.pem Stacks

ga2W5kXRdwLhBMMyekB/8Vb5KxYwcYt8kkzXYuBEXO37e1w/VRwCDWKrtPT1JNa0mXhVeVoKoimfJFXwheMIBS8hzfTxN5/YAgiBh6lcfPovG0joh4R5V5+cr7gTExp3tVCS/f7VoTghox+YVY3u4SZwLhJcHlluAYj+ZSaOB/InNwiuW05nKgJZeQ9achJ1cz/CmeGEdmhr8weMqkRbu+8n/TN4m2Q/V3DUZznkdIfTz0eEU+sjs0+DPyoB2H+M8gKba7bdfhjnNVLcgvZ1NEZpvg7L8RqlT0HaKTxO9rPXZLYGi7GbnP178RJ6E6Ifvso5EKldzrUJASKyM7ajkw==
```
> Result: The signature. This should be copied to your stack plist (see below).

###### Inside your stack's plist, add the following:

- `updateInfoPublicKey` - This Public.pem file created above.
```
Public.pem
```


- `updateInfo` - This is the data from the json.dat file you signed above.
```
{
    "id": "com.yourhead.stack.columns",
    "title": "2 Columns",
    "version": "3.6.5",
    "build": "12345"
}
```

- updateSignature - This is the output of the signing script from above.
```
ga2W5kXRdwLhBMMyekB/8Vb5KxYwcYt8kkzXYuBEXO37e1w/VRwCDWKrtPT1JNa0mXhVeVoKoimfJFXwheMIBS8hzfTxN5/YAgiBh6lcfPovG0joh4R5V5+cr7gTExp3tVCS/f7VoTghox+YVY3u4SZwLhJcHlluAYj+ZSaOB/InNwiuW05nKgJZeQ9achJ1cz/CmeGEdmhr8weMqkRbu+8n/TN4m2Q/V3DUZznkdIfTz0eEU+sjs0+DPyoB2H+M8gKba7bdfhjnNVLcgvZ1NEZpvg7L8RqlT0HaKTxO9rPXZLYGi7GbnP178RJ6E6Ifvso5EKldzrUJASKyM7ajkw==

```





