<?php

namespace OpositaTest\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use OpositaTest\Bundle\ReportBundle\DataFetchers;

class AverageTimeSubscriptionPurchasesDataFetcher extends TimePeriod
{
    /**
     * {@inheritdoc}
     */
    protected function getData(array $configuration = [])
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('DATE(o.completed_at) as date', 'av.integer_value as "Subscription Time"')
            ->from('sylius_order', 'o')
            ->leftJoin('o','sylius_order_item', 'oi', 'o.id = oi.order_id')
            ->leftJoin( 'oi','sylius_product_variant', 'v', 'oi.variant_id = v.id')
            ->leftJoin( 'v','sylius_product', 'p',  'v.product_id = p.id')
            ->leftJoin( 'p','sylius_product_attribute_value', 'av',  'p.id = av.product_id')
            ->leftJoin( 'av','sylius_product_attribute', 'a',  'a.id = av.attribute_id')
            ->where('o.completed_at IS NOT null')
            ->where('a.code = "suscripcion"')
            ->where('av.integer_value IS NOT null')
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
        return DataFetchers::AVERAGE_TIME_SUBSCRIPTION_PURCHASES;
    }
}
