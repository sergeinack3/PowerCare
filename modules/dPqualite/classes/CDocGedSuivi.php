<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Qualite;

use Ox\Core\CMbObject;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Suivi des documents qualité
 * Class CDocGedSuivi
 */
class CDocGedSuivi extends CMbObject {
  // DB Table key
  public $doc_ged_suivi_id;

  // DB Fields
  public $user_id;
  public $doc_ged_id;
  public $file_id;
  public $remarques;
  public $date;
  public $actif;
  public $etat;

  // Object References
  /** @var CDocGed */
  public $_ref_proc;
  /** @var CMediusers */
  public $_ref_user;
  /** @var  CFile */
  public $_ref_file;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'doc_ged_suivi';
    $spec->key   = 'doc_ged_suivi_id';

    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs               = parent::getProps();
    $specs["user_id"]    = "ref notNull class|CMediusers back|suivis__ged";
    $specs["doc_ged_id"] = "ref notNull class|CDocGed back|documents_ged_suivi";
    $specs["file_id"]    = "ref class|CFile back|documents_ged_suivi";
    $specs["remarques"]  = "text notNull";
    $specs["etat"]       = "enum notNull list|0|16|32|48|64";
    $specs["date"]       = "dateTime";
    $specs["actif"]      = "bool";

    return $specs;
  }

  /**
   * @see parent::loadRefsFwd()
   */
  function loadRefsFwd() {
    // Forward references
    $this->_ref_proc = new CDocGed;
    $this->_ref_proc->load($this->doc_ged_id);
    $this->_ref_user = new CMediusers;
    $this->_ref_user->load($this->user_id);
  }

  function loadProcComplete() {
    // Chargement des procédures Terminées
  }

  function loadHistory($doc_ged_id = null) {
    // Chargement de l'historique complet pour une procédure
    if (!$doc_ged_id) {
      $doc_ged_id = $this->doc_ged_suivi_id;
    }
  }

  function loadFile() {
    $this->_ref_file = new CFile();
    if ($this->file_id) {
      $this->_ref_file->load($this->file_id);
    }
  }

  function delete_suivi($doc_ged_id, $lastactif_id) {
    $supprSuivi          = new CDocGedSuivi;
    $where               = array();
    $where["doc_ged_id"] = "= '$doc_ged_id'";
    if ($lastactif_id) {
      $where["doc_ged_suivi_id"] = "> '$lastactif_id'";
    }
    $supprSuivi = $supprSuivi->loadList($where);
    // Supression de chacun des enregistrement
    foreach ($supprSuivi as $keySuppr => $currSuppr) {
      $supprSuivi[$keySuppr]->delete();
    }
  }

  /**
   * @see parent::delete()
   */
  function delete() {
    // Suppression du fichier correspondant
    if ($this->file_id) {
      $this->loadFile();
      if ($this->_ref_file->file_id) {
        $this->_ref_file->delete();
      }
    }

    //suppression de la doc
    return parent::delete();
  }
}
