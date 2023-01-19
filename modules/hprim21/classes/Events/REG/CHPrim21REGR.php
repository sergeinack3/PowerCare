<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Events\REG;

/**
 * Class CHPrim21REGR
 * Transfert de données de reglèment - Liaisons entre cabinets de radiologie et établissements cliniques ou hospitaliers
 */
class CHPrim21REGR extends CHPrim21REG {
  function __construct() {
    $this->type_liaison = "R";
    
    parent::__construct();
  }
}

