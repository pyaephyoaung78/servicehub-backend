<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case OnTheWay = 'on_the_way';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
}