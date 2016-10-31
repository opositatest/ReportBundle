<?php

namespace Opos\Bundle\ReportBundle\Form\Type\DataFetcher;

use Doctrine\ORM\EntityRepository;
use Sylius\Component\Attribute\Model\AttributeValue;
use Sylius\Component\Product\Model\Attribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Odiseo Team <team@odiseo.com.ar>
 */
class SalesTotalByAttributeType extends TimePeriodType
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
            $valuesAtrribute = array();
            /** @var AttributeValue $attributeValue */
            foreach($attribute->getValues() as $attributeValue)
            {
                $valuesAtrribute[$attributeValue->getId()] = $attributeValue->getValue();
            }

            $attributeChoices[$attribute->getCode()] = $valuesAtrribute;
        }

        $builder
            ->add('attributes', ChoiceType::class, [
                'choices' => $attributeChoices,
                'multiple' => true,
                'label' => 'Attribute',
            ])
            ->add('viewMode', ChoiceType::class, [
                'choices' => [
                    'quantity' => 'Ver cantidades',
                    'total' => 'Ver totales'
                ],
                'label' => 'Modo de vista',
            ])
            ->add('buyback', CheckboxType::class, [
                'label' => 'Solo recompras?',
                'required' => false
            ])
        ;

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opos_data_fetcher_sales_total_by_attribute';
    }
}
