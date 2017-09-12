<?php

/**
 * Copyright (c) 2017 François Kooman <fkooman@tuxed.net>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace fkooman\JsonSigner;

use DateTime;
use ParagonIE\ConstantTime\Base64;
use RuntimeException;

class Signer
{
    const SECRET_KEY_FILE = 'secret.key';
    const PUBLIC_KEY_FILE = 'public.key';

    /** @var string */
    private $configDir;

    /** @var \DateTime */
    private $dateTime;

    /**
     * @param string $configDir the directory that contains/will contain the public/private key
     */
    public function __construct($configDir)
    {
        self::createDir($configDir);
        $this->configDir = $configDir;
        $this->dateTime = new DateTime();
    }

    /**
     * @param bool $forceOverwrite
     *
     * @return string
     */
    public function init($forceOverwrite = false)
    {
        $secretKeyFile = sprintf('%s/%s', $this->configDir, self::SECRET_KEY_FILE);
        if (self::hasFile($secretKeyFile) && !$forceOverwrite) {
            throw new RuntimeException(sprintf('"%s" already exists, use "--force" to overwrite', $secretKeyFile));
        }
        $publicKeyFile = sprintf('%s/%s', $this->configDir, self::PUBLIC_KEY_FILE);

        $keyPair = \Sodium\crypto_sign_keypair();
        $encodedPublicKey = Base64::encode(\Sodium\crypto_sign_publickey($keyPair));
        $encodedSecretKey = Base64::encode(\Sodium\crypto_sign_secretkey($keyPair));
        self::writeFile($secretKeyFile, $encodedSecretKey);
        self::writeFile($publicKeyFile, $encodedPublicKey);

        return $encodedPublicKey;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return void
     */
    public function setDateTime(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param string $fileName the path to the JSON file to sign
     *
     * @return void
     */
    public function sign($fileName)
    {
        $secretKey = Base64::decode(self::readFile(sprintf('%s/%s', $this->configDir, self::SECRET_KEY_FILE)));

        // read the JSON data
        $jsonData = self::jsonDecode(self::readFile($fileName));

        // increment the "seq" key or add it if it was not there
        if (!array_key_exists('seq', $jsonData)) {
            $jsonData['seq'] = 0;
        }
        $jsonData['seq'] += 1;

        // add time of signing (UTC)
        $jsonData['signed_at'] = $this->dateTime->format('Y-m-d H:i:s');

        // write the JSON back to file
        $jsonText = self::jsonEncode($jsonData);
        self::writeFile($fileName, $jsonText);

        // calculate the signature and write it to file
        $fileSignature = \Sodium\crypto_sign_detached($jsonText, $secretKey);
        self::writeFile(sprintf('%s.sig', $fileName), Base64::encode($fileSignature));
    }

    /**
     * @param string $fileName the path to the JSON file to verify
     *
     * @return bool
     */
    public function verify($fileName)
    {
        $publicKey = Base64::decode(self::readFile(sprintf('%s/%s', $this->configDir, self::PUBLIC_KEY_FILE)));

        // read the jsonText
        $jsonText = self::readFile($fileName);
        // read the signature
        $fileSignature = Base64::decode(self::readFile(sprintf('%s.sig', $fileName)));

        return \Sodium\crypto_sign_verify_detached($fileSignature, $jsonText, $publicKey);
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return self::readFile(sprintf('%s/%s', $this->configDir, self::PUBLIC_KEY_FILE));
    }

    /**
     * @param string $fileName
     *
     * @return bool
     */
    private static function hasFile($fileName)
    {
        return @file_exists($fileName);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    private static function readFile($fileName)
    {
        if (false === $fileContent = @file_get_contents($fileName)) {
            throw new RuntimeException(sprintf('unable to read "%s"', $fileName));
        }

        return $fileContent;
    }

    /**
     * @param string $fileName
     * @param string $fileContent
     *
     * @return void
     */
    private static function writeFile($fileName, $fileContent)
    {
        if (false === @file_put_contents($fileName, $fileContent)) {
            throw new RuntimeException(sprintf('unable to write "%s"', $fileName));
        }
    }

    /**
     * @param string $jsonText
     *
     * @return array
     */
    private static function jsonDecode($jsonText)
    {
        if (null === $jsonData = json_decode($jsonText, true)) {
            throw new RuntimeException('unable to decode JSON');
        }

        return $jsonData;
    }

    /**
     * @param array $jsonData
     *
     * @return string
     */
    private static function jsonEncode(array $jsonData)
    {
        if (false === $jsonText = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) {
            throw new RuntimeException('unable to encode JSON');
        }

        return $jsonText;
    }

    /**
     * @param string $dirName
     *
     * @return void
     */
    private static function createDir($dirName)
    {
        if (!@file_exists($dirName)) {
            if (false === @mkdir($dirName, 0700, true)) {
                throw new RuntimeException(sprintf('unable to create directory "%s"', $dirName));
            }
        }
    }
}
