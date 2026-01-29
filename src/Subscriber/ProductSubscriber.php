<?php declare(strict_types=1);

namespace SimpleWebhooks\Subscriber;

use Shopware\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use SimpleWebhooks\Message\WebhookMessage;
use SimpleWebhooks\Utils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_WRITTEN_EVENT => 'onProductUpdated',
        ];
    }

    public function onProductUpdated(EntityWrittenEvent $event): void
    {
        foreach ($event->getWriteResults() as $result) {
            $data = [
                "product_id" => $result->getPrimaryKey(),
                "operation" => $result->getOperation(),
                "payload" => $result->getPayload(),
            ];

            $this->messageBus->dispatch(
                new WebhookMessage(
                    "cli.ping",
                    "SimpleWebhooks.config.productUpdateWebhook",
                    Utils\Common::buildWebhookPayload($event->getName(), $data)
                )
            );
        }
    }
}
