<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Transformations;

/**
 * Class CTransformation
 * EAI transformation
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;
use Ox\Interop\Hprimxml\CHPrimXMLEvenements;

class CTransformation extends CMbObject
{
    // DB Table key
    public $eai_transformation_id;

    // DB fields
    public $actor_id;
    public $actor_class;

    public $standard;
    public $domain;
    public $profil;
    public $transaction;
    public $message;
    public $version;
    public $extension;

    public $active;
    public $rank;

    public $eai_transformation_rule_id;

    /** @var CTransformationRule */
    public $_ref_eai_transformation_rule;

    /** @var CInteropActor */
    public $_ref_actor;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table = 'eai_transformation';
        $spec->key   = 'eai_transformation_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        /*$props["actor_id"]    = "ref notNull class|CInteropActor meta|actor_class back|actor_transformations";
        $props["actor_class"] = "str notNull class maxLength|80";*/

        $props["standard"]    = "str";
        $props["domain"]      = "str";
        $props["profil"]      = "str";
        $props["transaction"] = "str";
        $props["message"]     = "str";
        $props["version"]     = "str";
        $props["extension"]   = "str";

        $props["active"] = "bool default|0";
        $props["rank"]   = "num min|1 show|0";

        $props["eai_transformation_rule_id"] = "ref class|CTransformationRule autocomplete|text back|tranformation_rule";

        return $props;
    }

    /**
     * Load rule
     *
     * @return CTransformationRule|CStoredObject
     */
    function loadRefEAITransformationRule()
    {
        return $this->_ref_eai_transformation_rule = $this->loadFwdRef("eai_transformation_rule_id", true);
    }

    /**
     * Load group
     *
     * @return CInteropActor|CStoredObject
     */
    function loadRefActor()
    {
        return $this->_ref_actor = $this->loadFwdRef("actor_id", true);
    }

    /**
     * @see parent::store
     */
    function store()
    {
        if (!$this->_id) {
            $transformation              = new CTransformation();
            $transformation->actor_id    = $this->actor_id;
            $transformation->actor_class = $this->actor_class;

            $this->rank = $transformation->countMatchingList() + 1;
        }

        return parent::store();
    }

    /**
     * Bind event
     *
     * @param CInteropNorm                                   $message Standard
     * @param CHL7Event|CHPrimXMLEvenements|CHPrimSanteEvent $event   Event
     * @param CInteropACtor                                  $actor   Actor
     *
     * @return bool|void
     */
    function bindObject(CInteropNorm $message, $event, CInteropActor $actor)
    {
        $where = [];

        $where["actor_id"]    = " = '$actor->_id'";
        $where["actor_class"] = " = '$actor->_class";

        if ($event instanceof CHL7Event) {
            $where = [
                "profil = '$event->profil'",
            ];
        }

        return $where;
    }

    /**
     * Bind transformation rule
     *
     * @param CTransformationRule $transformation_rule Transformation rule
     * @param CInteropACtor       $actor               Actor
     *
     * @return bool|void
     */
    function bindTransformationRule(CTransformationRule $transformation_rule, CInteropActor $actor)
    {
        $this->eai_transformation_rule_id = $transformation_rule->_id;

        $this->actor_id    = $actor->_id;
        $this->actor_class = $actor->_class;

        $this->profil      = $transformation_rule->profil;
        $this->message     = $transformation_rule->message;
        $this->transaction = $transformation_rule->transaction;
        $this->version     = $transformation_rule->version;
        $this->extension   = $transformation_rule->extension;

        $this->active = $transformation_rule->active;
    }

    /**
     * @param string $content
     */
    public static function wellFormedContent(string $content, string $input = 'input') {
        try {
            if (!CHL7v2Message::isWellFormed($content)) {
                CAppUI::stepAjax("CTransformtion-msg-Content {$input} not valid", UI_MSG_ERROR);
            }
        } catch (CHL7v2Exception $e) {
            CApp::log('Content transformation échouée', $content);
            CAppUI::stepAjax("CTransformtion-msg-Content {$input} not valid", UI_MSG_ERROR, $e->getMessage());
        }
    }
}
