<?php

namespace App\Modules\Payment\Domain\Event;

enum DomainPaymentEventType: string
{
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case REQUIRES_ACTION = 'requires_action';
    case REFUND_SUCCEEDED = 'refund_succeeded';
    case IGNORED = 'ignored';
}
