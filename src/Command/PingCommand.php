<?php declare(strict_types=1);

namespace SimpleWebhooks\Command;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use SimpleWebhooks\Event\PingedEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'simplewebhooks:ping',
    description: 'Test the plugin from the command line.',
)]
class PingCommand extends Command
{
    private SystemConfigService $systemConfigService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(SystemConfigService $systemConfigService, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
        $this->systemConfigService = $systemConfigService;
        $this->eventDispatcher = $eventDispatcher;
    }

    // Provides a description, printed out in bin/console
    protected function configure(): void
    {
        $this->setDescription('Does something very special.');
    }

    // Actual code executed in the command
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allowCliPing = (bool) $this->systemConfigService->get('SimpleWebhooks.config.allowCliPing');

        // Dispatch the event
        $event = new PingedEvent($allowCliPing);
        $this->eventDispatcher->dispatch($event, PingedEvent::EVENT_NAME);

        if ($allowCliPing) {
            $output->writeln('Pong!');
        } else {
            $output->writeln('Pinging is disabled in the plugin configuration');
        }

        return Command::SUCCESS;
    }
}
