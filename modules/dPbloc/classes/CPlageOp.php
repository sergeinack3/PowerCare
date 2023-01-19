<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Exception;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CPlageHoraire;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\Prescription\CAdministrationDM;
use Symfony\Component\Routing\RouterInterface;

/**
 * Plage opératoire (vacations au bloc)
 * Class CPlageOp
 */
class CPlageOp extends CPlageHoraire
{
    /** @var string */
    public const RESOURCE_TYPE = 'plageop';

    /** @var string */
    public const RELATION_SALLE = "salle";

    /** @var string */
    public const RELATION_BLOC = "bloc";

    const RANK_VALIDATE = 1;
    const RANK_REORDER  = 2;

    static $minutes          = [];
    static $hours            = [];
    static $hours_start      = null;
    static $hours_stop       = null;
    static $minutes_interval = null;

    // DB Table key
    public $plageop_id;

    // DB References
    public $debut_reference;
    public $fin_reference;
    public $chir_id;
    public $anesth_id;
    public $spec_id;
    public $salle_id;
    public $spec_repl_id;
    public $secondary_function_id;
    public $original_owner_id;
    public $original_function_id;

    // DB fields
    public $unique_chir;
    public $temps_inter_op;
    public $max_intervention;
    public $max_ambu;
    public $max_hospi;
    public $verrouillage;
    public $delay_repl;
    public $actes_locked;
    public $entree_chir;
    public $entree_anesth;
    public $prevenance_date;
    public $urgence;
    public $status;
    public $pause;

    // Form Fields
    public $_day;
    public $_month;
    public $_year;
    public $_duree_prevue;
    public $_type_repeat;
    public $_count_operations;
    public $_count_operations_placees;
    public $_count_operations_annulees;
    public $_count_operations_ambu;
    public $_count_operations_hospi;
    public $_count_all_operations;
    public $_fill_rate;
    public $_fill_time;
    public $_fill_rate_color;
    public $_reorder_up_to_interv_id;
    public $_nbQuartHeure;
    public $_cumulative_minutes      = 0;
    public $_count_duplicated_plages = 0;
    public $_color_status;
    public $_time_ops;

    // Behaviour Fields
    public $_verrouillee     = [];
    public $_skip_collisions = false;

    /** @var CMbObject */
    public $_ref_owner;
    /** @var CMediusers */
    public $_ref_chir;
    /** @var CMediusers */
    public $_ref_anesth;
    /** @var CFunctions */
    public $_ref_spec;
    /** @var CMbObject */
    public $_ref_original_owner;
    /** @var CMediusers */
    public $_ref_original_chir;
    /** @var CFunctions */
    public $_ref_original_spec;

    /** @var CFunctions */
    public $_ref_spec_repl;

    /** @var CSalle */
    public $_ref_salle;

    /** @var COperation[] */
    public $_ref_operations = [];
    public $_nb_ref_operations;

    /** @var COperation[] */
    public $_unordered_operations = [];

    /** @var CAdministrationDM[] */
    public $_ref_lines_dm = [];

