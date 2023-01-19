<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Class CSMB
 */
class CSMB {
  public $hostname;
  public $username;
  public $port;
  public $workgroup;
  public $password;
  public $printer_name;

  function init($source) {   
    if (!$source) {
      throw new CMbException("CSourceFTP-no-source", $source->name);
    }

    $this->hostname     = $source->host;
    $this->username     = $source->user;
    $this->password     = $source->password;
    $this->port         = $source->port;
    $this->printer_name = $source->printer_name;
    $this->workgroup    = $source->workgroup;
  }

  function printFile($file) {
    // Test de la commande smbclient
    exec("whereis smbclient", $ret);
    if (preg_match("@\/smbclient@", $ret[0]) == 0) {
       CAppUI::stepAjax("La commande smbclient n'est pas disponible", UI_MSG_ERROR);
    }

    if (file_get_contents($file->_file_path) === false) {
      CAppUI::stepAjax("Impossible d'accéder au PDF", UI_MSG_ERROR);
    }

    // Construction de l'uri
    $uri = escapeshellarg("//$this->hostname/$this->printer_name") . " ";

    if ($this->password) {
      $uri .= escapeshellarg($this->password);
    }

    $uri .= " -c " . escapeshellarg('print ' . escapeshellarg($file->_file_path)) . " ";

    if ($this->username) {
      $uri .= "-U ". escapeshellarg($this->username) . " ";
    }

    if ($this->workgroup) {
      $uri .= "-W " . escapeshellarg($this->workgroup) . " ";
    }

    if (!$this->password) {
      $uri .= "-N";
    }

    exec("smbclient $uri", $res);

    if (count($res)) {
      $mess = "";
      foreach ($res as $_res) {
        $mess .= $_res . "\n";
      }
      CAppUI::stepAjax("Impression échouée \n" . $mess, UI_MSG_ERROR);
    }
    else {
      CAppUI::stepAjax("Impression réussie", UI_MSG_OK);
    }
  }
}
