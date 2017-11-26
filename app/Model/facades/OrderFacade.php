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
use App\Model\Persistence\Entity\ApplicationEntity;
use App\Model\Persistence\Entity\ChoiceEntity;
use App\Model\Persistence\Entity\EarlyEntity;
use App\Model\Persistence\Entity\EventEntity;
use App\Model\Persistence\Entity\OptionEntity;
use App\Model\Persistence\Entity\OrderEntity;
use App\Model\Persistence\Entity\SubstituteEntity;
use Kdyby\Doctrine\EntityManager;
use Nette\Mail\SendmailMailer;

class OrderFacade extends EntityFacade {

    /** @var EmailMessageFactory */
    private $emailMessageFactory;

    /** @var ApplicationFacade */
    private $applicationFacade;

    /** @var  PdfApplicationFacade */
    private $pdfApplicationFacade;

    public function __construct(EntityManager $entityManager, EmailMessageFactory $emailMessageFactory, ApplicationFacade $applicationFacade, PdfApplicationFacade $pdfApplicationFacade) {
        parent::__construct($entityManager);
        $this->emailMessageFactory = $emailMessageFactory;
        $this->applicationFacade = $applicationFacade;
        $this->pdfApplicationFacade = $pdfApplicationFacade;
    }


    protected function getEntityClass() {
        return OrderEntity::class;
    }

