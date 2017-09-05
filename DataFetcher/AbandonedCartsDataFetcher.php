<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Opos\Bundle\ReportBundle\Form\Type\DataFetcher\AbandonedCartsType;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Order\Model\OrderInterface;

/**
 * Un usuario que no completa el pago, es un carrito abandonado y queda en la base
 * de datos como pendiente. Muestra el número y porcentaje de carritos abandonados.
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

        $showByQuantity = $configuration['showByQuantity'];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('DATE(o.created_at) as date', 'COUNT(o.id) as "Abandoned carts"')
            ->from('sylius_order', 'o')
        ;
        $queryBuilder = $this->addTimePeriodQueryBuilder($queryBuilder, $configuration,'o.created_at');
        $totalCarts = $queryBuilder->execute()->fetchAll();

        $queryBuilder
            ->select('DATE(o.created_at) as date', 'COUNT(o.id) as "Abandoned carts"')
            ->from('sylius_order', 'o')
            ->andWhere('o.state = :state')
            ->andWhere('o.checkout_state = :checkout_state')
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('checkout_state', OrderCheckoutStates::STATE_CART)
        ;
        $queryBuilder = $this->addTimePeriodQueryBuilder($queryBuilder, $configuration,'o.created_at');

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
            // mostrar porcentaje o cantidad
            if(!$showByQuantity)
            {
                $abandonedCart[$labels[1]] = round($abandonedCart[$labels[1]]*100/$total, 2).'%';
            }

            $fetched[] = $abandonedCart;
        }

        return $fetched;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return AbandonedCartsType::class;
    }
}
