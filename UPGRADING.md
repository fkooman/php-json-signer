# Upgrading

## 3.0.0

Only the tool syntax has changed. See [README](README.md).

## 2.0.0

In >= 2.0.0 the public and secret key are stored as binary, no longer Base64 
encoded. If you still have Base64 encoded keys, you can convert them:

```bash
    $ cd $HOME/.local/share/php-json-signer
    $ cat secret.key | base64 -d > S
    $ cat public.key | base64 -d > P
    $ mv S secret.key
    $ mv P public.key
```
