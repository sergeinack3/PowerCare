<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;


use DOMNode;
use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIH;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\CGroupDomain;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CSourceIdentite;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHL7v2Segment
 */
class CHL7v2Segment extends CHL7v2Entity
{
    public $name;
    public $description;

    /** @var CHL7v2Field[] */
    public $fields = [];

    /** @var CHL7v2SegmentGroup */
    public $parent;

    /**
     * CHL7v2Segment constructor.
     *
     * @param CHL7v2SegmentGroup $parent Parent group
     */
    function __construct(CHL7v2SegmentGroup $parent)
    {
        parent::__construct();

        $this->parent = $parent;
    }

    /**
     * Build an HL7v2 segment
     *
     * @param string             $name   The name of the segment
     * @param CHL7v2SegmentGroup $parent The parent of the segment to create
     *
     * @return CHL7v2Segment The segment
     */
    static function create($name, CHL7v2SegmentGroup $parent)
    {
        $class = "CHL7v2Segment$name";

        if (class_exists($class)) {
            $segment = new $class($parent);
        } else {
            $segment = new self($parent);
        }

        $segment->name = substr($name, 0, 3);

        return $segment;
    }

    /**
     * @inheritdoc
     */
    function _toXML(DOMNode $node, $hl7_datatypes, $encoding)
    {
        $doc      = $node->ownerDocument;
        $new_node = $doc->createElement($this->name);

        foreach ($this->fields as $_field) {
            $_field->_toXML($new_node, $hl7_datatypes, $encoding);
        }

        $node->appendChild($new_node);
    }

    /**
     * @inheritdoc
     */
    function parse($data)
    {
        parent::parse($data);

        $message = $this->getMessage();

        $fields     = CHL7v2::split($message->fieldSeparator, $data);
        $this->name = array_shift($fields);

        $specs = $this->getSpecs();

        $this->description = $specs->queryTextNode("description");

        if ($this->name === $message->getHeaderSegmentName()) {
            array_unshift($fields, $message->fieldSeparator);
        }

        // Don't count empty fields on the right
        $count_fields = count($fields);
        for ($i = $count_fields - 1; $i >= 0; $i--) {
            if ($fields[$i] === "") {
                $count_fields--;
            } else {
                break;
            }
        }

        $_segment_specs = $specs->getItems();

        // Check the number of fields
        if ($count_fields > count($_segment_specs)) {
            $this->error(CHL7v2Exception::TOO_MANY_FIELDS, $data, $this, CHL7v2Error::E_WARNING);
        }

        foreach ($_segment_specs as $i => $_spec) {
            if (array_key_exists($i, $fields)) {
                $field = new CHL7v2Field($this, $_spec);
                $field->parse($fields[$i]);

                $this->fields[] = $field;
            } elseif ($_spec->isRequired()) {
                $field = new CHL7v2Field($this, $_spec);
                $this->error(CHL7v2Exception::FIELD_EMPTY, $field->getPathString(), $field);
            }
        }
    }

    /**
     * @inheritdoc
     */
    function getMessage()
    {
        return $this->parent->getMessage();
    }

    /**
     * @inheritdoc
     */
    function getSpecs()
    {
        $message      = $this->getMessage();
        $message_type = isset($message->name[0][1]) ? $message->name[0][1] : null;

        if (!$message_type) {
            $message_type = substr($message->event_name, -3, 3);
        }

        // Load a message-specific segment if exists
        if ($message_type) {
            $spec = $message->getSchema(self::PREFIX_SEGMENT_NAME, "{$this->name}_{$message_type}");

            if ($spec) {
                return $spec;
            }
        }

        return $message->getSchema(self::PREFIX_SEGMENT_NAME, $this->name);
    }

    /**
     * @inheritdoc
     */
    function fill($fields)
    {
        if (!$this->name) {
            return;
        }

        $specs = $this->getSpecs();

        $_segment_specs = $specs->getItems();
        foreach ($_segment_specs as $i => $_spec) {
            if (array_key_exists($i, $fields)) {
                $_data = $fields[$i];

                $field = new CHL7v2Field($this, $_spec);

                if ($_data === null || $_data === "" || $_data === []) {
                    if ($_spec->isRequired()) {
                        $this->error(CHL7v2Exception::FIELD_EMPTY, $field->getPathString(), $field);
                    }
                } else {
                    if ($_spec->isForbidden()) {
                        $this->error(CHL7v2Exception::FIELD_FORBIDDEN, $_data, $field);
                    }
                }

                if ($_data instanceof CMbObject) {
                    throw new CHL7v2Exception($_data->_class, CHL7v2Exception::UNEXPECTED_DATA_TYPE);
                }
                $field->fill($_data);

                $this->fields[] = $field;
            } elseif ($_spec->isRequired()) {
                $field = new CHL7v2Field($this, $_spec);
                $this->error(CHL7v2Exception::FIELD_EMPTY, $field->getPathString(), $field);
            }
        }
    }

    /**
     * @inheritdoc
     */
    function validate()
    {
        foreach ($this->fields as $field) {
            $field->validate();
        }
    }

    /**
     * @inheritdoc
     */
    function getSegment()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    function getVersion()
    {
        return $this->getMessage()->getVersion();
    }

    /**
     * @inheritdoc
     */
    function getPath($separator = ".", $with_name = false)
    {
        if (!$with_name) {
            return [];
        }

        return [$this->name];
    }

    /**
     * @see parent::__toString
     */
    function __toString()
    {
        $sep  = $this->getMessage()->fieldSeparator;
        $name = $this->name;

        if (CHL7v2Message::$decorateToString) {
            $sep  = "<span class='fs'>$sep</span>";
            $name = "<strong>$name</strong>";
        }

        $fields = $this->fields;

        if ($this->name === $this->getMessage()->getHeaderSegmentName()) {
            array_shift($fields);
        }

        $str = $name . $sep . implode($sep, $fields);

        if (CHL7v2Message::$decorateToString) {
            $str = "<div class='entity segment' id='entity-er7-$this->id' data-title='$this->description'>$str</div>";
        } else {
            $str .= $this->getMessage()->segmentTerminator;
        }

        return $str;
    }