    /** @var CFunctions */
    public $_ref_secondary_function;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        self::initHoursMinutes();
    }

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec                 = parent::getSpec();
        $spec->table          = 'plagesop';
        $spec->key            = 'plageop_id';
        $spec->collision_keys = ["salle_id"];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('bloc_plageop', ["plageop_id" => $this->_id]);
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                          = parent::getProps();
        $props["chir_id"]               = "ref class|CMediusers back|plages_op_chir fieldset|default";
        $props["anesth_id"]             = "ref class|CMediusers back|plages_op_anesth fieldset|extra";
        $props["spec_id"]               = "ref class|CFunctions back|plages_op fieldset|default";
        $props["salle_id"]              = "ref notNull class|CSalle back|plages_op fieldset|extra";
        $props["spec_repl_id"]          = "ref class|CFunctions back|plages_op_repl";
        $props["secondary_function_id"] = "ref class|CFunctions back|plage_op fieldset|extra";
        $props["original_owner_id"]     = "ref class|CMediusers back|plages_op_owner";
        $props["original_function_id"]  = "ref class|CFunctions back|plages_op_owner";
        $props["date"]                  = "date notNull fieldset|default";
        $props["debut"]                 = "time notNull fieldset|default";
        $props["fin"]                   = "time notNull moreThan|debut fieldset|default";
        $props["debut_reference"]       = "time notNull";
        $props["fin_reference"]         = "time notNull moreThan|debut_reference";
        $props["unique_chir"]           = "bool default|1";
        $props["temps_inter_op"]        = "time fieldset|default";
        $props["max_intervention"]      = "num min|0 fieldset|default";
        $props["max_ambu"]              = "num min|0 fieldset|default";
        $props["max_hospi"]             = "num min|0 fieldset|default";
        $props["verrouillage"]          = "enum list|defaut|non|oui default|defaut fieldset|default";
        $props["delay_repl"]            = "num min|0";
        $props["actes_locked"]          = "bool";
        $props["entree_chir"]           = "time show|0";
        $props["entree_anesth"]         = "time show|0";
        $props["prevenance_date"]       = "dateTime";
        $props['urgence']               = 'bool default|0';
        $props['status']                = 'enum list|occupied|free|deleted default|occupied fieldset|default';
        $props['pause']                 = 'time fieldset|default';

        $props["_type_repeat"]          = "enum list|simple|double|triple|quadruple|sameweek";
        $props['_count_operations']     = "num fieldset|default";
        $props['_count_all_operations'] = "num fieldset|default";
        $props["_time_ops"]             = "time fieldset|default";
        $props["_fill_rate"]            = "num fieldset|default";
        $props['_verrouillee']          = "str fieldset|default";

        return $props;
    }

    /**
     * Chargement des back références
     *
     * @param bool|int $annulee Prise en compte des interventions annulées
     *
     * @return void
     * @deprecated
     *
     */
    function loadRefs($annulee = true)
    {
        $this->loadRefsFwd();
        $this->loadRefsBack($annulee);
    }

    /**
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefChir();
        $this->loadRefAnesth();
        $this->loadRefSpec();
        $this->loadRefSalle();
        $this->loadRefOwner();
        $this->loadRefOriginalOwner();
        $this->multicountOperations();
    }

    /**
     * Chargement du propriétaire
     *
     * @return CMbObject
     */
    function loadRefOwner()
    {
        return $this->_ref_owner = $this->chir_id ? $this->loadRefChir() : $this->loadRefSpec();
    }

    /**
     * Chargement du premier propriétaire
     *
     * @return CMbObject
     */
    function loadRefOriginalOwner()
    {
        return $this->_ref_original_owner = $this->original_owner_id ? $this->loadRefOriginalChir(
        ) : $this->loadRefOriginalSpec();
    }

    /**
     * Chargement du premier praticien propriétaire
     *
     * @return CMediusers
     */
    function loadRefOriginalChir()
    {
        return $this->_ref_original_chir = $this->loadFwdRef("original_owner_id", true);
    }

    /**
     * Chargement de la première spécialité propriétaire
     *
     * @return CFunctions
     */
    function loadRefOriginalSpec()
    {
        return $this->_ref_original_spec = $this->loadFwdRef("original_function_id", true);
    }

    /**
     * Chargement du praticien correspondant
     *
     * @param bool $cache Utilisation du cache
     *
     * @return CMediusers
     */
    function loadRefChir($cache = true)
    {
        return $this->_ref_chir = $this->loadFwdRef("chir_id", $cache);
    }

    /**
     * Chargement de l'anesthésisite correspondant
     *
     * @param bool $cache Utilisation du cache
     *
     * @return CMediusers
     */
    function loadRefAnesth($cache = true)
    {
        return $this->_ref_anesth = $this->loadFwdRef("anesth_id", $cache);
    }

    /**
     * Chargement de la spécialité correspondante
     *
     * @param bool $cache Utilisation du cache
     *
     * @return CFunctions
     */
    function loadRefSpec($cache = true)
    {
        return $this->_ref_spec = $this->loadFwdRef("spec_id", $cache);
    }

    /**
     * Chargement de la spacialité de remplacement correspondante
     *
     * @param bool $cache Utilisation du cache
     *
     * @return CFunctions
     */
    function loadRefSpecRepl($cache = true)
    {
        return $this->_ref_spec_repl = $this->loadFwdRef("spec_repl_id", $cache);
    }

    /**
     * Chargement de la salle correspondante
     *
     * @param bool $cache Utilisation du cache
     *
     * @return CSalle
     */
    function loadRefSalle($cache = true)
    {
        return $this->_ref_salle = $this->loadFwdRef("salle_id", $cache);
    }

    /**
     * Chargement de la fonction associée
     *
     * @param bool $cache Utilisation du cache
     *
     * @return CFunctions
     */
    function loadRefSecondaryFunction($cache = true)
    {
        return $this->_ref_secondary_function = $this->loadFwdRef("secondary_function_id", $cache);
    }

    /**
     * Création de la vue de la plage
     *
     * @return string la vue de la plage
     */
    function makeView()
    {
        if ($this->status != 'occupied') {
            $this->_view = CAppUI::tr("CPlageOp-title-status_$this->status");
        } else {
            if ($this->spec_id) {
                $this->_view = $this->_ref_spec->_view;
            }

            if ($this->chir_id) {
                $this->_view = $this->_ref_chir->_view;
            }

            if ($this->urgence) {
                $this->_view = CAppUI::tr('CPlageOp-view-urgence');
            }

            if ($this->anesth_id) {
                $this->_view .= " - " . $this->_ref_anesth->_shortview;
            }
        }

        return $this->_view;
    }

    /**
     * @see parent::loadRefsFwd()
     * @deprecated
     */
    function loadRefsFwd($cache = true)
    {
        $this->loadRefChir($cache);
        $this->loadRefAnesth($cache);
        $this->loadRefSpec($cache);
        $this->loadRefSalle($cache);
        $this->makeView();
    }

    /**
     * Chargement des interventions
     *
     * @param bool   $annulee   Prise en compte des interventions annulées
     * @param string $order     Paramètre ORDER SQL
     * @param bool   $sorted    Utilisation du paramètre ORDER SQL passé en paramètre
     * @param null   $validated Uniquement les validées
     * @param array  $where     Tableau de paramètres WHERE SQL
     *
     * @return COperation[]
     */
    function loadRefsOperations(
        $annulee = true,
        $order = "rank, time_operation, rank_voulu, horaire_voulu",
        $sorted = false,
        $validated = null,
        $where = []
    ) {
        $where += [
            "plageop_id" => "= '$this->plageop_id'",
        ];

        if (!$annulee) {
            $where["annulee"] = "= '0'";
        }

        /** @var COperation[] $operations */
        $operations = [];

        $op = new COperation;

        if (!$sorted) {
            $operations = $op->loadList($where, $order);
        } else {
            $order = "rank, rank_voulu, horaire_voulu";

            if ($validated === null || $validated === true) {
                $where["rank"] = "> 0";
                $operations    = CMbArray::mergeKeys($operations, $op->loadList($where, $order));
            }

            if ($validated === null || $validated === false) {
                // Sans rank
                $where["rank"] = "= 0";

                $where["rank_voulu"] = "> 0";
                $operations          = CMbArray::mergeKeys($operations, $op->loadList($where, $order));

                // Sans rank voulu
                $where["rank_voulu"] = "= 0";

                $where["horaire_voulu"] = "IS NOT NULL";
                $operations             = CMbArray::mergeKeys($operations, $op->loadList($where, $order));

                $where["horaire_voulu"] = "IS NULL";
                $operations             = CMbArray::mergeKeys($operations, $op->loadList($where, $order));
            }
        }

        foreach ($operations as $_operation) {
            $_operation->_ref_plageop = $this;

            if ($_operation->temp_operation) {
                $this->_cumulative_minutes = $this->_cumulative_minutes + CMbDT::minutesRelative(
                        "00:00:00",
                        $_operation->temp_operation
                    );
            }
        }

        return $this->_ref_operations = $operations;
    }

    /**
     * Chargement des back references
     *
     * @param bool   $annulee prise en compte des interventions annulées
     * @param string $order   ordre du chargement
     *
     * @return COperation[]
     * @deprecated use loadRefsOperations instead
     */
    function loadRefsBack($annulee = true, $order = "rank, time_operation, rank_voulu, horaire_voulu")
    {
        $this->loadRefsOperations($annulee, $order);
    }

    /**
     * Mise à jour des horaires en fonction de l'ordre des operations,
     * et mise a jour des rank, de sorte qu'ils soient consecutifs
     *
     * @param int  $action action
     * @param bool $change_new_time
     *
     * @return bool
     */
    function reorderOp($action = null, $change_new_time = true)
    {
        $this->completeField("debut", "temps_inter_op", "date", 'salle_id');

        if (!count($this->_ref_operations)) {
            $with_cancelled = CAppUI::conf("dPplanningOp COperation save_rank_annulee_validee");
            $this->loadRefsOperations($with_cancelled, "rank, rank_voulu, horaire_voulu", true);
        }

        $new_time             = $this->debut;
        $plage_multipraticien = $this->spec_id && !$this->unique_chir;

        $multi_salle_op = CAppUI::gconf("dPplanningOp COperation multi_salle_op");

        $seconde_plage = new self();

        if ($multi_salle_op && $this->chir_id) {
            $seconde_plage = self::findSecondePlageChir($this, $new_time, $change_new_time);
        }

        $prev_op = new COperation();
        $i       = 0;

        foreach ($this->_ref_operations as $op) {
            // Intervention deja validée ou si on veut valider
            if ($op->rank || ($action & self::RANK_VALIDATE)) {
                $op->rank = ++$i;

                // Creation des pauses si plage multi-praticien
                if ($plage_multipraticien && ($action & self::RANK_VALIDATE)) {
                    if ($prev_op->_id) {
                        $op->time_operation = max($new_time, $op->horaire_voulu);

                        $prev_op->pause = CMbDT::subTime($new_time, $op->time_operation);
                        $prev_op->store(false);
                    } else {
                        $op->time_operation = $new_time;
                    }

                    $prev_op = $op;
                } else {
                    if ($multi_salle_op && $seconde_plage->_id) {
                        self::findPrevOp($op, $this, $seconde_plage, $new_time, true);

                        // En multi-salles, l'intervention peut ne pas faire partie de la même plage
                        $next_op = self::findNextOp($op, $this, $seconde_plage, $new_time);
                        if ($next_op->plageop_id == $seconde_plage->_id) {
                            $next_op->time_operation = CMbDT::addTime($op->temp_operation, $op->time_operation);
                            $next_op->updateFormFields();
                            $next_op->store(false);
                        }
                    } else {
                        if ($op->entree_salle && CAppUI::gconf('dPbloc CPlageOp reorder_real_duration')) {
                            $new_time = $op->entree_salle;
                        } elseif ($op->debut_op && CAppUI::gconf('dPbloc CPlageOp reorder_real_duration')) {
                            $new_time = $op->debut_op;
                        }
                    }
                    $op->time_operation = $new_time;
                }

                // Pour faire suivre un changement de salle
                if ($this->salle_id && $this->fieldModified("salle_id")) {
                    $op->salle_id = $this->salle_id;
                }
            } elseif (!$plage_multipraticien &&
                ($action & self::RANK_REORDER) &&
                ($op->horaire_voulu || $this->_reorder_up_to_interv_id)) {
                // Plage monopraticien
                $op->rank_voulu    = ++$i;
                $op->horaire_voulu = $new_time;
            }

            if ($this->_reorder_up_to_interv_id == $op->_id) {
                $this->_reorder_up_to_interv_id = null;
            }

            $op->updateFormFields();
            $op->store(false);

            $duration = $op->temp_operation;
            if (CAppUI::gconf('dPbloc CPlageOp reorder_real_duration')) {
                if ($op->_presence_salle) {
                    $duration = $op->_presence_salle;
                } elseif ($op->_duree_interv) {
                    $duration = $op->_duree_interv;
                } elseif ($op->debut_op || $op->entree_salle) {
                    break;
                }
            }

            // Durée de l'operation
            // + durée entre les operations
            // + durée de pause
            $new_time = CMbDT::addTime($duration, $new_time);
            $new_time = CMbDT::addTime($this->temps_inter_op, $new_time);
            $new_time = CMbDT::addTime($op->pause, $new_time);
            $new_time = CMbDT::addTime($op->duree_bio_nettoyage, $new_time);
            $new_time = CMbDT::addTime($op->duree_postop, $new_time);
        }

        return true;
    }

    /**
     * Calcul de l'horaire souhaité de l'intervention
     *
     * @return bool
     */
    function guessHoraireVoulu()
    {
        if ($this->spec_id && !$this->unique_chir) {
            return false;
        }
        $this->completeField("debut", "temps_inter_op");

        $new_time = $this->debut;
        foreach ($this->_ref_operations as $op) {
            $op->_horaire_voulu = $new_time;

            // Durée de l'operation
            // + durée entre les operations
            // + durée de pause
            $new_time = CMbDT::addTime($op->temp_operation, $new_time);
            $new_time = CMbDT::addTime($this->temps_inter_op, $new_time);
            $new_time = CMbDT::addTime($op->pause, $new_time);
            $new_time = CMbDT::addTime($op->duree_bio_nettoyage, $new_time);
            $new_time = CMbDT::addTime($op->duree_postop, $new_time);
        }

        return true;
    }

    /**
     * @see parent::check()
     */
    function check()
    {
        $this->updatePlainFields();
        $this->completeField("chir_id", "spec_id", "original_owner_id", "original_function_id", 'status');

        $old = new CPlageOp();
        if ($this->_id) {
            $old->load($this->_id);
            $old->loadRefsOperations();
        }

        // Data checking
        $msg = null;
        if (!$this->_id && !$this->chir_id && !$this->spec_id && !$this->urgence) {
            $msg .= "Vous devez choisir un praticien ou une spécialité<br />";
        }

        // Si on change de praticien alors qu'il y a déjà des interventions
        if ($this->fieldAltered("chir_id") && count($old->_ref_operations)) {
            // Si certaines ne sont pas annulées, on sort
            if ($this->countOperationsAnnulees() != count($old->_ref_operations)) {
                return CAppUI::tr("CPlageOp-failed-change_chir", count($old->_ref_operations));
            }
        }

        if ($this->fieldAltered('status') && count($old->_ref_operations) && $this->countOperationsAnnulees() != count(
                $old->_ref_operations
            )) {
            return CAppUI::tr('CPlageOp-failed-change_status', count($old->_ref_operations));
        }

        // Pas de changement de date si on a déjà des interventions
        if ($this->fieldModified("date") && count($old->_ref_operations)) {
            return CAppUI::tr("CPlageOp-failed-change_date", count($old->_ref_operations));
        }

        if (null !== $this->chir_id && $this->_id && !$this->unique_chir) {
            if (count($old->_ref_operations) && $old->spec_id && $this->chir_id) {
                return CAppUI::tr("CPlageOp-failed-multi_chir", count($old->_ref_operations));
            }
        }

        // Erreur si on créé-modifie une plage sur une salle bloquée
        $salle = $this->loadRefSalle();
        if ($this->checkBlocageSalle()) {
            return CAppUI::tr("CPlageOp-failed-use_locked_room", $salle->_view);
        }

        return $msg . parent::check();
    }

    /**
     * Check if there the room is bocked
     *
     * @return bool
     */
    public function checkBlocageSalle()
    {
        $count = 0;
        if ($this->salle_id && $this->date && $this->debut && $this->fin) {
            $salle   = $this->loadRefSalle();
            $blocage = new CBlocage();

            $debut = $this->date . ' ' . $this->debut;
            $fin   = $this->date . ' ' . $this->fin;

            $where = [
                'salle_id' => " = {$this->salle_id}",
                "'$debut' BETWEEN deb AND fin OR '$fin' BETWEEN deb AND fin",
            ];

            $count = $blocage->countList($where);
        }

        return $count != 0;
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->updatePlainFields();
        $this->completeField("chir_id", "spec_id", "original_owner_id", "original_function_id", "urgence");

        $old = new CPlageOp();
        if ($this->_id) {
            $old->load($this->_id);
            $old->loadRefsOperations();
        }

        if ($this->chir_id && $this->spec_id) {
            return "Une plage doit appartenir à un praticien ou une spécialité";
        }

        // Erreur si on est en multi-praticiens, qu'il y a des interventions et qu'on veut mettre un praticien
        if ($this->fieldValued("chir_id") && !$this->unique_chir && $old->spec_id && count($old->_ref_operations)) {
            CApp::log("all the same to me");
        }

        // Si on change de praticien alors qu'il y a déjà des interventions
        if ($this->fieldAltered("chir_id") && count($old->_ref_operations)) {
            // Si certaines ne sont pas annulées, on sort
            if ($this->countOperationsAnnulees() != count($old->_ref_operations)) {
                return CAppUI::tr("CPlageOp-failed-change_chir", count($old->_ref_operations));
            }

            // Si toutes les interventions sont annulées, on les met hors plage
            $this->completeField("salle_id", "date");
            foreach ($old->_ref_operations as $_op) {
                $_op->plageop_id = "";
                $_op->date       = $this->date;
                $_op->salle_id   = $this->salle_id;
                $_op->store();
            }
        }

        // Modification du salle_id de la plage -> repercussion sur les interventions
        if ($this->fieldModified("salle_id")) {
            foreach ($old->_ref_operations as $_operation) {
                if ($_operation->salle_id == $old->salle_id) {
                    $_operation->salle_id = $this->salle_id;
                    $_operation->store(false);
                }
            }
        }

        // Modification du début de la plage ou des minutes entre les interventions
        $this->completeField("debut", "temps_inter_op");
        if ($this->fieldModified("debut") || $this->fieldModified("temps_inter_op")) {
            if ($this->fieldModified("temps_inter_op")) {
                $with_cancelled = CAppUI::conf("dPplanningOp COperation save_rank_annulee_validee");
                $this->loadRefsOperations($with_cancelled, "rank, rank_voulu, horaire_voulu", true);
            }
            $this->reorderOp(null, false);
        }

        if (!$this->_id || (!$this->original_owner_id && !$this->original_function_id)) {
            $this->original_owner_id    = $this->chir_id;
            $this->original_function_id = $this->spec_id;
        }

        return parent::store();
    }

    /**
     * @see parent::delete()
     */
    function delete()
    {
        $this->completeField("salle_id", "date");
        $this->loadRefsOperations();

        foreach ($this->_ref_operations as $_op) {
            if ($_op->annulee) {
                $_op->plageop_id = "";
                $_op->date       = $this->date;
                $_op->salle_id   = $this->salle_id;
                $_op->store();
            }
        }

        return parent::delete();
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_duree_prevue = CMbDT::timeRelative($this->debut, $this->fin);
        $this->_view         = "Plage du " . $this->getFormattedValue("date");

        if (in_array($this->status, ["free", "deleted"])) {
            $this->_color_status = CAppUI::gconf("dPbloc CPlageOp color_$this->status");
        }
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();

        $this->completeField("debut_reference", "fin_reference");
        if (!$this->_id && !$this->debut_reference && !$this->fin_reference) {
            $this->completeField("debut", "fin");
            $this->debut_reference = $this->debut;
            $this->fin_reference   = $this->fin;
        }
    }

    /**
     * find the next plageop according
     * to the current plageop parameters
     * return the number of weeks jumped
     *
     * @param int $init_salle_id              Salle initiale
     * @param int $init_chir_id               Chirurgien intial
     * @param int $init_spec_id               Spécialité intiale
     * @param int $init_secondary_function_id Spécialité secondaire initiale
     *
     * @return int
     */
    function becomeNext(
        $init_salle_id = null,
        $init_chir_id = null,
        $init_spec_id = null,
        $init_secondary_function_id = null
    ) {
        $week_jumped = 0;
        if (!$this->_type_repeat) {
            $this->_type_repeat = "simple";
        }

        switch ($this->_type_repeat) {
            case "quadruple":
                $this->date  = CMbDT::date("+4 WEEK", $this->date);
                $week_jumped += 4;
                break;
            case "triple":
                $this->date  = CMbDT::date("+3 WEEK", $this->date);
                $week_jumped += 3;
                break;
            case "double":
                $this->date  = CMbDT::date("+2 WEEK", $this->date);
                $week_jumped += 2;
                break;
            case "simple":
                $this->date = CMbDT::date("+1 WEEK", $this->date);
                $week_jumped++;
                break;
            case "sameweek":
                $week_number = CMbDT::weekNumberInMonth($this->date);
                $next_month  = CMbDT::monthNumber(CMbDT::date("+1 MONTH", $this->date));
                $i           = 0;
                do {
                    $this->date = CMbDT::date("+1 WEEK", $this->date);
                    $week_jumped++;
                    $i++;
                } while (
                    $i < 10 &&
                    (CMbDT::monthNumber($this->date) < $next_month) ||
                    (CMbDT::weekNumberInMonth($this->date) != $week_number)
                );
                break;
        }

        // Stockage des champs modifiés
        $salle_id              = $this->salle_id;
        $chir_id               = $this->chir_id;
        $spec_id               = $this->spec_id;
        $secondary_function_id = $this->secondary_function_id === null ? "" : $this->secondary_function_id;
        $debut                 = $this->debut;
        $debut_reference       = $this->debut_reference;
        $fin                   = $this->fin;
        $fin_reference         = $this->fin_reference;
        $temps_inter_op        = $this->temps_inter_op;
        $max_intervention      = $this->max_intervention;
        $max_ambu              = $this->max_ambu;
        $max_hospi             = $this->max_hospi;
        $anesth_id             = $this->anesth_id;
        $delay_repl            = $this->delay_repl;
        $spec_repl_id          = $this->spec_repl_id;
        $type_repeat           = $this->_type_repeat;
        $unique_chir           = $this->unique_chir;
        $status                = $this->status;
        $urgence               = $this->urgence;

        // Recherche de la plage suivante
        $where             = [];
        $where["date"]     = "= '$this->date'";
        $where[]           = "`debut` = '$this->debut' OR `fin` = '$this->fin'";
        $where["salle_id"] = $init_salle_id ? "= '$init_salle_id'" : "= '$this->salle_id'";
        if ($chir_id || $init_chir_id) {
            $where["chir_id"] = $init_chir_id ? "= '$init_chir_id'" : "= '$chir_id'";
        } elseif ($spec_id || $init_chir_id) {
            $where["spec_id"] = $init_spec_id ? "= '$init_spec_id'" : "= '$spec_id'";
        } else {
            // Vacations d'urgence sans propriétaire
            $where["chir_id"] = "IS NULL";
            $where["spec_id"] = "IS NULL";
        }
        if ($secondary_function_id || $init_secondary_function_id) {
            $where["secondary_function_id"] = $init_secondary_function_id ? "= '$init_secondary_function_id'" : "= '$secondary_function_id'";
        }

        $plages = $this->loadList($where);
        if (count($plages) > 0) {
            $this->load(reset($plages)->plageop_id);
        } else {
            $this->plageop_id = null;
        }
        if (!$this->chir_id) {
            $this->chir_id = "";
        }
        if (!$this->spec_id) {
            $this->spec_id = "";
        }

        // Remise en place des champs modifiés
        $this->salle_id              = $salle_id;
        $this->chir_id               = $chir_id;
        $this->secondary_function_id = $secondary_function_id;
        $this->spec_id               = $spec_id;
        $this->debut                 = $debut;
        $this->debut_reference       = $debut_reference;
        $this->fin                   = $fin;
        $this->fin_reference         = $fin_reference;
        $this->temps_inter_op        = $temps_inter_op;
        $this->max_intervention      = $max_intervention;
        $this->max_ambu              = $max_ambu;
        $this->max_hospi             = $max_hospi;
        $this->anesth_id             = $anesth_id;
        $this->delay_repl            = $delay_repl;
        $this->spec_repl_id          = $spec_repl_id;
        $this->_type_repeat          = $type_repeat;
        $this->unique_chir           = $unique_chir;
        $this->status                = $status;
        $this->urgence               = $urgence;
        $this->updateFormFields();

        return $week_jumped;
    }

    /**
     * Récupération du taux d'occupation de la plage et du nombre d'interventions
     *
     * @param string $addedTime Durée ajouté manuellement
     *
     * @return int
     */
    function multicountOperations($addedTime = null)
    {
        $select_time = "\nSUM(TIME_TO_SEC(`operations`.`temp_operation`) + TIME_TO_SEC(`operations`.`pause`) + TIME_TO_SEC(`plagesop`.`temps_inter_op`)) AS time";

        $sql                     = "SELECT COUNT(`operations`.`operation_id`) AS total, $select_time
        FROM `operations`, `plagesop`
        WHERE `operations`.`plageop_id` = '$this->plageop_id'
        AND `operations`.`plageop_id` = `plagesop`.`plageop_id`
        AND `operations`.`annulee` = '0'";
        $result                  = $this->_spec->ds->loadHash($sql);
        $this->_count_operations = $result["total"];
        if ($addedTime) {
            $result["time"] = $result["time"] + $addedTime;
        }
        $this->_fill_rate = number_format($result["time"] * 100 / (strtotime($this->fin) - strtotime($this->debut)), 2);
        $this->_fill_time = $result["time"];

        $sql                             = "SELECT COUNT(`operations`.`operation_id`) AS total, $select_time
        FROM `operations`, `plagesop`
        WHERE `operations`.`plageop_id` = '$this->plageop_id'
        AND `operations`.`plageop_id` = `plagesop`.`plageop_id`
        AND `operations`.`rank` > 0
        AND `operations`.`annulee` = '0'";
        $result                          = $this->_spec->ds->loadHash($sql);
        $this->_count_operations_placees = $result["total"];

        if ($this->verrouillage == "oui") {
            $this->_verrouillee = ["force"];
        } elseif ($this->verrouillage == "non") {
            $this->_verrouillee = [];
        }
        if ($this->status == 'free') {
            $this->_verrouillee[] = 'free';
        } elseif ($this->status == 'deleted') {
            $this->_verrouillee[] = 'deleted';
        } else {
            $this->loadRefSalle();
            $this->_ref_salle->loadRefBloc();
            $date_min      = CMbDT::date("+ " . $this->_ref_salle->_ref_bloc->days_locked . " DAYS");
            $check_datemin = $this->date < $date_min;
            $check_fill    = ($this->_fill_rate > 100) && CAppUI::gconf("dPbloc CPlageOp locked");
            $check_max     = $this->max_intervention && $this->_count_operations >= $this->max_intervention;

            if ($check_datemin) {
                $this->_verrouillee[] = "datemin";
            }
            if ($check_fill) {
                $this->_verrouillee[] = "fill";
            }
            if ($check_max) {
                $this->_verrouillee[] = "max";
            }
        }

        $this->countOperationsAnnulees();

        if ($this->_fill_rate < 50) {
            $this->_fill_rate_color = CAppUI::gconf("dPbloc CPlageOp fill_rate_empty");
        } elseif ($this->_fill_rate < 90) {
            $this->_fill_rate_color = CAppUI::gconf("dPbloc CPlageOp fill_rate_normal");
        } elseif ($this->_fill_rate < 100) {
            $this->_fill_rate_color = CAppUI::gconf("dPbloc CPlageOp fill_rate_booked");
        } else {
            $this->_fill_rate_color = CAppUI::gconf("dPbloc CPlageOp fill_rate_full");
        }

        return $this->_count_all_operations = $this->_count_operations + $this->_count_operations_annulees;
    }


    /**
     * Load plages for a day or between 2 days
     * check for chir, anesth or function of given prat_id
     *
     * @param string      $prat_id  prat_id (mediuser_id)
     * @param string      $date_min date min to check
     * @param null|string $date_max date max
     *
     * @return CPlageOp[]
     */
    function loadForDays($prat_id, $date_min, $date_max = null)
    {
        $prat      = CMediusers::get($prat_id);
        $wherePrat = $prat->getUserSQLClause();

        // Identifiants des salles de l'établissement courant pour les vacations d'urgence
        $bloc     = new CBlocOperatoire();
        $where    = [
            "group_id" => "= '" . CGroups::get()->_id . "'",
        ];
        $bloc_ids = $bloc->loadIds($where);

        $salle = new CSalle();
        $where = [
            "bloc_id" => CSQLDataSource::prepareIn($bloc_ids),
        ];

        $salle_ids = $salle->loadIds($where);

        $where         = [];
        $where[]       = "chir_id $wherePrat OR anesth_id $wherePrat OR
                 spec_id = '$prat->function_id' OR
                (chir_id IS NULL AND spec_id IS NULL AND urgence = '1' AND salle_id " . CSQLDataSource::prepareIn(
                $salle_ids
            ) . ")";
        $where["date"] = ($date_max) ? "BETWEEN '$date_min' AND '$date_max'" : " = '$date_min'";

        return $this->loadList($where);
    }

    /**
     * Load plages for a day or between 2 days by function
     *
     * @param string      $function_id function_id
     * @param string      $date_min    date min to check
     * @param null|string $date_max    date max
     *
     * @return CPlageOp[]
     */
    function loadForDaysByFunction($function_id, $date_min, $date_max = null)
    {
        $ljoin = [];
        // Identifiants des salles de l'établissement courant pour les vacations d'urgence
        $bloc     = new CBlocOperatoire();
        $where    = [
            "group_id" => "= '" . CGroups::get()->_id . "'",
        ];
        $bloc_ids = $bloc->loadIds($where);

        $salle = new CSalle();
        $where = [
            "bloc_id" => CSQLDataSource::prepareIn($bloc_ids),
        ];

        $salle_ids = $salle->loadIds($where);

        $where                                = [];
        $ljoin["users_mediboard"]             = "users_mediboard.user_id = plagesop.chir_id";
        $where["users_mediboard.function_id"] = " = '$function_id'";
        $where["date"]                        = ($date_max) ? "BETWEEN '$date_min' AND '$date_max'" : " = '$date_min'";

        return $this->loadList($where, null, null, null, $ljoin);
    }

    /**
     * Récupération le nombre d'intervention pour la plage
     *
     * @return int
     */
    function countOperations()
    {
        return $this->_count_operations = $this->countBackRefs("operations");
    }

    /**
     * Récupération le nombre d'intervention annulées pour la plage
     *
     * @return int
     */
    function countOperationsAnnulees()
    {
        if (!$this->_id) {
            return $this->_count_operations_annulees = 0;
        }

        $operation             = new COperation();
        $operation->plageop_id = $this->_id;
        $operation->annulee    = '1';

        return $this->_count_operations_annulees = $operation->countMatchingList();
    }

    /**
     * Récupération le nombre d'intervention de type ambu et de type hospi
     *
     * @return int
     */
    function countOperationsAmbuHospi()
    {
        if (!$this->_id) {
            $this->_count_operations_ambu = 0;

            return $this->_count_operations_hospi = 0;
        }

        $ljoin                          = [];
        $ljoin["sejour"]                = "sejour.sejour_id = operations.sejour_id";
        $where                          = [];
        $where["operations.plageop_id"] = " = '$this->_id'";
        $where["operations.annulee"]    = " = '0'";
        $where["sejour.type"]           = " = 'ambu'";

        $operation                    = new COperation();
        $this->_count_operations_ambu = $operation->countList($where, null, $ljoin);
        $where["sejour.type"]         = " = 'comp'";

        return $this->_count_operations_hospi = $operation->countList($where, null, $ljoin);
    }

    /**
     * Count the number of CPlageOp duplicated from the current CPlageOp
     *
     * @return int
     */
    function countDuplicatedPlages()
    {
        $where = [
            'temps_inter_op' => " = '$this->temps_inter_op'",
            'debut'          => " = '$this->debut'",
            'fin'            => " = '$this->fin'",
            'date'           => " > '$this->date'",
            "WEEKDAY(`date`) = WEEKDAY('$this->date')",
            'salle_id'       => " = $this->salle_id",
        ];

        if ($this->chir_id) {
            $where['chir_id'] = " = $this->chir_id";
        }

        if ($this->anesth_id) {
            $where['anesth_id'] = " = $this->anesth_id";
        }

        if ($this->spec_id) {
            $where['spec_id'] = " = $this->spec_id";
        }

        if ($this->original_owner_id) {
            $where['original_owner_id'] = " = $this->original_owner_id";
        }

        if ($this->original_function_id) {
            $where['original_function_id'] = " = $this->original_function_id";
        }

        if ($this->urgence) {
            $where['urgence'] = " = '$this->urgence'";
        }

        return $this->_count_duplicated_plages = $this->countList($where);
    }

    /**
     * @see parent::getPerm()
     */
    function getPerm($permType)
    {
        if (!$this->_id) {
            return parent::getPerm($permType);
        }

        if (!$this->_ref_salle) {
            $this->loadRefSalle();
        }
        if ($this->chir_id && !$this->_ref_chir) {
            $this->loadRefChir();
        }
        if ($this->spec_id && !$this->_ref_spec) {
            $this->loadRefSpec();
        }

        $pratPerm = false;

        // Test de Permission
        if ($this->chir_id) {
            $pratPerm = $this->_ref_chir->getPerm($permType);
        } elseif ($this->spec_id) {
            $pratPerm = $this->_ref_spec->getPerm($permType);
        }

        return ($this->_ref_salle->getPerm($permType) && $pratPerm);
    }

    function loadPersonnelDisponible($listPers = [], $remove_pers = false)
    {
        if (!is_array($listPers) || !count($listPers)) {
            $listPers = [
                "iade"             => CPersonnel::loadListPers("iade"),
                "op"               => CPersonnel::loadListPers("op"),
                "op_panseuse"      => CPersonnel::loadListPers("op_panseuse"),
                "sagefemme"        => CPersonnel::loadListPers("sagefemme"),
                "manipulateur"     => CPersonnel::loadListPers("manipulateur"),
                "aux_puericulture" => CPersonnel::loadListPers("aux_puericulture"),
                "instrumentiste"   => CPersonnel::loadListPers("instrumentiste"),
                "aide_soignant"    => CPersonnel::loadListPers("aide_soignant"),
                "circulante"       => CPersonnel::loadListPers("circulante"),
                "brancardier"      => CPersonnel::loadListPers("brancardier"),
            ];
        }
        if ($remove_pers) {
            if (!$this->_ref_affectations_personnel) {
                $this->loadAffectationsPersonnel();
            }

            $affectations_personnel = $this->_ref_affectations_personnel;
            $personnel_ids          = [];
            foreach ($affectations_personnel as $_aff_by_type) {
                foreach ($_aff_by_type as $_aff) {
                    if (!$_aff->debut && !$_aff->fin) {
                        $personnel_ids[] = $_aff->personnel_id;
                    }
                }
            }

            // Suppression de la liste des personnels deja presents
            foreach ($listPers as $key => $persByType) {
                foreach ($persByType as $_key => $pers) {
                    if (in_array($pers->_id, $personnel_ids)) {
                        unset($listPers[$key][$_key]);
                    }
                }
            }
        }

        return $listPers;
    }

    static function findSecondePlageChir($plage, &$new_time = null, bool $change_new_time = true)
    {
        $plage->loadRefSalle();
        $seconde_plage = new self();
        $where         = [
            "chir_id"    => "= '$plage->chir_id'",
            "date"       => "= '$plage->date'",
            "debut"      => "< '$plage->fin'",
            "fin"        => "> '$plage->debut'",
            "plageop_id" => "!= '$plage->_id'",
            "bloc_id"    => "= '" . $plage->_ref_salle->bloc_id . "'",
            'status'     => "!= 'deleted'",
        ];

        $ljoin = [
            "sallesbloc" => "plagesop.salle_id = sallesbloc.salle_id",
        ];

        if ($seconde_plage->loadObject($where, null, null, $ljoin)) {
            $seconde_plage->loadRefsOperations(false, "rank, rank_voulu, horaire_voulu", true);
            if ($change_new_time && count($plage->_ref_operations)) {
                $first_op = reset($plage->_ref_operations);

                if ($first_op->time_operation >= $new_time) {
                    $new_time = $first_op->time_operation;
                }
            }
        }

        return $seconde_plage;
    }

    static function findPrevOp(&$op, $plage, $seconde_plage, &$new_time, $add_time = true)
    {
        $prev_op_seconde_plage = new COperation();
        $where                 = [
            "plageop_id"   => "IN ('$plage->_id', '$seconde_plage->_id')",
            "rank"         => "> 0",
            "operation_id" => "!= '$op->_id'",
        ];

        if (!CAppUI::conf("dPplanningOp COperation save_rank_annulee_validee")) {
            $where["annulee"] = "= '0'";
        }

        $where[] = ($new_time ? "time_operation <= '$new_time' AND " : "") . "time_operation >= '$plage->debut'";

        if ($prev_op_seconde_plage->loadObject($where, "time_operation DESC")) {
            if ($add_time) {
                $op->time_operation = CMbDT::addTime(
                    $prev_op_seconde_plage->temp_operation,
                    $prev_op_seconde_plage->time_operation
                );
                $op->time_operation = CMbDT::addTime($plage->temps_inter_op, $op->time_operation);
                $new_time           = $op->time_operation;

                // Si la précédente opération est de la seconde plage
                // Il peut y avoir n opérations précédentes dans cette seconde plage
                // => on ajoute donc les différentes durées trouvées
                if ($prev_op_seconde_plage->plageop_id !== $op->plageop_id) {
                    $where = [
                        "plageop_id"     => "= '$prev_op_seconde_plage->plageop_id'",
                        "rank"           => "> 0",
                        "operation_id"   => "!= '$prev_op_seconde_plage->_id'",
                        "time_operation" => "> '$prev_op_seconde_plage->time_operation' AND time_operation <= '$op->time_operation'",
                    ];

                    foreach ($prev_op_seconde_plage->loadList($where, "time_operation ASC") as $_op) {
                        $op->time_operation = CMbDT::addTime($_op->temp_operation, $op->time_operation);
                    }

                    if (isset($_op)) {
                        $prev_op_seconde_plage = $_op;
                    }

                    $new_time = $op->time_operation;
                }
            }
        }

        return $prev_op_seconde_plage;
    }

    static function findNextOp($op, $plage, $seconde_plage, $new_time)
    {
        $next_op_second_plage = new COperation();
        $where                = [
            "plageop_id"     => "IN ('$plage->_id', '$seconde_plage->_id')",
            "rank"           => "> 0",
            "time_operation" => "> '$new_time'",
            "operation_id"   => "!= '$op->_id'",
        ];

        if (!CAppUI::conf("dPplanningOp COperation save_rank_annulee_validee")) {
            $where["annulee"] = "= '0'";
        }

        $next_op_second_plage->loadObject($where, "time_operation ASC");

        return $next_op_second_plage;
    }

    /**
     * @return void
     */
    static function initHoursMinutes()
    {
        if (count(self::$hours) && count(self::$minutes)) {
            return;
        }

        self::$hours   = [];
        self::$minutes = [];

        $start = CAppUI::gconf("dPbloc CPlageOp hours_start");
        $stop  = CAppUI::gconf("dPbloc CPlageOp hours_stop");

        self::$hours_start      = str_pad(CValue::first($start, "08"), 2, "0", STR_PAD_LEFT);
        self::$hours_stop       = str_pad(CValue::first($stop, "20"), 2, "0", STR_PAD_LEFT);
        self::$minutes_interval = CValue::first(CAppUI::gconf("dPbloc CPlageOp minutes_interval"), "15");

        $listHours = range($start, $stop);
        $listMins  = range(0, 59, self::$minutes_interval);

        foreach ($listHours as $hour) {
            self::$hours[$hour] = str_pad($hour, 2, "0", STR_PAD_LEFT);
        }

        foreach ($listMins as $min) {
            self::$minutes[] = str_pad($min, 2, "0", STR_PAD_LEFT);
        }
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        if (!$date_min && !$date_max) {
            return true;
        }

        if ($date_min && $date_max) {
            if ($this->date <= $date_max && $this->date >= $date_min) {
                return true;
            }
        } elseif ($date_min) {
            if ($this->date >= $date_min) {
                return true;
            }
        } elseif ($date_max) {
            if ($this->date <= $date_max) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Item|null
     * @throws \Ox\Core\Api\Exceptions\ApiException
     */
    public function getResourceSalle(): ?Item
    {
        if (!$salle = $this->loadRefSalle()) {
            return null;
        }

        return new Item($salle);
    }

    /**
     * @return Item|null
     * @throws \Ox\Core\Api\Exceptions\ApiException
     */
    public function getResourceBloc(): ?Item
    {
        if (!$salle = $this->loadRefSalle()) {
            return null;
        }

        if (!$bloc = $salle->loadRefBloc()) {
            return null;
        }

        return new Item($bloc);
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public function loadPlagesPerDayOp(array $list_salles, int $prat_id, string $date): array
    {
        $plagesOp = [];

        $where = [
            'date'     => $this->getDS()->prepare('= ?', $date),
            'salle_id' => CSQLDataSource::prepareIn(array_keys($list_salles)),
        ];

        $where[] = "chir_id "
            . $this->getDS()->prepare($prat_id)
            . " OR anesth_id "
            . $this->getDS()->prepare($prat_id);

        /** @var self[] $plagesPerDayOp */
        $plagesPerDayOp = $this->loadList($where);

        $salles = CStoredObject::massLoadFwdRef($plagesPerDayOp, "salle_id");
        CStoredObject::massLoadFwdRef($salles, "bloc_id");

        CStoredObject::massLoadBackRefs(
            $plagesPerDayOp,
            "operations",
            "rank, time_operation, rank_voulu, horaire_voulu"
        );

        foreach ($plagesPerDayOp as $key => $plage) {
            $plage->loadRefSalle();
            $plage->_ref_salle->loadRefBloc();
            $plage->_ref_salle->_ref_bloc->loadRefGroup();

            $plage->loadRefsOperations(false);

            $sejours = CStoredObject::massLoadFwdRef($plage->_ref_operations, "sejour_id");
            CStoredObject::massLoadFwdRef($sejours, "patient_id");

            $plage->multicountOperations();
            $plagesOp[$plage->salle_id][$date][] = $plage;
        }

        return $plagesOp;
    }
}
