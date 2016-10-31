<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Opos\Bundle\ReportBundle\DataFetchers;

/**
 * Tiempo medio desde la registracion de un usuario hasta la primer compra.
 *
 * Ejemplo: La media desde que un usuario se registra hasta que compra es de 20 días.
 *
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class RegistrationTimePurchaseDataFetcher extends TimePeriod
{
    /**
     * {@inheritdoc}
     */
    protected function getData(array $configuration = [])
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('DATE(min(o.completed_at)) as date', 'DATEDIFF(DATE(o.completed_at), DATE(c.created_at)) as "Tiempo medio hasta la compra"')
            ->from('sylius_customer', 'c')
            ->innerJoin('c', 'sylius_order', 'o', 'o.customer_id = c.id')
            ->where('o.completed_at IS NOT null')
            ->andWhere($queryBuilder->expr()->gte('o.completed_at', ':from'))
            ->andWhere($queryBuilder->expr()->lte('o.completed_at', ':to'))
            ->setParameter('from', $configuration['start']->format('Y-m-d H:i:s'))
            ->setParameter('to', $configuration['end']->format('Y-m-d H:i:s'))
            ->orderBy('o.completed_at','ASC')
            ->groupBy('c.id')
        ;

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
