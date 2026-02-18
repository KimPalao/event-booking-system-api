<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PaymentService;

class PaymentServiceTest extends TestCase
{
    public function test_pay_returns_boolean(): void
    {
        $paymentService = new PaymentService();
        $result = $paymentService->pay(1, 100.00);
        $this->assertIsBool($result);
    }
}
