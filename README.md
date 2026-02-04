# OpenDXP E-Commerce Framework Payment Provider - PayPal Smart Payment Buttons

***

## Disclaimer

> OpenDXP is a community-driven fork based on the Pimcore® Community Edition (GPLv3).  
> OpenDXP is independent and maintained by its community and contributors.
> It is not affiliated with, endorsed by, or sponsored by Pimcore GmbH.   
> Original credits: [Pimcore GmbH](https://www.pimcore.com)

**OpenDXP E-Commerce Framework Payment Provider - PayPal Smart Payment Buttons is based on the Pimcore® Community Edition and remains licensed under GPLv3.**

***

## Installation

Install latest version with Composer:
```bash 
composer require open-dxp/payment-provider-paypal-smart-payment-button-bundle
```

Enable bundle via console:
```bash
php bin/console opendxp:bundle:enable OpenDxpPaymentProviderPayPalSmartPaymentButtonBundle
php bin/console opendxp:bundle:install OpenDxpPaymentProviderPayPalSmartPaymentButtonBundle
```

For configuration details see further below. For additional information of PayPal API 
credentials see [API Docs](https://developer.paypal.com/docs/api/overview/) 

## Configuration
The Payment Manager is responsible for implementation
of different Payment Provider to integrate them into the framework. 

For more information about Payment Manager, see 
[Payment Manager Docs](https://github.com/open-dxp/opendxp/blob/1.x/doc/Development_Documentation/10_E-Commerce_Framework/13_Checkout_Manager/07_Integrating_Payment.md). 

Configure payment provider in the `opendxp_ecommerce_config.payment_manager` config section: 
```yaml
opendxp_ecommerce_framework:
    payment_manager:
        payment_manager_id: OpenDxp\Bundle\EcommerceFrameworkBundle\PaymentManager\PaymentManager

        providers:
            paypal:
                provider_id: OpenDxp\Bundle\EcommerceFrameworkBundle\PaymentManager\Payment\PayPalSmartPaymentButton
                profile: sandbox
                profiles:
                    sandbox:
                        client_id: <YOUR PAYPAL REST API CLIENT ID>
                        client_secret: <YOUR PAYPAL REST API CLIENT SECRET>
                        
                        # defines, if payment caputure should take place automatic or manual, default is automatic
                        capture_strategy: automatic   
                        
                        # defines mode of PayPal API, default value is sandbox  
                        mode: sandbox                 
                        
                        # defines PayPal application context for shipping, default value is NO_SHIPPING
                        # see https://developer.paypal.com/docs/api/orders/v2/#definition-application_context 
                        shipping_preference: NO_SHIPPING

                        # defines PayPal application context for user action, default value is PAY_NOW
                        # see https://developer.paypal.com/docs/api/orders/v2/#definition-application_context                        
                        user_action: PAY_NOW

                    live:
                        client_id: <YOUR PAYPAL REST API CLIENT ID>
                        client_secret: <YOUR PAYPAL REST API CLIENT SECRET>
                        mode: live
```

## Implementation
Integrate the PayPal payment button to your view template

Integrate PayPal payment button and overwrite a few methods like in the sample. At least
`createOrder` and `onApprove` need to be overwritten.  

```php

    <?php
        /** @var \OpenDxp\Bundle\EcommerceFrameworkBundle\PaymentManager\Payment\PayPalSmartPaymentButton $payment */
     ?>
    <script src="<?= $payment->buildPaymentSDKLink() ?>">
    </script>

    <div id="paypal-button-container"></div>
    <script>
        paypal.Buttons({
            onCancel: function (data) {
                // e.g. redirect to a certain page or show message
                window.location.replace('...');
            },
            createOrder: function() {
                return fetch('/path/to/your/startPaymentAction', {
                    method: 'post',
                    headers: {
                        'content-type': 'application/json'
                    }
                }).then(function(res) {
                    return res.json();
                }).then(function(data) {
                    return data.id;
                });
            },
            onApprove: function(data) {
                
                // make sure you deliver orderID, payerID and paymentID to your 
                // handle response controller action, e.g. by creating a form and 
                // posting the data
                var form = document.createElement('form');
                document.body.appendChild(form);
                form.method = 'POST';
                form.action = '/path/to/your/handleResponseAction';

                var orderID = document.createElement('input');
                orderID.type = 'hidden';
                orderID.name = 'orderID';
                orderID.value = data['orderID'];
                form.appendChild(orderID);

                var payerID = document.createElement('input');
                payerID.type = 'hidden';
                payerID.name = 'payerID';
                payerID.value = data['payerID'];
                form.appendChild(payerID);

                var paymentID = document.createElement('input');
                paymentID.type = 'hidden';
                paymentID.name = 'paymentID';
                paymentID.value = data['paymentID'];
                form.appendChild(paymentID);

                form.submit();
            }
        }).render('#paypal-button-container');
    </script>


```

4) Create a startPaymentAction in your controller

Initialize checkout manager, call `startOrderPaymentWithPaymentProvider` of the payment 
implementation. It is creating an order at PayPal and its response is the default PayPal 
response, which need to be returned as a json response of the action.  

```php

//in your payment controller, e.g. startPaymentAction

public function startPaymentAction() {
    
    // ... some other stuff
    
        
        $paymentConfig = new AbstractRequest($config);
        
        $response = $checkoutManager->startOrderPaymentWithPaymentProvider($paymentConfig);
    
    
    $checkoutManager = Factory::getInstance()->getCheckoutManager($cart);
    $paymentInformation = $checkoutManager->initOrderPayment();
    $payment = $checkoutManager->getPayment();
    
    $config = [
        'return_url' => $returnUrl,
        'cancel_url' => $cancelUrl . 'payment?error=cancel',
        'OrderDescription' => 'My Order ' . $order->getOrdernumber() . ' at opendxp.io',
        'InternalPaymentId' => $paymentInformation->getInternalPaymentId()
    ];
    
    $paymentConfig = new AbstractRequest($config);
    
    $response = $checkoutManager->startOrderPaymentWithPaymentProvider($paymentConfig);
    return new \Symfony\Component\HttpFoundation\JsonResponse($response->getJsonString(), 200, [], true);

} 

```

5) Handle Response of PayPal

In handle response just call `handlePaymentResponseAndCommitOrderPayment` of checkout manager.
It does the rest - which is checking at PayPal if payment was authorized by the payer and 
committing the order.

Depending on your settings (see below), the payment is also automatically captured. If not
you need to capture the payment manually by calling `$payment->executeDebit()`.  

```php

public function handleResponseAction() {

    // ... do some stuff 
    
    $checkoutManager = Factory::getInstance()->getCheckoutManager($cart);
    $params = array_merge($request->query->all(), $request->request->all());

    $order = $checkoutManager->handlePaymentResponseAndCommitOrderPayment($params);
    
    // optional to clear payment
    // if this call is necessary depends on payment provider and configuration.
    // its possible to execute this later (e.g. when shipment is done)
//  $payment = $checkoutManager->getPayment();
//  $paymentStatus = $payment->executeDebit();
//  $orderAgent = Factory::getInstance()->getOrderManager()->createOrderAgent($order);
//  $orderAgent->updatePayment($paymentStatus);    
    
    // ... check order state and redirect user to error page or order success page
    
} 
```

### Further Information

For further information and/or more custom integrations, also have a look at following resources:
* [PayPal Developer Documentation](https://developer.paypal.com/docs/checkout/)
* [PayPal Rest APIs](https://developer.paypal.com/docs/api/overview/)
* [PayPal PHP SDK](https://github.com/paypal/PayPal-PHP-SDK)

***

## Upstream Origin & Version Transparency
This project is a fork of the [Pimcore payment-provider-paypal-smart-payment-button (9992d59 / v2.0.1)](https://github.com/pimcore/payment-provider-paypal-smart-payment-button/tree/9992d59badb8f8c04b8193ac0c224e4891adcc77), which is © Pimcore GmbH and licensed under GPLv3.

## License
Licensed under the GNU General Public License v3.0 (GPLv3). For details, please see [LICENSE.md](LICENSE.md).

## Copyright
© Pimcore GmbH  
© 2025 OpenDXP Contributors — GPLv3

## Trademarks
Pimcore® is a registered [trademark](https://www.trademarkelite.com/europe/trademark/trademark-detail/009309841/PIMCORE) of Pimcore GmbH.
Any use of the Pimcore® mark in this repository is purely descriptive to identify the original upstream project.

***

## Contact
For inquiries, suggestions, or contributions, feel free to reach us at contact@opendxp.ch.

## About
OpenDXP is a community-driven project initiated by [DACHCOM.DIGITAL](https://www.dachcom.com/de-ch) (Rheineck, Switzerland) and maintained by its community and contributors.
OpenDXP is independent and not affiliated with Pimcore GmbH.

The project’s purpose is to preserve and maintain a GPLv3‑licensed codebase for community use.

It is **not positioned as a competitor** to products or services of Pimcore GmbH and does **not** purport to replace or supersede any Pimcore offering.   
