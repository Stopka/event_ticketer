<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.18
 * Time: 11:51
 */

namespace App\Console;


use App\Model\Notifier\CartCreatedNotifier;
use App\Model\Persistence\Dao\CartDao;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendCartCreatedCommand extends Command {

    const ARG_CART_ID = 'cartId';

    /** @var CartCreatedNotifier */
    private $cartCreatedNotifier;

    /** @var CartDao */
    private $cartDao;

    public function __construct(CartCreatedNotifier $cartCreatedNotifier, CartDao $cartDao, ?string $name = null) {
        parent::__construct($name);
        $this->cartCreatedNotifier = $cartCreatedNotifier;
        $this->cartDao = $cartDao;
    }


    protected function configure() {
        $this->setName('debug:sendCartCreated')
            ->setDescription('Sends email about created cart')
            ->addArgument(self::ARG_CART_ID, InputArgument::OPTIONAL, 'Which cart should be sent', 1);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $cartId = $input->getArgument(self::ARG_CART_ID);
        $cart = $this->cartDao->getCart($cartId);
        $this->cartCreatedNotifier->sendNotification($cart);
    }


}