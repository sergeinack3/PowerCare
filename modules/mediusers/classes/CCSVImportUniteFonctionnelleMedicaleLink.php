<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Ox\Core\CAppUI;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;

/**
 * Import UFM links
 */
class CCSVImportUniteFonctionnelleMedicaleLink extends CMbCSVObjectImport {
  protected $msg_error = array();
  protected $nb_ok;
  protected $nb_exists;

  /**
   * @inheritdoc
   */
  function import() {
    $this->openFile();
    $this->setColumnNames();
    $this->setPointerToStart();

    $this->current_line = 1;
    while ($line = $this->readAndSanitizeLine()) {
      $this->current_line++;

      if (!$line['UF médicale']) {
        $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-no-code_ufm', $this->current_line);
        continue;
      }

      $ufm       = new CUniteFonctionnelle();
      $ufm->code = $line['UF médicale'];
      $ufm->type = 'medicale';
      $ufm->loadMatchingObjectEsc();

      if (!$ufm || !$ufm->_id) {
        $this->msg_error[] =
          CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-code_ufm-no-exists', $this->current_line, $line['UF médicale']);
        continue;
      }

      if (!$line['Nom utilisateur'] && (!$line['Nom'] || !$line['Prénom'])) {
        $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-no-info', $this->current_line);
        continue;
      }

      if ($line['Nom utilisateur']) {
        $user                = new CUser();
        $user->user_username = $line['Nom utilisateur'];
        $user->loadMatchingObjectEsc();

        if (!$user || !$user->_id) {
          $this->msg_error[] =
            CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-user-no-exists', $this->current_line, $line['Nom utilisateur']);
          continue;
        }

        $mediuser = $user->loadRefMediuser();

        if (!$mediuser || !$mediuser->_id) {
          $this->msg_error[] =
            CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-mediuser-no-exists', $this->current_line, $line['Nom utilisateur']);
          continue;
        }

        $func = $mediuser->loadRefFunction();
        if ($func->group_id != $ufm->group_id) {
          $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-mediuser-bad-group', $this->current_line);
          continue;
        }

        $this->storeLink($ufm, $mediuser);
        continue;
      }

      $user                  = new CUser();
      $user->user_first_name = $line['Prénom'];
      $user->user_last_name  = $line['Nom'];
      $users                 = $user->loadMatchingListEsc();

      if (!$users) {
        $this->msg_error[] = CAppUI::tr(
          'CCSVImportUniteFonctionnelleMedicaleLink-nom-prenom-no-exists', $this->current_line, $line['Nom'], $line['Prénom']
        );
        continue;
      }

      if ($users && count($users) > 1) {
        $this->msg_error[] =
          CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-user|pl', $this->current_line, $line['Nom'], $line['Prénom']);
        continue;
      }

      /** @var CUser $user */
      $user     = reset($users);
      $mediuser = $user->loadRefMediuser();

      if (!$mediuser || !$mediuser->_id) {
        $this->msg_error[] = CAppUI::tr(
          'CCSVImportUniteFonctionnelleMedicaleLink-mediuser-no-exists-nom-prenom', $this->current_line, $line['Nom'], $line['Prénom']
        );
        continue;
      }

      $func = $mediuser->loadRefFunction();

      if ($func && $func->group_id != $ufm->group_id) {
        $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleMedicaleLink-mediuser-bad-group', $this->current_line);
        continue;
      }

      $this->storeLink($ufm, $mediuser);
    }
  }

  /**
   * Store the link between a CMediuser and CUniteFonctionnelle
   *
   * @param CUniteFonctionnelle $uf     CUniteFonctionnelle to link
   * @param CMediusers          $object Mediuser to link
   *
   * @return void
   */
  protected function storeLink($uf, $object) {
    $link        = new CAffectationUniteFonctionnelle();
    $link->uf_id = $uf->_id;
    $link->setObject($object);
    $link->loadMatchingObjectEsc();

    if ($link && $link->_id) {
      $this->nb_exists++;

      return;
    }

    if ($error = $link->store()) {
      $this->msg_error[] = 'Ligne ' . $this->current_line . ' : ' . $error;

      return;
    }

    $this->nb_ok++;
  }

  /**
   * @return int
   */
  function getNbOk() {
    return ($this->nb_ok) ?: 0;
  }

  /**
   * @return int
   */
  function getNbExists() {
    return ($this->nb_exists) ?: 0;
  }

  /**
   * @return array
   */
  function getMsg() {
    return $this->msg_error;
  }
}
