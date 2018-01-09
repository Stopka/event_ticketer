<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 12.1.17
 * Time: 0:27
 */

namespace App\Model\Persistence\Dao;

use App\Model\Persistence\Entity\InsuranceCompanyEntity;

class InsuranceCompanyDao extends EntityDao {

    protected function getEntityClass(): string {
        return InsuranceCompanyEntity::class;
    }

    /**
     * @param null|string $id
     * @return InsuranceCompanyEntity|null
     */
    public function getInsuranceCompany(?string $id): ?InsuranceCompanyEntity {
        /** @var InsuranceCompanyEntity $result */
        $result = $this->get($id);
        return $result;
    }

    /**
     * @return array
     */
    public function getInsuranceCompanyList(): array {
        /** @var InsuranceCompanyEntity[] $companies */
        $companies = $this->getRepository()->findAll();
        $result = [null=>''];
        foreach ($companies as $company) {
            $result[$company->getIdAlphaNumeric()] = $company->getCode() . ' - ' . $company->getName();
        }
        return $result;
    }
}