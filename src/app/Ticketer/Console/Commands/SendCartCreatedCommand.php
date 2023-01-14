<?php

declare(strict_types=1);

namespace Ticketer\Console\Commands;

use Nette\Application\UI\InvalidLinkException;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Notifiers\CartCreatedNotifier;
use Ticketer\Model\Database\Daos\CartDao;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendCartCreatedCommand extends AbstractCommand
{
    private const ARG_CART_ID = 'cartId';

    /** @var CartCreatedNotifier */
    private $cartCreatedNotifier;

    /** @var CartDao */
    private $cartDao;

    protected static $defaultName = 'debug:sendCartCreated';

    public function __construct(CartCreatedNotifier $cartCreatedNotifier, CartDao $cartDao, ?string $name = null)
    {
        parent::__construct($name);
        $this->cartCreatedNotifier = $cartCreatedNotifier;
        $this->cartDao = $cartDao;
    }


    protected function configure(): void
    {
        $this->setName('debug:sendCartCreated')
            ->setDescription('Sends email about created cart')
            ->addArgument(self::ARG_CART_ID, InputArgument::REQUIRED, 'Which cart should be sent');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidLinkException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $cartIdArgument */
        $cartIdArgument = $input->getArgument(self::ARG_CART_ID);
        $uuid = Uuid::fromString($cartIdArgument);
        $cart = $this->cartDao->getCart($uuid);
        if (null === $cart) {
            $output->writeln('Cart not found');

            return self::FAILURE;
        }
        $this->cartCreatedNotifier->sendNotification($cart);

        return self::SUCCESS;
    }
}
