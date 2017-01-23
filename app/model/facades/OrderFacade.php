<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\EmailMessageFactory;
use App\Model\Entities\AdditionEntity;
use App\Model\Entities\ApplicationEntity;
use App\Model\Entities\ChoiceEntity;
use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OptionEntity;
use App\Model\Entities\OrderEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\Mail\SendmailMailer;

class OrderFacade extends EntityFacade {

    /** @var EmailMessageFactory */
    private $emailMessageFactory;

    public function __construct(EntityManager $entityManager, EmailMessageFactory $emailMessageFactory) {
        parent::__construct($entityManager);
        $this->emailMessageFactory = $emailMessageFactory;
    }


    protected function getEntityClass() {
        return OrderEntity::class;
    }

    /**
     * @param $values array
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @return OrderEntity
     */
    public function createOrderFromOrderForm($values, EventEntity $event = null, EarlyEntity $early = null) {
        $entityManager = $this->getEntityManager();
        $order = new OrderEntity();
        $order->setByValueArray($values);
        $order->setEarly($early);
        $order->setEvent($event);
        $entityManager->persist($order);
        $commonValues = $values['commons'];
        $optionRepository = $entityManager->getRepository(OptionEntity::class);
        $additionRepository = $entityManager->getRepository(AdditionEntity::class);
        /** @var AdditionEntity[] $additions */
        $additions = $additionRepository->findBy(['visible'=>false,'event.id'=>$event->getId()]);
        foreach ($values['children'] as $childValues) {
            $application = new ApplicationEntity();
            $application->setByValueArray($commonValues);
            $application->setByValueArray($childValues['child']);
            $application->setOrder($order);
            $entityManager->persist($application);
            foreach ($childValues['addittions'] as $additionId => $optionId) {
                /** @var OptionEntity $option */
                $option = $optionRepository->find($optionId);
                $choice = new ChoiceEntity();
                $choice->setOption($option);
                $choice->setApplication($application);
                $entityManager->persist($choice);
            }
            foreach ($additions as $addition){
                $options = $addition->getOptions();
                for ($i=0;$i<count($options)&&$i<$addition->getMinimum();$i++){
                    $option = $options[$i];
                    $choice = new ChoiceEntity();
                    $choice->setOption($option);
                    $choice->setApplication($application);
                    $entityManager->persist($choice);
                }
            }
        }
        $entityManager->flush();
        $this->sendRegistrationEmail($order);
        return $order;
    }

    public function createSubtituteFromOrderForm($values, EventEntity $event, EarlyEntity $early){
        $entityManager = $this->getEntityManager();
        $order = new OrderEntity(true);
        $order->setByValueArray($values);
        $order->setEarly($early);
        $order->setEvent($event);
        $entityManager->persist($order);
        for ($i=0; $i<$values['count']; $i++) {
            $application = new ApplicationEntity(true);
            $application->setOrder($order);
            $entityManager->persist($application);
        }
        $entityManager->flush();
        return $order;
    }

    /**
     * @param OrderEntity $order
     */
    public function sendRegistrationEmail(OrderEntity $order){
        if(!$order->getEmail()){
            return;
        }
        $message = $this->emailMessageFactory->create();
        $message->addTo($order->getEmail(),$order->getFullName());
        $message->setSubject('Přihláška na '.$order->getEvent()->getName());
        $message->setBody("<p>Dobrý den,</p>
<p> Děkujeme, že jste projevili zájem o přihlášku na náš tábor. V příloze zasíláme přihlášku, bezinfekčnost a lékařské potvrzení. Bezinfekčnost, lékařské potvrzení a list s informacemi můžete v případě ztráty získat na našich stránkách, konkrétně zde: <a href=\"http://www.ldtpardubice.cz/formulare/\">http://www.ldtmpp.cz/formulare/</a>.</p>
<p>Nyní je potřeba přihlášku vytisknout pro každé rezervované místo, dovyplnit, odeslat a ke každé přihlášce zaplatit rezervační poplatek. Další informace jsou uvedeny přímo v přihlášce.</p>
<p>Aktuální stav přihlášek můžete průběžně sledovat na adrese <a href=\"http://www.ldtmpp.cz/system/application/list/".$order->getGuid()."/\">http://www.ldtmpp.cz/system/application/list/".$order->getGuid()."/</a></p>
<p>V případě nějakého dotazu pište na ldtmpp@email.cz.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa.</em></p>\n");
        $mailer = new SendmailMailer();
        $mailer->send($message);
    }
}