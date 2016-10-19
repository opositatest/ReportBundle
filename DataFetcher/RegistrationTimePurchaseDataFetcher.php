<?php

namespace OpositaTest\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use OpositaTest\Bundle\ReportBundle\DataFetchers;
use Sylius\Bundle\ReportBundle\DataFetcher\TimePeriod;

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
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(array $configuration = [])
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('DATE(o.completed_at) as date', 'DATEDIFF(DATE(o.completed_at), DATE(c.created_at)) as "Tiempo medio hasta la compra"')
            ->from('sylius_customer', 'c')
            ->leftJoin('c', 'sylius_order', 'o', 'o.customer_id = c.id')
            ->where('o.completed_at IS NOT null')
        ;

        $ordersCompleted = $queryBuilder->execute()->fetchAll();

        if (empty($ordersCompleted)) {
            return [];
        }

        $labels = array_keys($ordersCompleted[0]);

        $datesMedia = array();
        foreach($ordersCompleted as $orderCompleted)
        {
            $date = new \DateTime($orderCompleted[$labels[0]]);
            $dateFormated = $date->format($configuration['presentationFormat']);

            $currentDateMedia = isset($datesMedia[$dateFormated])?$datesMedia[$dateFormated]:array('quantity' => 0, 'media' => 0);

            $currentDateMedia['quantity'] = $currentDateMedia['quantity']+1;
            $currentDateMedia['media'] = $currentDateMedia['media']+$orderCompleted[$labels[1]];

            $datesMedia[$dateFormated] = $currentDateMedia;
        }

        $fetched = [];
        foreach($datesMedia as $date => $dateMedia)
        {
            $fetched[] = [
                $labels[0] => $date,
                $labels[1] => round($dateMedia['media']/$dateMedia['quantity'], 1)
            ];
        }

        return $fetched;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return DataFetchers::REGISTRATION_TIME_PURCHASE;
    }
}