    /**
     * Build segment
     *
     * @param CHEvent $event Event
     * @param string  $name  Segment name
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function build(CHEvent $event, $name = null)
    {
        if (!$event->msg_codes) {
            throw new CHL7v2Exception(CHL7v2Exception::MSG_CODE_MISSING);
        }

        // This segment has the following fields
        if ($name) {
            $this->name = $name;
        }

        $this->getMessage()->appendChild($this);
    }

    /**
     * Get person identifiers
     *
     * @param CPatient      $patient Person
     * @param CGroups       $group   Group
     * @param CInteropActor $actor   Actor
     *
     * @return array
     * @throws CHL7v2Exception
     */
    function getPersonIdentifiers(CPatient $patient, CGroups $group, CInteropActor $actor = null)
    {
        if (CModule::getActive('doctolib') && $actor && $actor instanceof CReceiverHL7v2Doctolib) {
            return CReceiverHL7v2Doctolib::getPersonIdentifiers($patient, $group, $actor);
        }

        if (!$patient->_IPP) {
            $patient->loadIPP($group->_id);
        }

        $assigning_authority = $this->getAssigningAuthority("FINESS", $group->finess);

        if (CValue::read($actor->_configs, "build_PID_3_4") === "actor") {
            $assigning_authority = $this->getAssigningAuthority("actor", null, $actor);
        } elseif (CValue::read($actor->_configs, "build_PID_3_4") === "domain") {
            // Master domain
            $group_domain               = new CGroupDomain();
            $group_domain->group_id     = $group->_id;
            $group_domain->master       = 1;
            $group_domain->object_class = "CPatient";
            $group_domain->loadMatchingObject();

            $domain = $group_domain->loadRefDomain();

            $assigning_authority = $this->getAssigningAuthority("domain", null, null, $domain);
        }

        $IPP = $patient->_IPP;
        if (!$patient->_IPP && !CValue::read($actor->_configs, "send_not_master_IPP")) {
            $IPP = "===IPP_MISSING===";
        }

        // Table - 0203
        // RI - Resource identifier
        // PI - Patient internal identifier
        // INS-C - Identifiant national de santé calculé
        $identifiers = [];
        if (CHL7v2Message::$build_mode == "simple") {
            if ($actor->_configs["send_own_identifier"]) {
                $identifiers[] = [
                    $IPP,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    (!$actor->_configs["send_assigning_authority"]) ? null : $assigning_authority,
                    "PI",
                ];

                $identifiers[] = [
                    $patient->_id,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    (!$actor->_configs["send_assigning_authority"]) ?
                        null : $this->getAssigningAuthority("mediboard", null, null, null, $group->_id),
                    "RI",
                ];
            } else {
                $identifiers[] = [
                    (!$IPP) ? 0 : $IPP,
                ];
            }

            return $identifiers;
        }

        if (!$actor->_configs["send_evenement_to_mbdmp"]) {
            if ($IPP) {
                $identifiers[] = [
                    $IPP,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    (empty($actor->_configs["send_assigning_authority"])) ? null : $assigning_authority,
                    "PI",
                ];
            }
        } else {
            if (isset($patient->_activation_code)) {
                $identifiers[] = [
                    $patient->_activation_code,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    $this->getAssigningAuthority("mediboard", null, null, null, $actor->group_id),
                    "ACODE",
                ];
            }
        }

        // Ajout des identifiants des acteurs d'intégration
        $this->fillActorsIdentifiers($identifiers, $patient, $actor);

        // Ajout d'autres identifiants
        $this->fillOtherIdentifiers($identifiers, $patient, $actor);

        return $identifiers;
    }

    /**
     * Get assigning authority
     *
     * @param string        $name     Assigning authority type
     * @param string        $value    Namespace ID
     * @param CInteropActor $actor    Actor
     * @param CDomain       $domain   Domain
     * @param int           $group_id Group ID
     *
     * @return array
     * @throws CHL7v2Exception
     */
    function getAssigningAuthority(
        $name = "mediboard",
        $value = null,
        CInteropActor $actor = null,
        CDomain $domain = null,
        $group_id = null
    ) {
        switch ($name) {
            case "domain":
                return [
                    $domain->namespace_id,
                    $domain->OID,
                    "ISO",
                ];

            case "actor":
                $configs = $actor->_configs;

                return [
                    $configs["assigning_authority_namespace_id"],
                    $configs["assigning_authority_universal_id"],
                    $configs["assigning_authority_universal_type_id"],
                ];

            case "mediboard":
                $context = "global";
                if ($group_id) {
                    $context = "CGroups-$group_id";
                }
                if ($actor) {
                    $context = "CGroups-$actor->group_id";
                }

                return [
                    CAppUI::conf("hl7 CHL7 assigning_authority_namespace_id", $context),
                    CAppUI::conf("hl7 CHL7 assigning_authority_universal_id", $context),
                    CAppUI::conf("hl7 CHL7 assigning_authority_universal_type_id", $context),
                ];

            case "INS-C":
                return [
                    "ASIP-SANTE-INS-C",
                    "1.2.250.1.213.1.4.2",
                    "ISO",
                ];

            case "NIR":
                return [
                    "ASIP-SANTE-NIR",
                    "1.2.250.1.213.1.4.8",
                    "ISO",
                ];

            case "INS-NIR":
                return [
                    "ASIP-SANTE-INS-NIR",
                    "1.2.250.1.213.1.4.8",
                    "ISO",
                ];

            case "INS-NIA":
                return [
                    "ASIP-SANTE-INS-NIR",
                    "1.2.250.1.213.1.4.9",
                    "ISO",
                ];

            case "ADELI":
            case "RPPS":
                return [
                    "ASIP-SANTE-PS",
                    "1.2.250.1.71.4.2.1",
                    "ISO",
                ];

            case "FINESS":
                return [
                    $value,
                    null,
                    "M",
                ];

            case "INSEE":
                return [
                    "INSEE",
                    null,
                    "L",
                ];

            default:
                throw new CHL7v2Exception(CHL7v2Exception::UNKNOWN_AUTHORITY);
        }
    }

    /**
     * Get actors identifiers
     *
     * @param array         $identifiers Identifiers
     * @param CMbObject     $object      Object
     * @param CInteropActor $actor       Actor
     *
     * @return void
     * @throws CHL7v2Exception
     */
    function fillActorsIdentifiers(&$identifiers, CMbObject $object, CInteropActor $actor = null)
    {
        if (!$actor->_configs["send_actor_identifier"]) {
            return;
        }

        $domain                  = new CDomain();
        $where                   = [];
        $where["incrementer_id"] = "IS NULL";
        $where["actor_id"]       = "= '$actor->_id'";
        /** @var CDomain[] $domains */
        $domains = $domain->loadList($where);

        foreach ($domains as $_domain) {
            $value = CIdSante400::getValueFor($object, $_domain->tag);
            if (!$value) {
                continue;
            }

            $identifiers[] = [
                $value,
                null,
                null,
                // PID-3-4 Autorité d'affectation
                $this->getAssigningAuthority("domain", null, null, $_domain),
                $actor->_configs["build_identifier_authority"] == "PI_AN" ? "PI" : "RI",
            ];
        }
    }

    /**
     * Get other identifiers
     *
     * @param array         $identifiers Identifiers
     * @param CPatient      $patient     Person
     * @param CInteropActor $actor       Actor
     *
     * @return void
     */
    function fillOtherIdentifiers(&$identifiers, CPatient $patient, CInteropActor $actor = null)
    {
    }

