#!/usr/bin/env php
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
$autoloadFiles = [
    sprintf('%s/src/autoload.php', dirname(__DIR__)),
    sprintf('%s/vendor/autoload.php', dirname(__DIR__)),
];

foreach ($autoloadFiles as $autoloadFile) {
    if (@file_exists($autoloadFile)) {
        require_once $autoloadFile;
        break;
    }
}

use fkooman\JsonSigner\Signer;
use XdgBaseDir\Xdg;

try {
    if (2 > $argc) {
        throw new Exception(sprintf('SYNTAX: %s file_1.json [file_2.json ... file_n.json]', $argv[0]));
    }

    $xdg = new Xdg();
    $signer = new Signer(
        sprintf('%s/php-json-signer', $xdg->getHomeDataDir())
    );

    $failedAnywhere = false;

    for ($i = 1; $i < count($argv); ++$i) {
        try {
            if ($signer->verify($argv[$i])) {
                echo sprintf('OK: %s', $argv[$i]).PHP_EOL;
            } else {
                $failedAnywhere = true;
                echo sprintf('FAIL: %s', $argv[$i]).PHP_EOL;
            }
        } catch (RuntimeException $e) {
            echo sprintf('ERROR: unable to verify "%s": %s', $argv[$i], $e->getMessage()).PHP_EOL;
        }
    }

    if ($failedAnywhere) {
        exit(1);
    }
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
}
