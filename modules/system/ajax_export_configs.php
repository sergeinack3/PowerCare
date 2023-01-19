<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbConfig;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CMbXMLDocument;
use Ox\Core\Module\CModule;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\CConfigurationCompare;
use Ox\Mediboard\System\CConfigurationModelManager;

CCanDo::checkAdmin();

CView::enforceSlave();

$file = tempnam('tmp/', 'configs');

$xml = new CMbXMLDocument("utf-8");
$xml->setDocument($file);
$xml->formatOutput = true;
$root              = $xml->createElement("configurations-export");
$root->setAttribute("date", CMbDT::dateTime());
$xml->appendChild($root);

// Instance configs
global $dPconfig;

$configs = $dPconfig;

$forbidden_values = array(
  "root_dir",
  "base_url",
  "servers_ip"
);

foreach ($configs as $_key => $_config) {
  if (in_array($_key, $forbidden_values) || in_array($_key, array("db", "php"))) {
    unset($configs[$_key]);
  }
}
$instance_confs = array();
CMbConfig::buildConf($instance_confs, $configs, null);

$instance_configs = $xml->createElement('instance-configs');
$root->appendChild($instance_configs);

foreach ($instance_confs as $_feature => $_value) {
  $_node = $xml->createElement('instance-config');
  $_node->setAttribute('feature', CConfigurationCompare::sanitizeString($_feature));
  $_node->setAttribute('value', CConfigurationCompare::sanitizeString($_value));
  $instance_configs->appendChild($_node);
}

// CConfigurations
$group = CGroups::loadCurrent();
$context = $group->_guid;

$groups_configs = $xml->createElement('groups-configs');
$groups_configs->setAttribute('context', CConfigurationCompare::sanitizeString($context));
$groups_configs->setAttribute('real_name', CConfigurationCompare::sanitizeString($group->text));
$root->appendChild($groups_configs);

$configuration = new CConfiguration();
$ds            = $configuration->getDS();

$modules = CModule::getInstalled();
foreach ($modules as $_mod) {
  // Get the configurations for the module
  // If no configuration for the module, continue
  try {
    $values = CConfigurationModelManager::getValues($_mod->mod_name, $group->_class, $group->_id);
  }
  catch (Exception $e) {
    continue;
  }

  $_node_mod = $xml->createElement('module-config');
  $_node_mod->setAttribute('mod_name', CConfigurationCompare::sanitizeString($_mod->mod_name));

  if (!$values) {
    continue;
  }

  foreach ($values as $_sub_group => $_values) {
    if (!$_values || !is_array($_values)) {
      continue;
    }

    // Add the module node
    $groups_configs->appendChild($_node_mod);

    foreach ($_values as $_sub_context => $_confs) {
      $_sub_context = "{$_mod->mod_name} {$_sub_group} {$_sub_context}";
      if (!is_array($_confs)) {
        $_node_sub_context = $xml->createElement("config");
        $_node_mod->appendChild($_node_sub_context);
        $_node_sub_context->setAttribute('feature', CConfigurationCompare::sanitizeString($_sub_context));
        $_node_sub_context->setAttribute('value', CConfigurationCompare::sanitizeString($_confs));
        continue;
      }

      foreach ($_confs as $_conf_name => $_conf_value) {
        if (is_array($_conf_value)) {
          foreach ($_conf_value as $_feature => $_value) {
            if (is_array($_value)) {
              foreach ($_value as $_sub_feature => $_sub_value) {
                $_node_leaf = $xml->createElement('config');
                $_node_leaf->setAttribute(
                  'feature',
                  CConfigurationCompare::sanitizeString($_sub_context . ' ' . $_conf_name . ' ' . $_feature . ' ' . $_sub_feature)
                );
                $_node_leaf->setAttribute('value', CConfigurationCompare::sanitizeString($_sub_value));
                $_node_mod->appendChild($_node_leaf);
              }
            }
            else {
              $_node_leaf = $xml->createElement('config');
              $_node_leaf->setAttribute(
                'feature', CConfigurationCompare::sanitizeString($_sub_context . ' ' . $_conf_name . ' ' . $_feature)
              );
              $_node_leaf->setAttribute('value', CConfigurationCompare::sanitizeString($_value));
              $_node_mod->appendChild($_node_leaf);
            }
          }
        }
        else {
          $_node = $xml->createElement('config');
          $_node->setAttribute('feature', CConfigurationCompare::sanitizeString($_sub_context . ' ' . $_conf_name));
          $_node->setAttribute('value', CConfigurationCompare::sanitizeString($_conf_value));
          $_node_mod->appendChild($_node);
        }
      }
    }
  }
}

$xml->saveFile();

$title = CMbString::removeDiacritics($group->text);

// Direct download of the file
// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
// [http://bugs.php.net/bug.php?id=16173]
header("Pragma: ");
header("Cache-Control: ");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
// END extra headers to resolve IE caching bug

header("MIME-Version: 1.0");

header("Content-disposition: attachment; filename=\"configs-{$title}.xml\";");
header("Content-type: text/xml");
header("Content-length: " . filesize($file));

readfile($file);
unlink($file);
