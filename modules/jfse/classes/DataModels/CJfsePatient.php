<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Exception;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Patients\CPatient;

final class CJfsePatient extends CMbObject
{
    /** @var int */
    public $jfse_patient_id;

    /** @var int */
    public $patient_id;

    /** @var string */
    public $nir;

    /** @var string */
    public $certified_nir;

    /** @var string */
    public $birth_date;

    /** @var int */
    public $birth_rank;

    /** @var string */
    public $quality;

    /** @var string  */
    public $last_name;

    /** @var string  */
    public $first_name;

    /** @var string  */
    public $amo_regime_code;

    /** @var string  */
    public $amo_managing_fund;

    /** @var string  */
    public $amo_managing_center;

    /** @var string  */
    public $amo_managing_code;

    /** @var CPatient */
    public $_patient;

    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'jfse_patients';
        $spec->key   = 'jfse_patient_id';

        return $spec;
    }

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["patient_id"]          = "ref class|CPatient back|jfse_patient cascade";
        $props["nir"]                 = "str";
        $props['certified_nir']       = 'str';
        $props["birth_date"]          = "str";
        $props["birth_rank"]          = "num notNull";
        $props["quality"]             = "str notNull";
        $props['last_name']           = "str";
        $props['first_name']          = "str";
        $props['amo_regime_code']     = "str";
        $props['amo_managing_fund']   = "str";
        $props['amo_managing_center'] = "str";
        $props['amo_managing_code']   = "str";

        return $props;
    }

    public function loadPatient(): ?CPatient
    {
        if (!$this->_patient) {
            $this->_patient = $this->loadFwdRef('patient_id');
        }

        return $this->_patient;
    }

    public function store(): ?string
    {
        return parent::store();
    }

    /**
     * @param CPatient    $patient
     * @param string|null $vitale_nir An optional nir, to handle the case of children,
     *                                who can be linked to several vital cards
     *
     * @return static|null
     * @throws \Exception
     */
    public static function getFromPatient(CPatient $patient, string $vitale_nir = null): ?self
    {
        if ($patient->countBackRefs('jfse_patient') > 1 && $vitale_nir) {
            $back_refs = $patient->loadBackRefs('jfse_patient', null, '0, 1', null, null, null, '', [
                'jfse_patients.nir' => " = '$vitale_nir'"
            ]);
        } else {
            $back_refs = $patient->loadBackRefs('jfse_patient', null, '0, 1');
        }

        $jfse_patient = null;
        if (is_array($back_refs)) {
            $jfse_patient = reset($back_refs);
        }

        return ($jfse_patient && $jfse_patient->_id) ? $jfse_patient : null;
    }

    /**
     * Checks if the given patient is linked to a CJfsePatient with the given NIR
     *
     * @param CPatient $patient
     * @param string   $vitale_nir
     *
     * @return bool
     */
    public static function patientIsLinkedToNir(CPatient $patient, string $vitale_nir): bool
    {
        try {
            $result = (bool)$patient->countBackRefs('jfse_patient', ['jfse_patients.nir' => " = '$vitale_nir'"]);
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    public static function isPatientLinked(CPatient $patient): bool
    {
        $jfse_patient             = new self();
        $jfse_patient->patient_id = $patient->_id;
        $jfse_patient->loadMatchingObjectEsc();

        return isset($jfse_patient->_id) && isset($jfse_patient->jfse_patient_id);
    }
}
