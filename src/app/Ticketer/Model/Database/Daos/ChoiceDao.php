<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\Entities\ChoiceEntity;

class ChoiceDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return ChoiceEntity::class;
    }

    public function getChoice(?int $id): ?ChoiceEntity
    {
        /** @var ChoiceEntity $result */
        $result = $this->get($id);

        return $result;
    }
}
