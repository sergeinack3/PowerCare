<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21\Events\ORU;

/**
 * Class CHPrim21REGR
 * Transfert de donn�es de regl�ment - Liaisons entre cabinets de radiologie et �tablissements cliniques ou hospitaliers
 */
class CHPrim21ORUR extends CHPrim21ORU {
  function __construct() {
    $this->type_liaison = "R";
    
    parent::__construct();
  }
}

