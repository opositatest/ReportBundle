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
        $andOrChoices = array('and' => 'AND', 'or' => 'OR');

        /** @var Attribute $attribute */
        foreach($attributes as $attribute)
        {
            $attributeChoices[$attribute->getCode()] = $attribute->getName();
        }

        $len = count($attributes);
        foreach($attributes as $key=>$attribute)
        {
            $builder
                ->add('attribute'.$key, ChoiceType::class, [
                    'choices' => $attributeChoices,
                    'empty_value' => '',
                    'multiple' => false,
                    'required' => false,
                    'label' => 'Attribute',
                ])
                ->add('attributeValue'.$key, 'text', [
                    'label' => 'Attribute Value',
                    'required' => false,
                    'attr' => [
                        'class' => ''
                    ]
                ])
                ->add('operator'.$key, ChoiceType::class, [
                    'choices' => $andOrChoices,
                    'label' => 'Operator',
                    'required' => false,
                ])
            ;
        }

        $builder
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
