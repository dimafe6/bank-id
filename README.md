Bank-ID
=======

Library for connect Swedish BankID to your application.

[![codecov](https://codecov.io/gh/dimafe6/bank-id/branch/dev/graph/badge.svg)](https://codecov.io/gh/dimafe6/bank-id)
[![Latest Stable Version](https://poser.pugx.org/dimafe6/bank-id/v/stable)](https://packagist.org/packages/dimafe6/bank-id)
[![Latest Unstable Version](https://poser.pugx.org/dimafe6/bank-id/v/unstable)](https://packagist.org/packages/dimafe6/bank-id)
[![Total Downloads](https://poser.pugx.org/dimafe6/bank-id/downloads)](https://packagist.org/packages/dimafe6/bank-id)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](LICENSE.md)
[![Build Status](https://travis-ci.org/dimafe6/bank-id.svg?branch=dev)](https://travis-ci.org/dimafe6/bank-id)

## Requirements

* PHP 5.6+ or 7.0+
* [soap-client](http://php.net/manual/class.soapclient.php)

## Install

Via Composer

``` bash
$ composer require dimafe6/bank-id
```

## Usage

```php
<?php
// Create BankIDService
$bankIDService = new BankIDService(
    'https://appapi2.test.bankid.com/rp/v4?wsdl',
    ['local_cert' => 'PATH_TO_TEST_CERT.pem'],
    false
);

// Signing. Step 1 - Get orderRef
$response = $bankIDService->getSignResponse('PERSONAL_NUMBER', 'Test user data');

// Signing. Step 2 - Collect orderRef. 
// Repeat until $collectResponse->progressStatus == CollectResponse::PROGRESS_STATUS_COMPLETE
$collectResponse = $bankIDService->collectResponse($response->orderRef);
if($collectResponse->progressStatus == CollectResponse::PROGRESS_STATUS_COMPLETE) {
    return true; //Signed successfully
}

// Authorize. Step 1 - Get orderRef
$response = $bankIDService->getAuthResponse('PERSONAL_NUMBER');

// Authorize. Step 2 - Collect orderRef. 
// Repeat until $authResponse->progressStatus == CollectResponse::PROGRESS_STATUS_COMPLETE
$authResponse = $bankIDService->collectResponse($response->orderRef);
if($authResponse->progressStatus == CollectResponse::PROGRESS_STATUS_COMPLETE) {
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
