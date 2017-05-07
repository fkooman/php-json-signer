<?php
/**
 *  Copyright (C) 2017 SURFnet.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SURFnet\VPN\Signer\Tests;

use DateTime;
use PHPUnit_Framework_TestCase;
use SURFnet\VPN\Signer\Signer;

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
