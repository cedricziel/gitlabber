<?php

namespace AppBundle\Form\Gitlab;

use AppBundle\Entity\Gitlab\Host;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * {@inheritdoc}
 */
class MigrateProjectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('host', EntityType::class, ['class' => Host::class, 'choice_label' => 'name']);
    }
}
