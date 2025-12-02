<?php

namespace App\Form;

use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        switch ($options['flow_step']) {
            case 1:
                $builder
                    ->add('firstName', TextType::class, ['label' => 'Prénom'])
                    ->add('lastName', TextType::class, ['label' => 'Nom'])
                    ->add('email', EmailType::class, ['label' => 'Email'])
                    ->add('phone', TextType::class, ['label' => 'Téléphone', 'required' => false])
                    ->add('hasExperience', CheckboxType::class, ['label' => 'Avez-vous de l\'expérience ?', 'required' => false]);
                break;
            case 2:
                $builder->add('experienceDetails', TextareaType::class, ['label' => 'Détails de l\'expérience']);
                break;
            case 3:
                $builder->add('availabilityDate', DateType::class, [
                    'label' => 'Date de disponibilité',
                    'widget' => 'single_text',
                    'required' => false,
                ]);
                break;
            case 4:
                $builder->add('consentRGPD', CheckboxType::class, ['label' => 'J\'accepte les conditions RGPD']);
                break;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidate::class,
        ]);
        $resolver->setDefined('flow_step');
    }

    public function getBlockPrefix()
    {
        return 'candidature';
    }
}
