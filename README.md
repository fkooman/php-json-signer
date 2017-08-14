This application can be used to sign JSON files, by adding some fields that can
be used to determine their time of signing and the sequence number. The 
signature is "detached" so no complicated file syntax is needed to store the 
signature in the file itself.

# Install 
    
    $ git clone https://github.com/fkooman/json-signer.git
    $ cd json-signer
    $ composer install

# Configure 

Generate a keypair:

    $ php bin/init.php

If a keypair already exists, an error will be thrown. You can force generating
a new keypair using the `--force` parameter, or delete the keys first.

The public and private key are stored in the XDG home data folder, typically
this will be `${HOME}/.local/share/fkooman-json-signer`.

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

# Show 

To view the public key that is used to sign the JSON files:

    $ php bin/show-public-key.php

# Implementation

The PECL module [libsodium](https://paragonie.com/book/pecl-libsodium) is used. 
This module will be added to the default PHP installation starting with PHP
7.2.

This crypto library can be used on many platforms and in many programming 
languages and is considered very secure.
