<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Timeline;

use DateTime;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Core\Comparator;
use Ox\Core\ComparatorException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CMediusersComparator;
use Ox\Mediboard\Patients\CPatient;

/**
 * Class TimelineCategory
 */
abstract class TimelineCategory implements ITimelineCategory, IShortNameAutoloadable
{
    /** @var CMediusers */
    protected $users;
    /** @var CPatient */
    protected $patient;
    /** @var array */
    private $timeline = [];
    /** @var int */
    private $amount_events = 0;
    /** @var CMediusers[] */
    private $involved_users = [];

    /**
     * @inheritDoc
     */
    public function getAmountEvents(): int
    {
        return $this->amount_events;
    }

    /**
     * @inheritDoc
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    /**
     * @inheritDoc
     */
    public function setPatient(CPatient $patient): void
    {
        $this->patient = $patient;
    }

    /**
     * @inheritDoc
     */
    public function getInvolvedUsers(): array
    {
        return $this->involved_users;
    }

    /**
     * @return array
     */
    protected function getTimeline(): array
    {
        return $this->timeline;
    }

    /**
     * It's this method which will register every item of the timeline per category
     *
     * @param string $year
     * @param string $month
     * @param string $date
     * @param string $object_type - canonical name given to the menu
     * @param CStoredObject|array $object
     * @param string|null $context - also sort by context if needed
     *
     * @return void
     */
    protected function appendTimeline(
        string $year,
        string $month,
        string $date,
        string $object_type,
               $object,
        string $context = null
    ): void
    {
        $this->timeline[$year][$month][$date][$object_type][$context][] = $object;
    }

    /**
     * It's this method which will register every item of the timeline per category
     *
     * @param string $year
     * @param string $month
     * @param string $date
     * @param string $time
     * @param string $object_type - canonical name given to the menu
     * @param CStoredObject|array $object
     * @param string|null $context - also sort by context if needed
     *
     * @return void
     */
    protected function appendTimelineWithTime(
        string $year,
        string $month,
        string $date,
        string $time,
        string $object_type,
               $object,
        string $context = null
    ): void
    {
        $this->timeline[$year][$month][$date][$time][$object_type][$context][] = $object;
    }


    /**
     * Returns an array with the year, the year-month, the year-month-day and the time
     *
     * @param string $date
     *
     * @return string[]
     * @throws Exception
     */
    protected function makeListDatesTime(?string $date): array
    {
        if (CMbDT::isLunarDate($date)) {
            $date = CMbDT::lunarToGregorian($date);
        }

        $date = new DateTime($date ?? '');

        return [$date->format("Y"), $date->format("Y-m"), $date->format("Y-m-d"), $date->format("H-i")];
    }

    /**
     * Returns an array with the year, the year-month and the year-month-day
     *
     * @param string $date
     *
     * @return string[]
     * @throws Exception
     */
    protected function makeListDates(?string $date): array
    {
        if (CMbDT::isLunarDate($date)) {
            $date = CMbDT::lunarToGregorian($date);
        }

        $date = new DateTime($date ?? '');

        return [$date->format("Y"), $date->format("Y-m"), $date->format("Y-m-d")];
    }

    /**
     * @param CMediusers $user - can be null. Some categories cannot be associated to users
     *
     * @return bool
     * @throws ComparatorException
     */
    protected function selectedPractitioner(?CMediusers $user): bool
    {
        if (!$user && $this->users) {
            return false;
        }
        if ((!$user && !$this->users) || !$this->users) {
            return true;
        }

        foreach ($this->users as $_user) {
            $compare = new Comparator(new CMediusersComparator());
            if (!$compare->executeStrategy($_user, $user)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increments the amount of events for a type
     *
     * @return void
     */
    protected function incrementAmountEvents(): void
    {
        $this->amount_events++;
    }

    /**
     * Add the user to the involved user list
     *
     * @param CMediusers $user
     *
     * @return void
     * @throws Exception
     */
    protected function addToInvolvedUser(CMediusers $user): void
    {
        if (!in_array($user, $this->involved_users)) {
            $this->involved_users[] = $user;
        }
    }
}
