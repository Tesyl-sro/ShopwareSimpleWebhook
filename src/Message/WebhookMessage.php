<?php declare(strict_types=1);

namespace SimpleWebhooks\Message;

class WebhookMessage
{
    private string $event_name;
    private string $url_config_key;
    private ?array $payload;

    public function __construct(string $event_name, string $url_config_key, ?array $payload = null)
    {
        $this->event_name = $event_name;
        $this->url_config_key = $url_config_key;
        $this->payload = $payload;
    }

    public function getEventName(): string
    {
        return $this->event_name;
    }

    public function getUrlConfigKey(): string
    {
        return $this->url_config_key;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }
}