<?php

namespace Opos\Bundle\ReportBundle\Form\Type\DataFetcher;

use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ReportBundle\Form\Type\DataFetcher\TimePeriodType;
use Sylius\Component\Attribute\Model\AttributeInterface;
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
        parent::buildForm($builder, $options);

        $attributes = $this->attributeRepository->findAll();

        $attributeChoices = [];
        $andOrChoices = array('and' => 'AND', 'or' => 'OR');

        /** @var AttributeInterface $attribute */
        foreach($attributes as $attribute)
        {
            $attributeChoices[$attribute->getCode()] = $attribute->getName();
        }

        for($i=0; $i<2 ;$i++)
        {
            $builder
                ->add('attribute'.$i, ChoiceType::class, [
                    'choices' => $attributeChoices,
                    'multiple' => false,
                    'required' => false,
                    'label' => 'Attribute',
                ])
                ->add('attributeValue'.$i, 'text', [
                    'label' => 'Attribute Value',
                    'required' => false,
                    'attr' => [
                        'class' => ''
                    ]
                ])
            ;
            if($i < 1)
            {
                $builder
                    ->add('operator'.$i, ChoiceType::class, [
                    'choices' => $andOrChoices,
                    'label' => 'Operator',
                    'required' => false,
                ])
                ;
            }
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
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opos_data_fetcher_sales_total_by_attribute';
    }
}
