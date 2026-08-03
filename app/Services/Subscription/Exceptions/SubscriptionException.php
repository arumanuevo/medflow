<?php
// app/Services/Subscription/Exceptions/SubscriptionException.php

namespace App\Services\Subscription\Exceptions;

use Exception;

class SubscriptionException extends Exception
{
    protected array $context = [];

    public function __construct(string $message, string $code = 'subscription_error', array $context = [], int $statusCode = 403)
    {
        parent::__construct($message, $statusCode);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}

// app/Services/Subscription/Exceptions/LimitExceededException.php

namespace App\Services\Subscription\Exceptions;

class LimitExceededException extends SubscriptionException
{
    public function __construct(string $message, string $code = 'limit_exceeded', array $context = [])
    {
        parent::__construct($message, $code, $context, 403);
    }
}

// app/Services/Subscription/Exceptions/PlanNotAllowedException.php

namespace App\Services\Subscription\Exceptions;

class PlanNotAllowedException extends SubscriptionException
{
    public function __construct(string $message, string $code = 'plan_not_allowed', array $context = [])
    {
        parent::__construct($message, $code, $context, 403);
    }
}