    /**
     * Get old identifier concerning an INS-NIR modification or deletion
     *
     * @param CPatient        $patient Patient
     * @param CSourceIdentite $disable_insi_identity_source
     *
     * @return array Old INS-NIR identifier
     * @throws CHL7v2Exception
     * @throws Exception
     */
    public function getOldINSIdentifier(CPatient $patient, CSourceIdentite $disable_insi_identity_source): array
    {
        $patient_ins_nir = $disable_insi_identity_source->loadRefPatientINSNIR();
        $ins_type        = $patient_ins_nir->is_nia ? 'INS-NIA' : 'INS-NIR';

        $identifiers[] = [
            $patient_ins_nir->ins_nir,
            null,
            null,
            // PID-3-4 Autorité d'affectation
            $this->getAssigningAuthority($ins_type),
            'INS',
            null,
            $disable_insi_identity_source->debut ? CMbDT::date($disable_insi_identity_source->debut) : null,
            $disable_insi_identity_source->fin ? CMbDT::date($disable_insi_identity_source->fin) : null,
        ];

        return $identifiers;
    }

    /**
     * Get visit identifiers
     *
     * @param CSejour       $sejour           Admit
     * @param CGroups       $group            Group
     * @param CInteropActor $actor            Actor
     * @param CMbObject     $reference_object Reference object
     *
     * @return array
     * @throws CHL7v2Exception
     */
    function getVisitNumber(
        CSejour $sejour,
        CGroups $group,
        CInteropActor $actor = null,
        CMbObject $reference_object = null
    ) {
        $sejour->loadNDA($group->_id);
        $identifiers = [];

        $NDA = $sejour->_NDA;
        if (!$sejour->_NDA && !CValue::read($actor->_configs, "send_not_master_NDA")) {
            $NDA = "===NDA_MISSING===";
        }

        if ($actor->_configs["build_PV1_19"] == "simple") {
            $identifiers[] = $NDA;
        } else {
            if ($actor->_configs["build_NDA"] == "PV1_19") {
                $identifiers[] = $NDA ? [
                    $NDA,
                    null,
                    null,
                    // PID-3-4 Autorité d'affectation
                    $this->getAssigningAuthority("FINESS", $group->finess),
                    "AN",
                ] : [];
            } else {
                if (CModule::getActive(
                        "oxCabinetSIH"
                    ) && $reference_object && $reference_object instanceof CEvenementPatient) {
                    $identifier_id = CCabinetSIH::getObjectIdSIH($reference_object);

                    $identifiers[] = [
                        $identifier_id,
                        null,
                        null,
                        // PID-3-4 Autorité d'affectation
                        $this->getAssigningAuthority(
                            "actor",
                            null,
                            $actor,
                            null,
                            $actor->group_id
                        ),
                        $actor->_configs["build_PV1_19_identifier_authority"],
                    ];
                } else {
                    // On peut également passer l'identifiant externe du séjour
                    $idex = new CIdSante400();
                    if ($idex_tag = $actor->_configs["build_PV1_19_idex_tag"]) {
                        $idex = CIdSante400::getMatch($sejour->_class, $idex_tag, null, $sejour->_id);
                    }

                    $identifiers[] = [
                        $idex->_id ? $idex->id400 : $sejour->_id,
                        null,
                        null,
                        // PID-3-4 Autorité d'affectation
                        $this->getAssigningAuthority(
                            $idex->_id ? "actor" : "mediboard",
                            null,
                            $actor,
                            null,
                            $sejour->group_id
                        ),
                        $actor->_configs["build_PV1_19_identifier_authority"],
                    ];
                }
            }

            // Ajout des identifiants des acteurs d'intégration
            $this->fillActorsIdentifiers($identifiers, $sejour, $actor);
        }

        return $identifiers;
    }

    /**
     * Get XCN : extended composite ID number and name for persons
     *
     * @param CMbObject        $object     Object
     * @param CInteropReceiver $actor      Actor
     * @param bool             $repeatable Repeatable field
     *
     * @return array
     * @throws CHL7v2Exception
     */
    function getXCN(CMbObject $object, CInteropReceiver $actor, $repeatable = false)
    {
        $xcn1 = $xcn2 = $xcn3 = $xcn9 = $xcn13 = $xcn23 = null;

        $idex = new CIdSante400();
        if ($object instanceof CMedecin) {
            $object->completeField("adeli", "rpps");

            $idex = CIdSante400::getMatch("CMedecin", $actor->_tag_hl7, null, $object->_id);

            // On envoie le RPPS our le RI à AppFine
            if ($actor->_configs["send_evenement_to_mbdmp"]) {
                $xcn1  = $object->rpps ? $object->rpps : $object->_id;
                $xcn2  = CMbString::truncate($object->nom, 50);
                $xcn3  = $object->prenom;
                $xcn9  = $this->getXCN9($object, $idex, $actor);
                $xcn13 = $object->rpps ? "RPPS" : "RI";
            } else {
                $xcn1  = CValue::first($object->adeli, $object->rpps, $idex->id400, $object->_id);
                $xcn2  = $object->nom;
                $xcn3  = $object->prenom;
                $xcn9  = $this->getXCN9($object, $idex, $actor);
                $xcn13 = ($object->adeli ? "ADELI" : ($object->rpps ? "RPPS" : "RI"));
            }
            $xcn23 = $object->cp;
        }
        if ($object instanceof CUser) {
            $xcn1  = $object->_id;
            $xcn2  = $object->user_last_name;
            $xcn3  = $object->user_first_name;
            $xcn9  = $this->getXCN9($object);
            $xcn13 = "RI";
            $xcn23 = $object->user_zip;
        }
        if ($object instanceof CMediusers) {
            $object->completeField("adeli", "rpps");

            $idex = CIdSante400::getMatch("CMediusers", $actor->_tag_mediuser, null, $object->_id);

            // On envoie le RPPS our le RI à AppFine
            if ($actor->_configs["send_evenement_to_mbdmp"]) {
                $xcn1  = $object->rpps ? $object->rpps : $object->_id;
                $xcn2  = $object->_user_last_name;
                $xcn3  = $object->_user_first_name;
                $xcn9  = $this->getXCN9($object, $idex, $actor);
                $xcn13 = $object->rpps ? "RPPS" : "RI";
            } else {
                $xcn1  = CValue::first($object->rpps, $object->adeli, $idex->id400, $object->_id);
                $xcn2  = $object->_user_last_name;
                $xcn3  = $object->_user_first_name;
                $xcn9  = $this->getXCN9($object, $idex, $actor);
                $xcn13 = ($object->rpps ? "RPPS" : ($object->adeli ? "ADELI" : "RI"));
            }
            $xcn23 = $object->_user_cp;
        }
        if ($object instanceof CPatient) {
            $xcn1  = $object->_id;
            $xcn2  = $object->nom;
            $xcn3  = $object->prenom;
            $xcn9  = $this->getXCN9($object);
            $xcn13 = "RI";
            $xcn23 = $object->cp;
        }

        if ($repeatable && ($actor->_configs["build_PV1_7"] === "repeatable")) {
            $xcn = [
                null,
                $xcn2,
                $xcn3,
                null,
                null,
                null,
                null,
                null,
                $xcn9,
                "L",
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ];

            $xncs = [];

            // Ajout du RPPS
            if ($object->rpps) {
                $xcn[0]  = $object->rpps;
                $xcn[8]  = $this->getAssigningAuthority("RPPS");
                $xcn[12] = "RPPS";
                $xcn[22] = $xcn23 ?: null;

                $xncs[] = $xcn;
            }

            // Ajout de l'ADELI
            if ($object->adeli) {
                $xcn[0]  = $object->adeli;
                $xcn[8]  = $this->getAssigningAuthority("ADELI");
                $xcn[12] = "ADELI";
                $xcn[22] = $xcn23 ?: null;

                $xncs[] = $xcn;
            }

            // Ajout de l'Idex
            if ($idex->id400) {
                $xcn[0]  = $idex->id400;
                $xcn[8]  = $this->getAssigningAuthority("actor", null, $actor);
                $xcn[12] = "RI";
                $xcn[22] = $xcn23 ?: null;

                $xncs[] = $xcn;
            }

            if (!$object->rpps && !$object->adeli && !$idex->id400) {
                // Ajout de l'ID Mediboard
                $xcn[0]  = $object->_id;
                $xcn[8]  = $this->getAssigningAuthority("mediboard", null, null, null, $actor->group_id);
                $xcn[12] = "RI";
                $xcn[22] = $xcn23 ?: null;

                $xncs[] = $xcn;
            }

            return $xncs;
        }

        return [
            [
                // XCN-1
                $xcn1,
                // XCN-2
                $xcn2,
                // XCN-3
                $xcn3,
                // XCN-4
                null,
                // XCN-5
                null,
                // XCN-6
                null,
                // XCN-7
                null,
                // XCN-8
                null,
                // XCN-9
                // Autorité d'affectation
                $xcn9,
                // XCN-10
                // Table - 0200
                // L - Legal Name - Nom de famille
                "L",
                // XCN-11
                null,
                // XCN-12
                null,
                // XCN-13
                // Table - 0203
                // ADELI - Numéro au répertoire ADELI du professionnel de santé
                // RPPS  - N° d'inscription au RPPS du professionnel de santé
                // RI    - Ressource interne
                $xcn13,
                // XCN-14
                null,
                // XCN-15
                null,
                // XCN-16
                null,
                // XCN-17
                null,
                // XCN-18
                null,
                // XCN-19
                null,
                // XCN-20
                null,
                // XCN-21
                null,
                // XCN-22
                null,
                // XCN-23
                $xcn23 ? $xcn23 : null,
            ],
        ];
    }

