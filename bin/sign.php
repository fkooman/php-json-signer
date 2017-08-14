#!/usr/bin/env php
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
require_once sprintf('%s/vendor/autoload.php', dirname(__DIR__));

use SURFnet\VPN\Signer\Signer;
use XdgBaseDir\Xdg;

try {
    if (2 > $argc) {
        throw new Exception(sprintf('SYNTAX: %s file.json', $argv[0]));
    }

    $xdg = new Xdg();
    $signer = new Signer(
        sprintf('%s/fkooman-json-signer', $xdg->getHomeDataDir())
    );

    $signer->sign($argv[1]);
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
}
