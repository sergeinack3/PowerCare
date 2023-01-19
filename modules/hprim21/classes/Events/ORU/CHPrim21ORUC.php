<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Events\ORU;

/**
 * Class CHPrim21REGC
 * Transfert de donn�es de regl�ment - Liaisons entre laboratoires et �tablissements cliniques ou hospitaliers
 */
class CHPrim21ORUC extends CHPrim21ORU {
  function __construct() {
    $this->type_liaison = "C";
    
    parent::__construct();
  }
}

