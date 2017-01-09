<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Opos\Bundle\ReportBundle\DataFetchers;
use Sylius\Component\Core\OrderCheckoutStates;
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

    protected function getAttrValueClause($attrValue, $key)
    {
        return $whereAttrValue = 'av'.$key.'.text_value LIKE "'.$attrValue.'" OR av'.$key.'.integer_value LIKE "'.$attrValue.'" OR av'.$key.'.datetime_value LIKE "'.$attrValue.'" OR av'.$key.'.date_value LIKE "'.$attrValue.'" OR av'.$key.'.boolean_value LIKE "'.$attrValue.'" OR av'.$key.'.float_value LIKE "'.$attrValue.'"))'
        ;
    }

    protected function getValueOrNull($valueCollection, $valueIndex)
    {
        if(isset($valueCollection[$valueIndex]))
        {
            return $valueCollection[$valueIndex];
        }else{
            return null;
        }

    }

    /**
     * {@inheritdoc}
     */
    protected function getData(array $configuration = [])
    {
        $i = 0;
        $attributes = [];
        $attributesValue = [];
        $operators = [];
        while (isset($configuration['attribute'.$i]) && $configuration['attribute'.$i] != '')
        {
            $attributes[] = $this->getValueOrNull($configuration, 'attribute'.$i);

            $attributesValue[] =  $this->getValueOrNull($configuration, 'attributeValue'.$i);

            $operators[] = $this->getValueOrNull($configuration, 'operator'.$i);

            $i++;
        }

        $buyback = $configuration['buyback'];

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $baseCurrencyCode = $configuration['baseCurrency'] ? 'in '.$configuration['baseCurrency']->getCode() : '';
        $secondSelect = 'COUNT(DISTINCT(o.id)) as "Cantidad"';
        if($configuration['viewMode'] == 'total')
        {
            $secondSelect = 'TRUNCATE((o.total * o.exchange_rate)/100,2) as "total sum '.$baseCurrencyCode.'"';
        }

        $queryBuilder
            ->select('DATE(o.completed_at) as date', $secondSelect)
        ;

        $queryBuilder = $this->addQueriesByAttributeId($queryBuilder, $attributes, $attributesValue, $operators);
        $queryBuilder = $this->addTimePeriodQueryBuilder($queryBuilder, $configuration);

        if($buyback)
        {
            // Fetch the orders by attribute
            $ordersFetched = $this->getBuybackOrdersWithAttribute($configuration, $attributes, $attributesValue,$operators);

            // validacion - forzar a que no traiga ningun valor si no tuvo recompras
            if(count($ordersFetched)<=0)$ordersFetched = array(1=>0);

            $queryBuilder->andWhere($queryBuilder->expr()->in('o.id', $ordersFetched));
        }

        return $queryBuilder
            ->execute()
            ->fetchAll()
            ;
    }

    protected function getBuybackOrdersWithAttribute(array $configuration = [], $attributes, $attributesValue,$operators)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        //Get the orders to verify wich is a buyback
        $queryBuilder
            ->select('o.id as O_ID', 'c.id as C_ID', 'p.id P_ID')
        ;
        $queryBuilder = $this->addQueriesByAttributeId($queryBuilder, $attributes, $attributesValue,$operators);

        $orders = $queryBuilder->execute()->fetchAll();

        $ordersFetched = [];
        $productBuybacks = [];
        foreach($orders as $order)
        {
            $value = $order['C_ID'];
            if(in_array($value, $productBuybacks))
            {
                $ordersFetched[] = $order['O_ID'];
            }
            $productBuybacks[] = $value;
        }

        return $ordersFetched;
    }

    protected function addQueriesByAttributeId(QueryBuilder $queryBuilder, $attributes, $attributesValue,$operators)
    {
        $queryBuilder
            ->from('sylius_order', 'o')
            ->leftJoin('o','sylius_customer', 'c', 'c.id = o.customer_id')
            ->leftJoin('o','sylius_order_item', 'oi', 'o.id = oi.order_id')
            ->leftJoin( 'oi','sylius_product_variant', 'v', 'oi.variant_id = v.id')
            ->leftJoin( 'v','sylius_product', 'p',  'v.product_id = p.id')
            ->andWhere('o.completed_at IS NOT null')
            ->andWhere('o.state = :state')
            ->andWhere('o.checkout_state = :checkout_state OR o.payment_state = :payment_state')
            ->setParameter('state', OrderInterface::STATE_CONFIRMED)
            ->setParameter('checkout_state', OrderCheckoutStates::STATE_COMPLETED)
            ->setParameter('payment_state', PaymentInterface::STATE_COMPLETED)
        ;

        $andWhere = '';

        if(count($attributes) > 0 && count($attributesValue) > 0){
            foreach($attributes as $key=>$attribute)
            {
                $queryBuilder
                    ->leftJoin( 'p','sylius_product_attribute_value', 'av'.$key,  'p.id = av'.$key.'.product_id')
                    ->leftJoin( 'av'.$key,'sylius_product_attribute', 'a'.$key,  'a'.$key.'.id = av'.$key.'.attribute_id')
                ;
                if($key == 0){
                    $andWhere .= '(a'.$key.'.code = "'.$attribute.'"';
                   /* $queryBuilder
                        ->andWhere('a.code = :attribute'.$key)
                    ;*/
                }else if($operators[$key-1] == 'and'){
                    $andWhere .= ' AND (a'.$key.'.code = "'.$attribute.'"';
                }else{
                    $andWhere .= ' OR (a'.$key.'.code = "'.$attribute.'"';
                }

                $andWhere .= ' AND ('.$this->getAttrValueClause($attributesValue[$key], $key);

            }
        }
        $queryBuilder->andWhere($andWhere);
        return $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return DataFetchers::SALES_TOTAL_BY_ATTRIBUTE;
    }
}
