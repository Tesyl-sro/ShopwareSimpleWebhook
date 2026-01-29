<?php declare(strict_types=1);

namespace SimpleWebhooks\Subscriber;

use Shopware\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use SimpleWebhooks\Message\WebhookMessage;
use SimpleWebhooks\Utils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CustomerSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerRegisterEvent::class => 'onCustomerRegistered'
        ];
    }

    public function onCustomerRegistered(CustomerRegisterEvent $event): void
    {
        $customer = $event->getCustomer();

        $this->messageBus->dispatch(
            new WebhookMessage(
                $event->getName(),
                "SimpleWebhooks.config.customerRegistrationWebhook",
                Utils\Common::buildWebhookPayload($event->getName(), $customer->jsonSerialize())
            )
        );
    }
}
