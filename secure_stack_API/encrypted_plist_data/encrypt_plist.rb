#!/usr/bin/env ruby

require 'openssl'
require 'base64'

require 'net/http'
require 'uri'


#
# Public key encryption can only encrypt small bits of data. So
# to do the same with large amounts of data we use regular symetric
# encryption with a random password. Then public key encrypt the password.
# https://en.wikipedia.org/wiki/Public-key_cryptography
#

# Should be just one argument -- the plist file
if (!ARGV[0])
	abort("usage: stack_encrypt [plist file]")
end

# Make sure it exists
filePath = ARGV[0]
if (!File.exist?(filePath))
	STDERR.puts "Data file does not exist"
	abort("usage: stack_encrypt [plist file]")
end

# Read the data file
data = File.read (filePath)
if (!data)
	STDERR.puts "Could not read data file"
	abort("usage: stack_encrypt [plist file]")
end


# download the public key
stackPublicKeyURL = "https://raw.githubusercontent.com/yourhead/s3/master/secure_update_API/stack_public_key.pem"
publicKeyPem = Net::HTTP.get(URI.parse(stackPublicKeyURL))
publicKey = OpenSSL::PKey::RSA.new (File.read ('./public.pem'))
if (!publicKey)
	abort("Could not download stacks public key")
end

# Generate a random password and hash it
password = OpenSSL::Random.pseudo_bytes(64)
md5 = OpenSSL::Digest::MD5.new
symetricKey = md5.digest (password)
if (!password)
	abort("Could create a symetric key")
end

# encrypt the data using a simple fast RC 4 cipher
cipher = OpenSSL::Cipher.new('RC4')
cipher.key = symetricKey
dataEncrypted = cipher.update (data)
if (!dataEncrypted)
	abort("Symetric encryption failed")
end


# use the stacks public key to encrypt the password
passwordEncrypted = publicKey.public_encrypt (password)
if (!passwordEncrypted)
	abort("Public-key encryption failed")
end

# write the results to standard out, using strict base64 (no newlines!)
puts (Base64.strict_encode64 (passwordEncrypted)) + (Base64.strict_encode64 (dataEncrypted))

