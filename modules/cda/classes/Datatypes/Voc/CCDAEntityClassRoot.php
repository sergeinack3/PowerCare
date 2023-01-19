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
 * specDomain: V13922 (C-0-D10882-V13922-cpt)
 */
class CCDAEntityClassRoot extends CCDA_Datatype_Voc {

  public $_enumeration = array (
    'ENT',
    'HCE',
    'RGRP',
  );
  public $_union = array (
    'EntityClassLivingSubject',
    'EntityClassMaterial',
    'EntityClassOrganization',
    'EntityClassPlace',
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