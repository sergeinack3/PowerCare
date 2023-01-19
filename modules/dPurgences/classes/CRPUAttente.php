<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Attentes d'imagerie, biologie ou d'un spécialiste
 */
class CRPUAttente extends CMbObject {
  // DB Table key
  public $attente_id;

  public $rpu_id;
  public $type_attente;
  public $type_radio;
  public $demande;
  public $depart;
  public $retour;
  public $user_id;

  /** @var CRPU */
  public $_ref_rpu;
  /** @var CMediusers */
  public $_ref_user;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'rpu_attente';
    $spec->key   = 'attente_id';

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["rpu_id"]       = "ref notNull class|CRPU cascade back|attentes_rpu";
    $props["type_attente"] = "enum notNull list|radio|bio|specialiste";
    $props["type_radio"]   = "enum list|classic|echo|scanner|irm|scintigraphie";
    $props["demande"]      = "dateTime";
    $props["depart"]       = "dateTime";
    $props["retour"]       = "dateTime";
    $props["user_id"]      = "ref class|CMediusers back|demandes_bio_attente";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    $this->completeField("demande", "depart", "retour");
    //Supression de la demande lorsque l'ensemble de ses champs sont vidés
    if ($this->_id && !$this->demande && !$this->depart && !$this->retour) {
      if ($msg = $this->delete()) {
        return $msg;
      }

      return null;
    }
    // Standard Store
    if ($msg = parent::store()) {
      return $msg;
    }

    return null;
  }

  /**
   * Récupération des l'utilisateur ayant déposé la biologie
   *
   * @return CMediusers
   */
  function loadRefUser() {
    return $this->_ref_user = CMediusers::get($this->user_id);
  }
}