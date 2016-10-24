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

        $queryBuilder
            ->select('DATE(o.completed_at) as date', 'p.created_at as "Product Price"')
            ->from('sylius_order', 'o')
            ->leftJoin('o','sylius_order_item', 'oi', 'o.id = oi.order_id')
            ->leftJoin( 'oi','sylius_product_variant', 'v', 'oi.variant_id = v.id')
            ->leftJoin( 'v','sylius_product', 'p',  'v.product_id = p.id')
            ->where('o.completed_at IS NOT null')
        ;

        $productsPrice = $queryBuilder->execute()->fetchAll();

        if (empty($productsPrice)) {
            return [];
        }

        $labels = array_keys($productsPrice[0]);

        $ivaTax = 0;
        if(($configuration['iva']))
        {
            $ivaTax = 21;
        }

        $productPriceTotal = array();
        foreach($productsPrice as $productPrice)
        {
            $date = new \DateTime($productPrice[$labels[0]]);
            $dateFormated = $date->format($configuration['presentationFormat']);

            $currentProductPrice = isset($productPriceTotal[$dateFormated])?$productPriceTotal[$dateFormated]:array('price' => 0);

            $currentProductPrice['price'] = $currentProductPrice['price']+$productPrice[$labels[1]];

            $productPriceTotal[$dateFormated] = $currentProductPrice;
        }

        $fetched = [];
        foreach($productPriceTotal as $date => $productPrice)
        {
            $fetched[] = [
                $labels[0] => $date,
                $labels[1] => $productPrice['price']+(($productPrice['price']*$ivaTax)/100)
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
