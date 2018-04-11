<?php

namespace AppBundle\Form;

use AppBundle\Repository\UserAdminRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartieType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('joueur2', EntityType::class, [
            'class' => 'AppBundle\Entity\UserAdmin',
            'choice_label' => 'username',
            'label' => false,
            'query_builder' => function (UserAdminRepository $repo) {
                return $repo->createQueryBuilder('u')->orderBy('u.username', 'ASC');
            },
        ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Partie'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_partie';
    }


}
