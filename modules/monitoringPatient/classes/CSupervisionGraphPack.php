<?php
/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

/**
 * A supervision graph pack
 */
class CSupervisionGraphPack extends CMbObject
{
    public $supervision_graph_pack_id;

    public $owner_class;
    public $owner_id;

    public $title;
    public $disabled;
    public $timing_fields;
    public $use_contexts;
    public $planif_display_mode;
    public $anesthesia_type;
    public $main_pack;

    public $_protocol_md_stream = false;

    /** @var CSupervisionGraphToPack[] */
    public $_ref_graph_links;

    /** @var CGroups|CFunctions */
    public $_ref_owner;

    /** @var CTypeAnesth */
    public $_ref_anesthesia_type;

    /** @var array */
    public $_timing_fields;

    /** @var array */
    public $_timing_values;

    static $_operation_timing_fields = [
        "debut_prepa_preop",
        "fin_prepa_preop",
        "entree_salle",
        "sortie_salle",
        "remise_chir",
        "tto",
        "pose_garrot",
        "prep_cutanee",
        "debut_op",
        "fin_op",
        "retrait_garrot",
        "entree_reveil",
        "sortie_reveil_possible",
        "sortie_reveil_reel",
        "induction_debut",
        "induction_fin",
        "suture_fin",
        "entree_bloc",
        "cleaning_start",
        "cleaning_end",
        "installation_start",
        "installation_end",
        "incision",
    ];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                   = parent::getSpec();
        $spec->table            = "supervision_graph_pack";
        $spec->key              = "supervision_graph_pack_id";
        $spec->uniques["title"] = ["owner_class", "owner_id", "title"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                        = parent::getProps();
        $props["owner_class"]         = "enum notNull list|CGroups";
        $props["owner_id"]            = "ref notNull meta|owner_class class|CMbObject back|supervision_graph_packs";
        $props["title"]               = "str notNull";
        $props["disabled"]            = "bool notNull default|1";
        $props["timing_fields"]       = "text";
        $props["use_contexts"]        = "set list|preop|perop|sspi|parto|post_partum";
        $props["planif_display_mode"] = "enum list|token|in_place default|token";
        $props['anesthesia_type']     = 'ref class|CTypeAnesth back|grah_pack_anesthesia_types';
        $props['main_pack']           = 'bool default|0';

        return $props;
    }

    /**
     * Load graph links
     *
     * @return CSupervisionGraphToPack[]
     * @throws Exception
     */
    function loadRefsGraphLinks()
    {
        return $this->_ref_graph_links = $this->loadBackRefs("graph_links", "rank, supervision_graph_to_pack_id");
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->title;
    }

    /**
     * Load owner
     *
     * @return CGroups|CFunctions
     * @throws Exception
     */
    function loadRefOwner()
    {
        return $this->_ref_owner = $this->loadFwdRef("owner_id");
    }

    /**
     * Load the anesthesia type
     *
     * @return CTypeAnesth
     * @throws Exception
     */
    public function loadRefAnesthesiaType()
    {
        return $this->_ref_anesthesia_type = $this->loadFwdRef('anesthesia_type');
    }

    /**
     * Get all pakcs from an object
     *
     * @param CMbObject $object        The object to get the packs of
     * @param bool      $with_disabled List disabled items
     * @param string    $context       Use context
     *
     * @return CSupervisionGraphPack[]
     * @throws Exception
     */
    static function getAllFor(CMbObject $object, $with_disabled = false, $context = null)
    {
        $pack = new self;

        $where = [
            "owner_class" => "= '$object->_class'",
            "owner_id"    => "= '$object->_id'",
        ];

        if (!$with_disabled) {
            $where["disabled"] = "= '0'";
        }

        if ($context) {
            $where[] = "use_contexts IS NULL OR use_contexts " . $pack->getDS()->prepareLike("%$context%");
        }

        return $pack->loadList($where, "title");
    }

    /**
     * @return array
     */
    function getTimingFields()
    {
        if (!$this->timing_fields) {
            return $this->_timing_fields = [];
        }

        return $this->_timing_fields = json_decode($this->timing_fields, true);
    }

    /**
     * Tells if it can be used in the specified context
     *
     * @param string $context Context
     *
     * @return bool
     */
    function canUseInContext($context)
    {
        if (!$this->use_contexts) {
            return true;
        }

        $contexts = explode("|", $this->use_contexts);

        return in_array($context, $contexts);
    }

    /**
     * Get timing values
     *
     * @param COperation $interv Intervention to get timings of
     *
     * @return array
     */
    function getTimingValues(COperation $interv)
    {
        $fields = $this->getTimingFields();

        $timings = [];

        foreach ($fields as $_field => $_color) {
            $timings[] = [
                "field" => $_field,
                "label" => CAppUI::tr("$interv->_class-$_field"),
                "value" => $interv->{$_field},
                "color" => $_color,
            ];
        }

        return $this->_timing_values = $timings;
    }

    /**
     * Check if the graph is on the MD Stream protocol
     *
     * @return bool
     */
    public function isProtocolMDStream(): bool
    {
        $is_csharp_version = false;

        $graph_links = $this->loadRefsGraphLinks();
        CStoredObject::massLoadFwdRef($graph_links, "graph_id");

        foreach ($graph_links as $key_gl => $_gl) {
            $_go = $_gl->loadRefGraph();

            if ($_go->disabled) {
                continue;
            }

            if ($_go instanceof CSupervisionGraph && ($_go->automatic_protocol === CSupervisionGraph::PROTOCOL_MD_STREAM)) {
                $is_csharp_version = true;
                break;
            }
        }

        return $this->_protocol_md_stream = $is_csharp_version;
    }
}
