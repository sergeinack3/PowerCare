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
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

class OperationRepository extends ObjectRepository
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

    /** @var COperation|null */
    private ?COperation $operation_found = null;
    private ?string $date_operation = null;
    private ?string $praticien_id = null;
    private ?CPatient $patient = null;
    private ?CSejour $sejour = null;
    private array $parameters = [];

    /**
     * Get operation found
     *
     * @return COperation|null null if not found or not yet searched
     */
    public function getOperation(): ?COperation
    {
        return $this->operation_found;
    }

    /**
     * Try to find Operation
     *
     * @return COperation|null
     * @throws \Exception
     */
    public function find(): ?COperation
    {
        if (!$this->patient) {
            return null;
        }

        // complete default parameters
        $this->parameters = $this->completeDefaultParameters();

        // search operation
        $operation = null;
        foreach ($this->strategies as $strategy) {
            $operation = $this->search($strategy);

            if ($operation && $operation->_id) {
                break;
            }
        }

         return $this->operation_found = $operation;
    }

    /**
     * Search Operation in function of strategy selected
     *
     * @param string $strategy
     *
     * @return CConsultation|null
     * @throws Exception
     */
    private function search(string $strategy): ?COperation
    {
        switch ($strategy) {
            case self::STRATEGY_ONLY_DATE:
                $operation = $this->searchOperationFromDate();
                break;

            case self::STRATEGY_ONLY_DATE_EXTENDED:
                $operation = $this->searchOperationFromDateExtended();
                break;

            case self::STRATEGY_BEST:
            default:
                if (!$operation = $this->searchOperationFromDate()) {
                    $operation = $this->searchOperationFromDateExtended();
                }
        }

        // only operation link to the patient
        if ($operation) {
            $patient = $operation->loadRefPatient();
            if (!$patient || $patient->_id !== $this->patient->_id) {
                return null;
            }
        }

        return $operation && $operation->_id ? $operation : null;
    }

    /**
     * @inheritDoc
     */
    public function getOrFind(): ?COperation
    {
        return $this->getOperation() ?: $this->find();
    }

    /**
     * @param COperation|null $operation_found
     */
    public function setOperationFound(?COperation $operation_found): self
    {
        $this->operation_found = $operation_found;

        return $this;
    }

    /**
     * Set patient for search operation
     *
     * @param CPatient|null $patient
     *
     * @return OperationRepository
     */
    public function setPatient(?CPatient $patient): OperationRepository
    {
        if ($patient && $patient->_id) {
            $this->patient = $patient;
        }

        return $this;
    }

    /**
     * Set praticien id for search operation
     *
     * @param CMediusers|string|null $praticien
     *
     * @return OperationRepository
     */
    public function setPraticienId($praticien): OperationRepository
    {
        if ($praticien instanceof CMediusers && $praticien->_id) {
            $this->praticien_id = $praticien->_id;
        } elseif (is_string($praticien)) {
            $this->praticien_id = $praticien;
        }

        return $this;
    }

    /**
     * @param string|null $date_operation
     *
     * @return OperationRepository
     */
    public function setDateOperation(?string $date_operation): OperationRepository
    {
        $this->date_operation = $date_operation;

        return $this;
    }

    /**
     * @param CSejour|null $sejour
     *
     * @return OperationRepository
     */
    public function setSejour(?CSejour $sejour): OperationRepository
    {
        if ($sejour && $sejour->_id) {
            $this->sejour = $sejour;
        }

        return $this;
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
        if (!array_key_exists(self::PARAMETER_DATE_BEFORE, $parameters) && $this->date_operation) {
            $parameters[self::PARAMETER_DATE_BEFORE] = CMbDT::date("- 2 DAY", $this->date_operation);
        }

        // date_after
        if (!array_key_exists(self::PARAMETER_DATE_AFTER, $parameters) && $this->date_operation) {
            $parameters[self::PARAMETER_DATE_AFTER] = CMbDT::date("+ 1 DAY", $this->date_operation);
        }

        return $parameters;
    }

    /**
     * Override options for search operation
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return OperationRepository
     */
    public function setParameter(string $key, $value): OperationRepository
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Search operation with the date equals
     *
     * @return COperation|null
     * @throws \Exception
     */
    private function searchOperationFromDate(): ?COperation
    {
        if (!$dateTime = $this->date_operation) {
            return null;
        }

        $operation = new COperation();
        $ds        = $operation->getDS();

        $where = [
            "sejour.patient_id"  => $ds->prepare('= ?', $this->patient->_id),
            "operations.annulee" => $ds->prepare('= ?', 0),
        ];

        if ($this->sejour) {
            $where["sejour.sejour_id"] = $ds->prepare('= ?', $this->sejour->_id);
        }

        // Recherche d'une opération dans le séjour
        if ($praticien_id = $this->praticien_id) {
            $where["operations.chir_id"] = "= '$praticien_id'";
        }

        $where[] = "'$dateTime' BETWEEN operations.entree_bloc AND operations.sortie_reveil_reel OR 
      '$dateTime' BETWEEN operations.entree_bloc AND operations.sortie_salle";

        $leftjoin = [
            "sejour" => "operations.sejour_id = sejour.sejour_id",
        ];

        $operation->loadObject($where, null, null, $leftjoin);

        return $operation->_id ? $operation : null;
    }

    /**
     * Try to find operation with a date extended
     *
     * @return COperation|null
     * @throws Exception
     */
    private function searchOperationFromDateExtended(): ?COperation
    {
        if (!$this->date_operation) {
            return null;
        }

        $date_before = $this->parameters[self::PARAMETER_DATE_BEFORE];
        $date_after  = $this->parameters[self::PARAMETER_DATE_AFTER];

        $operation = new COperation();
        $ds        = $operation->getDS();

        $leftjoin = [
            "sejour"   => "operations.sejour_id = sejour.sejour_id",
            "plagesop" => "operations.plageop_id = plagesop.plageop_id",
        ];

        $where                 = [
            "sejour.patient_id"  => $ds->prepare('= ?', $this->patient->_id),
            "operations.annulee" => $ds->prepare('= ?', 0),
        ];
        $between_op_dates      = "operations.date BETWEEN '$date_before' AND '$date_after'";
        $between_plageop_dates = "plagesop.date BETWEEN '$date_before' AND '$date_after'";
        $where[]               = "($between_op_dates) OR ($between_plageop_dates)";

        if ($this->sejour) {
            $where['sejour.sejour_id'] = $ds->prepare('= ?', $this->sejour->_id);
        }

        // Recherche d'une opération dans le séjour
        if ($praticien_id = $this->praticien_id) {
            $where['operations.chir_id'] = $ds->prepare('= ?', $praticien_id);
        }

        $operation->loadObject($where, "plagesop.date DESC", null, $leftjoin);

        return $operation->_id ? $operation : null;
    }
}
