<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Mappers;

class LogMapper
{
    public $date;
    public $level;
    public $message;

    /**
     * LogMapper constructor.
     *
     * @param string $log
     */
    public function __construct(string $log)
    {
        // date
        $re = '/\[\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\.\d{6}\]/m';
        preg_match($re, $log, $matches);
        $date       = $matches[0];
        $log        = str_replace($date, '', $log);
        $this->date = str_replace(['[', ']'], '', $date);

        // type
        $re = '/\[\w+]/m';
        preg_match($re, $log, $matches);
        $this->level = $matches[0];
        $log         = str_replace($this->level, '', $log);
        // $type = str_replace(array('[', ']'), '', $level);

        // message
        $this->message = trim(substr($log, 0, strpos($log, '[context:')));
    }
}
