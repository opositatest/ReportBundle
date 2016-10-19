<?php

namespace OpositaTest\Bundle\ReportBundle\DataFetcher;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use OpositaTest\Bundle\ReportBundle\DataFetchers;
use Sylius\Bundle\ReportBundle\DataFetcher\TimePeriod;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Model\OrderInterface;

/**
 * Número de compras de un tipo de producto (un producto puede tener un atributo
 * cualquiera “XXXX”, saber cuantas compras de productos que tengan XXXX se han
 * hecho) y totales (con su importe)
 *
 * Ejemplo: El usuario elige la fecha y el atributo “ODISEO” entre todos los
 * disponibles, y verá como resultado el producto con atributo “ODISEO” se ha
 * comprado 70 veces entre el 1 de Enero de 2016 y el 15 de Marzo de 2016
 * con un importe total de $3.000
 *
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class SalesTotalByAttributeDataFetcher extends TimePeriod
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
        $queryBuilder = $this->orderRepository->createQueryBuilder('o');

        $queryBuilder
            ->andWhere('o.state = :state')
            ->setParameter('state', OrderInterface::STATE_CART)
        ;

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return DataFetchers::SALES_TOTAL_BY_ATTRIBUTE;
    }
}
