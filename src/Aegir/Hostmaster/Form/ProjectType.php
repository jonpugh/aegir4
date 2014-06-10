<?php

namespace Aegir\Hostmaster\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('name', 'text', array(
      'description' => 'The name of this project.',
    ));

    $builder->add('source_url', 'text', array(
      'description' => 'The source URL of this project. Can be a git URL, a makefile, or ...',
    ));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class'         => 'Aegir\Provision\Model\Project',
      'intention'          => 'project',
      'translation_domain' => 'AegirHostmaster'
    ));
  }

  public function getName()
  {
    return 'project';
  }
}
