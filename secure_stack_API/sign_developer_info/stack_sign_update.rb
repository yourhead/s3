#!/usr/bin/env ruby

require 'openssl'
require 'base64'

require 'net/http'
require 'uri'


# Should be exactly three arguments -- the data file, the pem file, and the password to the pem file
if (!ARGV[0] || !ARGV[1] || !ARGV[2])
	abort("usage: stack_sign_update [data_file.txt] [private_key_file.pem] [private_key_password]")
end

# Make sure it exists
filePath = ARGV[0]
if (!File.exist?(filePath))
	STDERR.puts "Data file does not exist"
	abort("usage: stack_sign_update [data_file.txt] [private_key_file.pem] [private_key_password]")
end

# Read the data file
data = File.read(filePath)
if (!data)
	STDERR.puts "Could not read data file"
	abort("usage: stack_sign_update [data_file.txt] [private_key_file.pem] [private_key_password]")
end





# Make sure it exists
keyPath = ARGV[1]
if (!File.exist?(keyPath))
	STDERR.puts "Private key file does not exist"
	abort("usage: stack_sign_update [data_file.txt] [private_key_file.pem] [private_key_password]")
end

# Read the data file
password = ARGV[2]
privateKey = OpenSSL::PKey::RSA.new File.read(keyPath), password
if (!privateKey)
	STDERR.puts "Could not read private key file"
	abort("usage: stack_sign_update [data_file.txt] [private_key_file.pem] [private_key_password]")
end






# hash the data
md5 = OpenSSL::Digest::MD5.new
hash = md5.digest(data)
if (!hash)
	abort("Could create md5 hash of the update info")
end

# use the stacks private key to encrypt the hash
hashEncrypted = privateKey.private_encrypt(hash)
if (!hashEncrypted)
	abort("Public-key encryption failed")
end

# write the results to standard out, using strict base64 (no newlines!)
puts(Base64.strict_encode64(hashEncrypted))

