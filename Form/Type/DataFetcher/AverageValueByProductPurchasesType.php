<?php

namespace Opos\Bundle\ReportBundle\Form\Type\DataFetcher;

use Doctrine\ORM\EntityRepository;
use Sylius\Bundle\ReportBundle\Form\Type\DataFetcher\TimePeriodType;
use Sylius\Bundle\TaxonomyBundle\Form\Type\TaxonAutocompleteChoiceType;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

    /**
     * @var EntityRepository
     */
    protected $taxonRepository;

    public function __construct(EntityRepository $attributeRepository, EntityRepository $taxonRepository)
    {
        $this->attributeRepository = $attributeRepository;
        $this->taxonRepository = $taxonRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $attributeChoices = [];
        /** @var AttributeInterface $attribute */
        foreach($this->attributeRepository->findAll() as $attribute)
        {
            if($attribute->getType() == 'integer')
            $attributeChoices[$attribute->getName()] = $attribute->getId();
        }

        $taxonChoices = [];
        /** @var TaxonInterface $taxon */
        foreach($this->taxonRepository->findAll() as $taxon)
        {
            $taxonChoices[$taxon->getName()] = $taxon->getId();
        }

        $builder
            ->add('taxons', ChoiceType::class, [
                'choices' => $taxonChoices,
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
