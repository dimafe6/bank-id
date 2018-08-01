<?php

namespace Dimafe6\BankID\Model;

/**
 * Class CertInfo
 *
 * notBefore and notAfter are the number of milliseconds since the UNIX Epoch, a.k.a.
 * "UNIX time" in milliseconds. It was chosen over ISO8601 for its simplicity and lack of error
 * prone conversions to/from string representations on the server and client side.
 *
 * @property string notBefore Start of validity of the users BankID. Unix ms.
 * @property string notAfter End of validity of the Users BankID. Unix ms.
 */
class CertInfo
{

}
