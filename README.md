Bank-ID
=======

Library for connect Swedish BankID to your application.
This library implements BankID API V5. If your needed library for a BankID API V4 please use version 1.*

[![codecov](https://codecov.io/gh/dimafe6/bank-id/branch/dev/graph/badge.svg)](https://codecov.io/gh/dimafe6/bank-id)
[![Latest Stable Version](https://poser.pugx.org/dimafe6/bank-id/v/stable)](https://packagist.org/packages/dimafe6/bank-id)
[![Latest Unstable Version](https://poser.pugx.org/dimafe6/bank-id/v/unstable)](https://packagist.org/packages/dimafe6/bank-id)
[![Total Downloads](https://poser.pugx.org/dimafe6/bank-id/downloads)](https://packagist.org/packages/dimafe6/bank-id)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/dimafe6/bank-id.svg?branch=dev)](https://travis-ci.org/dimafe6/bank-id)

## Requirements

* PHP 5.6+ or 7.0+
* [curl](http://php.net/manual/en/book.curl.php)

## Install

Via Composer

``` bash
$ composer require dimafe6/bank-id
```

## Usage
Either you let the user enter their personal number and pass to BankID or you only pass the order ref and receive the
personal number from the response from BankID.

```php
<?php
// Create BankIDService
$bankIDService = new BankIDService(
    'https://appapi2.test.bankid.com/rp/v5/',
    $_SERVER["REMOTE_ADDR"],
    [
        'verify' => false,
        'cert'   => 'PATH_TO_TEST_CERT.pem',
    ]
);

// OR Create BankIDService with ssl verification
$bankIDService = new BankIDService(
    'https://appapi2.test.bankid.com/rp/v5/',
    $_SERVER["REMOTE_ADDR"],
    [
        'verify' =>  'PATH_TO_TEST_CERT.ca',
        'cert'   => 'PATH_TO_TEST_CERT.crt',
        'ssl_key'   => 'PATH_TO_TEST_CERT.key',
    ]
);
```

##### Example with personal number
```php
// Signing. Step 1 - Get orderRef
/** @var OrderResponse $response */
$response = $bankIDService->getSignResponse('PERSONAL_NUMBER', 'User visible data');

// Signing. Step 2 - Collect orderRef. 
// Repeat until $collectResponse->status !== CollectResponse::STATUS_COMPLETED
$collectResponse = $bankIDService->collectResponse($response->orderRef);
if($collectResponse->status === CollectResponse::STATUS_COMPLETED) {
    return true; //Signed successfully
}

// Authorize. Step 1 - Get orderRef
$response = $bankIDService->getAuthResponse('PERSONAL_NUMBER');

// Authorize. Step 2 - Collect orderRef. 
// Repeat until $authResponse->status !== CollectResponse::STATUS_COMPLETED
$authResponse = $bankIDService->collectResponse($response->orderRef);
if($authResponse->status == CollectResponse::STATUS_COMPLETED) {
    return true; //Authorized
}

// Cancel auth or collect order
// Authorize. Step 1 - Get orderRef
$response = $bankIDService->getAuthResponse('PERSONAL_NUMBER');

// Cancel authorize order
if($bankIDService->cancelOrder($response->orderRef)) {
    return 'Authorization canceled';
}
```

##### Example without personal number
```php
// Authorize. Step 1 - Get orderRef
$response = $bankIDService->getAuthResponse();

// Authorize. Step 2 - Collect orderRef. 
// Repeat until $authResponse->status !== CollectResponse::STATUS_COMPLETED
$authResponse = $bankIDService->collectResponse($response->orderRef);
if($authResponse->status == CollectResponse::STATUS_COMPLETED) {
    echo $authResponse->completionData->user->personalNumber;
    return true; //Authorized
}
```

## Testing

1. Copy phpunit.xml.dist to phpunit.xml
``` bash
$ cp phpunit.xml.dist phpunit.xml
```

2. Execute

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
