<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Handle\Level3\ANS;

use DOMNode;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDABag;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Cda\CCDAReport;
use Ox\Interop\Cda\CCDAXPath;
use Ox\Interop\Cda\Exception\CCDAExceptionBio;
use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Handle\CCDAMetaParticipant;
use Ox\Interop\Eai\CDomain;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\ObservationResult\CObservationAbnormalFlag;
use Ox\Mediboard\ObservationResult\CObservationIdentifier;
use Ox\Mediboard\ObservationResult\CObservationResponsibleObserver;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultAutomaticDevice;
use Ox\Mediboard\ObservationResult\CObservationResultBattery;
use Ox\Mediboard\ObservationResult\CObservationResultComment;
use Ox\Mediboard\ObservationResult\CObservationResultExamen;
use Ox\Mediboard\ObservationResult\CObservationResultIsolat;
use Ox\Mediboard\ObservationResult\CObservationResultPerformer;
use Ox\Mediboard\ObservationResult\CObservationResultPrelevement;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationResultSetComment;
use Ox\Mediboard\ObservationResult\CObservationResultSpecimen;
use Ox\Mediboard\ObservationResult\CObservationResultSubject;
use Ox\Mediboard\ObservationResult\CObservationResultValue;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;
use Ox\Mediboard\ObservationResult\Interop\ObservationAbNormalManager;
use Ox\Mediboard\ObservationResult\Interop\ObservationReferenceRangeResolver;
use Ox\Mediboard\ObservationResult\Interop\ObservationResultSetLocator;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ucum\Ucum;

class CRepositoryCRBio
{
    /** @var CCDADomDocument */
    private $dom;

    /** @var CObservationResultSet */
    private $observation_result_set;

    /** @var CCDAHandleCRBio */
    private $handle;

    /** @var array|null */
    private $references;

    public function __construct(
        CCDAHandleCRBio $handle,
        CCDADomDocument $document
    ) {
        $this->dom    = $document;
        $this->handle = $handle;
    }

    /**
     * @param CObservationResultExamen $examen
     * @param string                   $reference
     *
     * @return string|null
     */
    protected function getReference(CObservationResultExamen $examen, string $reference): ?string
    {
        if (null === $this->references) {
            $this->generateReferences();
        }

        if (!$chapter = $examen->chapter_loinc) {
            return null;
        }
        $sub_chatper = $examen->subchapter_loinc ?: 'none';

        return CMbArray::getRecursive($this->references, "$chapter $sub_chatper $reference");
    }


    protected function generateReferences(): void
    {
        $structureBody = $this->dom->getStructuredBody();

        $chapters = $this->dom->queryFromTemplateId(
            "component/section",
            CCDAHandleCRBio::SECTION_FR_CR_BIO_CHAPITRE,
            $structureBody
        );

        foreach ($chapters as $chapter) {
            $chapter_code = $this->handle->getCodeAttributNode('code', $chapter);

            // handle each FR-CR-BIO-Sous-Chapitre
            $sub_chapters = $this->dom->queryFromTemplateId(
                "component/section",
                CCDAHandleCRBio::SECTION_FR_CR_BIO_SOUS_CHAPITRE,
                $chapter
            );

            foreach ($sub_chapters as $sub_chapter) {
                $sub_chapter_code = $this->handle->getCodeAttributNode('code', $sub_chapter);
                $this->generateReferenceNodes($chapter_code, $sub_chapter_code, $sub_chapter);
            }

            $this->generateReferenceNodes($chapter_code, 'none', $chapter);
        }
    }

    /**
     * @param string  $chapter
     * @param string  $sub_chapter
     * @param DOMNode $node
     */
    private function generateReferenceNodes(
        string $chapter,
        string $sub_chapter,
        DOMNode $node
    ): void {
        if (!$text_node = $this->dom->queryNode('text', $node)) {
            return;
        }

        $nodes = $this->dom->queryNodes(".//*[@ID]", $text_node);
        /** @var DOMNode $node */
        foreach ($nodes as $node) {
            $reference                                            = $this->dom->getValueAttributNode($node, 'ID');
            $this->references[$chapter][$sub_chapter][$reference] = $this->dom->queryTextNode('.', $node);
        }
    }

    /**
     * @param CObservationResultSet $observation_result_set
     */
    public function setObservationResultSet(CObservationResultSet $observation_result_set): void
    {
        $this->observation_result_set = $observation_result_set;
    }

    /**
     * @return CCDAReport
     */
    private function getReport(): CCDAReport
    {
        return $this->handle->getReport();
    }

    /**
     * @param CStoredObject $object
     * @param string        $err_msg
     */
    private function addItemFailed(CStoredObject $object, string $err_msg): void
    {
        $this->getReport()->addItemFailed($object, $err_msg);
    }

    /**
     * @param CStoredObject $object
     */
    private function addItemSuccess(CStoredObject $object): void
    {
        $this->getReport()->addItemsStored($object);
    }

