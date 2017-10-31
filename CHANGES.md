# Changelog

## 2.1.0 (2017-10-31)
- support PHP 7.2 by using `SodiumCompat`

## 2.0.0 (2017-10-30)
- public and secret key are automatically generated on first use of 
  application or if they no longer exist, see [UPGRADING](UPGRADING.md)
- public and secret key are now stored as binary, not as Base64 encoded string

## 1.0.1 (2017-08-22)
- support wildcard parameters to sign/verify multiple files at once

## 1.0.0 (2017-08-14)
- initial release
