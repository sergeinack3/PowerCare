<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Transformations;

use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Ihe\CIHE;
use ReflectionClass;

/**
 * Description
 */
class CTransformationRuleSequence extends CMbObject
{
    /**
     * @var array STANDARDS_ALLOWED
     */
    public const STANDARDS_ALLOWED = [
        CIHE::class,
        CHL7::class,
    ];

    /** @var integer Primary key */
    public $transformation_rule_sequence_id;

    // DB fields
    /** @var string */
    public $name;
    /** @var string */
    public $description;
    /** @var string */
    public $standard;
    /** @var string */
    public $domain;
    /** @var string */
    public $profil;
    /** @var string */
    public $message_type;
    /** @var string */
    public $message_example;
    /** @var string */
    public $transaction;
    /** @var string */
    public $version;
    /** @var string */
    public $extension;
    /** @var string */
    public $source;
    /** @var int */
    public $transformation_ruleset_id;

    // Form fields
    /** @var CTransformationRule[] */
    public $_ref_transformation_rules;
    /** @var CTransformationRuleSet */
    public $_ref_transformation_ruleset;

    /** @var CHL7v2Message|string */
    public $_message;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "transformation_rule_sequence";
        $spec->key   = "transformation_rule_sequence_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props = parent::getProps();

        $props["name"]            = "str notNull";
        $props["description"]     = "str";
        $props["standard"]        = "str";
        $props["domain"]          = "str";
        $props["profil"]          = "str";
        $props["message_type"]    = "str";
        $props["message_example"] = "text notNull";
        $props["transaction"]     = "str";
        $props["version"]         = "str";
        $props["extension"]       = "str";
        $props["source"]          = "str";

        $props["transformation_ruleset_id"] = "ref class|CTransformationRuleSet autocomplete|text back|transformation_rule_sequences";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function check()
    {
        // On vérifie si le message example est bien formé pour le standard
        //  return CHL7v2Message::isWellFormed($data, $strict);


        return parent::check();
    }

    /**
     * Load rules sequences
     *
     * @param array $where
     *
     * @return CTransformationRule[]|CStoredObject[]
     * @throws \Exception
     */
    public function loadRefsTransformationRules($where = [])
    {
        return $this->_ref_transformation_rules = $this->loadBackRefs(
            "transformation_rules",
            "rank ASC",
            null,
            null,
            null,
            null,
            'transformation_rules',
            $where
        );
    }

    /**
     * @return void
     * @throws CHL7v2Exception
     *
     */
    public function getMessage(): void
    {
        // On parse que si c'est du HL7
        if (preg_match('#MSH#', $this->message_example)) {
            $hl7_message = new CHL7v2Message();
            $hl7_message->parse($this->message_example);
            $this->_message = $hl7_message;
        } else {
            $this->_message = $this->message_example;
        }
    }

    /**
     * Check if sequence can be apply for event
     *
     * @param CHEvent $event
     *
     * @return bool
     */
    public function checkAvailability(CHL7Event $event): bool
    {
        // Message ?
        if ($this->message_type) {
            return $this->compareMessageType($event);
        }

        // Transaction ?
        if ($this->transaction && ($this->transaction == $event->transaction)) {
            return true;
        }

        // Profil ?
        if ($this->profil && ($this->profil == $event->profil)) {
            return true;
        }

        // TODO : Vérifier Domaine et Standard quand on fera les transformations pour les autres normes que HL7

        return false;
    }

    /**
     * Check if sequence can be apply for event
     *
     * @param CHEvent $event
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function compareMessageType(CHL7Event $event): bool
    {
        $reflect    = new ReflectionClass($event);
        $short_name = $reflect->getShortName();

        if ($short_name && $this->message_type) {
            return true;
        }

        return false;
    }

    /**
     * @param string        $content
     * @param CInteropActor $actor
     *
     * @return string
     * @throws CHL7v2Exception
     */
    public function applyRules(string $content, CInteropActor $actor): string {
        /** @var CTransformationRule $_rule */
        foreach ($this->loadRefsTransformationRules(['active' => " = '1' "]) as $_rule) {
            $actor->_content_altered = true;
            $content = $_rule->apply($content);
        }

        return $content;
    }
}
