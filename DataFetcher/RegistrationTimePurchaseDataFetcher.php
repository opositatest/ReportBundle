<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Opos\Bundle\ReportBundle\DataFetchers;

/**
 * Tiempo medio hasta la compra. Mostrar un gráfico de tiempo que calcula la
 * diferencia entre el registro y la compra, en el caso de ser un usuario que nunca ha
 * comprado. Filtrable por fechas.
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
            ->orderBy('o.completed_at','ASC')
            ->groupBy('c.id')
        ;

       // $queryBuilder = $this->addTimePeriodQueryBuilder($queryBuilder, $configuration);

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
