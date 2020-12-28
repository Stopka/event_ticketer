<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Ticketer\Model\Database\Entities\AdditionVisibilityEntity;
use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Exceptions\InvalidInputException;
use Ticketer\Model\Exceptions\NotFoundException;
use Ticketer\Model\Database\Daos\AdditionDao;
use Ticketer\Model\Database\Daos\ChoiceDao;
use Ticketer\Model\Database\Daos\OptionDao;
use Ticketer\Model\Database\Daos\TDoctrineEntityManager;
use Ticketer\Model\Database\Entities\AdditionEntity;
use Ticketer\Model\Database\Entities\ApplicationEntity;
use Ticketer\Model\Database\Entities\ChoiceEntity;
use Ticketer\Model\Database\EntityManager as EntityManagerWrapper;
use Nette\SmartObject;
use Ticketer\Model\Exceptions\NotReadyException;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 26.11.17
 * Time: 17:37
 */
class ChoiceManager
{
    use SmartObject;
    use TDoctrineEntityManager;

    /** @var  ChoiceDao */
    private $choiceDao;

    /** @var AdditionDao */
    private $additionDao;

    /** @var OptionDao */
    private $optionDao;

    /**
     * ChoiceManager constructor.
     * @param EntityManagerWrapper $entityManager
     * @param ChoiceDao $choiceDao
     * @param AdditionDao $additionDao
     * @param OptionDao $optionDao
     */
    public function __construct(
        EntityManagerWrapper $entityManager,
        ChoiceDao $choiceDao,
        AdditionDao $additionDao,
        OptionDao $optionDao
    ) {
        $this->injectEntityManager($entityManager);
        $this->choiceDao = $choiceDao;
        $this->additionDao = $additionDao;
        $this->optionDao = $optionDao;
    }

    /**
     * @param Uuid $choiceId
     */
    public function inverseChoicePayed(Uuid $choiceId): void
    {
        $choice = $this->choiceDao->getChoice($choiceId);
        if (null === $choice) {
            throw new NotFoundException("Choice not found");
        }
        $choice->inversePayed();
        $this->getEntityManager()->flush();
    }

    /**
     * @param Uuid $optionId
     * @param ApplicationEntity $application
     * @return ChoiceEntity
     */
    private function addChoice(Uuid $optionId, ApplicationEntity $application): ChoiceEntity
    {
        $option = $this->optionDao->getOption($optionId);
        if (null === $option) {
            throw new NotFoundException("Option was not found.");
        }
        $choice = new ChoiceEntity();
        $choice->setOption($option);
        $choice->setApplication($application);
        $this->getEntityManager()->persist($choice);

        return $choice;
    }

    /**
     * @param AdditionEntity $hiddenAddition
     * @return Uuid[]
     */
    private function selectHiddenAdditionOptionIds(AdditionEntity $hiddenAddition): array
    {
        $options = $hiddenAddition->getOptions();
        $optionIds = [];
        for ($i = 0; $i < count($options) && $i < $hiddenAddition->getMinimum(); $i++) {
            $option = $options[$i];
            $optionIds[] = $option->getId();
        }

        return $optionIds;
    }

    private function updateHiddenChoices(ApplicationEntity $application): void
    {
        $event = $application->getEvent();
        if (null === $event) {
            throw new InvalidInputException('Application has no event');
        }
        $hiddenAdditions = $this->additionDao->getEventAdditionsHiddenIn(
            $event,
            static function (AdditionVisibilityEntity $visibility): bool {
                return $visibility->isRegistration();
            }
        );
        foreach ($hiddenAdditions as $hiddenAddition) {
            $optionIds = $this->selectHiddenAdditionOptionIds($hiddenAddition);
            $processedOptionIds = [];
            $choices = $application->getChoices();
            foreach ($choices as $choice) {
                $option = $choice->getOption();
                if (null === $option) {
                    continue;
                }
                $addition = $option->getAddition();
                if (null === $addition || $addition->getId() !== $hiddenAddition->getId()) {
                    continue;
                }
                if (!in_array($option->getId(), $optionIds, true)) {
                    $this->getEntityManager()->remove($choice);
                }
                $processedOptionIds[] = $option->getId();
            }
            foreach ($optionIds as $optionId) {
                if (in_array($optionId, $processedOptionIds, true)) {
                    continue;
                }
                $this->addChoice($optionId, $application);
            }
        }
    }

    /**
     * @param array<mixed> $values
     * @param ApplicationEntity $application
     */
    private function updateVisibleAdditions(array $values, ApplicationEntity $application): void
    {
        foreach ($values as $additionId => $optionIds) {
            if (!is_array($optionIds)) {
                $optionIds = [$optionIds];
            }
            $additionUuid = Uuid::fromString($additionId);
            $processedOptionIds = [];
            $choices = $application->getChoices();
            foreach ($choices as $choice) {
                $option = $choice->getOption();
                if (null === $option) {
                    continue;
                }
                $addition = $option->getAddition();
                if (null === $addition || !$addition->getId()->equals($additionUuid)) {
                    continue;
                }
                if (!in_array($option->getId()->toString(), $optionIds, true)) {
                    $this->getEntityManager()->remove($choice);
                }
                $processedOptionIds[] = $option->getId()->toString();
            }
            foreach ($optionIds as $optionId) {
                if (in_array($optionId, $processedOptionIds, true)) {
                    continue;
                }
                $this->addChoice($optionId, $application);
            }
        }
    }

    /**
     * @param array<mixed> $values
     * @param ApplicationEntity $application
     * @param bool $simplified
     */
    private function processAdditionChoicesFrom(
        array $values,
        ApplicationEntity $application,
        bool $simplified = false
    ): void {
        if (!$simplified) {
            $this->updateHiddenChoices($application);
        }
        $this->updateVisibleAdditions($values, $application);
    }

    /**
     * @param array<mixed> $values
     * @param ApplicationEntity $application
     * @param bool $simplified
     */
    public function createAdditionChoicesToApplication(
        array $values,
        ApplicationEntity $application,
        bool $simplified = false
    ): void {
        $this->processAdditionChoicesFrom($values, $application, $simplified);
    }

    /**
     * @param array<mixed> $values
     * @param ApplicationEntity $application
     * @param bool $simplified
     */
    public function editAdditionChoicesInApplication(
        array $values,
        ApplicationEntity $application,
        bool $simplified = false
    ): void {
        $this->processAdditionChoicesFrom($values, $application, $simplified);
    }
}
