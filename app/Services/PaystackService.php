<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret') ?? '';
        $this->baseUrl = config('services.paystack.base_url') ?? 'https://api.paystack.co';
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

    public function getBanks(string $country = 'ghana')
    {
        return $this->client()->get(
            $this->baseUrl . "/bank?country={$country}"
        )->throw()->json();
    }

    public function resolveAccountNumber(string $accountNumber, string $bankCode)
    {
        return $this->client()->get(
            $this->baseUrl . "/bank/resolve?account_number={$accountNumber}&bank_code={$bankCode}"
        )->throw()->json();
    }

    /**
     * Create a Paystack transfer recipient for payouts.
     * Type can be 'mobile_money' or 'ghipss' / 'bank_account'.
     */
    public function createTransferRecipient(string $name, string $accountNumber, string $bankCode, string $type = 'mobile_money', string $currency = 'GHS')
    {
        return $this->client()->post(
            $this->baseUrl . '/transferrecipient',
            [
                'type' => $type,
                'name' => $name,
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
                'currency' => $currency,
            ]
        )->throw()->json();
    }

    /**
     * Initiate a transfer to a recipient.
     */
    public function initiateTransfer(float $amount, string $recipientCode, string $reason, string $reference)
    {
        // Amount in pesewas / sub-units (GHS 1.00 = 100 pesewas)
        $amountInPesewas = (int) round($amount * 100);

        return $this->client()->post(
            $this->baseUrl . '/transfer',
            [
                'source' => 'balance',
                'amount' => $amountInPesewas,
                'recipient' => $recipientCode,
                'reason' => $reason,
                'reference' => $reference,
                'currency' => 'GHS',
            ]
        )->throw()->json();
    }

    /**
     * Verify status of a transfer.
     */
    public function verifyTransfer(string $referenceOrCode)
    {
        return $this->client()->get(
            $this->baseUrl . "/transfer/verify/{$referenceOrCode}"
        )->throw()->json();
    }
}