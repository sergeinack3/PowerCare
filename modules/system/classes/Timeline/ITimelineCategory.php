<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Interface of a category of a timeline
 */
interface ITimelineCategory
{
    /**
     * @param CPatient $patient
     *
     * @return void
     */
    public function setPatient(CPatient $patient): void;

    /**
     * @param CMediusers[] $users
     *
     * @return void
     */
    public function setUsers(array $users): void;

    /**
     * @return array
     */
    public function getEventsByDate(): array;

    /**
     * @return array
     */
    public function getEventsByDateTime(): array;

    /**
     * @return int
     */
    public function getAmountEvents(): int;

    /**
     * @return array
     */
    public function getInvolvedUsers(): array;
}
