<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Repository;

use Exception;
use Ox\Core\CMbException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Sante400\CIdSante400;

class PatientRepository extends ObjectRepository
{
    /** @var string[] */
    public const STRATEGIES = [
        self::STRATEGY_INS,
        self::STRATEGY_IPP,
        self::STRATEGY_INS_IPP,
        self::STRATEGY_PATIENT_TRAITS,
        self::STRATEGY_RESOURCE_ID,
        self::STRATEGY_BEST,
    ];

    /** @var string */
    public const STRATEGY_INS = 'ins';

    /** @var string */
    public const STRATEGY_IPP = 'ipp';

    /** @var string */
    public const STRATEGY_INS_IPP = self::STRATEGY_INS . '-' . self::STRATEGY_IPP;

    /** @var string */
    public const STRATEGY_PATIENT_TRAITS = 'patient-traits';

    /** @var string */
    public const STRATEGY_RESOURCE_ID = 'intern_identifier'; // _id

    /** @var string|null */
    private $ipp_tag;

    /** @var string|null */
    private $ipp;

    /** @var string[]|null */
    private $ins;

    /** @var string|null */
    private $ins_type;

    /** @var CPatient */
    private $patient_searched;

    /** @var string|null */
    private $group_id;

    /** @var string|null */
    private $resource_id;

    /** @var CPatient|null */
    private $patient_found;

    /**
     * @throws Exception
     */
    public function find(): ?CPatient
    {
        // search patient
        $patient = null;
        foreach ($this->strategies as $strategy) {
            $patient = $this->search($strategy);

            if ($patient && $patient->_id) {
                break;
            }
        }

        return $this->patient_found = $patient;
    }

    /**
     * Search Patient in function of strategy selected
     *
     * @param string $strategy
     *
     * @return CPatient|null
     * @throws Exception
     */
    private function search(string $strategy): ?CPatient
    {
        switch ($strategy) {
            case self::STRATEGY_INS:
                $patient = $this->findByINS();
                break;

            case self::STRATEGY_IPP:
                $patient = $this->findByIPP();
                break;

            case self::STRATEGY_INS_IPP:
                if (!$patient = $this->findByINS()) {
                    $patient = $this->findByIPP();
                }
                break;

            case self::STRATEGY_PATIENT_TRAITS:
                $patient = $this->findByTraits();
                break;

            case self::STRATEGY_RESOURCE_ID:
                $patient = $this->findByResourceId();
                break;

            case self::STRATEGY_BEST:
                $patient = $this->findFromBestMatching();
                break;

            default:
                $patient = null;
        }

        return $patient;
    }

    /**
     * @return CPatient|null
     * @throws Exception
     */
    protected function findFromBestMatching(): ?CPatient
    {
        // first search
        $patient = $this->findByINS();

        // second search
        if (!$patient) {
            $patient = $this->findByIPP();
        }

        // third search
        if (!$patient) {
            $patient = $this->findByResourceId();
        }

        // fallback
        if (!$patient) {
            $patient = $this->findByTraits();
        }

        return $patient;
    }

    /**
     * @return CPatient|null
     * @throws Exception
     */
    protected function findByIPP(): ?CPatient
    {
        if (!$this->ipp || !$this->ipp_tag) {
            return null;
        }

        $IPP = CIdSante400::getMatch(
            "CPatient",
            $this->ipp_tag,
            $this->ipp
        );

        // Patient non retrouvé par son IPP
        if (!$IPP->_id) {
            return null;
        }

        $patient = new CPatient();
        $patient->load($IPP->object_id);

        return $patient->_id ? $patient : null;
    }

    /**
     * @return CPatient|null
     */
    protected function findByINS(): ?CPatient
    {
        if (!$this->ins || !$this->ins_type || !is_array($this->ins)) {
            return null;
        }

        $patient = null;
        foreach ($this->ins as $ins) {
            if ($patient) {
                break;
            }

            $patient_ins_nir          = new CPatientINSNIR();
            $patient_ins_nir->ins_nir = $ins;
            $patient_ins_nir->oid     = $this->ins_type;

            if (!$patient_ins_nir->loadMatchingObjectEsc()) {
                continue;
            }

            $patient = $patient_ins_nir->loadRefPatient();
        }

        return ($patient && $patient->_id) ? $patient : null;
    }

    /**
     * @return CPatient|null
     */
    protected function findByTraits(): ?CPatient
    {
        if (!$this->patient_searched) {
            return null;
        }

        $this->patient_searched->loadMatchingPatient(false, true, [], false, $this->group_id, true);

        return $this->patient_searched->_id ? $this->patient_searched : null;
    }

    /**
     * @param string[]|string|null $ins_nir
     * @param string[]|string|null $ins_nia
     * @param string[]|string|null $ins_test
     *
     * @return void
     */
    public function withINS($ins_nir, $ins_nia = null, $ins_test = null): self
    {
        if ($ins_nir) {
            $this->ins      = (is_array($ins_nir) ? $ins_nir : [$ins_nir]);
            $this->ins_type = CPatientINSNIR::OID_INS_NIR;
        } elseif ($ins_nia) {
            $this->ins      = (is_array($ins_nia) ? $ins_nia : [$ins_nia]);
            $this->ins_type = CPatientINSNIR::OID_INS_NIA;
        } elseif ($ins_test) {
            $this->ins      = (is_array($ins_test) ? $ins_test : [$ins_test]);
            $this->ins_type = CPatientINSNIR::OID_INS_NIR_TEST;
        }

        return $this;
    }

    /**
     * @param string|null $ipp
     * @param string|null $ipp_tag
     *
     * @return $this
     * @throws Exception|CMbException
     */
    public function withIPP(?string $ipp, ?string $ipp_tag): self
    {
        if ($ipp && !$ipp_tag) {
            throw new CMbException('PatientLocator-msg-tag ipp not given');
        }

        $this->ipp     = $ipp;
        $this->ipp_tag = $ipp_tag;

        return $this;
    }

    /**
     * @param CPatient|null $patient
     * @param string|null   $group_id
     *
     * @return $this
     */
    public function withPatientSearched(?CPatient $patient, ?string $group_id): self
    {
        $this->patient_searched = $patient;
        $this->group_id         = $group_id;

        return $this;
    }

    /**
     * @param string|null $resource_id
     *
     * @return $this
     */
    public function withResourceId(?string $resource_id): self
    {
        $this->resource_id = $resource_id;

        return $this;
    }

    /**
     * @return CPatient|null
     * @throws Exception
     */
    protected function findByResourceId(): ?CPatient
    {
        if (!$this->resource_id) {
            return null;
        }

        $patient = new CPatient();
        $patient->load($this->resource_id);

        return $patient->_id ? $patient : null;
    }

    /**
     * @return CPatient|null
     */
    public function getPatient(): ?CPatient
    {
        return $this->patient_found;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getOrFind(): ?CPatient
    {
        return $this->getPatient() ?: $this->find();
    }
}
