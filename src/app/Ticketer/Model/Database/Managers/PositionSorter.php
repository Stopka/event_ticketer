<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Managers;

use Doctrine\ORM\EntityRepository;
use Ticketer\Model\Database\Attributes\TPositionAttribute;
use Ticketer\Model\Database\Daos\OrderEnum;
use Ticketer\Model\Database\Entities\SortableEntityInterface;

class PositionSorter
{
    /** @var EntityRepository<SortableEntityInterface> */
    private $entityRepository;

    /** @var int|null */
    private $lastPosition;

    /**
     * @param SortableEntityInterface $positionable
     * @return int
     */
    public function setEndPosition(SortableEntityInterface $positionable): int
    {
        if (null === $this->lastPosition) {
            $last = $this->entityRepository->findOneBy([], ['position' => OrderEnum::DESC()->getValue()]);
            if (null === $last) {
                $this->lastPosition = 0;
            } else {
                $this->lastPosition = $last->getPosition();
            }
        }
        $newPosition = $this->lastPosition++;
        $positionable->setPosition($newPosition);

        return $newPosition;
    }

    /**
     * @param iterable<SortableEntityInterface> $entities
     */
    public function recalculatePositions(iterable $entities): void
    {
        $this->lastPosition = null;
        $position = 1;
        foreach ($entities as $entity) {
            $entity->setPosition($position);
            $position++;
        }
    }

    /**
     * @param SortableEntityInterface $item
     * @param iterable<SortableEntityInterface> $entities
     */
    public function moveEntityUp(SortableEntityInterface $item, iterable $entities): void
    {
        $this->lastPosition = null;
        $position = 1;
        $previousEntity = null;
        foreach ($entities as $entity) {
            if ($item->getId() === $entity->getId() && null !== $previousEntity) {
                $previousEntity->setPosition($position);
                $entity->setPosition($position - 1);
            } else {
                $entity->setPosition($position);
            }
            $previousEntity = $entity;
            $position++;
        }
    }

    /**
     * @param SortableEntityInterface $item
     * @param iterable<SortableEntityInterface> $entities
     */
    public function moveEntityDown(SortableEntityInterface $item, iterable $entities): void
    {
        $this->lastPosition = null;
        $position = 1;
        $shift = 0;
        foreach ($entities as $entity) {
            if ($item->getId() === $entity->getId()) {
                $entity->setPosition($position + 1);
                $shift++;
            } else {
                $entity->setPosition($position - $shift);
                if (0 !== $shift) {
                    $shift--;
                }
            }
            $position++;
        }
    }

    /**
     * @param SortableEntityInterface $item
     * @param iterable<SortableEntityInterface> $entities
     */
    public function moveEntityToEnd(SortableEntityInterface $item, iterable $entities): void
    {
        $this->lastPosition = null;
        $position = 1;
        foreach ($entities as $entity) {
            if ($item->getId() === $entity->getId()) {
                continue;
            }

            $entity->setPosition($position);
            $position++;
        }
        $item->setPosition($position);
    }

    /**
     * @param SortableEntityInterface $item
     * @param iterable<SortableEntityInterface> $entities
     */
    public function moveEntityToStart(SortableEntityInterface $item, iterable $entities): void
    {
        $this->lastPosition = null;
        $item->setPosition(1);
        $position = 2;
        foreach ($entities as $entity) {
            if ($item->getId() === $entity->getId()) {
                continue;
            }

            $entity->setPosition($position);
            $position++;
        }
    }
}
