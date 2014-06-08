<?php

namespace Aegir\Hostmaster\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServerType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('hostname', 'textfield', array(
      'description' => 'The hostname of the server. Must resolve to the IP addresses.',
    ));

    $builder->add('ip_addresses', 'textarea', array(
      'description' => 'A list of IP addresses this server is publicly available under, one per line. If none is specified, a DNS lookup will be performed based on the server hostname above. <br /><strong>This should point to the public network, if you have such a separation.</strong>',
    ));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class'         => 'Aegir\Provision\Model\Server',
      'intention'          => 'server',
      'translation_domain' => 'AegirHostmaster'
    ));
  }

  public function getName()
  {
    return 'server';
  }
}
