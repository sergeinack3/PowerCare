<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use Ox\Core\CMbObject;

/**
 * Interface CHL7EventADT 
 * Admit Discharge Transfer
 */
interface CHL7EventADT {
  /**
   * Construct
   *
   * @return CHL7EventADT
   */
  function __construct();

  /**
   * Build event
   *
   * @param CMbObject $object Object
   *
   * @see parent::build()
   *
   * @return void
   */
  function build($object);

  /**
   * Build i18n segements
   *
   * @param CMbObject $object Object
   *
   * @see parent::buildI18nSegments()
   *
   * @return void
   */
  function buildI18nSegments($object);
}