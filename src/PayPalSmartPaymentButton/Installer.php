<?php

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\EcommerceFrameworkBundle\PayPalSmartPaymentButton;

use OpenDxp\Bundle\EcommerceFrameworkBundle\Tools\PaymentProviderInstaller;

class Installer extends PaymentProviderInstaller
{
    protected string $bricksPath = __DIR__ . '/../../install/objectbrick_sources/';

    protected array $bricksToInstall = [
        'PaymentProviderPayPalSmartButton' => 'objectbrick_PaymentProviderPayPalSmartButton_export.json',
    ];
}