    /**
     * @param CObservationResultExamen|CObservationResultIsolat|CObservationResultBattery $context
     * @param DOMNode                                                                     $result
     * @param CCDABag                                                                     $bag
     *
     * @return CObservationResult|null
     * @throws Exception
     */
    public function handleResult(CStoredObject $context, DOMNode $result, CCDABag $bag): ?CObservationResult
    {
        // mapping
        $observation_result = new CObservationResult();
        $observation_result->setTarget($context);
        $observation_result->observation_result_set_id = $this->observation_result_set->_id;

        // code
        if (!$observation_result_identifier = $this->findOrCreateResultIdentifier($result)) {
            $this->addItemFailed($observation_result, CAppUI::tr('CObservationIdentifier.none'));

            return null;
        }
        $observation_result->identifier_id = $observation_result_identifier->_id;

        // datetime
        if ($datetime_cda = $this->handle->getValueAttributNode('effectiveTime', $result)) {
            $observation_result->datetime = CMbDT::dateTime($datetime_cda);
        }

        // try to find
        $observation_result->loadMatchingObject();

        // method
        if ($this->dom->queryNode('methodCode', $result)) {
            $method_code    = $this->handle->getCodeAttributNode('methodCode', $result);
            $method_system  = $this->handle->getCodeSystemAttributNode('methodCode', $result);
            $method_display = $this->handle->getAttributNode('methodCode', 'displayName', $result);

            $observation_result->method        = $method_system ? $method_code : $method_display;
            $observation_result->method_system = $method_system;
        }

        // status
        $status                     = $this->handle->getCodeAttributNode('statusCode', $result);
        $observation_result->status = $status === 'completed'
            ? CObservationResult::STATUS_FINAL
            : CObservationResult::STATUS_CANCELED;

        // responsible
        if ($responsible = $bag->get(CObservationResponsibleObserver::class)) {
            $observation_result->responsible_observer_id = $responsible->_id;
        }

        // store
        if ($msg = $observation_result->store()) {
            $this->addItemFailed($observation_result, $msg);

            return null;
        }

        // store all values
        $this->handleResultValues($observation_result, $result, $bag);

        // interpretation
        $this->handleFlags($observation_result, $result);

        return $observation_result;
    }

    /**
     * @param CObservationResult $observation_result
     * @param DOMNode            $result
     * @param CCDABag            $bag
     *
     * @return array|null
     * @throws Exception
     */
    public function handleResultValues(CObservationResult $observation_result, DOMNode $result, CCDABag $bag): ?array
    {
        $observation_value                               = new CObservationResultValue();
        $observation_value->observation_result_id        = $observation_result->_id;
        $observation_value->rank                         = 1;
        $observation_value_second                        = new CObservationResultValue();
        $observation_value_second->observation_result_id = $observation_result->_id;
        $observation_value_second->rank                  = 2;

        // value
        $prefix       = CCDAXPath::PREFIX_SCHEMA;
        $value_node   = $this->dom->queryNode('value', $result);
        $value_type   = explode(':', $this->handle->getAttributNode('value', "$prefix:type", $result));
        $value_type   = end($value_type);
        $value        = null;
        $unit         = null;
        $value_second = null;
        $unit_second  = null;
        switch ($value_type) {
            case "PQ":
                $value        = $this->handle->getValueAttributNode('value', $result);
                $unit         = $this->handle->getAttributNode('value', 'unit', $result);
                $value_second = $this->handle->getValueAttributNode('value/translation', $result);
                $unit_second  = $this->handle->getAttributNode('value/translation', 'code', $result);
                break;
            case "IVL_PQ":
                $low              = $this->handle->getValueAttributNode('value/low', $result);
                $high             = $this->handle->getValueAttributNode('value/high', $result);
                $unit_low         = $this->handle->getAttributNode('value/low', 'unit', $result);
                $unit_high        = $this->handle->getAttributNode('value/high', 'unit', $result);
                $low_second       = $this->handle->getValueAttributNode('value/low/translation', $result);
                $high_second      = $this->handle->getValueAttributNode('value/high/translation', $result);
                $unit_second_low  = $this->handle->getAttributNode('value/low/translation', 'code', $result);
                $unit_second_high = $this->handle->getAttributNode('value/high/translation', 'code', $result);

                // value
                if ($high !== null && $low !== null) {
                    $value = "$low - $high";
                } else {
                    $value = $high !== null ? $high : $low;
                }

                // value_second
                if ($high_second !== null && $low_second !== null) {
                    $value_second = "$low_second - $high_second";
                } elseif ($high_second !== null) {
                    $value_second = $high_second;
                } elseif ($low_second !== null) {
                    $value_second = $low_second;
                }

                // unit
                $unit = $unit_low !== null ? $unit_low : $unit_high;

                // unit_second
                if ($value_second !== null) {
                    $unit_second = $unit_second_low !== null ? $unit_second_low : $unit_second_high;
                }
                break;
            case "CD":
                if (!$identifier = $this->handle->getValueAttributNode("originalText/reference", $value_node)) {
                    break;
                }

                // sanitize
                $identifier = strpos($identifier, '#') === 0 ? substr($identifier, 1) : $identifier;
                $examen     = $bag->get(CObservationResultExamen::class);
                $value      = $this->getReference($examen, $identifier);
                break;
            case "ED":
                $explode_value = explode(' ', $value_node->textContent);
                $endKey        = array_key_last($explode_value);
                $unit          = $explode_value[$endKey];
                unset($explode_value[$endKey]);
                $value = implode(" ", $explode_value);
                break;
            default:
                $this->getReport()->addItemsIgnored($observation_value, "The type '$value_type' is not supported");

                return null;
        }

        if ($value === null) {
            return null;
        }

        // reference
        if ($reference_node = $this->dom->queryNode('referenceRange[@typeCode="REFV"]', $result)) {
            $low  = $this->handle->getValueAttributNode('observationRange/value/low', $reference_node);
            $high = $this->handle->getValueAttributNode('observationRange/value/high', $reference_node);

            $low_second  = $this->handle->getValueAttributNode(
                'observationRange/value/low/translation',
                $reference_node
            );
            $high_second = $this->handle->getValueAttributNode(
                'observationRange/value/high/translation',
                $reference_node
            );

            $reference_resolver = new ObservationReferenceRangeResolver();

            // primary range
            if ($low !== null || $high !== null) {
                [$low, $high] = $reference_resolver->resolve("$low - $high");
                $observation_value->setReferenceRange($low, $high);
            }

            // secondary range
            if ($low_second !== null || $high_second !== null) {
                [$low_second, $high_second] = $reference_resolver->resolve("$low_second - $high_second");
                $observation_value_second->setReferenceRange($low_second, $high_second);
            }
        }

        // try to find or store units
        if ($unit) {
            if ($observation_unit = $this->findOrCreateUnit($unit)) {
                $observation_value->unit_id = $observation_unit->_id;
            }
        }

        // mapping primary value
        $observation_value->value = $value;
        if (!$observation_value->loadMatchingObject()) {
            if ($msg = $observation_value->store()) {
                $this->addItemFailed($observation_value, $msg);
            }
        }
        if ($observation_value->_id) {
            $this->addItemSuccess($observation_value);
        }

        // mapping secondary value
        if ($value_second && $unit_second) {
            if ($observation_unit_second = $this->findOrCreateUnit($unit_second)) {
                $observation_value_second->value   = $value_second;
                $observation_value_second->unit_id = $observation_unit_second->_id;
                if (!$observation_value_second->loadMatchingObject()) {
                    if ($msg = $observation_value_second->store()) {
                        $this->addItemFailed($observation_value_second, $msg);
                    }
                }
            }
        }
        if ($observation_value_second->_id) {
            $this->addItemSuccess($observation_value_second);
        }

        return [
            $observation_value->_id ? $observation_value : null,
            isset($observation_value_second) && $observation_value_second->_id ? $observation_value_second : null,
        ];
    }

