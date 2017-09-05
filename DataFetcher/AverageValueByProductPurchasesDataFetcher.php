<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Opos\Bundle\ReportBundle\DataFetchers;
use Opos\Bundle\ReportBundle\Form\Type\DataFetcher\AverageValueByProductPurchasesType;

/**
 * Valor promedio del valor de un tipo de producto de todas las compras completadas
 *
 * EJ: tiempo medio de subscripcion
 *
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class AverageValueByProductPurchasesDataFetcher extends TimePeriod
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
        $taxons = $configuration['taxons'];
        $attributes = $configuration['attributes'];

        $queryBuilder
            ->select('DATE(o.completed_at) as date', 'av.integer_value as "Value"')
            ->from('sylius_order', 'o')
            ->leftJoin('o','sylius_order_item', 'oi', 'o.id = oi.order_id')
            ->leftJoin( 'oi','sylius_product_variant', 'v', 'oi.variant_id = v.id')
            ->leftJoin( 'v','sylius_product', 'p',  'v.product_id = p.id')
            ->leftJoin( 'p','sylius_product_attribute_value', 'av',  'p.id = av.product_id')
            ->leftJoin( 'av','sylius_product_attribute', 'a',  'a.id = av.attribute_id')
            ->where('o.completed_at IS NOT null')
            ->andWhere('av.integer_value IS NOT null')
        ;
        if($taxons != null) {
            foreach ($taxons as $taxon) {
                $queryBuilder
                    ->andWhere('p.main_taxon_id = :id')
                    ->setParameter('id', $taxon->getId());
            }
        }
        if($attributes != null) {
            foreach ($attributes as $attributeId) {
                $queryBuilder
                    ->andWhere('a.id = :attributeId')
                    ->setParameter('attributeId', $attributeId);
            }
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->gte('o.completed_at', ':from'))
            ->andWhere($queryBuilder->expr()->lte('o.completed_at', ':to'))
            ->setParameter('from', $configuration['start']->format('Y-m-d H:i:s'))
            ->setParameter('to', $configuration['end']->format('Y-m-d H:i:s'))
        ;
        $ordersCompleted = $queryBuilder->execute()->fetchAll();

        return $this->getMediaResults($ordersCompleted, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return AverageValueByProductPurchasesType::class;
    }
}