<?php
/**
 * @package Mediboard\System
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

CCanDo::checkAdmin();

$object_config_guid = CValue::request("object_config_guid");

$file = isset($_FILES['import']) ? $_FILES['import'] : null;

if (!empty($file) && ($contents = file_get_contents($file['tmp_name']))) {
  $object_config = CMbObject::loadFromGuid($object_config_guid);
  $object = $object_config->loadRefObject();

  $dom = new CMbXMLDocument();
  $dom->loadXML($contents);
  $root_name = $dom->documentElement->nodeName;
  $fields = $object_config->getPlainFields();
  unset($fields[$object_config->_spec->key]);
  unset($fields["object_id"]);

  if ($root_name == $object_config->_class) {
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
        $object_config->$key = $value;
        if ($msg = $object_config->store()) {
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
      de la configuration choisie ('$object_config->_class')", 
      UI_MSG_ERROR
    );
  }
  
  CAppUI::callbackAjax(
    'window.parent.uploadCallback', 
    array(
      "message"             => CAppUI::getMsg(), 
      "object_id"           => $object->_id,
      "object_configs_guid" => $object_config->_guid
    )
  );
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object_config_guid", $object_config_guid);
$smarty->display("import_config.tpl");