This application can be used to sign JSON files, by adding some fields that can
be used to determine their time of signing and the sequence number. The 
signature is "detached" so no complicated file syntax is needed to store the 
signature in the file itself.

# Install 
    
    $ git clone https://github.com/fkooman/php-json-signer.git
    $ cd php-json-signer
    $ composer install

# Configure 

The public and secret key are stored in the XDG home data folder, typically
this will be `${HOME}/.local/share/php-json-signer`. They are automatically 
generated on first use of the application.

# Sign

Sign a JSON file:

    $ echo '{"foo": "bar"}' > foo.json
    $ php bin/app.php --sign foo.json

This adds some additional fields:

    $ cat foo.json
    {
        "foo": "bar",
        "seq": 1,
        "signed_at": "2017-08-14 21:17:39"
    }

The field `seq` will be incremented by one, or added and set to `1` when it 
does not yet exist.

The `signed_at` field will be set to the current date/time (UTC).

A "detached" signature is generated with the extension `.sig`. From the 
example above there will be a (modified) `foo.json` with the `seq` and
`signed_at` fields, and a signature file `foo.json.sig`.
 
# Verify

Verify a JSON signature:

    $ php bin/app.php --verify foo.json

It is assumed that the signature file is placed in the same directory, i.e. 
`foo.json.sig` should be placed in the same directory as `foo.json`.

# Show 

To view the public key that is used to sign the JSON files:

    $ php bin/app.php --show

# Multi Key

It is possible to use multiple keys for signing and verifying. You can use 
the flag `--name` with the name you want to use as extra parameter, e.g.:

    $ php bin/app.php --sign --name production foo.json

The `--name` flag also works for `--verify` and `--show`. Not specifying 
the `--name` flag will fall back to the "default" key.

# Implementation

The PECL module [libsodium](https://paragonie.com/book/pecl-libsodium) is used. 
This module will be added to the default PHP installation starting with PHP
7.2.

This crypto library can be used on many platforms and in many programming 
languages and is considered very secure.
