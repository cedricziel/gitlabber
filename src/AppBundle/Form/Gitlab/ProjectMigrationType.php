<?php

namespace AppBundle\Form\Gitlab;

use AppBundle\Entity\Gitlab\Group;
use AppBundle\Entity\Gitlab\Host;
use AppBundle\Model\ProjectMigration;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * {@inheritdoc}
 */
class ProjectMigrationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('targetHost', EntityType::class, ['class' => Host::class, 'choice_label' => 'name']);

        $groupFormModifier = function (FormInterface $form, Host $host = null) {
            if ($host === null) {
                return;
            }
            $groups = null === $host ? [] : $host->getGroups();

            $form->add(
                'targetGroup',
                EntityType::class,
                [
                    'class'        => Group::class,
                    'placeholder'  => '',
                    'choices'      => $groups,
                    'choice_label' => 'name',
                ]
            );
        };

        $nameFormModifier = function (FormInterface $form) {
            $form->add(
                'targetName',
                TextType::class,
                ['required' => true, 'constraints' => [new NotBlank(), new Length(['min' => 3])]]
            );
        };

        $resultingTextModifier = function (FormInterface $form) {
            $form->add('yes', SubmitType::class);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $formEvent) use ($groupFormModifier) {
                /** @var ProjectMigration $data */
                $data = $formEvent->getData();

                $groupFormModifier($formEvent->getForm(), $data->getTargetHost());
            }
        );

        $builder->get('targetHost')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($groupFormModifier) {
                $host = $event->getForm()->getData();

                $groupFormModifier($event->getForm()->getParent(), $host);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($nameFormModifier) {

                $data = $event->getData();

                if (array_key_exists('targetGroup', $data) && $data['targetGroup'] !== null) {
                    $nameFormModifier($event->getForm());
                }
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($resultingTextModifier) {

                $data = $event->getData();

                if (array_key_exists('targetName', $data)) {
                    $resultingTextModifier($event->getForm());
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => ProjectMigration::class]);
    }
}