    /**
     * Get XCN-9 : assigning authority
     *
     * @param CUser|CMedecin|CMediusers|CMbObject $person Object
     * @param CIdSante400                         $idex   Idex
     * @param CInteropReceiver                    $actor  Actor
     *
     * @return array|null
     * @throws CHL7v2Exception
     */
    function getXCN9(CMbObject $person, CIdSante400 $idex = null, CInteropReceiver $actor = null)
    {
        if (empty($actor->_configs["send_assigning_authority"])) {
            return null;
        }

        // Autorité d'affectation de l'ADELI
        if ($person->adeli) {
            return $this->getAssigningAuthority("ADELI");
        } // Autorité d'affectation du RPPS
        elseif ($person->rpps) {
            return $this->getAssigningAuthority("RPPS");
        } // Autorité d'affectation de l'idex
        elseif ($idex && $idex->id400) {
            return $this->getAssigningAuthority("actor", null, $actor);
        }

        // Autorité d'affectation de Mediboard
        return $this->getAssigningAuthority("mediboard");
    }

    /**
     * Get XPN : extended composite name & ID for organizations
     *
     * @param CMbObject        $object   Object
     * @param CInteropReceiver $receiver Receiver
     *
     * @return array
     */
    function getXPN(CMbObject $object, CInteropReceiver $receiver = null)
    {
        $names = [];

        if ($object instanceof CPatient) {
            $anonyme = is_numeric($object->nom);

            $mode_identito_vigilance = "light";
            if ($receiver) {
                $mode_identito_vigilance = $receiver->_configs["mode_identito_vigilance"];
            }

            $nom_usuel    = CPatient::applyModeIdentitoVigilance(
                $object->nom,
                false,
                $mode_identito_vigilance,
                $anonyme
            );
            $prenom_usuel = CPatient::applyModeIdentitoVigilance(
                $object->prenom_usuel,
                true,
                $mode_identito_vigilance,
                $anonyme
            );

            $civilite = CHL7v2TableEntry::mapTo("9002", $object->civilite);

            // Nom usuel
            $patient_usualname = [
                $nom_usuel,
                $prenom_usuel ?: ($receiver && $receiver->_configs["build_PID_5_2"] ? $object->prenom : null),
                null,
                null,
                $civilite ? $civilite : $object->civilite,
                null,
                // Table 0200
                // A - Alias Name
                // B - Name at Birth
                // C - Adopted Name
                // D - Display Name
                // I - Licensing Name
                // L - Legal Name
                // M - Maiden Name
                // N - Nickname /_Call me_ Name/Street Name
                // P - Name of Partner/Spouse (retained for backward compatibility only)
                // R - Registered Name (animals only)
                // S - Coded Pseudo-Name to ensure anonymity
                // T - Indigenous/Tribal/Community Name
                // U - Unspecified
                is_numeric($nom_usuel) ? "S" : "D",
                // Table 465
                // A - Alphabetic (i.e., Default or some single-byte)
                // I - Ideographic (i.e., Kanji)
                // P - Phonetic (i.e., ASCII, Katakana, Hiragana, etc.)
                "A",
            ];

            $patient_birthname = [];
            // Cas nom de naissance
            if ($object->nom_jeune_fille) {
                $nom_jeune_fille = CPatient::applyModeIdentitoVigilance(
                    $object->nom_jeune_fille,
                    true,
                    $mode_identito_vigilance,
                    $anonyme
                );

                $patient_birthname    = $patient_usualname;
                $patient_birthname[0] = $nom_jeune_fille;
                $patient_birthname[1] = $object->prenom;
                $patient_birthname[2] = $object->prenoms;
                // Display Name devient Legal Name
                $patient_birthname[6] = "L";
            }
            $names[] = $patient_usualname;

            if ($object->nom_jeune_fille && $receiver && $receiver->_configs["build_PID_6"] === "none") {
                $names[] = $patient_birthname;
            }
        }

        if ($object instanceof CCorrespondantPatient) {
            $names[] = [
                CMbString::truncate($object->nom, 50),
                $object->prenom,
                null,
                null,
                null,
                null,
                is_numeric($object->nom) ? "S" : "L",
                "A",
            ];
        }

        return $names;
    }

    /**
     * Get XTN : extended telecommunication number
     *
     * @param CInteropReceiver $receiver           Receiver
     * @param string           $tel_number         Telephone number
     * @param string           $tel_use_code       Telecommunication use code
     * @param string           $tel_equipment_type Telecommunication equiment type
     *
     * @return array
     */
    function getXTN(CInteropReceiver $receiver, $tel_number, $tel_use_code, $tel_equipment_type)
    {
        return [
            ($receiver->_configs["build_telephone_number"] === "XTN_1") ? $tel_number : null,
            // Table - 0201
            $tel_use_code,
            // Table - 0202
            $tel_equipment_type,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            ($receiver->_configs["build_telephone_number"] === "XTN_12") ? $tel_number : null,
        ];
    }

