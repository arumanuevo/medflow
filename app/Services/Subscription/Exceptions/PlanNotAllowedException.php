<?php
// app/Services/Subscription/Exceptions/PlanNotAllowedException.php

namespace App\Services\Subscription\Exceptions;

class PlanNotAllowedException extends SubscriptionException
{
    public function __construct(string $message, string $code = 'gate_blocked', array $context = [])
    {
        parent::__construct($message, $code, $context, 403);
    }
}