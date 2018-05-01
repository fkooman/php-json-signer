#!/usr/bin/env php
<?php

/*
 * Copyright (c) 2017, 2018 François Kooman <fkooman@tuxed.net>
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

/** @psalm-suppress UnresolvableInclude */
require_once \sprintf('%s/vendor/autoload.php', \dirname(__DIR__));

use fkooman\JsonSigner\Signer;
use fkooman\JsonSigner\Xdg;

$appExec = $argv[0];
$syntaxMsg = <<<EOT
SYNTAX: 
    $appExec --sign   [--name N] file_1.json [file_2.json ... file_n.json]
    $appExec --verify [--name N] file_1.json [file_2.json ... file_n.json]
    $appExec --show   [--name N]
EOT;

try {
    if (2 > $argc) {
        throw new Exception($syntaxMsg);
    }

    $fileList = [];
    $nextIsName = false;
    $keyPairName = null;
    $appAction = null;
    for ($i = 1; $i < \count($argv); ++$i) {
        if ($nextIsName) {
            $keyPairName = $argv[$i];
            $nextIsName = false;
            continue;
        }
        if ('--sign' === $argv[$i]) {
            $appAction = 'sign';
            continue;
        }
        if ('--verify' === $argv[$i]) {
            $appAction = 'verify';
            continue;
        }
        if ('--show' === $argv[$i]) {
            $appAction = 'show';
            continue;
        }
        if ('--name' === $argv[$i]) {
            $nextIsName = true;
            continue;
        }

        $fileList[] = $argv[$i];
    }

    $dataDir = \sprintf('%s/php-json-signer', Xdg::getDataHome());
    $signer = new Signer(
        null === $keyPairName ? $dataDir : \sprintf('%s/%s', $dataDir, $keyPairName)
    );

    switch ($appAction) {
        case 'show':
            echo $signer->getPublicKey().PHP_EOL;
            break;
        case 'sign':
            foreach ($fileList as $fileName) {
                try {
                    $signer->sign($fileName);
                } catch (RuntimeException $e) {
                    echo \sprintf('ERROR: unable to sign "%s": %s', $fileName, $e->getMessage()).PHP_EOL;
                }
            }
            break;
        case 'verify':
            $failedAnywhere = false;
            foreach ($fileList as $fileName) {
                try {
                    if ($signer->verify($fileName)) {
                        echo \sprintf('OK: %s', $fileName).PHP_EOL;
                    } else {
                        $failedAnywhere = true;
                        echo \sprintf('FAIL: %s', $fileName).PHP_EOL;
                    }
                } catch (RuntimeException $e) {
                    echo \sprintf('ERROR: unable to verify "%s": %s', $fileName, $e->getMessage()).PHP_EOL;
                }
            }

            if ($failedAnywhere) {
                exit(1);
            }
            break;
        default:
            throw new Exception($syntaxMsg);
    }
} catch (Exception $e) {
    echo \sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
}
