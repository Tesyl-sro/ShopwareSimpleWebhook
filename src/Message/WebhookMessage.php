<?php declare(strict_types=1);

namespace SimpleWebhooks\Message;

class WebhookMessage
{
    private string $event_name;
    private ?array $payload;

    public function __construct(string $event_name, ?array $payload = null)
    {
        $this->event_name = $event_name;
        $this->payload = $payload;
    }

    public function getEventName(): string
    {
        return $this->event_name;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }
}