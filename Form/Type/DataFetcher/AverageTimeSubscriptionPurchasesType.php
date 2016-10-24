<?php

namespace OpositaTest\Bundle\ReportBundle\Form\Type\DataFetcher;

use Sylius\Bundle\CoreBundle\DataFetcher\NumberOfOrdersDataFetcher;
use Sylius\Component\Core\Model\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class AverageTimeSubscriptionPurchasesType extends TimePeriodType
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
        return 'opositatest_data_fetcher_average_time_subscription_purchases';
    }
}
