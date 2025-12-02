<?php

namespace App\Form\Flow;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;
use App\Form\CandidatureType;

class CandidateApplicationFlow extends FormFlow {

    protected function loadStepsConfig() {
        return [
            [
                'label' => 'Informations personnelles',
                'form_type' => CandidatureType::class,
            ],
            [
                'label' => 'Expérience',
                'form_type' => CandidatureType::class,
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    $formData = $flow->getFormData();
                    return !$formData->isHasExperience();
                },
            ],
            [
                'label' => 'Disponibilité',
                'form_type' => CandidatureType::class,
            ],
            [
                'label' => 'Consentement',
                'form_type' => CandidatureType::class,
            ],
            [
                'label' => 'Confirmation',
            ],
        ];
    }

}
