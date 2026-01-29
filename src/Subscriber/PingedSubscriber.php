<?php declare(strict_types=1);

namespace SimpleWebhooks\Subscriber;

use Psr\Log\LoggerInterface;
use SimpleWebhooks\Event\PingedEvent;
use SimpleWebhooks\Message\WebhookMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PingedSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;

    public function __construct(LoggerInterface $logger, MessageBusInterface $messageBus)
    {
        $this->logger = $logger;
        $this->messageBus = $messageBus;
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

        $this->messageBus->dispatch(
            new WebhookMessage(
                "cli.ping",
                "SimpleWebhooks.config.pingWebhook"
            )
        );

        $this->logger->debug("Ping webhook dispatched");
    }
}