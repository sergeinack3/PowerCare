<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Hospi;

use Ox\Core\CAppUI;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Core\CMbObject;

/**
 * Import links for UF
 */
class CCSVImportUniteFonctionnelleLink extends CMbCSVObjectImport {
  protected $msg_error = array();
  protected $nb_exists_soins;
  protected $nb_exists_hebergement;
  protected $nb_new_soins;
  protected $nb_new_hebergement;

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

      if (!$line['Code UF Soins'] && !$line['Code UF Hébergement']) {
        $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleLink-no-uf-code', $this->current_line);
        continue;
      }

      if (!$line['Nom du Service']) {
        $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleLink-service.none', $this->current_line);
        continue;
      }

      $service      = new CService();
      $service->nom = $line['Nom du Service'];
      $service->loadMatchingObjectEsc();

      if (!$service || !$service->_id) {
        $this->msg_error[] =
          CAppUI::tr('CCSVImportUniteFonctionnelleLink-service-not-exist', $this->current_line, $line['Nom du Service']);
        continue;
      }

      $type = null;
      $ufh  = null;
      $ufs  = null;
      if ($line['Code UF Hébergement']) {
        $ufh           = new CUniteFonctionnelle();
        $ufh->code     = $line['Code UF Hébergement'];
        $ufh->type     = 'hebergement';
        $ufh->group_id = $service->group_id;
        $ufh->loadMatchingObjectEsc();

        if (!$ufh || !$ufh->_id) {
          $this->msg_error[] =
            CAppUI::tr('CCSVImportUniteFonctionnelleLink-ufh-no-exists', $this->current_line, $line['Code UF Hébergement']);
          continue;
        }
        $type = 'hebergement';
      }

      if (!$type && $line['Code UF Soins']) {
        $ufs           = new CUniteFonctionnelle();
        $ufs->code     = $line['Code UF Soins'];
        $ufs->type     = 'soins';
        $ufs->group_id = $service->group_id;
        $ufs->loadMatchingObjectEsc();

        if (!$ufs || !$ufs->_id) {
          $this->msg_error[] =
            CAppUI::tr('CCSVImportUniteFonctionnelleLink-ufs-no-exists', $this->current_line, $line['Code UF Soins']);
          continue;
        }
        $type = 'soins';
      }

      if ($type == 'soins') {
        $this->importLink($ufs, $service);
        continue;
      }

      if ($type == 'hebergement') {
        if (!$line['Nom de la chambre'] && !$line['Nom du lit']) {
          $this->importLink($ufh, $service);
          continue;
        }

        if (!$line['Nom de la chambre']) {
          $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleLink-chambre.empty', $this->current_line);
          continue;
        }

        $chambre             = new CChambre();
        $chambre->nom        = $line['Nom de la chambre'];
        $chambre->service_id = $service->_id;
        $chambres            = $chambre->loadMatchingListEsc();

        if (!$chambres) {
          $this->msg_error[] =
            CAppUI::tr('CCSVImportUniteFonctionnelleLink-chambre-no-exists', $this->current_line, $line['Nom de la chambre']);
          continue;
        }
        if ($chambres && count($chambres) > 1) {
          $this->msg_error[] =
            CAppUI::tr('CCSVImportUniteFonctionnelleLink-chambre|pl', $this->current_line, $line['Nom de la chambre']);
          continue;
        }

        $chambre = reset($chambres);

        if (!$line['Nom du lit']) {
          $this->importLink($ufh, $chambre);
          continue;
        }

        $lit             = new Clit();
        $lit->nom        = $line['Nom du lit'];
        $lit->chambre_id = $chambre->_id;

        $lits = $lit->loadMatchingListEsc();

        if (!$lits) {
          $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleLink-lit-no-exists', $this->current_line, $line['Nom du lit']);
          continue;
        }
        if ($lits && count($lits) > 1) {
          $this->msg_error[] = CAppUI::tr('CCSVImportUniteFonctionnelleLink-lit|pl', $this->current_line, $line['Nom du lit']);
        }

        $lit = reset($lits);

        $this->importLink($ufh, $lit);
      }
    }
  }

  /**
   * Create and store the link (CAffectationUniteFonctionnelle)
   *
   * @param CUniteFonctionnelle $uf     UF to use for the link
   * @param CMbObject           $object Object to link to the UF
   *
   * @return void
   */
  protected function importLink($uf, $object) {
    $link        = new CAffectationUniteFonctionnelle();
    $link->uf_id = $uf->_id;
    $link->setObject($object);
    $link->loadMatchingObjectEsc();

    if ($link->_id) {
      if ($uf->type == 'hebergement') {
        $this->nb_exists_hebergement++;
      }
      else {
        $this->nb_new_soins++;
      }

      return;
    }

    if ($error = $link->store()) {
      $this->msg_error[] = 'Ligne ' . $this->current_line . ' : ' . $error;

      return;
    }

    if ($uf->type == 'hebergement') {
      $this->nb_new_hebergement++;
    }
    else {
      $this->nb_new_soins++;
    }
  }

  /**
   * Get the error messages
   *
   * @return array
   */
  function getMsg() {
    return $this->msg_error;
  }

  /**
   * @return int
   */
  function getNbExistsHebergement() {
    return ($this->nb_exists_hebergement) ?: 0;
  }

  /**
   * @return int
   */
  function getNbExistsSoins() {
    return ($this->nb_exists_soins) ?: 0;
  }

  /**
   * @return int
   */
  function getNbHebergement() {
    return ($this->nb_new_hebergement) ?: 0;
  }

  /**
   * @return int
   */
  function getNbSoins() {
    return ($this->nb_new_soins) ?: 0;
  }
}
