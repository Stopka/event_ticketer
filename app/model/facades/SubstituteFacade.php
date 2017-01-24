<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.17
 * Time: 20:18
 */

namespace App\Model\Facades;


use App\Model\EmailMessageFactory;
use App\Model\Entities\EarlyEntity;
use App\Model\Entities\EventEntity;
use App\Model\Entities\OrderEntity;
use App\Model\Entities\SubstituteEntity;
use Grido\DataSources\Doctrine;
use Kdyby\Doctrine\EntityManager;
use Nette\Mail\SendmailMailer;

class SubstituteFacade extends EntityFacade {

    /** @var EmailMessageFactory */
    private $emailMessageFactory;

    public function __construct(EntityManager $entityManager, EmailMessageFactory $emailMessageFactory) {
        parent::__construct($entityManager);
        $this->emailMessageFactory = $emailMessageFactory;
    }


    protected function getEntityClass() {
        return SubstituteEntity::class;
    }

    public function createSubtituteFromForm($values, EventEntity $event, EarlyEntity $early = null){
        $entityManager = $this->getEntityManager();
        $substitute = new SubstituteEntity();
        $substitute->setByValueArray($values);
        $substitute->setEarly($early);
        $substitute->setEvent($event);
        $entityManager->persist($substitute);
        $entityManager->flush();
        return $substitute;
    }

    /**
     * @param OrderEntity $order
     */
    public function sendRegistrationEmail(OrderEntity $order){
        if(!$order->getEmail()){
            return;
        }
        $link = $this->emailMessageFactory->link('Front:Order:',['id'=>$order->getHashId()]);
        $message = $this->emailMessageFactory->create();
        $message->addTo($order->getEmail(),$order->getFullName());
        $message->setSubject('Přihláška na '.$order->getEvent()->getName());
        $message->setHtmlBody("<p>Dobrý den,</p>
<p> Děkujeme, že jste projevili zájem o přihlášku na <strong>".$order->getEvent()->getName()."</strong>. V příloze zasíláme přihlášku, bezinfekčnost a lékařské potvrzení. Bezinfekčnost, lékařské potvrzení a list s informacemi můžete v případě ztráty získat na našich stránkách.</p>
<p>Nyní je potřeba přihlášku vytisknout pro každé rezervované místo, dovyplnit, odeslat a ke každé přihlášce zaplatit rezervační poplatek. Další informace jsou uvedeny přímo v přihlášce.</p>
<p>Aktuální stav Vašich přihlášek můžete průběžně sledovat na adrese <a href='$link'>$link</a></p>
<p>V případě nějakého dotazu pište na ldtmpp@email.cz.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa.</em></p>");
        $mailer = new SendmailMailer();
        $mailer->send($message);
    }

    /**
     * @return Doctrine
     */
    public function getAllSubstitutesGridModel(EventEntity $event){
        $qb = $this->getRepository()->createQueryBuilder('s');
        $qb->addSelect('s')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('s.event',$event->getId())
            ));
        return new Doctrine($qb);
    }

    /**
     * @param string $hashId
     * @return SubstituteEntity|null
     */
    public function getReadySubstituteByHash($hashId){
        list($id,$guid) = SubstituteEntity::parseHashIdToArray($hashId);
        if(!$id||!$guid)
            return NULL;
        /** @var SubstituteEntity $substitute */
        $substitute = $this->get($id);
        if($substitute->getGuid()!=$guid||!$substitute->isActive()){
            return NULL;
        }
        return $substitute;
    }
}