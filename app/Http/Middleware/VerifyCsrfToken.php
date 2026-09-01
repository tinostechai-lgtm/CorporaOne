<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
   protected $except = [
    'plan/paytm/*',
    '/customer/paytm/*',
    'plan-pay-with-paymentwall/*',
    'invoice-pay-with-paymentwall/*',
    'iyzipay/callback/*',
    'paytab-success/*',
    '/aamarpay*',
    'plan-easebuzz-payment-notify*',
    'invoice-easebuzz-payment-notify*',
    'webhook/leads',
    'webhook/*',
    
    // Add these exact paths
    'bonvoice/test-connection',
    '/bonvoice/test-connection',  // Try with leading slash
    'bonvoice/*',                  // All bonvoice routes

     'work-report/submit',
    'work-report/*/review',
    'work-reports',
];
}