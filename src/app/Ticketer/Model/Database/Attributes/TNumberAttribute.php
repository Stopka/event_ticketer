<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Ticketer\Model\Database\Daos\OrderEnum;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

trait TNumberAttribute
{

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $number;

    /**
     * @var int|null
     */
    private static $nextNumber;

    /**
     * @return array<mixed>
     */
    abstract protected function getLastNumberSearchCriteria(): array;

    public function setNextNumber(EntityManager $entityManager): int
    {
        $repository = $entityManager->getRepository(self::class);
        if (null === self::$nextNumber) {
            $entity = $repository->findOneBy(
                $this->getLastNumberSearchCriteria(),
                ['number' => OrderEnum::DESC()->getValue()]
            );
            if (null === $entity) {
                self::$nextNumber = 1;
            } else {
                self::$nextNumber = $entity->getNumber() + 1;
            }
        }
        $this->setNumber(self::$nextNumber);
        self::$nextNumber++;

        return $this->getNumber();
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    protected function setNumber(int $number): void
    {
        $this->number = $number;
    }
}
