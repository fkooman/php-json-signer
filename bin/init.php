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

try {
    $forceOverwrite = false;
    foreach ($argv as $arg) {
        if ('--force' === $arg) {
            $forceOverwrite = true;
        }
    }
    $signer = new Signer(
        sprintf('%s/config', dirname(__DIR__))
    );

    $publicKey = $signer->init($forceOverwrite);

    echo sprintf('Public Key: %s', $publicKey).PHP_EOL;
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
}
