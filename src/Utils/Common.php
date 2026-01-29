<?php declare(strict_types=1);

namespace SimpleWebhooks\Utils;

use DateTime;

final class Common
{
    public static function buildWebhookPayload(string $event_name, ?array $data): array
    {
        return [
            'event' => $event_name,
            'timestamp' => (new DateTime())->format(DateTime::ATOM),
            'data' => $data
        ];
    }
}