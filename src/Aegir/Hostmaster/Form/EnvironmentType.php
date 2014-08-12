<?php

namespace Aegir\Hostmaster\Form;
namespace Aegir\Provision\Project;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EnvironmentType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('name', 'text', array(
      'description' => 'The name of this environment. Must be unique within the project',
    ));
    $builder->add('projects', 'entity', array(
      'class' => 'AegirProvision:Project',
      'property' => 'name',
    ));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class'         => 'Aegir\Provision\Model\Environment',
      'intention'          => 'environment',
      'translation_domain' => 'AegirHostmaster'
    ));
  }

  public function getName()
  {
    return 'environment';
  }
}
