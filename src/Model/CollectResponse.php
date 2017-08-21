<?php

namespace Dimafe6\BankID\Model;

/**
 * Class CollectResponse
 *
 * @category PHP
 * @package  Dimafe6\BankID\Model
 * @author   Dmytro Feshchenko <dimafe2000@gmail.com>
 */
class CollectResponse
{
    const PROGRESS_STATUS_OUTSTANDING_TRANSACTION = 'OUTSTANDING_TRANSACTION';
    const PROGRESS_STATUS_NO_CLIENT = 'NO_CLIENT';
    const PROGRESS_STATUS_STARTED = 'STARTED';
    const PROGRESS_STATUS_USER_SIGN = 'USER_SIGN';
    const PROGRESS_STATUS_USER_REQ = 'USER_REQ';
    const PROGRESS_STATUS_COMPLETE = 'COMPLETE';

    /**
     * @var string
     */
    public $progressStatus;

    /**
     * String (b64). XML-signature. (If the order is COMPLETE). The content of the
     * signature is described in BankID Signature Profile specification.
     *
     * @var string
     */
    public $signature;

    /**
     * UserInfoType (If the order is COMPLETE)
     *
     * @var string
     */
    public $userInfo;

    /**
     * String (b64). OCSP-response (If the order is COMPLETE). The OCSP response
     * is signed by a certificate that has the same issuer as the certificate
     * being verified. The OSCP response has an extension for Nonce.
     * The nonce is calculated as:
     * SHA-1 hash over the base 64 XML signature encoded as UTF-8.
     * 12 random bytes is added after the hash
     * The nonce is 32 bytes (20 + 12)
     *
     * @var string
     */
    public $ocspResponse;
}
