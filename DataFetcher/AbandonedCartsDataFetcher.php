<?php

namespace OpositaTest\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use OpositaTest\Bundle\ReportBundle\DataFetchers;
use Sylius\Component\Order\Model\OrderInterface;

/**
 * Un usuario que no completa el pago, es un carrito abandonado y queda en la base
 * de datos como pendiente. Mostrar el número y porcentaje de carritos abandonados.
 *
 * Ejemplo: 20% de los carritos han sido abandonado el mes de Enero de 2016
 *
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class AbandonedCartsDataFetcher extends TimePeriod
{
    /**
     * {@inheritdoc}
     */
    protected function getData(array $configuration = [])
    {
        $groupBy = $this->getGroupBy($configuration);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('DATE(o.created_at) as date', 'COUNT(o.id) as "Abandoned carts"')
            ->from('sylius_order', 'o')
            ->groupBy($groupBy)
            ->orderBy($groupBy)
        ;
        $totalCarts = $queryBuilder->execute()->fetchAll();

        $queryBuilder
            ->select('DATE(o.created_at) as date', 'COUNT(o.id) as "Abandoned carts"')
            ->from('sylius_order', 'o')
            ->andWhere('o.state = :state')
            ->setParameter('state', OrderInterface::STATE_CART)
            ->groupBy($groupBy)
            ->orderBy($groupBy)
        ;
        $totalAbandonedCarts = $queryBuilder->execute()->fetchAll();

        if (empty($totalAbandonedCarts)) {
            return [];
        }

        $labels = array_keys($totalAbandonedCarts[0]);

        $fetched = array();
        foreach($totalAbandonedCarts as $abandonedCart)
        {
            $total = $abandonedCart[$labels[1]];
            foreach($totalCarts as $cart)
            {
                switch ($configuration['period']) {
                    case self::PERIOD_DAY:
                        if($cart[$labels[0]] == $abandonedCart[$labels[0]])
                        {
                            $total = $cart[$labels[1]];
                        }
                        break;
                    case self::PERIOD_MONTH:
                        $cartDate = new \DateTime($cart[$labels[0]]);
                        $abandonedCartDate = new \DateTime($abandonedCart[$labels[0]]);
                        if($cartDate->format('m') == $abandonedCartDate->format('m'))
                        {
                            $total = $cart[$labels[1]];
                        }
                        break;
                    case self::PERIOD_YEAR:
                        $cartDate = new \DateTime($cart[$labels[0]]);
                        $abandonedCartDate = new \DateTime($abandonedCart[$labels[0]]);
                        if($cartDate->format('Y') == $abandonedCartDate->format('Y'))
                        {
                            $total = $cart[$labels[1]];
                        }
                        break;
                }
            }

            $abandonedCart[$labels[1]] = round($abandonedCart[$labels[1]]*100/$total, 2).'%';
            $fetched[] = $abandonedCart;
        }

        return $fetched;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return DataFetchers::ABANDONED_CARTS;
    }
}
