<?php
namespace ProductBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AttributeAdmin extends AbstractAdmin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', 'text', array(
                'label' => 'Название'
            ))
            ->add('code', 'text', array(
                'label' => 'Код'
            ))
            ->add('productTypes', EntityType::class, [
                'label' => 'Тип продукта',
                'class' => 'ProductBundle:ProductType',
                'choice_label' => 'name',
                'required' => true,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('minValue', 'number', array(
                'label' => 'Минимальное значение',
                'required'  => false
            ))
            ->add('maxValue', 'number', array(
                'label' => 'Минимальное значение',
                'required'  => false
            ))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('code')
        ;
    }

    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
        ;
    }
}
