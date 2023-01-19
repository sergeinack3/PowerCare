<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Repository;

use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Eai\Repository\Exceptions\ConsultationRepositoryException;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

class ConsultationRepository extends ObjectRepository
{
    /** @var string[] */
    public const STRATEGIES = [
        self::STRATEGY_ONLY_DATE,
        self::STRATEGY_ONLY_DATE_EXTENDED,
        self::STRATEGY_BEST
    ];

    /** @var string */
    public const STRATEGY_ONLY_DATE = 'only-date';

    /** @var string */
    public const STRATEGY_ONLY_DATE_EXTENDED = "only-date-extended";

    /** @var string */
    public const PARAMETER_DATE_BEFORE = 'before_date';
    /** @var string */
    public const PARAMETER_DATE_AFTER = 'after_date';

    private ?CConsultation $consultation_found = null;
    private array $parameters = [];
    private ?CPatient $patient = null;
    private ?string $date_consultation = null;
    private ?string $praticien_id = null;
    private ?CSejour $sejour = null;

    /**
     * @return CConsultation|null
     */
    public function getConsultation(): ?CConsultation
    {
        return $this->consultation_found;
    }

    /**
     * Try to find consultation
     *
     * @return CConsultation|null
     * @throws Exception
     */
    public function find(): ?CConsultation
    {
        if (!$this->patient) {
            return null;
        }

        // complete default parameters
        $this->parameters = $this->completeDefaultParameters();

        // search consultation
        $consultation = null;
        foreach ($this->strategies as $strategy) {
            $consultation = $this->search($strategy);

            if ($consultation && $consultation->_id) {
                break;
            }
        }

        return $this->consultation_found = $consultation;
    }

    /**
     * Search Consultation in function of strategy selected
     *
     * @param string $strategy
     *
     * @return CConsultation|null
     * @throws Exception
     */
    private function search(string $strategy): ?CConsultation
    {
        switch ($strategy) {
            case self::STRATEGY_ONLY_DATE:
                $consultation = $this->searchConsultationFromDate();
                break;

            case self::STRATEGY_ONLY_DATE_EXTENDED:
                $consultation = $this->searchConsultationFromDateExtended();
                break;

            case self::STRATEGY_BEST:
            default:
                if (!$consultation = $this->searchConsultationFromDate()) {
                    $consultation = $this->searchConsultationFromDateExtended();
                }
        }

        // only consultation link to the patient
        if ($consultation && $this->patient->_id !== $consultation->patient_id) {
            throw new ConsultationRepositoryException(ConsultationRepositoryException::PATIENT_DIVERGENCE_FOUND);
        }

        return $consultation && $consultation->_id ? $consultation : null;
    }

    /**
     * @param CConsultation $consultation_found
     *
     * @return ConsultationRepository
     */
    public function setConsultationFound(CConsultation $consultation_found): ConsultationRepository
    {
        $this->consultation_found = $consultation_found;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrFind(): ?CConsultation
    {
        return $this->getConsultation() ?: $this->find();
    }

    /**
     * Complete field parameters with default options for search operation
     *
     * @return array
     */
    private function completeDefaultParameters(): array
    {
        $parameters = $this->parameters;
        // date_before
        if (!array_key_exists(self::PARAMETER_DATE_BEFORE, $parameters) && $this->date_consultation) {
            $parameters[self::PARAMETER_DATE_BEFORE] = CMbDT::date("- 2 DAY", $this->date_consultation);
        }

        // date_after
        if (!array_key_exists(self::PARAMETER_DATE_AFTER, $parameters) && $this->date_consultation) {
            $parameters[self::PARAMETER_DATE_AFTER] = CMbDT::date("+ 1 DAY", $this->date_consultation);
        }

        return $parameters;
    }

    /**
     * Search consultation for the date
     *
     * @return CConsultation|null
     * @throws \Exception
     */
    protected function searchConsultationFromDate(): ?CConsultation
    {
        if (!$this->date_consultation) {
            return null;
        }

        $consultation = new CConsultation();
        $ds           = $consultation->getDS();
        $where        = [
            "patient_id"        => $ds->prepare('= ?', $this->patient->_id),
            "annule"            => $ds->prepare('= ?', 0),
            "plageconsult.date" => $ds->prepare('= ?', CMbDT::format($this->date_consultation, CMbDT::ISO_DATE)),
        ];

        return $this->searchConsultation($where);
    }

    /**
     * Search consultation in a range of date
     *
     * @return CConsultation|null
     * @throws \Exception
     */
    protected function searchConsultationFromDateExtended(): ?CConsultation
    {
        if (!$this->date_consultation) {
            return null;
        }

        $consultation = new CConsultation();
        $ds = $consultation->getDS();
        $date_before = $this->parameters[self::PARAMETER_DATE_BEFORE];
        $date_after  = $this->parameters[self::PARAMETER_DATE_AFTER];

        $where        = [
            "patient_id"        => $ds->prepare('= ?', $this->patient->_id),
            "annule"            => $ds->prepare('= ?', 0),
            "plageconsult.date" => "BETWEEN '$date_before' AND '$date_after'",
        ];

        return $this->searchConsultation($where);
    }

    /**
     * Set patient for search consultation
     *
     * @param CPatient|null $patient
     *
     * @return ConsultationRepository
     */
    public function setPatient(?CPatient $patient): ConsultationRepository
    {
        if ($patient && $patient->_id) {
            $this->patient = $patient;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameter(string $key, $value): ConsultationRepository
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Set date for search consultation
     *
     * @param string|null $date_consultation
     *
     * @return ConsultationRepository
     */
    public function setDateConsultation(?string $date_consultation): ConsultationRepository
    {
        $this->date_consultation = $date_consultation;

        return $this;
    }

    /**
     * Set praticien id for search operation
     *
     * @param CMediusers|string|null $praticien
     *
     * @return ConsultationRepository
     */
    public function setPraticienId($praticien): ConsultationRepository
    {
        if ($praticien instanceof CMediusers && $praticien->_id) {
            $this->praticien_id = $praticien->_id;
        } elseif (is_string($praticien)) {
            $this->praticien_id = $praticien;
        }

        return $this;
    }

    /**
     * @param CSejour|null $sejour
     *
     * @return ConsultationRepository
     */
    public function setSejour(?CSejour $sejour): ConsultationRepository
    {
        if ($sejour && $sejour->_id) {
            $this->sejour = $sejour;
        }

        return $this;
    }

    /**
     * Search consultation
     *
     * @param array $where
     *
     * @return CConsultation|null
     * @throws Exception
     */
    private function searchConsultation(array $where): ?CConsultation
    {
        $consultation = new CConsultation();
        $ds = $consultation->getDS();

        if ($this->sejour) {
            $where["sejour_id"] = $ds->prepare('= ?', $this->sejour->_id);
        }

        // Praticien renseigné dans le message, on recherche par ce dernier
        if ($praticien_id = $this->praticien_id) {
            $where["plageconsult.chir_id"] = $ds->prepare('= ?', $praticien_id);
        }

        $leftjoin = ["plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id"];
        $consultation->loadObject($where, "plageconsult.date DESC", null, $leftjoin);

        return $consultation->_id ? $consultation : null;
    }
}
