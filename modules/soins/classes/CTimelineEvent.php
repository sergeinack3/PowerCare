<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Description
 */
class CTimelineEvent implements IShortNameAutoloadable {

  /** @var string The datetime of the event */
  public $datetime;

  /** @var string The event type */
  public $type;

  /** @var boolean If true, the object of the event is a collection of objects */
  public $collection = false;

  /** @var CMbObject The object */
  public $object;

  /** @var CMbObject[] The objects (in case of a collection) */
  public $objects = array();

  /** @var string The icon associated with the type */
  public $icon;

  /** @var string The name of the template depending on the type */
  public $template;

  /** @var string The category of the event */
  public $category;

  /** @var CMediusers The user */
  public $user;

  /** @var string The types of event */
  public static $types = array(
    'affectation_begin'       => array(
      'icon'     => 'far fa-calendar-check',
      'template' => 'inc_affectation_begin',
      'category' => 'movements'
    ),
    'affectation_end'         => array(
      'icon'     => 'far fa-calendar-times',
      'template' => 'inc_affectation_end',
      'category' => 'movements'
    ),
    'CBrancardageItem'        => array(
      'icon'     => 'fa fa-bed',
      'template' => 'inc_CBrancardageItem',
      'category' => 'movements'
    ),
    'CPrescriptionLine_begin' => array(
      'icon'     => 'icon-i-pharmacy',
      'template' => 'inc_CPrescriptionLine_begin',
      'category' => 'prescriptions'
    ),
    'CPrescriptionLine_end'   => array(
      'icon'     => 'icon-i-pharmacy',
      'template' => 'inc_CPrescriptionLine_end',
      'category' => 'prescriptions'
    ),
    'CAdministration'         => array(
      'icon'     => 'icon-i-immunizations',
      'template' => 'inc_CAdministration',
      'category' => 'prescriptions'
    ),
    'CConsultation'           => array(
      'icon'     => 'fa fa-user-md',
      'template' => 'inc_CConsultation',
      'category' => 'consultations_sejour'
    ),
    'CConsultAnesth'          => array(
      'icon'     => 'icon-i-anesthesia',
      'template' => 'inc_CConsultAnesth',
      'category' => 'consultations_sejour'
    ),
    'VisiteAnesth'            => array(
      'icon'     => 'icon-i-anesthesia',
      'template' => 'inc_visiteAnesth',
      'category' => 'consultations_sejour'
    ),
    'CTransmissionMedicale'   => array(
      'icon'     => 'fa fa-sticky-note',
      'template' => 'inc_CTransmissionMedicale',
      'category' => 'saisies'
    ),
    'CObservationMedicale'    => array(
      'icon'     => 'fa fa-eye',
      'template' => 'inc_CObservationMedicale',
      'category' => 'saisies'
    ),
    'CConstantesMedicales'    => array(
      'icon'     => 'fas fa-chart-line',
      'template' => 'inc_CConstantesMedicales',
      'category' => 'saisies'
    ),
    'COperation'              => array(
      'icon'     => 'icon-i-surgery',
      'template' => 'inc_COperation',
      'category' => 'operations'
    ),
    'CCompteRendu'            => array(
      'icon'     => 'fas fa-file-alt',
      'template' => 'inc_CCompteRendu',
      'category' => 'documents_sejour'
    ),
    'CFile'                   => array(
      'icon'     => 'fa fa-file',
      'template' => 'inc_CFile',
      'category' => 'documents_sejour'
    ),
    'CExObject'               => array(
      'icon'     => 'fa fa-list-alt',
      'template' => 'inc_CExObject',
      'category' => 'documents_sejour'
    ),
    'score'                   => array(
      'icon'     => 'fa fa-calculator',
      'template' => 'inc_score',
      'category' => 'saisies'
    ),
    /**** Module Addictologie ****/
    'pathologie_begin'        => array(
      'icon'     => 'fas fa-sign-in-alt',
      'template' => 'inc_pathologie_begin',
      'category' => 'pathologies'
    ),
    'pathologie_end'          => array(
      'icon'     => 'fas fa-sign-out-alt',
      'template' => 'inc_pathologie_end',
      'category' => 'pathologies'
    ),
    'suivi_begin'             => array(
      'icon'     => 'fas fa-sign-in-alt',
      'template' => 'inc_suivi_begin',
      'category' => 'suivis'
    ),
    'suivi_end'               => array(
      'icon'     => 'fas fa-sign-out-alt',
      'template' => 'inc_suivi_end',
      'category' => 'suivis'
    ),
    'objectifs_soins_open'   => array(
      'icon'     => 'fas fa-book-open',
      'template' => 'inc_objectif_soin_open',
      'category' => 'objectifs_soins'
    ),
    'objectifs_soins_achieved' => array(
      'icon'     => 'fas fa-book',
      'template' => 'inc_objectif_soin_achieved',
      'category' => 'objectifs_soins'
    ),
    'objectifs_soins_not_achieved' => array(
      'icon'     => 'far fa-times-circle',
      'template' => 'inc_objectif_soin_not_achieved',
      'category' => 'objectifs_soins'
    ),
    'note_suite_medical'      => array(
      'icon'     => 'fas fa-file-medical',
      'template' => 'inc_note_suite_medical',
      'category' => 'notes_suite'
    ),
    'note_suite_psycho'       => array(
      'icon'     => 'fas fa-brain',
      'template' => 'inc_note_suite_psycho',
      'category' => 'notes_suite'
    ),
    'note_suite_social'       => array(
      'icon'     => 'fas fa-hands-helping',
      'template' => 'inc_note_suite_social',
      'category' => 'notes_suite'
    ),
    'note_suite_other'        => array(
      'icon'     => 'fas fa-question',
      'template' => 'inc_note_suite_other',
      'category' => 'notes_suite'
    ),
  );

  /**
   * CTimelineEvent constructor.
   *
   * @param string     $datetime The datetime of the event
   * @param string     $type     The type of event
   * @param CMbObject  $object   The object
   * @param CMediusers $user     The user
   */
  public function __construct($datetime, $type, $object, $user = null) {
    $this->datetime  = $datetime;
    $this->type      = $type;
    $this->object    = $object;
    $this->objects[] = $object;

    $this->icon     = self::$types[$this->type]['icon'];
    $this->template = self::$types[$this->type]['template'];
    $this->category = self::$types[$this->type]['category'];

    if ($user) {
      $this->user = $user;
    }
  }

  /**
   * Transform the object in a collection, and add the given object to the collection
   *
   * @param CMbObject $object The object to add
   *
   * @return void
   */
  public function addObject($object) {
    $this->objects[] = $object;

    if (count($this->objects) > 1 && !$this->collection) {
      $this->collection = true;
    }
  }
}
