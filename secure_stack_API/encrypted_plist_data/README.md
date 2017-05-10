## Encrypted P-List Data

Stacks 3.5 will check each stack plist for an encrypted data block.  This encrypted block will be decrypted and the data merged into the plist. This provides a way of hiding sensitive data from users and competitors.  You can, for instance, place the URL for your update server, and your Update Info into the encrypted data.

### Backward compatible and Opt In

Stacks without encrypted data will continue to work as always. Developers can choose if and when to utilize this feature.

### Goals

The goal is to provide a lightweight mechanism to protect sensitive data. The simple RC4 cipher is not military grade and should not be used to store passwords, personal or financial information.

### How it works

The developer uses the Stacks public key to encrypt a plist file. The encrypted data is added to the stack. When Stacks 3 loads the stack into the Stacks Library it will decrypt the information and incorporate it into the stack.

### Ruby script

A ruby script is provided to encrypt data. Use Terminal.app to run the script providing a Plist file.

#### How to use the script

 1. Create a separate plist for your private info.
This plist can contain anything you like. The data in the private plist will override the values in your normal plist.
 2. Encrypt the plist using the ruby script
`% encrypt_plist.rb Private_Plist.plist > encrypted.dat`
 3. Open the resulting file with a text editor
 4. Cmd-A then Cmd-C to Copy all of the data.
 5. Open our regular Info.plist
 6. Create a new item with key: `stackData` type: `String` value: paste in the encrypted data







### Important details

 - The encryption used here is RC4. It's a streaming cipher that's very fast. How secure is it? It was what SSL was using a few years back -- so it's not bad -- but given enough CPU power there are several known ways to compromise it. The NSA is rumored to
 be able to decrypt RC4 streams in real time with specialized hardware. I chose this cipher because it is well understood and because it was used with SSL (web security and such) the implementations on each platform are very easy to make compatible. e.g.: we can encrypt things using macOS version of Ruby or even on a Php backend server and Stacks can decrypt them using OpenSSL.
 - Please use the public key that is provided in this github repository. Note: This key may change in the future.
 - There is a different public key inside the Stacks 3 bundle. It is a different type and will not validate stack updates.