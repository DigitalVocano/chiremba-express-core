<?php

namespace App\Http\Controllers\Traits;

use Paynow\Payments\Paynow;

trait HandlesPaynow
{
    /**
     * Initializes the Paynow instance.
     * The controller using this trait should define protected properties:
     * $paynowCheckUrl and $paynowResultUrl
     *
     * @return Paynow
     */
    public function paynow(): Paynow
    {
        $id = env('PAYNOW_INTEGRATION_ID') ?: env('PAYNOW_INTERGRATION_ID');
        $key = env('PAYNOW_INTEGRATION_KEY') ?: env('PAYNOW_INTERGRATION_KEY');

        if (empty($id) || empty($key)) {
            $gateway = \App\Models\PaymentGatewayDetail::where('gateway_name', 'paynow')->first();
            if ($gateway) {
                $id = $gateway->key;
                $key = $gateway->secret;
            }
        }

        return new Paynow(
            $id ?? '19933',
            $key ?? '21a7f08a-6e44-4b5a-b310-d6c92a44ed20',
            $this->paynowCheckUrl ?? url('/'),
            $this->paynowResultUrl ?? url('/')
        );
    }
}

