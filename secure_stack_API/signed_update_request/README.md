## Signed Update Requests

Stacks 3.5 will securely sign all sparkle update requests. This allows stack developer to ensure they only deliver updates directly to Stacks and can reject everything else.

### Backward compatible and Opt In

The update signature and info provided to verify it are in addition to the existing appcast request. Developers can choose if and when to incorporate signed updates.

### Goals

The goal is to prevent unwanted hacking/pirating of updates and the developer's update server. To accomplish this we use the stacks public key to verify the signature of the update request. Only requests signed with the stacks private key will be valid. This ensures all requests came from stacks.

### Php Example

The code provided in this directory demonstrates how to load the public key and use it to validate the request. It's written in Php to make integrating with your update server as easy as possible.

### Important details

 - The signed data is provided in an HTTP header. You should use the info provided in this header (since that is the data that is signed) instead of relying on the GET parameters.  The GET parameters will still be provided and **should** be identical to the HTTP headers, but since they are not signed they should not be relied upon.
 - Please use the public key that is provided in this github repository. Note: This key may change in the future.
 - There is a different public key inside the Stacks 3 bundle. It is a different type and will not validate stack updates.