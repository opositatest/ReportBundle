<?php

namespace OpositaTest\Bundle\ReportBundle\Form\Type\DataFetcher;

use Doctrine\ORM\EntityRepository;
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
            $attributeChoices[$attribute->getId()] = $attribute->getName();
        }

        $builder
            ->add('attribute', ChoiceType::class, [
                'choices' => $attributeChoices,
                'multiple' => false,
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
        return 'opositatest_data_fetcher_sales_total_by_attribute';
    }
}
