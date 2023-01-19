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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectationUfSecondaire;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;

/**
 * Description
 */
class CCSVImportFunctions extends CMbCSVObjectImport {
  protected $msg_error = array();
  protected $msg_ok = array();
  protected $nb_new_functions;
  protected $nb_new_link;

  /**
   * @inheritdoc
   */
  function import() {
    $this->openFile();
    $this->setColumnNames();
    $this->setPointerToStart();

    $group = CGroups::loadCurrent();

    $this->current_line = 1;
    while ($line = $this->readAndSanitizeLine()) {
      $this->current_line++;

      if (!$line['intitule']) {
        $this->msg_error[] = CAppUI::tr('CCSVImportFunctions-no-intitule-%d', $this->current_line);
        continue;
      }

      if (!$line['type']) {
        $this->msg_error[] = CAppUI::tr('CCSVImportFunctions-no-type-%d', $this->current_line);
        continue;
      }

      $function           = new CFunctions();
      $function->text     = $line['intitule'];
      $function->type     = $line['type'];
      $function->group_id = $group->_id;

      $function->loadMatchingObjectEsc();

      if ($function && $function->_id) {
        $this->msg_ok[] = CAppUI::tr('CFunctions-msg-found');
      }
      else {
        $function->soustitre             = (isset($line['sous-titre'])) ? $line['sous-titre'] : '';
        $function->color                 = (isset($line['couleur'])) ? $line['couleur'] : '';
        $function->initials              = (isset($line['intiales'])) ? $line['intiales'] : '';
        $function->adresse               = (isset($line['adresse'])) ? $line['adresse'] : '';
        $function->cp                    = (isset($line['cp'])) ? $line['cp'] : '';
        $function->ville                 = (isset($line['ville'])) ? $line['ville'] : '';
        $function->fax                   = (isset($line['fax'])) ? $line['fax'] : '';
        $function->tel                   = (isset($line['tel'])) ? $line['tel'] : '';
        $function->email                 = (isset($line['mail'])) ? $line['mail'] : '';
        $function->siret                 = (isset($line['siret'])) ? $line['siret'] : '';
        $function->quotas                = (isset($line['quotas'])) ? $line['quotas'] : '';
        $function->actif                 = (isset($line['actif'])) ? $line['actif'] : '';
        $function->compta_partagee       = (isset($line['compta_partage'])) ? $line['compta_partage'] : '';
        $function->consults_events_partagees    = (isset($line['consult_partage'])) ? $line['consult_partage'] : '';
        $function->admission_auto        = (isset($line['adm_auto'])) ? $line['adm_auto'] : '';
        $function->facturable            = (isset($line['facturable'])) ? $line['facturable'] : '';
        $function->create_sejour_consult = (isset($line['creation_sejours'])) ? $line['creation_sejours'] : '';

        if ($msg = $function->store()) {
          $this->msg_error[] = $msg;
          continue;
        }

        $this->nb_new_functions++;
      }

      if (isset($line['ufs']) && $line['ufs']) {
        $ufs_codes = explode('|', $line['ufs']);
        foreach ($ufs_codes as $_code) {
          $uf = new CUniteFonctionnelle();
          $uf->group_id = $group->_id;
          $uf->code = $_code;
          $uf->type = 'medicale';

          $uf->loadMatchingObjectEsc();

          if (!$uf->_id) {
            $this->msg_error[] = CAppUI::tr('CCSVImportFunction-no-uf-%d', $this->current_line);
            continue;
          }

          $link = new CAffectationUniteFonctionnelle();
          $link->setObject($function);
          $link->uf_id = $uf->_id;
          $link->loadMatchingObjectEsc();

          if ($link->_id) {
            $this->msg_ok[] = CAppUI::tr('CAffectationUniteFonctionnelle-msg-found', $link->_view);
            continue;
          }

          if ($msg = $link->store()) {
            $this->msg_error[] = $msg;
            continue;
          }

          $this->nb_new_link++;
        }
      }

      if (isset($line['ufs_secondaires']) && $line['ufs_secondaires']) {
        $ufs_codes = explode('|', $line['ufs_secondaires']);
        foreach ($ufs_codes as $_code) {
          $uf = new CUniteFonctionnelle();
          $uf->group_id = $group->_id;
          $uf->code = $_code;
          $uf->type = 'medicale';

          $uf->loadMatchingObjectEsc();

          if (!$uf->_id) {
            $this->msg_error[] = CAppUI::tr('CCSVImportFunction-no-uf-%d', $this->current_line);
            continue;
          }

          $link = new CAffectationUfSecondaire();
          $link->setObject($function);
          $link->uf_id = $uf->_id;
          $link->loadMatchingObjectEsc();

          if ($link->_id) {
            $this->msg_ok[] = CAppUI::tr('CAffectationUfSecondaire-msg-found', $link->_view);
            continue;
          }

          if ($msg = $link->store()) {
            $this->msg_error[] = $msg;
            continue;
          }

          $this->nb_new_link++;
        }
      }
    }
  }

  /**
   * @return array
   */
  public function getMsgError() {
    return $this->msg_error;
  }

  /**
   * @return array
   */
  public function getMsgOk() {
    return $this->msg_ok;
  }

  /**
   * @return mixed
   */
  public function getNbNewFunctions() {
    return $this->nb_new_functions;
  }

  /**
   * @return mixed
   */
  public function getNbNewLink() {
    return $this->nb_new_link;
  }


}


