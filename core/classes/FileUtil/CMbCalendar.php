<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\FileUtil;

use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Vcalendar;
use Ox\Core\CAppUI;

class CMbCalendar
{
    private Vcalendar $vcalendar;

    /**
     * Create a calendar
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, string $description = null)
    {
        if ($name === "") {
            throw new InvalidArgumentException("Name is mandatory for creating calendar");
        }

        $this->vcalendar = Vcalendar::factory();

        // Properties
        $this->vcalendar->setMethod(IcalInterface::PUBLISH);
        $this->vcalendar->setXprop(IcalInterface::X_WR_CALNAME, $name);

        if ($description !== null) {
            $this->vcalendar->setXprop(IcalInterface::X_WR_CALDESC, $description);
        }

        $this->vcalendar->setXprop(IcalInterface::X_WR_TIMEZONE, CAppUI::conf("timezone"));
    }

    /**
     * Add an event to created calendar
     *
     * @throws Exception
     */
    public function addEvent(
        string $location,
        string $summary,
        string $guid,
        string $start,
        string $end,
        string $description = null,
        string $comment = null,
        bool $cancelled = false
    ): void {
        $vevent = $this->vcalendar->newVevent()
            ->setLocation($location)
            ->setSummary($summary)
            ->setUid($guid)
            ->setDtstart($start)
            ->setDtend($end);

        if ($description !== null) {
            $vevent->setDescription($description);
        }

        if ($comment !== null) {
            $vevent->setComment($comment);
        }

        if ($cancelled) {
            $vevent->setStatus(IcalInterface::CANCELLED);
            $this->vcalendar->setMethod(IcalInterface::CANCEL);
        }
    }

    /**
     * @throws Exception
     */
    public function createCalendar(): string
    {
        return $this->vcalendar->createCalendar();
    }
}