    /**
     * Get PV1.4 : admission type
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CSejour          $sejour   Admit
     *
     * @return mixed|null
     */
    function getPV14(CInteropReceiver $receiver, CSejour $sejour)
    {
        // Défaut
        $value = "R";

        // Hospital Service
        switch (CMbArray::get($receiver->_configs, "build_PV1_4")) {
            case 'charge_price_indicator':
                $value = $sejour->loadRefChargePriceIndicator()->code;

                break;

            default:
                $naissance                   = new CNaissance();
                $naissance->sejour_enfant_id = $sejour->_id;
                $naissance->loadMatchingObject();
                // Cas d'une naissance
                if ($naissance->_id || $sejour->_naissance) {
                    $value = "N";
                } // Cas accouchement maternité
                elseif ($sejour->type_pec == "O") {
                    $value = "L";
                }
        }

        if (!$value) {
            return null;
        }

        // Table - 0007
        // A  - Accident
        // C  - Elective - Confort (chirurgie esthétique)
        // E  - Emergency
        // L  - Labor and Delivery - Accouchement maternité
        // N  - Newborn (Birth in healthcare facility) - Nouveau né
        // R  - Routine (par défaut)
        // U  - Urgent - Caractère d'urgence aigue du problème quel que soit le service d'entrée
        // RM - Rétrocession du médicament - FR
        // IE - Prestation inter-établissements - FR
        return CHL7v2TableEntry::mapTo("7", $value);
    }

    /**
     * Get PV1.10 : hospital service
     *
     * @param CInteropReceiver $receiver    Receiver
     * @param CSejour          $sejour      Admit
     * @param CAffectation     $affectation Affectation
     *
     * @return mixed|null
     */
    function getPV110(CInteropReceiver $receiver, CSejour $sejour, CAffectation $affectation = null)
    {
        $value = null;
        if (!empty($receiver->_configs["build_PV1_10"])) {
            $value = $receiver->_configs["build_PV1_10"];
        }

        // Hospital Service
        switch ($value) {
            // idex du service
            case 'service':
                if (!$affectation) {
                    // Chargement de l'affectation courante
                    $affectation = $sejour->getCurrAffectation();

                    // Si on n'a pas d'affectation on va essayer de chercher la première
                    if (!$affectation->_id) {
                        $sejour->loadSurrAffectations();
                        $affectation = $sejour->_ref_prev_affectation;
                    }
                }

                $service_id = $affectation->service_id;
                if (!$service_id) {
                    if (!$sejour->service_id) {
                        return null;
                    }

                    $service_id = $sejour->service_id;
                }

                return CIdSante400::getMatch("CService", $receiver->_tag_service, null, $service_id)->id400;

            case 'finess':
                return $sejour->loadRefEtablissement()->finess;

            // Discipline médico-tarifaire
            default:
                return $sejour->discipline_id;
        }
    }

    /**
     * Get PV1.14 : admit source
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CSejour          $sejour   Admit
     *
     * @return mixed|null
     */
    function getPV114(CInteropReceiver $receiver, CSejour $sejour)
    {
        // Mode d'entrée personnalisable
        if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_entree")) {
            return $sejour->loadRefModeEntree()->code;
        }

        $value = null;
        if (!empty($receiver->_configs["build_PV1_14"])) {
            $value = $receiver->_configs["build_PV1_14"];
        }

