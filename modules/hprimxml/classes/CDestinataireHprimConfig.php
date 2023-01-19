<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CMbObjectConfig;
use Ox\Core\CMbObjectSpec;

/**
 * Class CDestinataireHprimConfig
 */
class CDestinataireHprimConfig extends CMbObjectConfig {
  public $dest_hprim_config_id;
  
  public $object_id; // CDestinataireHprim
  
  // Format
  public $encoding;
  public $uppercase_fields;

  // Application
  public $receive_ack;
  
  // Send
  public $send_sortie_prevue;
  public $send_all_patients;
  public $send_default_serv_with_type_sej;
  public $send_volet_medical;
  public $send_birth;
  public $send_movement_location;
  public $send_insured_without_admit;
  public $send_child_admit;
  public $send_no_facturable;
  public $send_timing_bloc;
  public $send_actes;
  public $send_actes_only_functions;
  public $send_prescripteur_ngap;

  // Build
  public $build_id_sejour_tag;
  public $build_frais_divers;
  public $build_id_professionnel_sante;
  public $transform_X_code_CIM;

  //AppFine
  public $send_appFine;

  // SIH-Cabinet
  public $sih_cabinet_id;
  
  public $_categories = array(
    // Format
    "format" => array(
      "encoding", 
      "uppercase_fields",
    ),
    
    // Application
    "application" => array(
      "receive_ack" 
    ),
    
    // Send
    "send" => array(
      'trigger' => array(
        "send_sortie_prevue",
        "send_all_patients",
        "send_default_serv_with_type_sej",
        "send_volet_medical",
        "send_birth",
        "send_movement_location",
        "send_insured_without_admit",
        "send_child_admit",
        "send_no_facturable",
        "send_timing_bloc",
        "send_actes",
        "send_actes_only_functions",
        "send_prescripteur_ngap",
      ),

      // AppFine
      'appFine' => array(
        'send_appFine',
      ),

      // SIH-Cabinet
      'sih-cabinet' => array(
        'sih_cabinet_id',
      )
    ),

    // Build
    "build" => array(
      "build_id_sejour_tag",
      "build_frais_divers",
      "build_id_professionnel_sante",
      "transform_X_code_CIM"
    )
  );

  /**
   * Initialize object specification
   *
   * @return CMbObjectSpec the spec
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = "destinataire_hprim_config";
    $spec->key   = "dest_hprim_config_id";
    $spec->uniques["uniques"] = array("object_id");
    return $spec;
  }

  /**
   * Get properties specifications as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["object_id"] = "ref class|CDestinataireHprim back|object_configs";
    
    // Format
    $props["encoding"]         = "enum list|UTF-8|ISO-8859-1 default|UTF-8";
    $props["uppercase_fields"] = "bool default|0";
    
    // Send
    $props["send_sortie_prevue"]              = "bool default|1"; 
    $props["send_all_patients"]               = "bool default|0";
    $props["send_default_serv_with_type_sej"] = "bool default|0";
    $props["send_volet_medical"]              = "bool default|0";
    $props["send_birth"]                      = "bool default|0";
    $props["send_movement_location"]          = "bool default|0";
    $props["send_insured_without_admit"]      = "bool default|0";
    $props["send_child_admit"]                = "bool default|1";
    $props["send_no_facturable"]              = "enum list|0|1|2 default|1";
    $props["send_appFine"]                    = "bool default|0";
    $props["send_timing_bloc"]                = "bool default|0";
    $props["send_prescripteur_ngap"]          = "enum list|acte|demande default|acte";
    $props["send_actes"]                      = "enum list|ccamngap|ccam|ngap default|ccamngap";
    $props["send_actes_only_functions"]       = "str";
    $props["build_id_sejour_tag"]             = "str";
    $props["build_frais_divers"]              = "enum list|fd|presta default|fd";
    $props["build_id_professionnel_sante"]    = "enum list|adeli|rpps default|adeli";
    $props["transform_X_code_CIM"]            = "bool default|0";

    // Application
    $props["receive_ack"] = "bool default|1";

    // SIH-Cabinet
    $props['sih_cabinet_id'] = 'num';
    
    return $props;
  }
}