    /**
     * @param CObservationResultExamen|CObservationResultIsolat $context
     * @param DOMNode                                           $battery
     * @param CCDABag                                           $bag
     *
     * @return CObservationResultBattery|null
     * @throws Exception
     */
    public function handleBattery(CStoredObject $context, DOMNode $battery, CCDABag $bag): ?CObservationResultBattery
    {
        if (!$context->_id) {
            return null;
        }

        $code   = $this->handle->getCodeAttributNode('code', $battery);
        $system = $this->handle->getCodeSystemAttributNode('code', $battery);
        if ($datetime = $this->handle->getValueAttributNode('effectiveTime', $battery)) {
            $datetime = CMbDT::dateTime($datetime);
        }

        // mapping
        $observation_battery = new CObservationResultBattery();
        $observation_battery->setTarget($context);
        $observation_battery->code     = $code;
        $observation_battery->system   = $system;
        $observation_battery->datetime = $datetime;

        // load match or store
        if (!$observation_battery->loadMatchingObject()) {
            if ($msg = $observation_battery->store()) {
                $this->addItemFailed($observation_battery, $msg);

                return null;
            }
        }

        $this->addItemSuccess($observation_battery);

        return $observation_battery;
    }

    /**
     * @param CObservationResultExamen $examen
     * @param DOMNode                  $isolat
     * @param CCDABag                  $bag
     *
     * @return CObservationResultIsolat|null
     * @throws Exception
     */
    public function handleIsolat(
        CObservationResultExamen $examen,
        DOMNode $isolat,
        CCDABag $bag
    ): ?CObservationResultIsolat {
        if (!$examen->_id) {
            return null;
        }

        $observation_isolat                               = new CObservationResultIsolat();
        $observation_isolat->observation_result_examen_id = $examen->_id;

        // handle code
        if (!$code = $this->handle->getCodeAttributNode('code', $isolat)) {
            $system                     = $this->handle->getCodeSystemAttributNode('code', $isolat);
            $observation_isolat->code   = $code;
            $observation_isolat->system = $system;
        }

        // handle specimen code
        $specimen_code   = $this->handle->getCodeAttributNode(
            'specimen/specimenRole/specimenPlayingEntity/code',
            $isolat
        );
        $specimen_system = $this->handle->getCodeSystemAttributNode(
            'specimen/specimenRole/specimenPlayingEntity/code',
            $isolat
        );

        // specimen
        $observation_specimen = null;
        if ($specimen_code && $specimen_system) {
            $observation_specimen = CObservationResultSpecimen::loadMatch($specimen_code, $specimen_system, $msg_err);
            if ($msg_err) {
                $this->addItemFailed(new CObservationResultSpecimen(), $msg_err);
            }
        }
        if ($observation_specimen) {
            $observation_isolat->specimen_id = $observation_specimen->_id;
            $this->addItemSuccess($observation_specimen);
        }

        if ($datetime = $this->handle->getValueAttributNode('effectiveTime', $isolat)) {
            $observation_isolat->datetime = CMbDT::dateTime($datetime);
        }

        // load match or store
        if (!$observation_isolat->loadMatchingObject()) {
            if ($msg = $observation_isolat->store()) {
                $this->addItemFailed($observation_isolat, $msg);

                return null;
            }
        }

        $this->addItemSuccess($observation_isolat);

        return $observation_isolat;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResult $context
     * @param DOMNode                                                               $prelevement
     * @param CCDABag                                                               $bag
     *
     * @return CObservationResultPrelevement|null
     * @throws Exception
     */
    public function handlePrelevement(
        CStoredObject $context,
        DOMNode $prelevement,
        CCDABag $bag
    ): ?CObservationResultPrelevement {
        $observation_prelevement = new CObservationResultPrelevement();
        $observation_prelevement->setObject($context);

        // code
        if (!$code = $this->handle->getCodeAttributNode('code', $prelevement)) {
            $this->getReport()->addItemsIgnored($observation_prelevement, 'no code provided');

            return null;
        }
        $system                          = $this->handle->getCodeSystemAttributNode('code', $prelevement);
        $observation_prelevement->code   = $code;
        $observation_prelevement->system = $system;

        // datetime
        if ($datetime = $this->handle->getValueAttributNode('effectiveTime/high', $prelevement)) {
            $observation_prelevement->datetime = CMbDT::dateTime($datetime);
        }

        $observation_prelevement->loadMatchingObject();

        // échantillion id
        $participant_role_node = $this->dom->queryNode('participant/participantRole', $prelevement);
        if ($sample_id = $this->handle->getAttributNode('id', 'extension', $participant_role_node)) {
            $observation_prelevement->sample_id = $sample_id;

            // specimen
            $specimen_code   = $this->handle->getCodeAttributNode('playingEntity/code', $participant_role_node);
            $specimen_system = $this->handle->getCodeSystemAttributNode('playingEntity/code', $participant_role_node);
            if ($specimen_code && $specimen_system) {
                if ($observation_specimen = CObservationResultSpecimen::loadMatch(
                    $specimen_code,
                    $specimen_system,
                    $msg_err
                )) {
                    $observation_prelevement->specimen_id = $observation_specimen->_id;
                    $this->addItemSuccess($observation_specimen);
                }

                if ($msg_err) {
                    $this->addItemFailed(new CObservationResultSpecimen(), $msg_err);
                }
            }

            // nature
            $sample_nature                          = $this->handle->getAttributNode(
                'playingEntity/code',
                'displayName',
                $participant_role_node
            );
            $observation_prelevement->sample_nature = $sample_nature;

            // quantity
            $quantity      = $this->handle->getValueAttributNode('playingEntity/quantity', $participant_role_node);
            $quantity_unit = $this->handle->getAttributNode('playingEntity/quantity', 'unit', $participant_role_node);

            $observation_prelevement->quantity      = $quantity;
            $observation_prelevement->unit_quantity = $quantity_unit;
        }

        // datetime reception
        if ($datetime = $this->handle->getValueAttributNode('entryRelationship/act/effectiveTime', $prelevement)) {
            $observation_prelevement->datetime_reception = CMbDT::dateTime($datetime);
        }

        // preleveur
        if ($preleveur_node = $this->dom->queryNode("performer/assignedEntity", $prelevement)) {
            if ($medecin = $this->findMedecin($preleveur_node)) {
                $observation_prelevement->preleveur_id = $medecin->_id;
            }
        }

        if ($msg = $observation_prelevement->store()) {
            $this->addItemFailed($observation_prelevement, $msg);

            return null;
        }
        $this->addItemSuccess($observation_prelevement);

        return $observation_prelevement;
    }

    /**
     * @param CObservationResultExamen|CObservationResultIsolat|CObservationResultBattery|CObservationResult $context
     * @param DOMNode                                                                                        $comment
     * @param CCDABag                                                                                        $bag
     *
     * @return CObservationResultComment|null
     * @throws Exception
     */
    public function handleComment(CStoredObject $context, DOMNode $comment, CCDABag $bag): ?CObservationResultComment
    {
        if (!$context->_id) {
            return null;
        }

        if (!$text = $this->dom->queryTextNode('text', $comment)) {
            if (!$text = $this->handleReferenceComment($comment, $bag)) {
                return null;
            }
        }

        // mapping
        $observation_comment               = new CObservationResultComment();
        $observation_comment->object_class = $context->_class;
        $observation_comment->object_id    = $context->_id;
        $observation_comment->text         = $text;

        // load match or store
        if (!$observation_comment->loadMatchingObject()) {
            if ($msg = $observation_comment->store()) {
                $this->addItemFailed($observation_comment, $msg);

                return null;
            }
        }

        $this->addItemSuccess($observation_comment);

        return $observation_comment;
    }

    private function handleReferenceComment(DOMNode $comment, CCDABag $bag): ?string
    {
        if (!$reference = $this->handle->getValueAttributNode('text/reference', $comment)) {
            return null;
        }

        // sanitize
        $reference = strpos($reference, '#') === 0 ? substr($reference, 1) : $reference;
        $examen    = $bag->get(CObservationResultExamen::class);

        return $this->getReference($examen, $reference);
    }

    /**
     * @param CObservationResultExamen|CObservationResultIsolat|CObservationResultBattery|CObservationResult $context
     * @param DOMNode                                                                                        $image
     *
     * @return CFile|null
     * @throws Exception
     */
    public function handleImage(CStoredObject $context, DOMNode $image): ?CFile
    {
        $id   = $image->attributes->getNamedItem('ID');
        $type = $this->handle->getAttributNode('value', 'mediaType', $image);
        if (!$node = $this->dom->queryNode('value', $image)) {
            return null;
        }

        $content = base64_decode($node->textContent);

        $file = new CFile();
        $file->setObject($context);
        $file->file_name = ($id ? $id->textContent . "_" : "") . $context->_class;
        $file->file_type = $type;

        // try to load
        $file->loadMatchingObject();

        // set content
        $file->setContent($content);
        $file->fillFields();
        $file->updateFormFields();

        // store
        if ($msg = $file->store()) {
            $this->addItemFailed($file, $msg);

            return null;
        }

        $this->addItemSuccess($file);

        return $file;
    }

    /**
     * @param DOMNode $examen
     * @param CCDABag $bag
     *
     * @return CObservationResultExamen|null
     * @throws Exception
     */
    public function handleExamen(DOMNode $examen, CCDABag $bag): ?CObservationResultExamen
    {
        $observation_examen = new CObservationResultExamen();
        $act_node           = $this->dom->queryNode('act', $examen);
        $code               = $this->handle->getCodeAttributNode('code', $act_node);
        $code_system        = $this->handle->getCodeSystemAttributNode('code', $act_node);
        if (!$code || !$code_system) {
            return null;
        }

        // mapping
        $observation_examen->observation_result_set_id = $this->observation_result_set->_id;
        $observation_examen->code                      = $code;
        $observation_examen->system                    = $code_system;

        // chapter
        /** @var DOMNode $chapter_node */
        $chapter_node                      = $bag->get(CCDAHandleCRBio::SECTION_FR_CR_BIO_CHAPITRE);
        $chapter                           = $this->handle->getCodeAttributNode('code', $chapter_node);
        $observation_examen->chapter_loinc = $chapter;

        // sub chapter
        /** @var DOMNode $sub_chapter_node */
        if ($sub_chapter_node = $bag->get(CCDAHandleCRBio::SECTION_FR_CR_BIO_SOUS_CHAPITRE)) {
            $sub_chapter                          = $this->handle->getCodeAttributNode('code', $sub_chapter_node);
            $observation_examen->subchapter_loinc = $sub_chapter;
        }

        // load match or store
        if (!$observation_examen->loadMatchingObject()) {
            if ($msg = $observation_examen->store()) {
                $this->addItemFailed($observation_examen, $msg);

                return null;
            }
        }

        $this->addItemSuccess($observation_examen);

        return $observation_examen;
    }

    /**
     * @param DOMNode $comment
     *
     * @return CObservationResultSetComment|null
     * @throws Exception
     */
    public function handleResultSetComment(DOMNode $comment): ?CObservationResultSetComment
    {
        if (!$text = $this->dom->queryTextNode('text', $comment)) {
            return null;
        }
        $title = $this->dom->queryTextNode('title', $comment);

        // mapping and store
        $result_set_comment                            = new CObservationResultSetComment();
        $result_set_comment->text                      = $text;
        $result_set_comment->title                     = $title;
        $result_set_comment->observation_result_set_id = $this->observation_result_set->_id;
        if (!$result_set_comment->loadMatchingObject()) {
            if ($msg = $result_set_comment->store()) {
                $this->addItemFailed($result_set_comment, $msg);

                return null;
            }
        }

        $this->addItemSuccess($result_set_comment);

        return $result_set_comment->_id ? $result_set_comment : null;
    }

    /**
     * @param DOMNode $result
     *
     * @return CObservationIdentifier|null
     * @throws Exception
     */
    private function findOrCreateResultIdentifier(DOMNode $result): ?CObservationIdentifier
    {
        $observation_identifier = new CObservationIdentifier();
        $code                   = $this->handle->getCodeAttributNode('code', $result);
        $system                 = $this->handle->getCodeSystemAttributNode('code', $result);
        $display_name           = $this->handle->getAttributNode('code', 'displayName', $result);
        $system_name            = $this->handle->getAttributNode('code', 'codeSystemName', $result);

        $observation_identifier->identifier    = $code;
        $observation_identifier->coding_system = $system;
        if (!$system) {
            $observation_identifier->alt_identifier = $system_name;
        }
        // try to find
        if ($observation_identifier->loadMatchingObject()) {
            $this->addItemSuccess($observation_identifier);

            return $observation_identifier;
        }

        // store
        $observation_identifier->text           = $display_name;
        $observation_identifier->alt_identifier = $system_name;
        if ($msg = $observation_identifier->store()) {
            $this->addItemFailed($observation_identifier, $msg);

            return null;
        }

        $this->addItemSuccess($observation_identifier);

        return $observation_identifier;
    }

    /**
     * @param string $unit
     *
     * @return CObservationValueUnit
     */
    private function findOrCreateUnit(string $unit): ?CObservationValueUnit
    {
        // validate Ucum unit
        $coding_system = CObservationValueUnit::SYSTEM_UNKNOWN;
        $ucum          = new Ucum();
        if ($ucum->callValidation($unit, true)) {
            $coding_system = Ucum::CODE_SYSTEM;
        }

        // Retrieve Observation Unit
        $observation_unit = new CObservationValueUnit();
        $observation_unit->loadMatch($unit, $coding_system, $unit);

        return $observation_unit->_id ? $observation_unit : null;
    }

    /**
     * @param DOMNode $node
     *
     * @return CMedecin|null
     * @throws Exception
     */
    private function findMedecin(DOMNode $node): ?CMedecin
    {
        $root = $this->handle->getAttributNode('id', 'root', $node);
        if (!$root || $root !== CMedecin::OID_IDENTIFIER_NATIONAL) {
            return null;
        }

        if (!$identifier = $this->handle->getAttributNode('id', 'extension', $node)) {
            return null;
        }
        $is_rpps = strlen($identifier) === 11;

        $medecin = new CMedecin();
        if ($is_rpps) {
            $medecin->rpps = $identifier;
        } else {
            $medecin->adeli = $identifier;
        }

        if (!$medecin->loadMatchingObject()) {
            return null;
        }

        return $medecin;
    }

    /**
     * @param DOMNode $subject
     * @param CCDABag $bag
     *
     * @return CObservationResultSubject|null
     * @throws Exception
     */
    public function handleSubject(DOMNode $subject, CCDABag $bag): ?CObservationResultSubject
    {
        if (!$type = $this->handle->getAttributNode('relatedSubject/code', 'displayName', $subject)) {
            return null;
        }

        $name  = $this->handle->getValueAttributNode('relatedSubject/code/qualifier/name', $subject);
        $value = $this->handle->getAttributNode('relatedSubject/code/qualifier/value', 'displayName', $subject);

        // format name
        $value = $name && $value ? "($value)" : $value;
        $name  = $name ? $name . ($value ?: '') : $value;

        $streetAddressLine_nodes = $this->dom->queryNodes('relatedSubject/addr/streetAddressLine', $subject);
        $address                 = null;
        if ($streetAddressLine_nodes->count() > 0) {
            /** @var DOMNode $node */
            foreach ($streetAddressLine_nodes as $node) {
                $text = $node->textContent;
                if ($text) {
                    $address = $address ? $address . " $text" : $text;
                }
            }
        }

        $observation_subject                  = new CObservationResultSubject();
        $observation_subject->name            = $name;
        $observation_subject->type            = $type;
        $observation_subject->place_of_origin = $address;
        if (!$observation_subject->loadMatchingObject()) {
            if ($msg = $observation_subject->store()) {
                $this->addItemFailed($observation_subject, $msg);

                return null;
            }
        }

        $this->addItemSuccess($observation_subject);

        return $observation_subject;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $participant_node
     * @param CCDABag                                                                                        $bag
     *
     * @return CObservationResponsibleObserver|null
     * @throws Exception
     */
    public function handleParticipantValidator(
        $context,
        DOMNode $participant_node,
        CCDABag $bag
    ): ?CObservationResponsibleObserver {
        if (!$participant_role_node = $this->dom->queryNode('participantRole', $participant_node)) {
            return null;
        }

        // id
        $system_id = $this->dom->queryAttributeNodeValue('id', $participant_role_node, 'root');
        $code_id   = $this->dom->queryAttributeNodeValue('id', $participant_role_node, 'extension');
        $is_rpps   = $system_id === CMedecin::OID_IDENTIFIER_NATIONAL && $code_id;
        if ($is_rpps && ($medecin = CMedecin::getFromRPPS($code_id))) {
            try {
                $responsible = CObservationResponsibleObserver::getFromMedecin($medecin);
            } catch (CMbException $e) {
                $this->addItemFailed(new CObservationResponsibleObserver(), $e->getMessage());
            }
        } else {
            $name_node = $this->dom->queryNode('playingEntity/name', $participant_role_node);
            $responsible              = new CObservationResponsibleObserver();
            $responsible->given_name  = $this->dom->queryTextNode('given', $name_node);
            $responsible->family_name = $this->dom->queryTextNode('family', $name_node);
            $responsible->prefix = $this->dom->queryTextNode('prefix', $name_node);
            $responsible->suffix = $this->dom->queryTextNode('suffix', $name_node);
            $responsible->id          = substr(sha1($responsible->family_name . ' ' . $responsible->given_name), 0, 15);

            if ($msg = $responsible->store()) {
                $this->addItemFailed($responsible, $msg);
            }
        }

        if (isset($responsible) && $responsible->_id) {
            $this->addItemSuccess($responsible);
        }

        return isset($responsible) && $responsible->_id ? $responsible : null;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $participant_node
     * @param CCDABag                                                                                        $bag
     *
     * @return CCDAMetaParticipant|null
     * @throws Exception
     */
    public function handleParticipantResponsible(
        $context,
        DOMNode $participant_node,
        CCDABag $bag
    ): ?CCDAMetaParticipant {
        return $this->handleParticipant($context, CCDAMetaParticipant::TYPE_RESPONSIBLE, $participant_node, $bag);
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $author_node
     * @param CCDABag                                                                                        $bag
     *
     * @return null
     * @throws Exception
     */
    public function handleParticipantAuthor(
        CStoredObject $context,
        DOMNode $author_node,
        CCDABag $bag
    ): ?CCDAMetaParticipant {
        return $this->handleParticipant($context, CCDAMetaParticipant::TYPE_AUTHOR, $author_node, $bag);
    }

    /**
     * Handle flag on observation result
     *
     * @param CObservationResult $observation_result
     * @param DOMNode            $result
     *
     * @return void
     * @throws Exception
     */
    private function handleFlags(CObservationResult $observation_result, DOMNode $result): void
    {
        $code_system_obs_interpretation = '2.16.840.1.113883.5.83';
        $code_system_result = $this->handle->getCodeSystemAttributNode('interpretationCode', $result);
        if ($code_system_result !== $code_system_obs_interpretation) {
            return;
        }

        if (!$interpretation_code = $this->handle->getCodeAttributNode('interpretationCode', $result)) {
            return;
        }

        $manager_ab_normal = new ObservationAbNormalManager();
        $manager_ab_normal->synchronizeFlags($observation_result, [$interpretation_code]);
        $errors = array_merge(
            $manager_ab_normal->getErrorStoringProcess(),
            $manager_ab_normal->getErrorDeletingProcess()
        );
        foreach ($errors as $msg) {
            $this->addItemFailed(new CObservationAbnormalFlag(), $msg);
        }

        foreach ($manager_ab_normal->getFlagStored() as $abnormal_flag) {
            $this->addItemSuccess($abnormal_flag);
        }
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $participant_node
     * @param CCDABag                                                                                        $bag
     *
     * @return CObservationResultAutomaticDevice|null
     * @throws Exception
     */
    public function handleParticipantAutomate(
        $context,
        DOMNode $participant_node,
        CCDABag $bag
    ): ?CObservationResultAutomaticDevice {
        if (!$context || !$context->_id) {
            return null;
        }

        if (!$this->dom->queryNode('participantRole/playingDevice')) {
            return null;
        }

        // datetime
        $datetime = null;
        $low      = $this->handle->getValueAttributNode('time/low', $participant_node);
        $high     = $this->handle->getValueAttributNode('time/high', $participant_node);
        if ($low || $high) {
            $datetime = CMbDT::dateTime($high ?: $low);
        }

        $code          = $this->handle->getAttributNode(
            'participantRole/playingDevice/code',
            'displayName',
            $participant_node
        );
        $model_name    = $this->handle->getAttributNode(
            'participantRole/playingDevice/manufacturerModelName',
            'displayName',
            $participant_node
        );
        $logiciel_name = $this->handle->getAttributNode(
            'participantRole/playingDevice/softwareName',
            'displayName',
            $participant_node
        );

        $observation_automate                = new CObservationResultAutomaticDevice();
        $observation_automate->datetime      = $datetime;
        $observation_automate->object_class  = $context->_class;
        $observation_automate->object_id     = $context->_id;
        $observation_automate->code          = $code;
        $observation_automate->model_name    = $model_name;
        $observation_automate->logiciel_name = $logiciel_name;
        if (!$observation_automate->loadMatchingObject()) {
            if ($msg = $observation_automate->store()) {
                $this->addItemFailed($observation_automate, $msg);

                return null;
            }
        }

        $this->addItemSuccess($observation_automate);

        return $observation_automate;
    }


    /**
     * @param CStoredObject $context
     * @param string        $type
     * @param DOMNode       $participant_node
     * @param CCDABag       $bag
     *
     * @return CCDAMetaParticipant|null
     * @throws Exception
     */
    private function handleParticipant(
        CStoredObject $context,
        string $type,
        DOMNode $participant_node,
        CCDABag $bag
    ): ?CCDAMetaParticipant {
        if (!$context || !$context->_id) {
            return null;
        }

        $meta_participant = new CCDAMetaParticipant();
        if (!in_array($type, CCDAMetaParticipant::TYPES)) {
            $this->addItemFailed($meta_participant, "Type '$type' is not accepted");

            return null;
        }

        $meta_participant->setTargetParticipant($context);
        $meta_participant->handle($this->handle, $type, $participant_node);

        return $meta_participant;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $labo_node
     * @param CCDABag                                                                                        $bag
     *
     * @return CObservationResultPerformer|null
     * @throws Exception
     */
    public function handleLaboExecutant(
        CStoredObject $context,
        DOMNode $labo_node,
        CCDABag $bag
    ): ?CObservationResultPerformer {
        $identifier = $this->handle->getAttributNode(
            'assignedEntity/representedOrganization/id[@root="1.2.250.1.71.4.2.2"]',
            'extension',
            $labo_node
        );
        if (!$identifier) {
            return null;
        }

        $type       = $identifier[0];
        $identifier = substr($identifier, 1);

        $exercice_place = new CExercicePlace();
        switch (intval($type)) {
            case 1:
                $exercice_place->finess = $identifier;
                break;
            case 2:
                $exercice_place->siren = $identifier;
                break;
            case 3:
                $exercice_place->siret = $identifier;
                break;
            default:
                return null;
        }

        if (!$exercice_place->loadMatchingObject()) {
            return null;
        }

        $observation_performer                    = new CObservationResultPerformer();
        $observation_performer->object_id         = $context->_id;
        $observation_performer->object_class      = $context->_class;
        $observation_performer->exercice_place_id = $exercice_place->_id;
        if (!$observation_performer->loadMatchingObject()) {
            if ($msg = $observation_performer->store()) {
                $this->addItemFailed($observation_performer, $msg);

                return null;
            }
        }

        $this->addItemSuccess($observation_performer);

        return $observation_performer;
    }

    /**
     * @return CObservationResultSet
     * @throws CCDAExceptionBio
     * @throws Exception
     */
    public function findOrCreateResultSet(): CObservationResultSet
    {
        $meta    = $this->handle->getMeta();
        $sender  = $this->dom->getSender();
        $patient = $this->handle->getPatient();

        // cas du remplacment de document
        // annulation de l'ancien result set
        if ($meta->relatedDocumentId) {
            $locator_old_result_set = (new ObservationResultSetLocator($meta->relatedDocumentId, $sender, $patient));
            if ($result_set_to_cancel = $locator_old_result_set->find()) {
                $result_set_to_cancel->actif = 0;
                if ($msg = $result_set_to_cancel->store()) {
                    $this->addItemFailed($result_set_to_cancel, $msg);
                } else {
                    $this->addItemSuccess($result_set_to_cancel);
                }
            }
        }

        // resolve identifier
        if (!$identifier = $meta->getId()) {
            throw CCDAExceptionBio::identifierNotFound();
        }

        // resolve target
        $target = $this->handle->getTargetObject();
        if (!$target || !$target->_id) {
            $target = null;
        }

        // resolve NDA
        $type_of_care = $this->handle->getMeta()->_current_type_of_care;
        $domain_nda   = CDomain::getMasterDomainSejour($this->dom->getSender()->group_id);
        if ($domain_nda->OID && $domain_nda->OID === $type_of_care->encounterIdCodeSystem) {
            $NDA = $type_of_care->encounterId;
        } else {
            $NDA = null;
        }

        if ($target instanceof CSejour) {
            $target->loadNDA($sender->group_id);
            if ($target->_NDA) {
                $NDA = $target->_NDA;
            }
        }

        // find or create CObservationResultSet
        try {
            $result_set_locator = (new ObservationResultSetLocator($identifier, $sender, $patient))
                ->setDatetime($this->dom->getEffectiveTime())
                ->setIdentifierSejour($NDA)
                ->setTarget($target);

            $result_set = $result_set_locator->findOrCreate();
            $this->addItemSuccess($result_set);
        } catch (Exception $exception) {
            throw CCDAExceptionBio::resultSetFailed($exception->getMessage());
        }

        // save target on meta
        $meta->setTarget($result_set);
        if ($msg = $meta->store()) {
            $this->addItemFailed($meta, $msg);
        }

        // mark file like labo file after store it
        $this->handle->attachEvent(CCDAHandle::EVENT_AFTER_STORE_FILE, [$this, 'markFile'], 20);

        return $this->observation_result_set = $result_set;
    }

    /**
     * @param CFile $file
     *
     * @return void
     * @throws CMbException
     */
    public function markFile(CFile $file): void
    {
        $file_related = null;
        if ($meta = $this->handle->getMeta()) {
            $file_related = $meta->getRelatedDocument();
        }

        CObservationResultSet::markFileLabo(
            $file,
            $this->observation_result_set->_id,
            $this->dom->getSender(),
            $file_related
        );
    }
}
