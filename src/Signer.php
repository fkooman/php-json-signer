<?php

/*
 * Copyright (c) 2019 François Kooman <fkooman@tuxed.net>
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
    private $dataDir;

    /** @var \DateTime */
    private $dateTime;

    /**
     * @param string $dataDir
     * @param bool   $createKey
     */
    public function __construct($dataDir, $createKey)
    {
        $this->dataDir = $dataDir;
        $this->dateTime = new DateTime();
        $this->init($createKey);
    }

    /**
     * @param bool $createKey
     *
     * @return void
     */
    public function init($createKey)
    {
        $secretKeyFile = sprintf('%s/%s', $this->dataDir, self::SECRET_KEY_FILE);
        $publicKeyFile = sprintf('%s/%s', $this->dataDir, self::PUBLIC_KEY_FILE);
        if (!self::hasFile($secretKeyFile)) {
            if (!$createKey) {
                throw new RuntimeException(sprintf('key "%s" does not exist', $secretKeyFile));
            }
            self::createDir($this->dataDir);
            $keyPair = sodium_crypto_sign_keypair();
            self::writeFile($secretKeyFile, sodium_crypto_sign_secretkey($keyPair));
            self::writeFile($publicKeyFile, sodium_crypto_sign_publickey($keyPair));
        }
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
        $secretKey = self::readFile(sprintf('%s/%s', $this->dataDir, self::SECRET_KEY_FILE));

        // read the JSON data
        $jsonData = Json::decode(self::readFile($fileName));

        // increment the "seq" key or add it if it was not there
        if (!\array_key_exists('seq', $jsonData)) {
            $jsonData['seq'] = 0;
        }
        ++$jsonData['seq'];

        // add time of signing (UTC)
        $jsonData['signed_at'] = $this->dateTime->format('Y-m-d H:i:s');

        // write the JSON back to file
        $jsonText = Json::encode($jsonData);
        self::writeFile($fileName, $jsonText);

        // calculate the signature and write it to file
        $fileSignature = sodium_crypto_sign_detached($jsonText, $secretKey);
        self::writeFile(sprintf('%s.sig', $fileName), Base64::encode($fileSignature));
    }

    /**
     * @param string $fileName the path to the JSON file to verify
     *
     * @return bool
     */
    public function verify($fileName)
    {
        $publicKey = self::readFile(sprintf('%s/%s', $this->dataDir, self::PUBLIC_KEY_FILE));

        // read the jsonText
        $jsonText = self::readFile($fileName);
        // read the signature
        $fileSignature = Base64::decode(self::readFile(sprintf('%s.sig', $fileName)));

        return sodium_crypto_sign_verify_detached($fileSignature, $jsonText, $publicKey);
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return Base64::encode(self::readFile(sprintf('%s/%s', $this->dataDir, self::PUBLIC_KEY_FILE)));
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
