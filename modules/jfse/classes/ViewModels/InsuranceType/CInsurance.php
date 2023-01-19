<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\InsuranceType;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\Insurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CInsurance
 * @package Ox\Mediboard\Jfse\ViewModels\InsuranceType
 */
class CInsurance extends CJfseViewModel
{
    /** @var array[] */
    public static $types = [
        MedicalInsurance::CODE      => [
            'code' => MedicalInsurance::CODE,
            'label' => 'medical'
        ],
        WorkAccidentInsurance::CODE => [
            'code' => WorkAccidentInsurance::CODE,
            'label' => 'work_accident'
        ],
        MaternityInsurance::CODE    => [
            'code' => MaternityInsurance::CODE,
            'label' => 'maternity'
        ],
        FmfInsurance::CODE          => [
            'code' => FmfInsurance::CODE,
            'label' => 'free_medical_fees'
        ],
    ];

    /** @var array */
    public static $name_code_types = [
        'medical'           => MedicalInsurance::CODE,
        'work_accident'     => WorkAccidentInsurance::CODE,
        'maternity'         => MaternityInsurance::CODE,
        'free_medical_fees' => FmfInsurance::CODE,
    ];

    /** @var string */
    public $invoice_id;

    /** @var int */
    public $selected_insurance_type;

    /** @var CMedicalInsurance */
    public $medical_insurance;

    /** @var CMaternityInsurance */
    public $maternity_insurance;

    /** @var CWorkAccidentInsurance */
    public $work_accident_insurance;

    /** @var CFmfInsurance */
    public $fmf_insurance;

    /**
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['invoice_id']              = 'str';
        $props['selected_insurance_type'] = 'num notNull';

        return $props;
    }

    /**
     * Create a new view model and sets its properties from the given entity
     *
     * @param AbstractEntity $entity
     *
     * @return static|null
     */
    public static function getFromEntity(AbstractEntity $entity): ?CJfseViewModel
    {
        /** @var Insurance $entity */
        $view_model = parent::getFromEntity($entity);

        $view_model->medical_insurance       = CMedicalInsurance::getFromEntity($entity->getMedicalInsurance());
        $view_model->maternity_insurance     = CMaternityInsurance::getFromEntity($entity->getMaternityInsurance());
        $view_model->work_accident_insurance = CWorkAccidentInsurance::getFromEntity(
            $entity->getWorkAccidentInsurance()
        );
        $view_model->fmf_insurance           = CFmfInsurance::getFromEntity($entity->getFmfInsurance());

        return $view_model;
    }


}
