This application is used to sign JSON files, which are in turn verified by the
VPN applications.

# Install 
    
    $ git clone https://github.com/eduvpn/vpn-disco-signer.git
    $ cd vpn-disco-signer
    $ composer install

# Configure 

Generate a key pair:

    $ php bin/generate.php

# Sign

Sign a JSON file:

    $ php bin/sign.php instances.json

The field `seq` will be incremented by one, or added and set to `1` when it 
does not yet exist.

The `signed_at` field will be set to the current date/time (UTC).

A "detached" signature is generated with the extension `.sig`. From the 
example above there will be a (modified) `instances.json` with the `seq` and
`signed_at` fields, and a signature file `instances.json.sig`.
 
# Verify

Verify a JSON signature:

    $ php bin/verify.php instances.json

It is assumed that the signature file is placed in the same directory, i.e. 
`instance.json.sig` should be placed in the same directory as `instances.json`.

# Implementation

The PECL module [libsodium](https://paragonie.com/book/pecl-libsodium) is used. 
This module will be added to the default PHP installation starting with PHP
7.2.

This crypto library can be used on many platforms and in many programming 
languages and is considered very secure.
