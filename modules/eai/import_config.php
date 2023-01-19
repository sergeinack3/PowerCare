<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

/**
 * Import CExchangeDataFormatConfig
 */
CCanDo::checkAdmin();

$actor_guid         = CValue::request("actor_guid");
$format_config_guid = CValue::request("format_config_guid");

$file = isset($_FILES['import']) ? $_FILES['import'] : null;

if (!empty($file) && ($contents = file_get_contents($file['tmp_name']))) {
  $actor         = CMbObject::loadFromGuid($actor_guid);
  $format_config = CMbObject::loadFromGuid($format_config_guid);
  
  if (!$format_config->sender_class || !$format_config->sender_id) {
    $format_config->sender_class = $actor->_class;
    $format_config->sender_id    = $actor->_id;
  }
  
  $dom = new CMbXMLDocument();
  $dom->loadXML($contents);
  $root_name = $dom->documentElement->nodeName;
  $fields = $format_config->getPlainFields();
  unset($fields[$format_config->_spec->key]);
  unset($fields["sender_id"]);
  unset($fields["sender_class"]);
  
  if ($root_name == $format_config->_class) {
    $xpath = new CMbXPath($dom);
    $nodeList = $xpath->query("//$root_name/*");
    
    $array_configs = array();
    foreach ($nodeList as $_node) {
      $config = $xpath->getValueAttributNode($_node, "config");
      $value  = $xpath->getValueAttributNode($_node, "value");
      
      $array_configs[$config] = $value;
    }
    
    if ($count = array_diff_key($array_configs, $fields)) {
      CAppUI::setMsg(
        "Trop de données ('".count($array_configs)."') par rapport aux 
        champs de l'objet ('".count($fields)."')", 
        UI_MSG_ERROR
      );
    }
    else {
      foreach ($array_configs as $key => $value) {
        $format_config->$key = $value;
        if ($msg = $format_config->store()) {
          CAppUI::setMsg("Erreur lors de l'import de la configuration : " . $msg, UI_MSG_ERROR);
        }
        else {
          CAppUI::setMsg("Configuration correctement importée");
        }
      }
    } 
  }
  else {
    CAppUI::setMsg(
      "La classe du fichier de configuration importé ('$root_name'), ne correspond pas à celle 
      de la configuration choisie ('$format_config->_class')", 
      UI_MSG_ERROR
    );
  }

  CAppUI::callbackAjax(
    'window.parent.uploadCallback', 
    array(
      "message" => CAppUI::getMsg(), 
      "sender"  => array(
        "sender_class" => $actor->_class,
        "sender_id"    => $actor->_id, 
      )
    )
  );
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor_guid"        , $actor_guid);
$smarty->assign("format_config_guid", $format_config_guid);
$smarty->display("import_config.tpl");