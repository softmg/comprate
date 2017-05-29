<?php

namespace ProductBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ProductAdmin extends AbstractAdmin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('name', 'text', ['label' => 'Название'])
            ->add('type', null, ['label' => 'Тип'])
            ->add('rate', null, ['label' => 'Рейтинг'])
            ->add('isActual', null, ['label' => 'Актуальный?'])
            ->end()
            ->with('productAttributes')
            ->add('productAttributes', 'sonata_type_collection', [
                    'by_reference' => false
                ], [
                'edit' => 'inline',
                'inline' => 'table',
                'sortable' => 'name',
            ]);
    }
    
    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name', null, ['show_filter' => true])
            ->add('type', null, ['show_filter' => true]);
    }
    
    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('name')->add('type')->add("ProductAttributesString");
    }
    
    // Fields to be shown on show action
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('name')->add('vendor')->add('attributes');
    }
}
