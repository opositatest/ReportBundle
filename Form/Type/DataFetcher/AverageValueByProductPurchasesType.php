<?php

namespace Opos\Bundle\ReportBundle\Form\Type\DataFetcher;

use Doctrine\ORM\EntityRepository;
use Sylius\Component\Product\Model\Attribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class AverageValueByProductPurchasesType extends TimePeriodType
{
    /**
     * @var EntityRepository
     */
    protected $attributeRepository;

    public function __construct(EntityRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attributes = $this->attributeRepository->findAll();

        $attributeChoices = [];
        /** @var Attribute $attribute */
        foreach($attributes as $attribute)
        {
            if($attribute->getType() == 'integer')
            $attributeChoices[$attribute->getId()] = $attribute->getCode();
        }
        parent::buildForm($builder, $options);
        $builder
            ->add('taxons', 'sylius_taxon_choice', [
                'required' => false,
                'multiple' => true,
                'label' => 'sylius.form.product.taxons',
            ])
            ->add('attributes', ChoiceType::class, [
                'choices' => $attributeChoices,
                'multiple' => true,
                'required' => true,
                'label' => 'Attribute',
                'placeholder' => 'Choose attribute',
                'empty_data'  => null
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opos_data_fetcher_average_value_by_product_purchases';
    }
}
