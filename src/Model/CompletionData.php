<?php

namespace Dimafe6\BankID\Model;

/**
 * Class CompletionData
 *
 * @property UserInfo user Information related to the user
 * @property DeviceInfo device Information related to the user
 * @property CertInfo cert Information related to the users certificate (BankID)
 * @property string signature The signature. The content of the signature is described in BankID Signature Profile specification.
 * @property string ocspResponse The OCSP response. String. Base64-encoded. The OCSP response is signed by a certificate that
 *      has the same issuer as the certificate being verified. The OSCP response has an extension for
 *      Nonce. The nonce is calculated as:
 *          - SHA-1 hash over the base 64 XML signature encoded as UTF-8.
 *          - 12 random bytes is added after the hash
 *          - The nonce is 32 bytes (20 + 12)
 */
class CompletionData
{

}
