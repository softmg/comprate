<?php
namespace ProductBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ProductAdmin extends AbstractAdmin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array(
                'label' => 'Название'
            ))
            ->add('vendor', EntityType::class, [
                'label' => 'Производитель',
                'class' => 'ProductBundle:Vendor',
                'choice_label' => 'name',
                'required' => true,
                'multiple' => false,
                'expanded' => true
            ])->end()->with('productAttributes')->add('productAttributes', 'sonata_type_collection', ['by_reference' => false],[
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'name',
                ])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('vendor')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('vendor')
            ->add("ProductAttributesString")
        ;
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('vendor')
            ->add('attributes')
        ;
    }


}
