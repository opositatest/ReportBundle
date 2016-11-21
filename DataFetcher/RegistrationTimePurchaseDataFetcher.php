<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Opos\Bundle\ReportBundle\DataFetchers;
use Sylius\Component\Taxation\Model\TaxCategory;
use Doctrine\ORM\EntityManager;

/**
 * Tiempo medio desde la registracion de un usuario hasta la primer compra.
 *
 * Ejemplo: La media desde que un usuario se registra hasta que compra es de 20 días.
 *
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class RegistrationTimePurchaseDataFetcher extends TimePeriod
{
    protected function getData(array $configuration = [])
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder
          //  ->select('o.id, DATE(min(o.completed_at)) as date', 'DATEDIFF(DATE(o.completed_at), DATE(c.created_at)) as "Tiempo medio hasta la compra"')
            ->select('o.id')
            ->from('sylius_customer', 'c')
            ->innerJoin('c', 'sylius_order', 'o', 'o.customer_id = c.id')
            ->where('o.completed_at IS NOT null')
        ;


        $queryBuilder
            ->orderBy('o.completed_at','ASC')
            ->groupBy('c.id')
        ;

        $ordersCompleted = $queryBuilder->execute()->fetchAll();
        $ordersId = array_map(function($n){
            return "'".$n['id']."'";
        }, $ordersCompleted);
        $ordersId=implode(",",$ordersId);

        $queryBuilder
            ->select('DATE((o.completed_at)) as date', 'DATEDIFF(DATE(o.completed_at), DATE(c.created_at)) as "Tiempo medio hasta la compra"')
            ->andWhere("o.id IN(".$ordersId.")")
        ;

        $queryBuilder = $this->addTimePeriodQueryBuilder($queryBuilder, $configuration);

        $ordersCompleted = $queryBuilder->execute()->fetchAll();

        return $this->getMediaResults($ordersCompleted, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return DataFetchers::REGISTRATION_TIME_PURCHASE;
    }
}
