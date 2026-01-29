<?php declare(strict_types=1);

namespace SimpleWebhooks\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use SimpleWebhooks\Utils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
        ];
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
        $productLoadedWehookUrl = (string) $this->systemConfigService->get('SimpleWebhooks.config.productLoadedWebhook');

        if (empty($productLoadedWehookUrl)) {
            $this->logger->debug("onOrderPlaced has an emtpy webhook URL");
            return;
        }

        $this->logger->debug("onOrderPlaced has a valid webhook URL");

        $order = $event->getOrder();

        try {
            $payload = Utils\Common::buildWebhookPayload($event->getName(), $order->jsonSerialize());

            $response = $this->httpClient->request('POST', $productLoadedWehookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Shopware-Event' => 'orders.created',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200 || $statusCode !== 201) {
                $this->logger->debug("onOrderPlaced webhook OK");
            } else {
                $this->logger->warning("onOrderPlaced webhook failed: $statusCode");
            }
        } catch (\Exception $e) {
            $this->logger->error("onOrderPlaced webhook process failed: " . $e->getMessage());
        }
    }
}
