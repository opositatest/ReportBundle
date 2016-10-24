<?php

namespace OpositaTest\Bundle\ReportBundle\Form\Type\DataFetcher;

use Sylius\Bundle\CoreBundle\DataFetcher\NumberOfOrdersDataFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class AbandonedCartsType extends TimePeriodType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opositatest_data_fetcher_abandoned_carts';
    }
}
