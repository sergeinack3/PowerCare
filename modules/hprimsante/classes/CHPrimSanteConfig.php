<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Ox\Core\CMbFieldSpec;
use Ox\Interop\Eai\CExchangeDataFormatConfig;
use Ox\Interop\Eai\Manager\FileManager;
use Ox\Interop\Eai\Repository\PatientRepository;

/**
 * Config hprim sante
 */
class CHPrimSanteConfig extends CExchangeDataFormatConfig {

  static $config_fields = array(
    // Format
    "strict_segment_terminator",
    "segment_terminator",

    //handle
    "action",
    "notifier_entree_reelle",
    "handle_oru_type",
    "handle_patient_ORU",
    "search_patient_strategy",
    'associate_category_to_a_file',
    'define_name',
    'id_category_patient',
    'object_attach_OBX',
    'mode_sas',
    'creation_date_file_like_treatment',
  );

  /** @var integer Primary key */
  public $hprimsante_config_id;

  // Format
  public $strict_segment_terminator;
  public $segment_terminator;

  public $action;
  public $notifier_entree_reelle;
  public $search_patient_strategy;

  // handle oru
  public $handle_oru_type;
  public $handle_patient_ORU;
  public $associate_category_to_a_file;
  public $define_name;
  public $id_category_patient;
  public $object_attach_OBX;
  public $mode_sas;
  public $creation_date_file_like_treatment;

  /**
   * @var array Categories
   */
  public $_categories = array(
    "format" => array(
      "strict_segment_terminator",
      "segment_terminator",
    ),
    "handle" => array(
      "action",
      "notifier_entree_reelle",
      "search_patient_strategy"
    ),

    "handle oru" => [
        "handle_oru_type",
        "handle_patient_ORU",
        'associate_category_to_a_file',
        'define_name',
        'id_category_patient',
        'object_attach_OBX',
        'mode_sas',
        'creation_date_file_like_treatment',
    ]
  );

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "hprimsante_config";
    $spec->key    = "hprimsante_config_id";
    $spec->uniques["uniques"] = array("sender_id", "sender_class");
    return $spec;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props['sender_id'] .= ' back|config_hprimsante';

    // Encoding
    $props["strict_segment_terminator"] = "bool default|0";
    $props["segment_terminator"]        = "enum list|CR|LF|CRLF";

    //handle
    $props["action"]                  = "enum list|IPP_NDA|Patient|Sejour|Patient_Sejour default|IPP_NDA";
    $props["notifier_entree_reelle"]  = "bool default|1";

    // handle ORU
    $props["search_patient_strategy"] = "enum list|"
        . implode('|', PatientRepository::STRATEGIES) . " default|" . PatientRepository::STRATEGY_BEST;
    $props["handle_oru_type"]         = "enum list|files|labo";
    $props["handle_patient_ORU"]      = "bool default|0";
    $props['associate_category_to_a_file'] = 'bool default|0';
    $props['define_name'] = 'enum list|enum list|'
        . implode('|', FileManager::STRATEGIES_FILENAME)
        . ' default|' . FileManager::STRATEGY_FILENAME_DEFAULT;
    $props['id_category_patient'] = 'num';
    $props['object_attach_OBX'] = 'enum list|CPatient|CSejour|COperation|CMbObject|CFilesCategory default|CMbObject';
    $props['mode_sas'] = 'bool default|0';
    $props['creation_date_file_like_treatment'] = 'bool default|0';

    return $props;
  }

  /**
   * Get config fields
   *
   * @return array
   */
  function getConfigFields() {
    return $this->_config_fields = self::$config_fields;
  }
}
