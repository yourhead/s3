#!/usr/bin/env ruby

require 'openssl'





# Should be exactly one argument -- the password for the private pem file
if (!ARGV[0])
	abort("usage: generate_keys [password]")
end
password = ARGV[0]




if (File.exist?('Private.pem'))
	abort("generate keys cannot overwrite the Private.pem file in this directory")
end



if (File.exist?('Public.pem'))
	abort("generate keys cannot overwrite the Public.pem file in this directory")
end





rsa_key = OpenSSL::PKey::RSA.new(2048)
cipher =  OpenSSL::Cipher::Cipher.new('des3')

private_key = rsa_key.to_pem(cipher, password)
public_key = rsa_key.public_key.to_pem

privateFile = open('Private.pem', 'w')
privateFile.write(private_key)
privateFile.close

publicFile = open('Public.pem', 'w')
publicFile.write(public_key)
publicFile.close
