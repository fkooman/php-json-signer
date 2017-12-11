# Changelog

## 3.0.2 (2017-12-12)
- cleanup autoloading

## 3.0.1 (2017-12-11)
- rework (lib)sodium compatiblity
- remove dependency on `dnoegel/php-xdg-base-dir`
- support PHPUnit 6

## 3.0.0 (2017-11-20)
- support multiple key-pairs by using `--name NAME` flag
- wrap all tools in 1 executable and introduce `--sign`, `--verify` and 
  `--show` flags

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
