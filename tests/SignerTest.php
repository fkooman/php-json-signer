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

namespace fkooman\JsonSigner\Tests;

use DateTime;
use fkooman\JsonSigner\Signer;
use PHPUnit_Framework_TestCase;

class SignerTest extends PHPUnit_Framework_TestCase
{
    public function testSign()
    {
        $signer = new Signer(
            sprintf('%s/config', __DIR__)
        );
        $signer->setDateTime(new DateTime('2017-01-01'));
        $fileName = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($fileName, file_get_contents(sprintf('%s/data/foo.json', __DIR__)));
        $signer->sign($fileName);
        $this->assertTrue($signer->verify($fileName));
        $this->assertSame(
            [
                'foo' => 'bar',
                'seq' => 1,
                'signed_at' => '2017-01-01 00:00:00',
            ],
            json_decode(file_get_contents($fileName), true)
        );
        $this->assertSame(
            'NGc7L2MGWhuw5MAk/Qp7v1JGhSfzFUOqDvo8YXyiIFV35jEPjI2AMn3KB1PKqvwy5lfgERq+48oC1mqfb/kjBg==',
            file_get_contents(sprintf('%s.sig', $fileName))
        );
    }

    public function testFailingSign()
    {
        $signer = new Signer(
            sprintf('%s/config', __DIR__)
        );
        $signer->setDateTime(new DateTime('2017-01-01'));
        $fileName = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($fileName, file_get_contents(sprintf('%s/data/foo.json', __DIR__)));
        $signer->sign($fileName);
        $jsonData = json_decode(file_get_contents($fileName), true);
        // increase the SEQ to break the signature...
        ++$jsonData['seq'];
        file_put_contents($fileName, json_encode($jsonData, JSON_PRETTY_PRINT));
        $this->assertFalse($signer->verify($fileName));
    }

    public function testInit()
    {
        $signer = new Signer(
            sprintf('%s/%s', sys_get_temp_dir(), mt_rand())
        );
        $signer->setDateTime(new DateTime('2017-01-01'));
        $signer->init();
        $fileName = tempnam(sys_get_temp_dir(), 'tst');
        file_put_contents($fileName, file_get_contents(sprintf('%s/data/foo.json', __DIR__)));
        $signer->sign($fileName);
        $this->assertTrue($signer->verify($fileName));
        $this->assertSame(
            [
                'foo' => 'bar',
                'seq' => 1,
                'signed_at' => '2017-01-01 00:00:00',
            ],
            json_decode(file_get_contents($fileName), true)
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDoubleInit()
    {
        $signer = new Signer(
            sprintf('%s/%s', sys_get_temp_dir(), mt_rand())
        );
        $signer->setDateTime(new DateTime('2017-01-01'));
        $signer->init();
        $signer->init();
    }
}
