<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Daos;

use Ticketer\Model\Database\Entities\InsuranceCompanyEntity;

class InsuranceCompanyDao extends EntityDao
{

    protected function getEntityClass(): string
    {
        return InsuranceCompanyEntity::class;
    }

    /**
     * @param null|int $id
     * @return InsuranceCompanyEntity|null
     */
    public function getInsuranceCompany(?int $id): ?InsuranceCompanyEntity
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
            $result[$company->getId()] = $company->getCode() . ' - ' . $company->getName();
        }

        return $result;
    }
}
