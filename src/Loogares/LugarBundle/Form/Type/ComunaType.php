<?php
namespace Acme\LugarBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ComunaType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Loogares\ExtraBundle\Entity\Couna',
        );
    }

    public function getName()
    {
        return 'nombre';
    }
}