<?php
/**
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CPlageConge
 */
class CPlageConge extends CMbObject {
  // DB Table key
  public $plage_id;

  // DB Fields
  public $date_debut;
  public $date_fin;
  public $libelle;
  public $user_id;
  public $replacer_id;
  public $pct_retrocession;

  // Object References
  /** @var CMediusers */
  public $_ref_user;
  /** @var CMediusers */
  public $_ref_replacer;

  // Form fields
  public $_duree;

  // Behaviour fields
  public $_activite; // For pseudo plages

  /** @var string Minimal date (searching purpose) */
  public $_date_min;

  /** @var string Maximal date (searching purpose) */
  public $_date_max;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $specs        = parent::getSpec();
    $specs->table = "plageconge";
    $specs->key   = "plage_id";

    return $specs;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $specs                     = parent::getProps();
    $specs["user_id"]          = "ref class|CMediusers notNull back|plages_conge";
    $specs["date_debut"]       = "dateTime notNull";
    $specs["date_fin"]         = "dateTime moreEquals|date_debut notNull";
    $specs["libelle"]          = "str notNull";
    $specs["replacer_id"]      = "ref class|CMediusers back|remplacements";
    $specs["pct_retrocession"] = "pct default|70 show|0";

    $specs["_duree"]    = "num";
    $specs["_date_min"] = "date";
    $specs["_date_max"] = "date";

    return $specs;
  }

  /**
   * @see parent::updateFormFields()
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_shortview = $this->_view = $this->libelle;
  }

  /**
   * check before store
   *
   * @return null|string
   */
  function check() {
    $this->completeField("date_debut", "date_fin", "user_id");
    $plage_conge          = new CPlageConge();
    $plage_conge->user_id = $this->user_id;
    $plages_conge         = $plage_conge->loadMatchingList();
    unset($plages_conge[$this->_id]);

    /** @var $plages_conge CPlageConge[] */
    foreach ($plages_conge as $_plage) {
      if (CMbRange::collides($this->date_debut, $this->date_fin, $_plage->date_debut, $_plage->date_fin)) {
        return CAppUI::tr("CPlageConge-conflit %s", $_plage->_view);
      }
    }

    return parent::check();
  }

  /**
   * loadFor
   *
   * @param string $user_id user_id
   * @param string $date    date to check
   */
  function loadFor($user_id, $date) {
    $where["user_id"] = "= '$user_id'";
    $where[]          = "'$date' BETWEEN DATE(date_debut) AND DATE(date_fin)";
    $this->loadObject($where);
  }

  /**
   * load list for a range
   *
   * @param string $user_id user id
   * @param string $min     date min
   * @param string $max     date max
   *
   * @return CPlageConge[]
   */
  function loadListForRange($user_id, $min, $max) {
    $where["user_id"]    = "= '$user_id'";
    $where["date_debut"] = "<= '$max'";
    $where["date_fin"]   = ">= '$min'";
    $order               = "date_debut";

    return $this->loadList($where, $order);
  }

  /**
   * @param array  $user_ids list of user's id
   * @param string $date     date targeted
   *
   * @return CPlageConge[]
   */
  static function loadForIdsForDate($user_ids, $date) {
    $plage   = new self();
    $where   = array(
      "user_id" => CSQLDataSource::prepareIn($user_ids)
    );
    $where[] = "'$date' BETWEEN DATE(date_debut) AND DATE(date_fin)";
    $plages  = $plage->loadList($where);

    return $plages;
  }

  /**
   * LoadRefsReplacementFor, load the list of replacment for a user
   *
   * @param string $user_id user to check
   *
   * @param string $date    date
   *
   * @return CPlageConge[]
   */
  function loadRefsReplacementsFor($user_id, $date) {
    $where["replacer_id"] = "= '$user_id'";
    $where[]              = "'$date' BETWEEN date_debut AND date_fin";

    return $this->loadList($where);
  }

  /**
   * load the user replacing
   *
   * @return CMediusers|null
   * @throws Exception
   */
  function loadRefReplacer() {
    return $this->_ref_replacer = $this->loadFwdRef("replacer_id", true);
  }

  /**
   * load the user informations
   *
   * @return CMediusers|null
   * @throws Exception
   */
  function loadRefUser() {
    $this->_ref_user = $this->loadFwdRef("user_id", true);
    $this->_ref_user->loadRefFunction();

    return $this->_ref_user;
  }

  /**
   * get perms
   *
   * @param int $permType permtype
   *
   * @return bool
   */
  function getPerm($permType) {
    if ($this->user_id == CAppUI::$user->_id) {
      return true;
    }

    return $this->loadRefUser()->getPerm($permType);
  }

  /**
   * create a pseudoPlage
   *
   * @param string $user_id  user id
   *
   * @param string $activite activity type (deb or fin)
   *
   * @param string $limit    date limit chosen
   *
   * @return CPlageConge
   */
  static function makePseudoPlage($user_id, $activite, $limit) {
    // Parameter check
    if (!in_array($activite, array("deb", "fin"))) {
      CMbObject::error("Activite%s should be one of 'deb' or 'fin'", $activite);
    }

    // Make plage
    $plage            = new self;
    $plage->_id       = "$activite-$user_id";
    $plage->user_id   = $user_id;
    $plage->_activite = $activite;
    $plage->libelle   = CAppUI::tr("$plage->_class._activite.$activite");

    // Concerned user
    $user = CMediusers::get($user_id);

    // Dates for deb case
    if ($activite == "deb") {
      $plage->date_debut = "$limit 00:00:00";
      $plage->date_fin   = CMbDT::dateTime("-1 DAY", $user->deb_activite);
    }

    // Dates for fin case
    if ($activite == "fin") {
      $plage->date_debut = CMbDT::dateTime("+1 DAY", $user->fin_activite);
      $plage->date_fin   = "$limit 23:59:59";
    }

    return $plage;
  }

    public function store()
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        if ($this->loadRefUser()->isProfessionnelDeSante()) {
            try {
                $this->lockPlageConsult();
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }
    }

    /**
     * Verrouillage des plage de consultations après la création de la plage de congés
     * @throws Exception
     */
    private function lockPlageConsult(): void
    {
        $plage_consult = new CPlageconsult();
        $ds            = $plage_consult->getDS();
        $request       = new CRequest();
        $table         = $plage_consult->_spec->table;
        $select        = "plageconsult_id";
        $where         = [
            "chir_id" => $ds->prepare("= ?", $this->user_id),
            "locked"  => $ds->prepare("= '0'"),
            "date"    => $ds->prepareBetween(CMbDT::date($this->date_debut), CMbDT::date($this->date_fin)),
        ];

        $request->addSelect($select);
        $request->addTable($table);
        $request->addWhere($where);

        $result = $ds->loadList($request->makeSelect());

        $plages_consult = $plage_consult->loadAll(CMbArray::pluck($result, "plageconsult_id"));

        foreach ($plages_consult as $plage_consult) {
            $plage_consult->locked = 1;

            if ($msg = $plage_consult->store()) {
                throw new Exception($msg);
            }
        }
    }
}