<?php

namespace ProductBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;

class ProductAttributeAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('value')
            ->add('maxValue')
            ->add('maxNumber')
            ->add('isRequired')
            ->add('isRequiredChoice');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('product.name')
            ->add('value')
            ->add('maxValue')
            ->add('maxNumber')
            ->add('isRequired')
            ->add('isRequiredChoice')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('product', 'sonata_type_model_autocomplete', ['property' => 'name',
                'to_string_callback' => function ($entity, $property) {
                    return $entity->getName();
                }]
        );

        $formMapper
            ->add('attribute', 'sonata_type_model', [
                'property' => 'nameWithProductType'
            ])
            ->add('value')
            ->add('maxValue')
            ->add('maxNumber')
            ->add('isRequired')
            ->add('isRequiredChoice');
    }
    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('product')
            ->add('value')
            ->add('maxValue')
            ->add('maxNumber')
            ->add('isRequired')
            ->add('isRequiredChoice');
    }
}
