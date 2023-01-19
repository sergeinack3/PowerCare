<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

class TdbReport
{
    public CConsultation $filter;

    private ?string $board_access;

    private CMediusers $user;

    private array $sejours = [];

    private array $praticiens = [];

    private CSQLDataSource $ds;

    /**
     * @param CMediusers $user
     *
     * @throws CMbException
     * @throws Exception
     */
    public function __construct(CMediusers $user)
    {
        if (!$user->_id) {
            throw new CMbException("User not found");
        }
        $this->ds           = CSQLDataSource::get("std");
        $this->user         = $user;
        $this->board_access = CAppUI::pref("allow_other_users_board");
        $this->loadPraticiens(true);
    }


    /**
     * @param string $date_min
     * @param string $date_max
     *
     * @throws CMbException
     */
    public function getCodingReport(string $date_min, string $date_max): void
    {
        if ($date_min === "" || $date_max === "" || ($date_min > $date_max)) {
            throw new CMbException("common-error-Invalid data");
        }
        $this->filter            = new CConsultation();
        $this->filter->_date_min = $date_min;
        $this->filter->_date_max = $date_max;
    }

    /**
     * @throws Exception
     */
    public function getTransmissionReport(CMediusers $user): void
    {
        $date_max = CMbDT::dateTime();
        $date_min = CMbDT::dateTime("-1 DAY", $date_max);

        $where = [
            "sejour.praticien_id" => $this->ds->prepare("= ?", $user->_id),
        ];

        $ljoin = [
            "transmission_medicale" => "transmission_medicale.sejour_id = sejour.sejour_id",
            "observation_medicale"  => "observation_medicale.sejour_id = sejour.sejour_id",
        ];

        $whereOr = [
            "transmission_medicale.date " . $this->ds->prepareBetween($date_min, $date_max),
            "observation_medicale.date " . $this->ds->prepareBetween($date_min, $date_max),
        ];

        $where[] = implode(" OR ", $whereOr);

        /** @var CSejour[] $sejours */
        $this->sejours = (new CSejour())->loadList($where, null, null, "sejour_id", $ljoin);

        foreach ($this->sejours as $_sejour) {
            $_sejour->loadRefPatient();
            $_sejour->loadRefsTransmissions();
            $_sejour->loadRefsObservations();
        }
    }

    /**
     * @param bool $use_group
     *
     * @return void
     * @throws Exception
     */
    private function loadPraticiens(bool $use_group = true): void
    {
        $mediuser         = new CMediusers();
        $this->praticiens = $mediuser->loadPraticiens();

        if ($this->user->isProfessionnelDeSante() && $this->board_access === "only_me") {
            $this->praticiens = [$this->user->_id => $this->user];
        } elseif ($this->user->isProfessionnelDeSante() && $this->board_access === "same_function") {
            $this->praticiens = $mediuser->loadPraticiens(
                PERM_READ,
                $this->user->function_id,
                null,
                null,
                true,
                $use_group
            );
        } elseif ($this->user->isProfessionnelDeSante() && $this->board_access === "write_right") {
            $this->praticiens = $mediuser->loadPraticiens(PERM_EDIT, null, null, null, true, $use_group);
        }
        CStoredObject::massLoadFwdRef($this->praticiens, "function_id");

        foreach ($this->praticiens as $_praticien) {
            $_praticien->loadRefFunction();
        }
    }

    /**
     * @return array
     */
    public function getPraticiens(): array
    {
        return $this->praticiens;
    }

    /**
     * @return array
     */
    public function getSejours(): array
    {
        return $this->sejours;
    }

    /**
     * @return CConsultation
     */
    public function getFilter(): CConsultation
    {
        return $this->filter;
    }
}
