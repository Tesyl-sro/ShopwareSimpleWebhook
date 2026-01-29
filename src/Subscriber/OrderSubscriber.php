<?php declare(strict_types=1);

namespace SimpleWebhooks\Subscriber;

use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use SimpleWebhooks\Message\WebhookMessage;
use SimpleWebhooks\Utils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
        ];
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $order = $event->getOrder();

        $this->messageBus->dispatch(
            new WebhookMessage(
                $event->getName(),
                "SimpleWebhooks.config.newOrderWebhook",
                Utils\Common::buildWebhookPayload($event->getName(), $order->jsonSerialize())
            )
        );
    }
}
