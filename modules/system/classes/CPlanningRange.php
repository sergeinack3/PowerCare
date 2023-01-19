<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;

/**
 * Weekly planning range
 */
class CPlanningRange implements IShortNameAutoloadable
{
    /** @var string */
    public $guid;
    /** @var string int */
    public $internal_id;

    /** @var string */
    public $title;
    /** @var string */
    public $type;

    /** @var string */
    public $start;
    /** @var float|int|string */
    public $end;
    /** @var int */
    public $length;
    /** @var float|int|mixed|string */
    public $day;

    /** @var float|int|string */
    public $hour;
    /** @var float|int|string */
    public $minutes;

    /** @var int */
    public $width;
    /** @var int */
    public $offset;
    /** @var string */
    public $color;
    /** @var bool */
    public $important;
    /** @var string */
    public $icon;
    /** @var string */
    public $icon_desc;
    /** @var bool */
    public $disabled;

    /**
     * Range constructor
     *
     * @param string $guid      GUID
     * @param string $date      Date
     * @param int    $length    Length
     * @param string $title     Title
     * @param null   $color     Color
     * @param null   $css_class CSS class
     */
    public function __construct(
        string $guid,
        string $date,
        int $length = 0,
        ?string $title = "",
        string $color = null,
        string $css_class = null,
        string $icon = null,
        string $icon_desc = null
    ) {
        $this->guid        = $guid;
        $this->internal_id = "CPlanningRange-" . uniqid();

        $this->start     = $date;
        $this->length    = $length;
        $this->title     = CMbString::htmlEntities($title);
        $this->color     = $color;
        $this->icon      = $icon;
        $this->icon_desc = $icon_desc;
        $this->css_class = is_array($css_class) ? implode(" ", $css_class) : $css_class;

        if (preg_match("/[0-9]+ /", $this->start)) {
            $parts         = explode(" ", $this->start);
            $this->end     = "{$parts[0]} " . CMbDT::time("+{$this->length} MINUTES", $parts[1]);
            $this->day     = $parts[0];
            $this->hour    = CMbDT::format($parts[1], "%H");
            $this->minutes = CMbDT::format($parts[1], "%M");
        } else {
            $this->day     = CMbDT::date($date);
            $this->end     = CMbDT::dateTime("+{$this->length} MINUTES", $date);
            $this->hour    = CMbDT::format($date, "%H");
            $this->minutes = CMbDT::format($date, "%M");
        }
    }

    /**
     * Check range collision
     *
     * @param CPlanningRange $range The range to test colission with
     *
     * @return bool
     */
    public function collides(self $range): bool
    {
        if ($range == $this || $this->length == 0 || $range->length == 0) {
            return false;
        }

        return ($range->start < $this->end && $range->end > $this->end) ||
            ($range->start < $this->start && $range->end > $this->start) ||
            ($range->start >= $this->start && $range->end <= $this->end);
    }
}
