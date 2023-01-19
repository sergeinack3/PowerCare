<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;


use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Printing\CSourceLPR;

/**
 * LPR driver
 */
class CLPR {
  public $hostname;
  public $username;
  public $port;
  public $printer_name;

  /**
   * Initialize the LPR driver
   *
   * @param CSourceLPR $source The LPR source
   *
   * @throws CMbException
   * @return void
   */
  function init(CSourceLPR $source) {
    if (!$source) {
      throw new CMbException("CSourceFTP-no-source", $source->name);
    }

    $this->hostname = $source->host;
    $this->username = $source->user;
    $this->port     = $source->port;
    $this->printer_name = $source->printer_name;
  }

  /**
   * Print a file
   *
   * @param CFile $file The file to print
   *
   * @return void
   */
  function printFile(CFile $file) {
    // Test de la commande lpr
    $cmd_ok = true;
    $windows = strpos(PHP_OS, "WIN") !== false;

    // Windows
    if ($windows) {
      exec("lpr", $ret);
      $cmd_ok = count($ret) > 0;
    }

    // Others
    else {
      exec("whereis lpr", $ret);
      if (preg_match("@\/lpr@", $ret[0]) == 0) {
        $cmd_ok = false;
      }
    }

    if (!$cmd_ok) {
      CAppUI::stepAjax("La commande 'lpr' n'est pas disponible", UI_MSG_ERROR);
    }
    
    if (file_get_contents($file->_file_path) === false) {
      CAppUI::stepAjax("Impossible d'accéder au PDF", UI_MSG_ERROR);
    }

    $server = "";
    if ($windows && $this->hostname) {
      $server = "-S " . escapeshellarg($this->hostname);
    }

    $printer = "";
    if ($this->printer_name) {
      $printer = "-P " . escapeshellarg($this->printer_name);
    }

    $u = "";

    if ($this->username) {
      $u = "-U " . escapeshellarg($this->username);
    }
    /*if ($this->port) {
      $host .= ":$this->port";
    }*/

    // La commande lpr interprête mal le hostname pour établir la requête
    // $host = "$this->hostname";
    // $command = "lpr -H $host $u $printer '$file->_file_path'";
    
    // Ajout préalable de l'imprimante via cups du serveur web
    $command = "lpr $u $server $printer " . escapeshellarg(realpath($file->_file_path));

    exec($command, $res, $success);

    // La commande lpr retourne 0 si la transmission s'est bien effectuée
    if ($success == 0) {
      CAppUI::stepAjax("Impression réussie", UI_MSG_OK);  
    }
    else {
      CAppUI::stepAjax("Impression échouée, vérifiez la configuration", UI_MSG_ERROR);
    }
  }
}
