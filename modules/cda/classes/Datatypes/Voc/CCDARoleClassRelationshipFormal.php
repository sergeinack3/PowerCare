<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Voc;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Voc;

/**
 * abstDomain: V10416 (C-0-D11555-V13940-V19313-V19316-V10416-cpt)
 */
class CCDARoleClassRelationshipFormal extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'CIT',
    'COVPTY',
    'CRINV',
    'CRSPNSR',
    'GUAR',
    'PAT',
    'PAYEE',
    'PAYOR',
    'POLHOLD',
    'QUAL',
    'SPNSR',
    'STD',
    'UNDWRT',
  );
  public $_union = array (
    'LicensedEntityRole',
    'RoleClassAgent',
    'RoleClassEmployee',
    'RoleClassInvestigationSubject',
  );


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "str xml|data enum|".implode("|", $this->getEnumeration(true));
    return $props;
  }
}