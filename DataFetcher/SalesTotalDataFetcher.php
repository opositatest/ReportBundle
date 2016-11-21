<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Opos\Bundle\ReportBundle\DataFetchers;

/**
 * Total de compras. Con y sin IVA
 *
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class SalesTotalDataFetcher extends TimePeriod
{
    /**
     * {@inheritdoc}
     */
    protected function getData(array $configuration = [])
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $baseCurrencyCode = $configuration['baseCurrency'] ? 'in '.$configuration['baseCurrency']->getCode() : '';

        $queryBuilder
            ->select('DATE(o.completed_at) as date', 'TRUNCATE((o.total * o.exchange_rate)/100,3) as "Total '.$baseCurrencyCode.'"')
            ->from('sylius_order', 'o')
            ->leftJoin('o','sylius_order_item', 'oi', 'o.id = oi.order_id')
            ->leftJoin( 'oi','sylius_product_variant', 'v', 'oi.variant_id = v.id')
            ->leftJoin( 'v','sylius_product', 'p',  'v.product_id = p.id')
            ->where('o.completed_at IS NOT null')
        ;

        $queryBuilder
            ->andWhere($queryBuilder->expr()->gte('o.completed_at', ':from'))
            ->andWhere($queryBuilder->expr()->lte('o.completed_at', ':to'))
            ->setParameter('from', $configuration['start']->format('Y-m-d H:i:s'))
            ->setParameter('to', $configuration['end']->format('Y-m-d H:i:s'))
        ;

        $ordersCompleted = $queryBuilder->execute()->fetchAll();

        if (empty($ordersCompleted)) {
            return [];
        }

        $labels = array_keys($ordersCompleted[0]);

        $ivaTax = 0;
        if(($configuration['iva']))
        {
            $ivaTax = 21;
        }

        $productPriceTotal = array();
        foreach($ordersCompleted as $orderCompleted)
        {
            $date = new \DateTime($orderCompleted[$labels[0]]);
            $dateFormated = $date->format($configuration['presentationFormat']);

            $currentProductPrice = isset($productPriceTotal[$dateFormated])?$productPriceTotal[$dateFormated]:array('price' => 0);

            $currentProductPrice['price'] = $currentProductPrice['price']+$orderCompleted[$labels[1]];

            $productPriceTotal[$dateFormated] = $currentProductPrice;
        }

        $fetched = [];
        foreach($productPriceTotal as $date => $productPrice)
        {
            $fetched[] = [
                $labels[0] => $date,
                $labels[1] => round($productPrice['price']+(($productPrice['price']*$ivaTax)/100),2,PHP_ROUND_HALF_UP)
            ];
        }

        return $fetched;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return DataFetchers::SALES_TOTAL;
    }
}
