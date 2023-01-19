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
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\Repository\Exceptions\SejourRepositoryException;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

class SejourRepository extends ObjectRepository
{
    /** @var string[] */
    public const STRATEGIES = [
        self::STRATEGY_ONLY_NDA,
        self::STRATEGY_ONLY_DATE,
        self::STRATEGY_ONLY_DATE_EXTENDED,
        self::STRATEGY_CURRENT_SEJOUR,
        self::STRATEGY_RESOURCE_ID,
        self::STRATEGY_BEST,
    ];

    /** @var string */
    public const STRATEGY_ONLY_NDA = 'only-nda'; // NDA

    /** @var string */
    public const STRATEGY_ONLY_DATE = 'only-curr-date'; // date of sejour

    /** @var string */
    public const STRATEGY_ONLY_DATE_EXTENDED = "only-date-extended"; // -x days before +y days after sejour

    /** @var string */
    public const STRATEGY_CURRENT_SEJOUR = "only-curr_sejour"; // current sejour of patient

    /** @var string */
    public const STRATEGY_RESOURCE_ID = 'intern_identifier'; // _id

    /** @var string */
    public const PARAMETER_DATE_BEFORE = 'before_date';
    /** @var string */
    public const PARAMETER_DATE_AFTER = 'after_date';

    private ?CSejour $sejour_found = null;
    private ?CPatient $patient      = null;
    private array $parameters   = [];
    private ?string $date_sejour  = null;
    private ?string $NDA          = null;
    private ?string $tag_sejour   = null;
    private ?string $object_id    = null;
    private ?string $group_id     = null;
    private ?string $praticien_id = null;

    /**
     * Get sejour found
     *
     * @return CSejour|null
     */
    public function getSejour(): ?CSejour
    {
        return $this->sejour_found;
    }

    /**
     * Try to find sejour in function of strategy selected
     *
     * @return CSejour|null
     * @throws Exception
     */
    public function find(): ?CSejour
    {
        // complete default parameters
        $this->parameters = $this->completeDefaultParameters();

        // search sejour
        $sejour = null;
        foreach ($this->strategies as $strategy) {
            // allow retrieve sejour without patient only with NDA
            if (!$this->patient && $strategy !== self::STRATEGY_ONLY_NDA) {
                continue;
            }

            $sejour = $this->search($strategy);

            if ($sejour && $sejour->_id) {
                break;
            }
        }

        return $this->sejour_found = $sejour;
    }

    /**
     * Search Sejour in function of strategy selected
     *
     * @param string $strategy
     *
     * @return CSejour|null
     * @throws Exception
     */
    private function search(string $strategy): ?CSejour
    {
        switch ($strategy) {
            case self::STRATEGY_ONLY_NDA:
                $sejour = $this->searchFromNDA();
                break;

            case self::STRATEGY_RESOURCE_ID:
                $sejour = $this->searchFromObjectID();
                break;

            case self::STRATEGY_ONLY_DATE:
                $sejour = $this->searchFromDate();
                break;

            case self::STRATEGY_CURRENT_SEJOUR:
                $sejour = $this->getCurrentSejour();
                break;

            case self::STRATEGY_ONLY_DATE_EXTENDED:
                $sejour = $this->searchFromDateExtended();
                break;

            case self::STRATEGY_BEST:
            default:
                // nda
                $sejour = $this->searchFromNDA();

                // resource id
                if (!$sejour) {
                    $sejour = $this->searchFromObjectID();
                }

                // date
                if (!$sejour) {
                    $sejour = $this->searchFromDate();
                }

                // current sejour
                if (!$sejour) {
                    $sejour = $this->getCurrentSejour();
                }

                // date extended
                if (!$sejour) {
                    $sejour = $this->searchFromDateExtended();
                }
        }

        // only sejour link to the patient
        if ($sejour && ($this->patient && $this->patient->_id !== $sejour->patient_id)) {
            throw new SejourRepositoryException(SejourRepositoryException::PATIENT_DIVERGENCE_FOUND);
        }

        return $sejour && $sejour->_id ? $sejour : null;
    }

    /**
     * Set sejour founded
     *
     * @param CSejour|null $sejour_found
     *
     * @return SejourRepository
     */
    public function setSejourFound(?CSejour $sejour_found): self
    {
        $this->sejour_found = $sejour_found;

        return $this;
    }

    /**
     * Get or find sejour
     *
     * @return CStoredObject|null
     * @throws Exception
     */
    public function getOrFind(): ?CSejour
    {
        return $this->getSejour() ?: $this->find();
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
        if (!array_key_exists(self::PARAMETER_DATE_BEFORE, $parameters) && $this->date_sejour) {
            $parameters[self::PARAMETER_DATE_BEFORE] = CMbDT::date("- 2 DAY", $this->date_sejour);
        }

        // date_after
        if (!array_key_exists(self::PARAMETER_DATE_AFTER, $parameters) && $this->date_sejour) {
            $parameters[self::PARAMETER_DATE_AFTER] = CMbDT::date("+ 1 DAY", $this->date_sejour);
        }

        return $parameters;
    }

