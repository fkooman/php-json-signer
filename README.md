**Summary**: Sign JSON files with Ed25519 signature for eduVPN

**Description**: This application can be used to sign JSON files, by adding 
some fields that can be used to determine their time of signing and the 
sequence number. The signature is "detached" so no complicated file syntax is 
needed to store the signature in the file itself.

**License**: MIT

# Introduction

This application can be used to sign JSON files, by adding some fields that can
be used to determine their time of signing and the sequence number. The 
signature is "detached" so no complicated file syntax is needed to store the 
signature in the file itself.

# Install 
    
```bash
    $ git clone https://git.tuxed.net/fkooman/php-json-signer
    $ cd php-json-signer
    $ composer install
```

# Configure 

The public and secret key are stored in the XDG home data folder, typically
this will be `${HOME}/.local/share/php-json-signer`.

# Sign

Create a new key, and sign a JSON file:

```bash
    $ echo '{"foo": "bar"}' > foo.json
    $ php bin/app.php --create --name foo --sign foo.json
```

This adds some additional fields:

```bash
    $ cat foo.json
    {
        "foo": "bar",
        "seq": 1,
        "signed_at": "2017-08-14 21:17:39"
    }
```

The field `seq` will be incremented by one, or added and set to `1` when it 
does not yet exist.

The `signed_at` field will be set to the current date/time (UTC).

A "detached" signature is generated with the extension `.sig`. From the 
example above there will be a (modified) `foo.json` with the `seq` and
`signed_at` fields, and a signature file `foo.json.sig`.
 
# Verify

Verify a JSON signature:

```bash
    $ php bin/app.php --name foo --verify foo.json
```

It is assumed that the signature file is placed in the same directory, i.e. 
`foo.json.sig` should be placed in the same directory as `foo.json`.

# Show 

To view the public key that is used to sign the JSON files:

```bash
    $ php bin/app.php --name foo --show
```

# Implementation

The PECL module [libsodium](https://paragonie.com/book/pecl-libsodium) is used. 
This module will be added to the default PHP installation starting with PHP
7.2.

This crypto library can be used on many platforms and in many programming 
languages and is considered very secure.
