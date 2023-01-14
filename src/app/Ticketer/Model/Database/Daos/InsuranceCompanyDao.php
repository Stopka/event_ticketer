<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Dtos\Uuid;
use Ticketer\Model\Database\Entities\InsuranceCompanyEntity;

class InsuranceCompanyDao extends EntityDao
{
    protected function getEntityClass(): string
    {
        return InsuranceCompanyEntity::class;
    }

    /**
     * @param Uuid $id
     * @return InsuranceCompanyEntity|null
     */
    public function getInsuranceCompany(Uuid $id): ?InsuranceCompanyEntity
    {
        /** @var InsuranceCompanyEntity $result */
        $result = $this->get($id);

        return $result;
    }

    /**
     * @return array<int|string>
     */
    public function getInsuranceCompanyList(): array
    {
        /** @var InsuranceCompanyEntity[] $companies */
        $companies = $this->getRepository()->findAll();
        $result = [null => ''];
        foreach ($companies as $company) {
            $result[$company->getId()->toString()] = $company->getCode() . ' - ' . $company->getName();
        }

        return $result;
    }
}
