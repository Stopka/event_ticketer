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
use App\Model\Entities\SubstituteEntity;
use App\Model\Exceptions\ApplicationException;
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
        if(!$substitute||$substitute->getGuid()!=$guid||!$substitute->isActive()){
            return NULL;
        }
        return $substitute;
    }

    /**
     * @param $substituteId integer
     */
    public function activate($substituteId){
        /** @var SubstituteEntity $substitute */
        $substitute = $this->get($substituteId);
        if(!$substitute||in_array($substitute->getState(),[SubstituteEntity::STATE_ACTIVE,SubstituteEntity::STATE_ORDERED])){
            return;
        }
        $substitute->setState(SubstituteEntity::STATE_ACTIVE);
        $this->getEntityManager()->flush();
        $this->sendActivationEmail($substitute);
    }

    /**
     * @param SubstituteEntity $substitute
     */
    public function sendActivationEmail(SubstituteEntity $substitute){
        if(!$substitute->getEmail()){
            return;
        }
        if(!$substitute->isActive()){
            throw new ApplicationException('Náhradník není aktivní.');
        }
        $link = $this->emailMessageFactory->link('Front:Substitute:',['id'=>$substitute->getHashId()]);
        $message = $this->emailMessageFactory->create();
        $message->addTo($substitute->getEmail(),$substitute->getFullName());
        $message->setSubject('Uvolněné místo na '.$substitute->getEvent()->getName());

        $message_body="<p>Dobrý den,</p>
<p>S potěšením oznamujeme, že se pro Vás uvolnilo místo na <strong>".$substitute->getEvent()->getName()."</strong>. Přihlášku získáte po registraci na následující adrese: <br />
<a href='$link'>$link</a></p>";
        $endDate = $substitute->getEndDate()?$substitute->getEndDate()->format('d.m.Y H:i:s'):null;
        $message_endDate = ($endDate?"<p>Místo pro vás držíme do $endDate, poté dáme šanci dalšímu náhradníkovi v pořadí.</p>":"");
        $message_foot = "<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa náhradníka.</em></p>";

        $message->setHtmlBody($message_body.$message_endDate.$message_foot);
        $mailer = new SendmailMailer();
        $mailer->send($message);
    }
}