    /**
     * Set date for search sejour
     *
     * @param string|null $date_sejour
     *
     * @return SejourRepository
     */
    public function setDateSejour(?string $date_sejour): SejourRepository
    {
        $this->date_sejour = $date_sejour;

        return $this;
    }

    /**
     * Search sejour with NDA
     *
     * @return void
     * @throws Exception
     */
    private function searchFromNDA(): ?CSejour
    {
        if (!$this->NDA || !$this->tag_sejour) {
            return null;
        }

        $NDA = CIdSante400::getMatch(
            "CSejour",
            $this->tag_sejour,
            $this->NDA
        );

        // Séjour retrouvé par son NDA
        if (!$NDA->_id) {
            return null;
        }

        $sejour = new CSejour();
        $sejour->load($NDA->object_id);

        return $sejour->_id ? $sejour : null;
    }

    /**
     * Get current sejour of patient
     *
     * @return CSejour|null
     */
    private function getCurrentSejour(): ?CSejour
    {
        $sejours        = $this->patient->getCurrSejour($this->date_sejour, $this->group_id, $this->praticien_id);
        $current_sejour = reset($sejours);

        return $current_sejour && $current_sejour->_id ? $current_sejour : null;
    }

    /**
     * Search sejour from date
     *
     * @param string|null $date_start
     * @param string|null $date_end
     *
     * @return void|null
     * @throws Exception
     */
    protected function searchFromDate(?string $date_start = null, ?string $date_end = null): ?CSejour
    {
        if (!$this->date_sejour && !$date_start && !$date_end) {
            return null;
        }

        $sejour = new CSejour();
        $ds     = $sejour->getDS();

        $where = [
            'patient_id' => $ds->prepare('= ?', $this->patient->_id),
            'annule'     => $ds->prepare('= ?', 0),
        ];

        if ($praticien_id = $this->praticien_id) {
            $where["praticien_id"] = $ds->prepare('= ?', $praticien_id);
        }

        if ($group_id = $this->group_id) {
            $where['group_id'] = $ds->prepare('= ?', $group_id);
        }

        if ($date_start && $date_end) {
            $patter_datetime_iso = "/\d{4}-\d{2}-\d{2} \d{2}:\d{2}/";
            if (!preg_match($patter_datetime_iso, $date_start)) {
                $date_start = "$date_start 00:00:00";
            }

            if (!preg_match($patter_datetime_iso, $date_end)) {
                $date_end = "$date_end 23:59:59";
            }

            $where[] = "sejour.entree BETWEEN '$date_start' AND '$date_end'";
        } else {
            $date    = CMbDT::format($this->date_sejour, CMbDT::ISO_DATE);
            $where[] = "sejour.entree <= '$date 23:59:59'";
            $where[] = "sejour.sortie >= '$date 00:00:00'";
        }

        $sejours = $sejour->loadList($where);
        if (count($sejours) > 1) {
            throw new SejourRepositoryException(SejourRepositoryException::MULTIPLE_SEJOUR_FOUND);
        }

        return reset($sejours) ?: null;
    }

    /**
     * Search sejour from his object_id
     *
     * @return CSejour|null
     * @throws Exception
     */
    private function searchFromObjectID(): ?CSejour
    {
        if (!$this->object_id) {
            return null;
        }

        $sejour = new CSejour();
        $sejour->load($this->object_id);

        return $sejour->_id ? $sejour : null;
    }

    /**
     * Search sejour with extended date
     *
     * @return CSejour|null
     * @throws Exception
     */
    private function searchFromDateExtended(): ?CSejour
    {
        if (!$this->date_sejour) {
            return null;
        }

        $date_before = $this->parameters[self::PARAMETER_DATE_BEFORE];
        $date_after  = $this->parameters[self::PARAMETER_DATE_AFTER];

        return $this->searchFromDate($date_before, $date_after);
    }

    /**
     * @param string|null $object_id
     *
     * @return SejourRepository
     */
    public function setObjectId(?string $object_id): SejourRepository
    {
        $this->object_id = $object_id;

        return $this;
    }

    /**
     * @param string|null $group_id
     *
     * @return SejourRepository
     */
    public function setGroupId(?string $group_id): SejourRepository
    {
        $this->group_id = $group_id;

        return $this;
    }

    /**
     * @param string|null $praticien_id
     *
     * @return SejourRepository
     */
    public function setPraticienId(?string $praticien_id): SejourRepository
    {
        $this->praticien_id = $praticien_id;

        return $this;
    }

    /**
     * @param CPatient|null $patient
     *
     * @return SejourRepository
     */
    public function setPatient(?CPatient $patient): SejourRepository
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * Set NDA and Tag for search sejour from NDA
     *
     * @param string|null $NDA
     * @param string|null $tag_sejour
     *
     * @return SejourRepository
     */
    public function setNDA(?string $NDA, ?string $tag_sejour): SejourRepository
    {
        if ($NDA && $tag_sejour) {
            $this->NDA        = $NDA;
            $this->tag_sejour = $tag_sejour;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return SejourRepository
     */
    public function setParameter(string $key, $value): SejourRepository
    {
        $this->parameters[$key] = $value;

        return $this;
    }
}
