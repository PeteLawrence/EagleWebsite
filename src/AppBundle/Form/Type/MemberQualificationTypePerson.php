<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use AppBundle\Entity\Person;

class MemberQualificationTypePerson extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('qualification', EntityType::class, [
                'class' => 'AppBundle:Qualification',
                'choice_label' => 'name',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('q')->orderBy('q.name');
                }
            ])
            ->add('reference', TextType::class, [
                'attr' => [
                    'placeholder' => 'eg. Certificate Number',
                ],
                'required' => false
            ])
            ->add('validFrom', DateType::class, [
                'html5' => true,
                'widget' => 'choice',
                'format' => 'd MMMM y',
                'years' => range(1975, 2030)
            ])
            ->add('validTo', DateType::class, [
                'html5' => true,
                'widget' => 'choice',
                'format' => 'd MMMM y',
                'label' => 'Valid to (if applicable)',
                'years' => range(1975, 2030),
                'required' => false
            ])
            ->add('notes', TextareaType::class, [
                'attr' => [
                    'rows' => 5
                ],
                'required' => false
            ])
            ->add('superseded', CheckboxType:: class, [
                'required' => false
            ])
        ;
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\MemberQualification'
        ));
    }
}