    /**
     * @param $values array
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @return \App\Model\Persistence\Entity\OrderEntity
     */
    public function createOrderFromOrderForm($values, EventEntity $event = null, EarlyEntity $early = null, SubstituteEntity $substitute = null) {
        $entityManager = $this->getEntityManager();
        $order = new OrderEntity();
        $order->setByValueArray($values);
        $order->setEarly($early);
        $order->setEvent($event);
        $order->setSubstitute($substitute);
        $entityManager->persist($order);
        $commonValues = $values['commons'];
        $optionRepository = $entityManager->getRepository(OptionEntity::class);
        $additionRepository = $entityManager->getRepository(AdditionEntity::class);
        /** @var AdditionEntity[] $additions */
        $additions = $additionRepository->findBy(['visible' => false, 'event.id' => $event->getId()]);
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
            foreach ($additions as $addition) {
                $options = $addition->getOptions();
                for ($i = 0; $i < count($options) && $i < $addition->getMinimum(); $i++) {
                    $option = $options[$i];
                    $choice = new ChoiceEntity();
                    $choice->setOption($option);
                    $choice->setApplication($application);
                    $entityManager->persist($choice);
                }
            }
        }
        $entityManager->flush();
        if ($event->isCapacityFull($this->applicationFacade->countIssuedApplications($event))) {
            $event->setCapacityFull();
        }
        $entityManager->flush();
        $this->sendRegistrationEmail($order);
        return $order;
    }

    /**
     * @param $values array
     * @param EventEntity|null $event
     * @param EarlyEntity|null $early
     * @return \App\Model\Persistence\Entity\OrderEntity
     */
    public function editOrderFromOrderForm($values, EventEntity $event = null, EarlyEntity $early = null, SubstituteEntity $substitute = null, OrderEntity $order = null) {
        $entityManager = $this->getEntityManager();
        //$order = new OrderEntity();
        $order->setByValueArray($values);
        $entityManager->persist($order);
        $commonValues = $values['commons'];
        $optionRepository = $entityManager->getRepository(OptionEntity::class);
        $additionRepository = $entityManager->getRepository(AdditionEntity::class);
        /** @var AdditionEntity[] $additions */
        $additions = $additionRepository->findBy(['visible' => false, 'event.id' => $event->getId()]);
        foreach ($values['children'] as $id => $childValues) {
            foreach ($order->getApplications() as $application){
                if($application->getId()!=$id){
                    continue;
                }
                $application->setByValueArray($commonValues);
                $application->setByValueArray($childValues['child']);
                //$entityManager->persist($application);
                foreach ($childValues['addittions'] as $additionId => $optionId) {
                    foreach ($application->getChoices() as $choice) {
                        if($choice->getOption()->getAddition()->getId()!=$additionId){
                            continue;
                        }
                        /** @var \App\Model\Persistence\Entity\OptionEntity $option */
                        $option = $optionRepository->find($optionId);
                        $choice->setOption($option);
                        //$entityManager->persist($choice);
                    }
                }
            }
        }
        $entityManager->flush();
        return $order;
    }

    /**
     * @param OrderEntity $order
     */
    public function sendRegistrationEmail(OrderEntity $order) {
        if (!$order->getEmail()) {
            return;
        }
        $link = $this->emailMessageFactory->link('Front:Order:', ['id' => $order->getHashId()]);
        $message = $this->emailMessageFactory->create();
        $message->addTo($order->getEmail(), $order->getFullName());
        $message->setSubject('Přihláška na ' . $order->getEvent()->getName());
        $message->setHtmlBody("<p>Dobrý den,</p>
<p> Děkujeme, že jste projevili zájem o přihlášku na <strong>" . $order->getEvent()->getName() . "</strong>. V příloze zasíláme přihlášku, bezinfekčnost a lékařské potvrzení. Bezinfekčnost, lékařské potvrzení a list s informacemi můžete v případě ztráty získat na našich stránkách.</p>
<p>Nyní je potřeba přihlášku vytisknout pro každé rezervované místo, dovyplnit, odeslat a ke každé přihlášce zaplatit rezervační poplatek. Další informace jsou uvedeny přímo v přihlášce.</p>
<p>Aktuální stav Vašich přihlášek můžete průběžně sledovat na následující adrese: <br />
 <a href='$link'>$link</a></p>
<p>V případě dotazu pište na ldtmpp@email.cz.</p>
<p><em>Zpráva byla vygenerována a odeslána automaticky ze stránek ldtpardubice.cz na základě rezervace místa.</em></p>");
        foreach ($order->getApplications() as $application){
            $file_path = $this->pdfApplicationFacade->getPdfPath($application);
            $message->addAttachment('přihláška_'.$application->getId().'.pdf',@file_get_contents($file_path));
        }
        foreach ($this->pdfApplicationFacade->getFilePaths($order->getEvent()) as $file){
            $message->addAttachment($file);
        }
        $mailer = new SendmailMailer();
        $mailer->send($message);
    }

    /**
     * @param $hash string
     * @return \App\Model\Persistence\Entity\OrderEntity|null
     */
    public function getViewableOrderByHash($hash) {
        list($id, $guid) = OrderEntity::parseHashIdToArray($hash);
        if (!$id || !$guid)
            return NULL;
        /** @var \App\Model\Persistence\Entity\OrderEntity $order */
        $order = $this->get($id);
        if ($order && $order->getGuid() == $guid && $order->getState() == OrderEntity::STATE_ORDER)
            return $order;
        return NULL;
    }

    /**
     * @param array $values
     * @param EventEntity $event
     */
    public function createOrdersFromReserveForm($values, EventEntity $event) {
        $entityManager = $this->getEntityManager();
        $optionRepository = $entityManager->getRepository(OptionEntity::class);
        $additionRepository = $entityManager->getRepository(AdditionEntity::class);
        /** @var AdditionEntity[] $additions */
        $additions = $additionRepository->findBy(['visible' => false, 'event.id' => $event->getId()]);
        for ($j = 0; $j < $values['count']; $j++) {
            $order = new OrderEntity();
            $order->setEvent($event);
            $entityManager->persist($order);
            $application = new ApplicationEntity();
            $application->setOrder($order);
            $entityManager->persist($application);
            foreach ($values['addittions'] as $additionId => $optionId) {
                /** @var \App\Model\Persistence\Entity\OptionEntity $option */
                $option = $optionRepository->find($optionId);
                $choice = new ChoiceEntity();
                $choice->setOption($option);
                $choice->setApplication($application);
                $entityManager->persist($choice);
            }
            foreach ($additions as $addition) {
                $options = $addition->getOptions();
                for ($i = 0; $i < count($options) && $i < $addition->getMinimum(); $i++) {
                    $option = $options[$i];
                    $choice = new ChoiceEntity();
                    $choice->setOption($option);
                    $choice->setApplication($application);
                    $entityManager->persist($choice);
                }
            }
        }
        if ($event->isCapacityFull($this->applicationFacade->countIssuedApplications($event))) {
            $event->setCapacityFull();
        }
        $this->getEntityManager()->flush();
    }

    /**
     * @param $id
     * @return null|\App\Model\Persistence\Entity\OrderEntity
     */
    public function getOrder($id){
        return $this->get($id);
    }
}