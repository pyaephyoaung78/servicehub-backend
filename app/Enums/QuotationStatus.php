<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
}