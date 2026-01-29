<?php declare(strict_types=1);

namespace SimpleWebhooks\Subscriber;

use Psr\Log\LoggerInterface;
use SimpleWebhooks\Event\PingedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PingedSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PingedEvent::EVENT_NAME => 'onPingCommandExecuted',
        ];
    }

    public function onPingCommandExecuted(PingedEvent $event): void
    {
        $this->logger->info('Ping command was executed', [
            'pingAllowed' => $event->isPingAllowed(),
            'executedAt' => $event->getExecutedAt()->format(\DateTime::ATOM),
        ]);

        $this->logger->warning('Ping command was executed', [
            'pingAllowed' => $event->isPingAllowed(),
            'executedAt' => $event->getExecutedAt()->format(\DateTime::ATOM),
        ]);

        $this->logger->error('Ping command was executed', [
            'pingAllowed' => $event->isPingAllowed(),
            'executedAt' => $event->getExecutedAt()->format(\DateTime::ATOM),
        ]);

        $this->logger->debug('Ping command was executed', [
            'pingAllowed' => $event->isPingAllowed(),
            'executedAt' => $event->getExecutedAt()->format(\DateTime::ATOM),
        ]);
    }
}