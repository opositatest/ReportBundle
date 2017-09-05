<?php

namespace Opos\Bundle\ReportBundle\Form\Type\DataFetcher;

use Sylius\Bundle\ReportBundle\Form\Type\DataFetcher\TimePeriodType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class SalesTotalType extends TimePeriodType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('iva', 'checkbox', [
                'label' => 'Con Iva?',
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opos_data_fetcher_sales_total';
    }
}
