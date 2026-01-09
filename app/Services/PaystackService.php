<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret');
        $this->baseUrl = config('services.paystack.base_url');
    }

    protected function client()
    {
        return Http::withToken($this->secretKey)
            ->acceptJson();
    }

    public function initializePayment(array $data)
    {
        return $this->client()->post(
            $this->baseUrl . '/transaction/initialize',
            $data
        )->throw()->json();
    }

    public function verifyPayment(string $reference)
    {
        return $this->client()->get(
            $this->baseUrl . "/transaction/verify/{$reference}"
        )->throw()->json();
    }

    public function getBanks()
    {
        return $this->client()->get(
            $this->baseUrl . '/bank'
        )->throw()->json();
    }

    public function resolveAccountNumber(string $accountNumber, string $bankCode)
    {
        return $this->client()->get(
            $this->baseUrl . "/bank/resolve?account_number={$accountNumber}&bank_code={$bankCode}"
        )->throw()->json();
    }
}