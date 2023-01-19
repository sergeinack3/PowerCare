<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

/**
 * Class CSyslogAuditMessage (IHE ATNA)
 */
class CSyslogAuditMessage extends CSyslogMessage {
  const FACILITY = '10';
  const SEVERITY = '5';
  const MSGID    = 'IHE+RFC-3881';

  function __construct($encoding = "iso-8859-1") {
    parent::__construct($encoding);

    $this->setPri(self::FACILITY, self::SEVERITY);
    $this->msgid = self::MSGID;
  }
}
