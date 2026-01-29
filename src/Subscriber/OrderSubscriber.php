<?php declare(strict_types=1);

namespace SimpleWebhooks\Subscriber;

use Exception;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use SimpleWebhooks\Message\WebhookMessage;
use SimpleWebhooks\Utils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $messageBus;
    private EntityRepository $orderTransactionRepository;
    private EntityRepository $orderDeliveryRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        EntityRepository $orderTransactionRepository,
        EntityRepository $orderDeliveryRepository
    ) {
        $this->messageBus = $messageBus;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->orderDeliveryRepository = $orderDeliveryRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
            'state_machine.order.state_changed' => 'onOrderStateChanged',
            'state_machine.order_transaction.state_changed' => 'onOrderStateChanged',
            'state_machine.order_delivery.state_changed' => 'onOrderStateChanged',
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

    public function onOrderStateChanged(StateMachineStateChangeEvent $event): void
    {
        $context = $event->getContext();
        $entityName = $event->getTransition()->getEntityName();
        $entityId = $event->getTransition()->getEntityId();

        $orderId = match ($entityName) {
            'order' => $entityId,
            'order_transaction' => $this->getOrderIdFromRepo($entityId, $context, $this->orderTransactionRepository),
            'order_delivery' => $this->getOrderIdFromRepo($entityId, $context, $this->orderDeliveryRepository),
            default => throw new Exception("unexpedted entity name"),
        };

        $payload = [
            'order_id' => $orderId,
            'property' => $entityName,
            'old_state' => $event->getPreviousState()->getTechnicalName(),
            'new_state' => $event->getNextState()->getTechnicalName(),
        ];

        $this->messageBus->dispatch(
            new WebhookMessage(
                $event->getName(),
                "SimpleWebhooks.config.orderStatusUpdatedWebhook",
                Utils\Common::buildWebhookPayload($event->getName(), $payload)
            )
        );
    }

    private function getOrderIdFromRepo(string $id, Context $context, EntityRepository $repo): string
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('order');

        $transaction = $repo
            ->search($criteria, $context)
            ->first();

        return $transaction->getOrderId();
    }
}
