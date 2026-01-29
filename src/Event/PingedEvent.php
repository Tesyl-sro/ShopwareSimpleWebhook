<?php declare(strict_types=1);

namespace SimpleWebhooks\Event;

use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

class PingedEvent extends Event
{
    public const EVENT_NAME = 'simplewebhooks.ping.executed';

    private bool $pingAllowed;
    private ?DateTimeImmutable $executedAt;

    public function __construct(bool $pingAllowed, ?DateTimeImmutable $executedAt = null)
    {
        $this->pingAllowed = $pingAllowed;
        $this->executedAt = $executedAt ?? new DateTimeImmutable();
    }

    public function isPingAllowed(): bool
    {
        return $this->pingAllowed;
    }

    public function getExecutedAt(): DateTimeImmutable
    {
        return $this->executedAt;
    }
}