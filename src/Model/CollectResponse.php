<?php

namespace Dimafe6\BankID\Model;

/**
 * Class CollectResponse
 *
 * Response from collect method
 *
 * @property string $orderRef The orderRef in question
 * @property string status The order status.
 *      pending: The order is being processed. hintCode describes the status of the order.
 *      failed: Something went wrong with the order. hintCode describes the error.
 *      complete: The order is complete. completionData holds user information
 * @property string hintCode Only present for pending and failed orders.
 * @property CompletionData completionData Only present for complete orders.
 */
class CollectResponse extends AbstractResponseModel
{
    const STATUS_COMPLETED = 'complete';
    const STATUS_PENDING = 'pending';
    const STATUS_FAILED = 'failed';

    const HINT_COMPLETED = 'complete';

    const HINT_PENDING_OUTSTANDING_TRANSACTION = 'outstandingTransaction';
    const HINT_PENDING_NO_CLIENT = 'noClient';
    const HINT_PENDING_STARTED = 'started';
    const HINT_PENDING_USER_SIGN = 'userSign';

    const HINT_FAILED_EXPIRED_TRANSACTION = 'expiredTransaction';
    const HINT_FAILED_CERTIFICATE_ERR = 'certificateErr';
    const HINT_FAILED_USER_CANCEL = 'userCancel';
    const HINT_FAILED_CANCELLED = 'cancelled';
    const HINT_FAILED_START_FAILED = 'startFailed';
}
