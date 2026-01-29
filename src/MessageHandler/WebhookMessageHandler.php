<?php declare(strict_types=1);

namespace SimpleWebhooks\MessageHandler;

use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use SimpleWebhooks\Message\WebhookMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class WebhookMessageHandler
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private SystemConfigService $systemConfigService;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        SystemConfigService $systemConfigService
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->systemConfigService = $systemConfigService;
    }

    public function __invoke(WebhookMessage $message): void
    {
        $webhookUrl = (string) $this->systemConfigService->get('SimpleWebhooks.config.productLoadedWebhook');

        if (empty($webhookUrl)) {
            $this->logDebug($message->getEventName(), "empty webhook URL");
            return;
        }

        $this->logger->debug("onOrderPlaced has a valid webhook URL");
        $this->logDebug($message->getEventName(), "valid webhook URL");

        try {
            $response = $this->httpClient->request('POST', $webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Shopware-Event' => $message->getEventName(),
                ],
                'json' => $message->getPayload(),
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200 || $statusCode !== 201) {
                $this->logDebug($message->getEventName(), "webhook OK");
            } else {
                $this->logWarning($message->getEventName(), "webhook failed: $statusCode");
            }
        } catch (\Exception $e) {
            $this->logError($message->getEventName(), "webhook call failed: " . $e->getMessage());
        }
    }
    private function logDebug(string $event_name, string $message, mixed $context = [])
    {
        $this->logger->debug("$event_name: $message", $context);
    }

    private function logWarning(string $event_name, string $message, mixed $context = [])
    {
        $this->logger->warning("$event_name: $message", $context);
    }

    private function logError(string $event_name, string $message, mixed $context = [])
    {
        $this->logger->error("$event_name: $message", $context);
    }
}