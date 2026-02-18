<?php

namespace App\Services;

class PaymentService {
    function pay($bookingId, $amount) {
        // Simulate payment processing with a random success or failure
        return rand(0, 1) === 1;
    }
}
