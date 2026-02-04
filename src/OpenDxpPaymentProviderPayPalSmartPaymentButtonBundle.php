<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace OpenDxp\Bundle\EcommerceFrameworkBundle;

use OpenDxp\Bundle\EcommerceFrameworkBundle\DependencyInjection\OpenDxpPaymentProviderPayPalSmartPaymentButtonExtension;
use OpenDxp\Bundle\EcommerceFrameworkBundle\PayPalSmartPaymentButton\Installer;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class OpenDxpPaymentProviderPayPalSmartPaymentButtonBundle extends AbstractOpenDxpBundle
{
    use PackageVersionTrait;

    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new OpenDxpPaymentProviderPayPalSmartPaymentButtonExtension();
        }

        return $this->extension;
    }

    protected function getComposerPackageName(): string
    {
        return 'open-dxp/payment-provider-paypal-smart-payment-button-bundle';
    }

    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }
}
