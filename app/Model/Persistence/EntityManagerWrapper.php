<?php
/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 20.1.18
 * Time: 13:15
 */

namespace App\Model\Persistence;


use App\Model\Exception\ORMException;
use Doctrine;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Persistence;
use Nette\SmartObject;

class EntityManagerWrapper {
    use SmartObject;

    private $entityManager;

    public function getEntityManager(): EntityManager {
        return $this->entityManager;
    }

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function createQueryBuilder($alias = NULL, $indexBy = NULL) {
        return $this->entityManager->createQueryBuilder($alias, $indexBy);
    }

    public function createSelection() {
        return $this->entityManager->createSelection();
    }

    public function clear($entityName = null) {
        return $this->entityManager->clear($entityName);
    }

    public function remove($entity) {
        return $this->entityManager->remove($entity);
    }

    public function persist($entity) {
        return $this->entityManager->persist($entity);
    }

    /**
     * @param null $entity
     * @return \Kdyby\Doctrine\EntityManager
     * @throws ORMException
     */
    public function flush($entity = null) {
        try {
            return $this->entityManager->flush($entity);
        } catch (\Exception $e) {
            throw new ORMException("Flushing failed", 0, $e);
        }
    }

    public function close() {
        $this->entityManager->close();
    }

    /**
     * @param $entity
     * @return bool|object
     * @throws ORMException
     */
    public function safePersist($entity) {
        try {
            return $this->entityManager->safePersist($entity);
        } catch (\Exception $e) {
            throw new ORMException("Failed to persist safely", 0, $e);
        }
    }

    /**
     * @param int $hydrationMode
     * @return Doctrine\ORM\Internal\Hydration\AbstractHydrator
     * @throws ORMException
     */
    public function newHydrator($hydrationMode) {
        try {
            return $this->entityManager->newHydrator($hydrationMode);
        } catch (Doctrine\ORM\ORMException $e) {
            throw new ORMException($e->getMessage(), 0, $e);
        }
    }

    public function fetch(Persistence\Query $queryObject, $hydrationMode = AbstractQuery::HYDRATE_OBJECT) {
        return $this->entityManager->fetch($queryObject, $hydrationMode);
    }

    public function fetchOne(Persistence\Query $queryObject) {
        return $this->entityManager->fetchOne($queryObject);
    }

    public function getConnection() {
        return $this->entityManager->getConnection();
    }

    public function getMetadataFactory() {
        return $this->entityManager->getMetadataFactory();
    }

    public function getExpressionBuilder() {
        return $this->entityManager->getExpressionBuilder();
    }

    public function beginTransaction() {
        $this->entityManager->beginTransaction();
    }

    public function getCache() {
        return $this->entityManager->getCache();
    }

    /**
     * @param $func
     * @return bool|mixed
     * @throws ORMException
     */
    public function transactional($func) {
        try {
            return $this->entityManager->transactional($func);
        } catch (\Throwable $e) {
            throw new ORMException("Transactional failed", 0, $e);
        }
    }

    public function commit() {
        $this->entityManager->commit();
    }

    public function rollback() {
        $this->entityManager->rollback();
    }

    public function getClassMetadata($className) {
        return $this->entityManager->getClassMetadata($className);
    }

    public function createQuery($dql = '') {
        return $this->entityManager->createQuery($dql);
    }

    public function createNamedQuery($name) {
        return $this->entityManager->createNamedQuery($name);
    }

    public function createNativeQuery($sql, ResultSetMapping $rsm) {
        return $this->entityManager->createNativeQuery($sql, $rsm);
    }

    public function createNamedNativeQuery($name) {
        return $this->entityManager->createNamedNativeQuery($name);
    }

    /**
     * @param string $entityName
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return null|object
     * @throws ORMException
     */
    public function find($entityName, $id, $lockMode = null, $lockVersion = null) {
        try {
            return $this->entityManager->find($entityName, $id, $lockMode, $lockVersion);
        } catch (\Exception $e) {
            throw new ORMException("Entity finding failed", 0, $e);
        }
    }

    /**
     * @param $entityName
     * @param $id
     * @return bool|Doctrine\Common\Proxy\Proxy|null|object
     * @throws ORMException
     */
    public function getReference($entityName, $id) {
        try {
            return $this->entityManager->getReference($entityName, $id);
        } catch (Doctrine\ORM\ORMException $e) {
            throw new ORMException($e->getMessage(), 0, $e);
        }
    }

    public function getPartialReference($entityName, $identifier) {
        return $this->entityManager->getPartialReference($entityName, $identifier);
    }

    /**
     * @param object $entity
     * @throws ORMException
     */
    public function refresh($entity) {
        try {
            $this->entityManager->refresh($entity);
        } catch (Doctrine\ORM\ORMException $e) {
            throw new ORMException($e->getMessage(), 0, $e);
        }
    }

    public function detach($entity) {
        $this->entityManager->detach($entity);
    }

    /**
     * @param object $entity
     * @return object
     * @throws ORMException
     */
    public function merge($entity) {
        try {
            return $this->entityManager->merge($entity);
        } catch (Doctrine\ORM\ORMException $e) {
            throw new ORMException($e->getMessage(), 0, $e);
        }
    }

    public function copy($entity, $deep = false) {
        $this->entityManager->copy($entity, $deep);
    }

    public function lock($entity, $lockMode, $lockVersion = null) {
        $this->entityManager->lock($entity, $lockMode, $lockVersion);
    }

    public function getRepository($entityName) {
        return $this->entityManager->getRepository($entityName);
    }

    public function contains($entity) {
        return $this->entityManager->contains($entity);
    }

    public function getEventManager() {
        return $this->entityManager->getEventManager();
    }

    public function getConfiguration() {
        return $this->entityManager->getConfiguration();
    }

    public function isOpen() {
        return $this->entityManager->isOpen();
    }

    public function getUnitOfWork() {
        return $this->entityManager->getUnitOfWork();
    }

    public function getHydrator($hydrationMode) {
        return $this->entityManager->getHydrator($hydrationMode);
    }

    public function getProxyFactory() {
        return $this->entityManager->getProxyFactory();
    }

    public function initializeObject($obj) {
        $this->entityManager->initializeObject($obj);
    }

    public function getFilters() {
        return $this->entityManager->getFilters();
    }

    public function isFiltersStateClean() {
        return $this->entityManager->isFiltersStateClean();
    }

    public function hasFilters() {
        return $this->entityManager->hasFilters();
    }

}