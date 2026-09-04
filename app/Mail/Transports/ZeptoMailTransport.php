<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportException;
use Illuminate\Support\Facades\Http;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class ZeptoMailTransport extends AbstractTransport
{
    protected string $apiKey;
    protected string $region;
    protected string $endpoint;

    public function __construct(string $apiKey, string $region = 'com', ?EventDispatcherInterface $dispatcher = null, ?LoggerInterface $logger = null)
    {
        parent::__construct($dispatcher, $logger);

        // Normalize authorization header token
        $apiKey = trim($apiKey);
        if (!str_starts_with($apiKey, 'Zoho-enczapikey ')) {
            $this->apiKey = 'Zoho-enczapikey ' . $apiKey;
        } else {
            $this->apiKey = $apiKey;
        }

        $this->region = $region ?: 'com';
        $domain = ($this->region === 'com' || empty($this->region)) ? 'zeptomail.com' : "zeptomail.{$this->region}";
        $this->endpoint = "https://api.{$domain}/v1.1/email";
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromAddress = $email->getFrom()[0] ?? new Address(config('mail.from.address', 'noreply@shoutoutgh.com'), config('mail.from.name', 'ShoutOutGh'));

        $payload = [
            'from' => $this->formatAddress($fromAddress),
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject() ?: '(No Subject)',
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->formatAddresses($cc);
        }

        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($bcc);
        }

        if ($replyTo = $email->getReplyTo()) {
            $payload['reply_to'] = $this->formatAddresses($replyTo);
        }

        $html = $email->getHtmlBody();
        if ($html) {
            $payload['htmlbody'] = is_resource($html) ? stream_get_contents($html) : (string) $html;
        }

        $text = $email->getTextBody();
        if ($text) {
            $payload['textbody'] = is_resource($text) ? stream_get_contents($text) : (string) $text;
        }

        if (empty($payload['htmlbody']) && empty($payload['textbody'])) {
            $payload['textbody'] = ' ';
        }

        if ($attachments = $email->getAttachments()) {
            $payload['attachments'] = [];
            foreach ($attachments as $att) {
                $payload['attachments'][] = [
                    'content' => base64_encode($att->getBody()),
                    'mime_type' => $att->getContentType(),
                    'name' => $att->getFilename() ?: 'attachment',
                ];
            }
        }

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(15)->post($this->endpoint, $payload);

        if (!$response->successful()) {
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? $response->body() ?? 'Unknown ZeptoMail API error';

            if (!empty($errorData['error']['details'])) {
                $errorMessage .= ': ' . json_encode($errorData['error']['details']);
            } elseif (!empty($errorData['data'])) {
                $errorMessage .= ': ' . json_encode($errorData['data']);
            }

            throw new TransportException("ZeptoMail API error [HTTP {$response->status()}]: {$errorMessage}");
        }
    }

    protected function formatAddress(Address $address): array
    {
        return [
            'address' => $address->getAddress(),
            'name' => $address->getName() ?: $address->getAddress(),
        ];
    }

    protected function formatAddresses(array $addresses): array
    {
        $formatted = [];
        foreach ($addresses as $address) {
            $formatted[] = [
                'email_address' => $this->formatAddress($address),
            ];
        }
        return $formatted;
    }

    public function __toString(): string
    {
        return "zeptomail_api({$this->endpoint})";
    }
}