        // Admit source
        switch ($value) {
            // Combinaison du ZFM
            // ZFM.1 + ZFM.3
            case 'ZFM':
                // Si mutation des urgences
                /*if ($sejour->provenance == "8" || $sejour->provenance == "5") {
                  return $sejour->mode_entree;
                }*/

                // Sinon concaténation du code mode d'entrée et du code de provenance
                return $sejour->mode_entree . $sejour->provenance;

            // Mode d'entrée
            default:
                // 1  - Envoyé par un médecin extérieur
                // 3  - Convocation à l'hôpital
                // 4  - Transfert depuis un autre centre hospitalier
                // 6  - Entrée par transfert interne
                // 7  - Entrée en urgence
                // 8  - Entrée sous contrainte des forces de l'ordre
                // 90 - Séjour programmé
                // 91 - Décision personnelle
                if ($sejour->adresse_par_prat_id) {
                    return 1;
                }
                if ($sejour->etablissement_entree_id) {
                    return 4;
                }
                if ($sejour->service_entree_id) {
                    return 6;
                }
                if ($sejour->type === "urg") {
                    return 7;
                }

                return 90;
        }
    }

    /**
     * Get PV1.26 : contract amount
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CSejour          $sejour   Admit
     *
     * @return mixed|null
     */
    function getPV126(CInteropReceiver $receiver, CSejour $sejour)
    {
        $value = null;
        if (!empty($receiver->_configs["build_PV1_26"])) {
            $value = $receiver->_configs["build_PV1_26"];
        }

        // Identifiant du mouvement
        switch ($value) {
            case 'movement_id':
                return $sejour->_ref_hl7_movement->_id;

            // Ne rien envoyer
            default:
                return null;
        }
    }

    /**
     * Get PV1.36 : discharge disposition
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CSejour          $sejour   Admit
     *
     * @return mixed|null
     */
    function getPV136(CInteropReceiver $receiver, CSejour $sejour)
    {
        // Mode de sortie personnalisable
        if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
            return $sejour->loadRefModeSortie()->code;
        }

        // Discharge Disposition
        $value = null;
        if (!empty($receiver->_configs["build_PV1_36"])) {
            $value = $receiver->_configs["build_PV1_36"];
        }

        switch ($value) {
            // Combinaison du ZFM
            // ZFM.2 + ZFM.4
            case 'ZFM':
                $mode_sortie = $this->getModeSortie($sejour);
                // Si décès
                if ($mode_sortie == "9") {
                    return $mode_sortie;
                }

                // Sinon concaténation du code mode de sortie et du code destination
                return $mode_sortie . $sejour->destination;

            // Circonstance de sortie
            default:
                // 2   - Messures disciplinaires
                // 3   - Décision médicale (valeur par défaut)
                // 4   - Contre avis médicale
                // 5   - En attente d'examen
                // 6   - Convenances personnelles
                // R   - Essai (contexte psychatrique)
                // E   - Evasion
                // F   - Fugue
                // A   - Absence (< 12h)
                // P   - Permission (< 72h)
                // S   - Sortie avec programme de soins
                // B   - Départ vers MCO
                // REO - Réorientation
                // PSA - Patient parti sans attendre les soins
                $discharge_disposition = $sejour->confirme ? "3" : "4";

                return CHL7v2TableEntry::mapTo("112", $discharge_disposition);
        }
    }

    /**
     * Get output mode
     *
     * @param CSejour $sejour Admit
     *
     * @return string
     */
    function getModeSortie(CSejour $sejour)
    {
        switch ($sejour->mode_sortie) {
            case "transfert_acte":
                return 0;

            case "mutation":
                return 6;

            case "transfert":
                return 7;

            case "normal":
                return 8;

            case "deces":
                return 9;

            default:
                return null;
        }
    }

    /**
     * Get PV1.36 : discharge disposition
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CSejour          $sejour   Admit
     * @param CHL7v2Event      $event    Event
     *
     * @return mixed|null
     */
    function getPV137(CInteropReceiver $receiver, CSejour $sejour, CHL7v2Event $event)
    {
        if ($sejour->mode_destination_id) {
            return [
                $sejour->loadRefModeDestination()->code,
            ];
        }

        $codes = ["A03", "A16", "A21"];
        if (!CMbArray::in($event->code, $codes)) {
            return null;
        }

        return $sejour->etablissement_sortie_id ? [$sejour->loadRefEtablissementTransfert()->finess] : null;
    }

    /**
     * Get PV2.45 : operation
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CSejour          $sejour   Admit
     *
     * @return array|null
     * @throws Exception
     */
    function getPV245(CInteropReceiver $receiver, CSejour $sejour)
    {
        $operation = $sejour->loadRefFirstOperation();
        $operation->loadRefPlageOp();

        $value = null;
        if (!empty($receiver->_configs["build_PV2_45"])) {
            $value = $receiver->_configs["build_PV2_45"];
        }

        // Advance Directive Code
        switch ($value) {
            // Transmission de l'intervention
            case 'operation':
                if (!$operation) {
                    return null;
                }

                $datetime = CHL7v2::getDateTime($operation->_datetime);

                $type_anesth = new CIdSante400();
                if ($operation->type_anesth) {
                    $tag_hl7     = $receiver->_tag_hl7;
                    $type_anesth = CIdSante400::getMatch("CTypeAnesth", $tag_hl7, null, $operation->type_anesth);
                }

                $idex_chir = CIdSante400::getMatchFor($operation->loadRefChir(), $receiver->_tag_mediuser);

                $anesth      = $operation->loadRefAnesth();
                $idex_anesth = new CIdSante400();
                if ($anesth->_id) {
                    $idex_anesth = CIdSante400::getMatchFor($anesth, $receiver->_tag_mediuser);
                }

                $libelle = $operation->libelle;

                $PV2_45_2 = "";
                // Datetime
                if ($datetime) {
                    $PV2_45_2 .= "$datetime";
                }
                $PV2_45_2 .= "#";
                // Type anesth
                if ($type_anesth->id400) {
                    $PV2_45_2 .= "$type_anesth->id400";
                }
                $PV2_45_2 .= "#";
                // Idex chir
                if ($idex_chir->id400) {
                    $PV2_45_2 .= "$idex_chir->id400";
                }
                $PV2_45_2 .= "#";
                // Idex anesth
                if ($idex_anesth->id400) {
                    $PV2_45_2 .= "$idex_anesth->id400";
                }
                $PV2_45_2 .= "#";
                // Libelle
                if ($libelle) {
                    $PV2_45_2 .= "$libelle";
                }

                return [
                    [
                        // CE-1
                        null,
                        // CE-2
                        $PV2_45_2,
                    ],
                ];

            default:
                return null;
        }
    }

    /**
     * Get PL : previous person location
     *
     * @param CInteropReceiver $receiver Receiver
     * @param CSejour          $sejour   Admit
     *
     * @return array|null
     * @throws CHL7v2Exception
     */
    function getPreviousPL(CInteropReceiver $receiver, CSejour $sejour)
    {
        $sejour->loadSurrAffectations();
        if ($prev_affectation = $sejour->_ref_prev_affectation) {
            return $this->getPL($receiver, $sejour, $prev_affectation);
        }

        return null;
    }

    /**
     * Get PL : person location
     *
     * @param CInteropReceiver $receiver    Receiver
     * @param CSejour          $sejour      Admit
     * @param CAffectation     $affectation Affectation
     *
     * @return array
     * @throws CHL7v2Exception
     */
    function getPL(CInteropReceiver $receiver, CSejour $sejour, CAffectation $affectation = null)
    {
        $group        = $sejour->loadRefEtablissement();
        $default_name = null;
        if ($receiver && $receiver->_id && isset($receiver->_configs)) {
            $default_name = $receiver->_configs["build_PV1_3_1_default"];
        }

        if (!$affectation || !$affectation->_id) {
            if (isset($sejour->_ref_hl7_movement)) {
                $movement    = $sejour->_ref_hl7_movement;
                $affectation = $movement->loadRefAffectation();
            }
        }

        if (!$affectation || !$affectation->_id) {
            // Chargement de l'affectation courante
            $affectation = $sejour->getCurrAffectation();

            // Si on n'a pas d'affectation on va essayer de rechercher la plus "optimale"
            if (!$affectation->_id) {
                // Dans les autres cas on prend la première
                $affectation = $sejour->loadRefFirstAffectation();
            }
        }

        $affectation->loadRefLit()->loadRefChambre();

        $current_uf = $sejour->getUFs(null, $affectation->_id);
        $name       = null;

        if ($receiver && $receiver->_id && isset($receiver->_configs)) {
            switch ($receiver->_configs["build_PV1_3_1"]) {
                case "UF":
                    $ufh  = CMbArray::get($current_uf, "hebergement");
                    $name = $ufh ? $ufh->code : null;
                    break;
                case "service":
                    if ($affectation->_id) {
                        $name = $affectation->loadRefService()->code;
                    }
                    if (!$name) {
                        $name = $sejour->loadRefService()->code;
                    }
                    break;
                default:
            }
        }

        $pl1 = $name ?: $default_name;

        if (!$pl1 && (!$affectation || !$affectation->_id)) {
            //return null;
        }

        return [
            [
                // PL-1 - Code UF hébergement
                $pl1,
                // PL-2 - Chambre
                $this->getPL2($receiver, $affectation),
                // PL-3 - Lit
                $this->getPL3($receiver, $affectation),
                // PL-4 - Etablissement hospitalier
                $this->getGroupAssigningAuthority($sejour->loadRefEtablissement()),
                // PL-5 - Statut du lit
                // Table - 0116
                // O - Occupé
                // U - Libre
                $this->getPL5($receiver),
                // PL-6 - Person location type
                null,
                // PL-7 - Building
                CHL7v2TableEntry::mapTo("307", $group->_id),
            ],
        ];
    }

    /**
     * Get PL2 : room
     *
     * @param CInteropReceiver $receiver    Receiver
     * @param CAffectation     $affectation Affectation
     *
     * @return mixed|null
     */
    function getPL2(CInteropReceiver $receiver, CAffectation $affectation = null)
    {
        $value = null;
        if (!empty($receiver->_configs["build_PV1_3_2"])) {
            $value = $receiver->_configs["build_PV1_3_2"];
        }

        // Chambre
        switch ($value) {
            // Valeur en config
            case 'config_value':
                return CAppUI::conf("hl7 CHL7v2Segment PV1_3_2");

            // Identifiant externe
            case 'idex':
                if (!$affectation->_id || !$affectation->lit_id) {
                    return null;
                }

                return CIdSante400::getMatch(
                    "CChambre",
                    $receiver->_tag_chambre,
                    null,
                    $affectation->_ref_lit->_ref_chambre->_id
                )->id400;

            // Nom de la chambre
            default:
                if (!$affectation->_id || !$affectation->lit_id) {
                    return null;
                }

                return $affectation->_ref_lit->_ref_chambre->nom;
        }
    }

    /**
     * Get PL3 : bed
     *
     * @param CInteropReceiver $receiver    Receiver
     * @param CAffectation     $affectation Affectation
     *
     * @return mixed|null
     */
    function getPL3(CInteropReceiver $receiver, CAffectation $affectation = null)
    {
        $value = null;
        if (!empty($receiver->_configs["build_PV1_3_3"])) {
            $value = $receiver->_configs["build_PV1_3_3"];
        }

        // Lit
        switch ($value) {
            // Valeur en config
            case 'config_value':
                return CAppUI::conf("hl7 CHL7v2Segment PV1_3_3");

            // Identifiant externe
            case 'idex':
                if (!$affectation->_id || !$affectation->lit_id) {
                    return null;
                }

                return CIdSante400::getMatch("CLit", $receiver->_tag_lit, null, $affectation->_ref_lit->_id)->id400;

            // Nom du lit
            default:
                if (!$affectation->_id || !$affectation->lit_id) {
                    return null;
                }

                return $affectation->_ref_lit->nom;
        }
    }

    /**
     * Get assigning authority for group
     *
     * @param CGroups $group Group
     *
     * @return array
     * @throws CHL7v2Exception
     */
    function getGroupAssigningAuthority(CGroups $group)
    {
        return $this->getAssigningAuthority("FINESS", $group->finess);
    }

    /**
     * Get PL5 : location status
     *
     * @param CInteropReceiver $receiver Receiver
     *
     * @return mixed|null
     */
    function getPL5(CInteropReceiver $receiver)
    {
        $value = null;
        if (!empty($receiver->_configs["build_PV1_3_5"])) {
            $value = $receiver->_configs["build_PV1_3_5"];
        }

        // Statut du lit
        switch ($value) {
            // Ne rien envoyer
            case 'null':
                return null;

            // Occupé - Libre
            default:
                // O - Occupé
                // U - Libre
                return "O";
        }
    }

    /**
     * Get price indicator
     *
     * @param CInteropReceiver $receiver  Receiver
     * @param CSejour          $sejour    Admit
     * @param CNaissance       $naissance Naissance
     *
     * @return string
     * @throws Exception
     */
    function getModeTraitement(CInteropReceiver $receiver, CSejour $sejour, CNaissance $naissance = null)
    {
        // Dans le cas d'une naissance, pas de mode de traitement
        if ($receiver && $receiver->_id && isset($receiver->_configs)
            && ($receiver->_configs["send_not_price_indicator_for_birth"] && $naissance && $naissance->_id)
        ) {
            return null;
        }


        if ($sejour->charge_id) {
            return $sejour->loadRefChargePriceIndicator()->code;
        }

        $charge = new CChargePriceIndicator();

        $where["type"]     = " = '$sejour->type'";
        $where["group_id"] = " = '$sejour->group_id'";
        $where["actif"]    = " = '1'";
        $where[]           = "type_pec = '$sejour->type_pec' OR type_pec IS NULL";

        $order = "type_pec IS NULL ASC";

        $charge->loadObject($where, $order, null, null, null, null, false);

        return $charge->code;
    }

    /**
     * Get provenance
     *
     * @param CSejour $sejour Admit
     *
     * @return string
     */
    function getModeProvenance(CSejour $sejour)
    {
        return ($sejour->provenance == "8") ? "5" : $sejour->provenance;
    }

    /**
     * Get segment action code
     *
     * @param CHL7v2Event $event Event
     *
     * @return string|null
     */
    function getSegmentActionCode(CHL7v2Event $event)
    {
        switch ($event->code) {
            case 'S12':
                return "A";

            case 'S13':
            case 'S14':
                return "U";

            case 'S15':
                return "D";

            default:
        }

        return null;
    }

    /**
     * Get filler statuts code
     *
     * @param CConsultation $appointment Appointment
     *
     * @return string
     */
    function getFillerStatutsCode(CConsultation $appointment)
    {
        // Table - 0278
        // Waiting   - Patient in waiting room
        // Pending   - Appointment has not yet been confirmed
        // Waitlist  - Appointment has been placed on a waiting list for a particular slot, or set of slots
        // Booked    - The indicated appointment is booked
        // Started   - The indicated appointment has begun and is currently in progress
        // Complete  - The indicated appointment has completed normally (was not discontinued, canceled, or deleted)
        // Cancelled - The indicated appointment was stopped from occurring (canceled prior to starting)
        // Dc        - The indicated appointment was discontinued (DC'ed while in progress, discontinued parent appointment,
        //             or discontinued child appointment)
        // Deleted   - The indicated appointment was deleted from the filler application
        // Blocked   - The indicated time slot(s) is(are) blocked
        // Overbook  - The appointment has been confirmed; however it is confirmed in an overbooked state
        // Noshow    - The patient did not show up for the appointment

        if ($appointment->annule) {
            if ($appointment->motif_annulation == "not_arrived") {
                return "Noshow";
            }

            return "Cancelled";
        }

        switch ($appointment->chrono) {
            case '32':
                return "Waiting";

            case '48':
                $value = CHL7v2TableEntry::mapTo("0278", 'Started');

                return $value ?: 'Started';

            case '64':
                return "Complete";

            default:
        }

        return "Booked";
    }

    /**
     * Get IN1.2 : type de débiteur
     *
     * @param CPatient $patient Patient
     *
     * @return string
     */
    function getIN12(CPatient $patient)
    {
        if ($patient->c2s) {
            return "CMU";
        }

        if ($patient->ame) {
            return "AME";
        }

        return "AMO";
    }

    /**
     * Get segment struct
     *
     * @return array
     */
    function getStruct()
    {
        $data = [];

        foreach ($this->fields as $_field) {
            $data[] = $_field->getStruct();
        }

        return $data;
    }

    /**
     * Get file content
     *
     * @param CDocumentItem  $docItem  Document
     * @param CReceiverHL7v2 $receiver receiver
     *
     * @return array|string|null
     * @throws CMbException
     */
    function getContent(CDocumentItem $docItem, $receiver)
    {
        $value_type = CMbArray::get($receiver->_configs, "build_OBX_2");
        if (!$value_type) {
            $value_type = "ED";
        }

        switch ($value_type) {
            case 'ED':
                if (CModule::getActive("appFine")) {
                    $content = CAppFineServer::getContent($docItem, $receiver);
                } elseif (CModule::getActive("appFineClient") && $receiver->_configs["send_evenement_to_mbdmp"]) {
                    $content = CAppFineClient::getContent($docItem, $receiver);
                } else {
                    if ($docItem instanceof CFile) {
                        $file_type = $docItem->file_type;
                        $ext       = $this->getExtension($docItem->file_type);
                    } else {
                        $file_type = $ext = "pdf";
                    }

                    // Envoi d'un document a tout le monde
                    $content = [
                        CAppUI::conf("hl7 CHL7 sending_application", "CGroups-$receiver->group_id"),
                        $docItem instanceof CCompteRendu ? "application/$file_type" : $file_type,
                        $ext,
                        "Base64",
                        base64_encode($docItem->getBinaryContent()),
                        $docItem->_guid,
                    ];
                }

                return implode("^", $content);

            case 'RP':
                $extension = pathinfo($docItem->file_name, PATHINFO_EXTENSION);
                $type_data = $this->getDataType($docItem);

                if (preg_match('#^http://#i', $docItem->_link_file) || preg_match(
                        '#^https://#i',
                        $docItem->_link_file
                    )) {
                    $content = [
                        "$docItem->_link_file",
                        "URL",
                        "HTML",
                        "URL",
                    ];
                } else {
                    $content = [
                        "$docItem->_link_file",
                        "PATH",
                        $extension,
                        "PATH",
                    ];
                }

                $chaine = implode("^", $content);

                return $chaine;

            default:
                return null;
        }
    }

    /**
     * Return the data type which correspond at the extension of file
     *
     * @param CMBObject $object objet
     *
     * @return string|null
     */
    public function getExtension(string $file_type): ?string
    {
        switch ($file_type) {
            case 'application/txt':
                return "txt";

            case "image/gif":
                return "gif";

            case "image/jpeg":
            case "image/jpg":
                return "jpeg";

            case "image/png":
                return "png";

            case "application/rtf":
                return "rtf";

            case "text/html":
                return "html";

            case "image/tiff":
                return "tiff";

            case "application/xml":
                return "xml";

            case "application/pdf":
                return "pdf";

            default:
                return null;
        }
    }

    /**
     * Return the data type which correspond at the extension of file
     *
     * @param CMBObject $object objet
     *
     * @return string|null
     */
    function getDataType(CMBObject $object)
    {
        switch (pathinfo($object->file_name, PATHINFO_EXTENSION)) {
            case 'txt':
                return "application/txt";
                break;
            case "gif":
                return "image/gif";
                break;
            case "jpeg":
            case "jpg":
                return "image/jpeg";
                break;
            case "png":
                return "image/png";
                break;
            case "rtf":
                return "application/rtf";
                break;
            case "html":
                return "text/html";
                break;
            case "tiff":
                return "image/tiff";
                break;
            case "xml":
                return "application/xml";
                break;
            case "pdf":
                return "application/pdf";
                break;

            default:
                return null;
        }
    }

    /**
     * Get filler appointment ID
     *
     * @param CConsultation  $appointment Appointment
     * @param CReceiverHL7v2 $receiver    Receiver HL7v2
     *
     * @return array
     */
    function getFillerAppointmentID(CConsultation $appointment, $receiver)
    {
        $identifiers = [];

        $appointment->loadExternalIdentifiers($receiver->group_id);

        if (isset($appointment->_ref_appFine_idex) && $appointment->_ref_appFine_idex->_id) {
            $configs       = $receiver->_configs;
            $identifiers[] = [
                // Entity identifier
                $appointment->_ref_appFine_idex->id400,
                // Autorité assignement
                $configs["assigning_authority_namespace_id"],
                $configs["assigning_authority_universal_id"],
                $configs["assigning_authority_universal_type_id"],
            ];

            return $identifiers;
        }

        if (isset($appointment->_ref_doctolib_idex) && $appointment->_ref_doctolib_idex->_id) {
            $configs       = $receiver->_configs;
            $identifiers[] = [
                // Entity identifier
                $appointment->_ref_doctolib_idex->id400,
                // Autorité assignement
                $configs["assigning_authority_namespace_id"],
            ];

            return $identifiers;
        }

        // Dans le cas d'AppFine, on envoie juste notre RI
        if (CModule::getActive('appFineClient') && $receiver->_configs['send_evenement_to_mbdmp']) {
            $identifiers[] = [
                // Entity identifier
                $appointment->_id,
                // Autorité assignement
                CAppUI::conf("hl7 CHL7 assigning_authority_namespace_id", "CGroups-$receiver->group_id"),
                CAppUI::conf("hl7 CHL7 assigning_authority_universal_id", "CGroups-$receiver->group_id"),
                CAppUI::conf("hl7 CHL7 assigning_authority_universal_type_id", "CGroups-$receiver->group_id"),
            ];

            return $identifiers;
        }

        $identifiers[] = [
            // Entity identifier
            $appointment->_id,
            // Autorité assignement
            CAppUI::conf("hl7 CHL7 assigning_authority_namespace_id", "CGroups-$receiver->group_id"),
            CAppUI::conf("hl7 CHL7 assigning_authority_universal_id", "CGroups-$receiver->group_id"),
            CAppUI::conf("hl7 CHL7 assigning_authority_universal_type_id", "CGroups-$receiver->group_id"),
        ];

        $idex = CIdSante400::getMatch("CConsultation", $receiver->_tag_consultation, null, $appointment->_id);
        if ($idex->_id) {
            $configs       = $receiver->_configs;
            $identifiers[] = [
                // Entity identifier
                $idex->id400,
                // Autorité assignement
                $configs["assigning_authority_namespace_id"],
                $configs["assigning_authority_universal_id"],
                $configs["assigning_authority_universal_type_id"],
            ];
        }

        return $identifiers;
    }

    /**
     * Get value type DMP
     *
     * @param CDocumentItem $docItem
     *
     * @return array
     * @throws Exception
     */
    public function getValueTypeDMP(CDocumentItem $docItem): array
    {
        // Le champ OBX.3 est obligatoire => On met donc deux chaines vides dans OBX.3.1 et OBX.3.2
        if (!$docItem->type_doc_dmp) {
            return [
                [
                    '',
                    '',
                ],
            ];
        }

        $datas_type_doc_dmp = explode('^', $docItem->type_doc_dmp);
        $type_dmp           = CMbArray::get($datas_type_doc_dmp, 1);
        if (!$type_dmp) {
            return [
                [
                    '',
                    '',
                ],
            ];
        }

        $datas_type_dmp = CANSValueSet::loadEntries("typeCode", $type_dmp);
        if (!CMbArray::get($datas_type_dmp, 'code') || !CMbArray::get($datas_type_dmp, 'displayName')) {
            return [
                [
                    '',
                    '',
                ],
            ];
        }

        return [
            [
                CMbArray::get($datas_type_dmp, 'code'),
                CMbArray::get($datas_type_dmp, 'displayName'),
            ],
        ];
    }
}
