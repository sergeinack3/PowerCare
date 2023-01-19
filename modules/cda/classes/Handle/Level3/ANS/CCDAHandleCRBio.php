<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Interop\Cda\Handle\Level3\ANS;

use DOMNode;
use DOMNodeList;
use Exception;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDABag;
use Ox\Interop\Cda\Handle\CCDAMetaParticipant;
use Ox\Interop\Cda\Handle\Level3\CCDAHandleLevel3;
use Ox\Mediboard\Files\CFile;
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
use Ox\Mediboard\ObservationResult\CObservationResultSubject;

class CCDAHandleCRBio extends CCDAHandleLevel3
{
    // sections
    /** @var string */
    public const DOCUMENT_CR_BIO = '2.16.840.1.113883.6.1^11502-2';

    /** @var string */
    public const SECTION_FR_CR_BIO_CHAPITRE = '1.3.6.1.4.1.19376.1.3.3.2.1';

    /** @var string */
    public const SECTION_FR_CR_BIO_SOUS_CHAPITRE = '1.2.250.1.213.1.1.2.71';

    /** @var string */
    public const SECTION_FR_COMMENTAIRE_NON_CODE = '1.2.250.1.213.1.1.2.73';
    // entries
    /** @var string */
    public const ENTRY_FR_RESULTATS_EXAMENS_DE_BIOLOGIE_MEDICALE = '1.2.250.1.213.1.1.3.21';

    /** @var string */
    public const ENTRY_FR_PRELEVEMENT = '1.2.250.1.213.1.1.3.77';

    /** @var string */
    public const ENTRY_FR_ISOLAT_MICROBIOLOGIQUE = '1.2.250.1.213.1.1.3.79';

    /** @var string */
    public const ENTRY_FR_BATTERIE_EXAMENS_BIOLOGIE_MEDICALE = '1.2.250.1.213.1.1.3.78';

    /** @var string */
    public const ENTRY_FR_RESULTAT_EXAMEN_PERTINENT = '1.2.250.1.213.1.1.3.80';

    /** @var string */
    public const ENTRY_FR_COMMENTAIRE = '1.2.250.1.213.1.1.3.32';

    /** @var string */
    public const ENTRY_FR_IMAGE_ILLUSTRATIVE = '1.2.250.1.213.1.1.3.103';

    /** @var string */
    public const ENTRY_FR_SUJET_NON_HUMAIN = '1.2.250.1.213.1.1.3.22';

    /** @var string */
    public const ENTRY_FR_PATIENT_AVEC_SUJET_NON_HUMAIN = '1.2.250.1.213.1.1.3.101';

    /** @var string */
    public const ENTRY_FR_LABORATOIRE_EXECUTANT = '1.2.250.1.213.1.1.3.23';

    /** @var string */
    public const ENTRY_CI_SIS_AUTHOR = '1.2.250.1.213.1.1.1.1.10.7';

    /** @var string */
    public const ENTRY_FR_PARTICIPANT = '1.2.250.1.213.1.1.3.109';

    /** @var CObservationResultSet */
    private $observation_result_set;

    /** @var CRepositoryCRBio */
    private $repository;

    /**
     * CCDAHandleCRBio constructor.
     *
     * @param CRepositoryCRBio|null $repository
     */
    public function __construct(CRepositoryCRBio $repository = null)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * @throws Exception
     */
    protected function handleComponents(): void
    {
        if (!$this->repository) {
            $this->repository = $this->getDefaultRepository();
        }

        $this->observation_result_set = $this->repository->findOrCreateResultSet();

        $cda_dom_document = $this->getCDADomDocument();
        $structureBody    = $cda_dom_document->getStructuredBody();

        // handle comments
        $main_comments = $cda_dom_document->queryFromTemplateId(
            'component/section',
            self::SECTION_FR_COMMENTAIRE_NON_CODE,
            $structureBody
        );
        foreach ($main_comments as $comment) {
            $this->handleResultSetComment($comment);
        }

        // handle chapters
        $chapters = $cda_dom_document->queryFromTemplateId(
            "component/section",
            self::SECTION_FR_CR_BIO_CHAPITRE,
            $structureBody
        );

        // handle each FR-CR-BIO-Chapitre
        foreach ($chapters as $chapter) {
            $this->handleChapter($chapter);
        }
    }

    /**
     * Handle Section FR-Commentaire-non-Code
     *
     * @param DOMNode $comment
     *
     * @return mixed|null
     * @throws Exception
     */
    protected function handleResultSetComment(DOMNode $comment): ?CObservationResultSetComment
    {
        if (!$result_set_comment = $this->repository->handleResultSetComment($comment)) {
            return null;
        }

        return $result_set_comment;
    }

    /**
     * @param DOMNode $chapter
     *
     * @throws Exception
     */
    protected function handleChapter(DOMNode $chapter): void
    {
        // handle each FR-CR-BIO-Sous-Chapitre
        $sub_chapters = $this->cda_dom_document->queryFromTemplateId(
            "component/section",
            self::SECTION_FR_CR_BIO_SOUS_CHAPITRE,
            $chapter
        );

        // handle all FR-Resultats-examens-de-biologie-medicale
        if ($sub_chapters->count() > 0) {
            // retrieve from sub chapters
            foreach ($sub_chapters as $sub_chapter) {
                $this->handleSubChapter(
                    $sub_chapter,
                    new CCDABag(
                        [
                            self::SECTION_FR_CR_BIO_CHAPITRE      => $chapter,
                            self::SECTION_FR_CR_BIO_SOUS_CHAPITRE => $sub_chapter,
                        ]
                    )
                );
            }
        } else {
            $results = $this->cda_dom_document->queryFromTemplateId(
                "entry",
                self::ENTRY_FR_RESULTATS_EXAMENS_DE_BIOLOGIE_MEDICALE,
                $chapter
            );

            foreach ($results as $result) {
                $this->handleResultsExam(
                    $result,
                    new CCDABag(
                        [
                            self::SECTION_FR_CR_BIO_CHAPITRE                      => $chapter,
                            self::ENTRY_FR_RESULTATS_EXAMENS_DE_BIOLOGIE_MEDICALE => $result,
                        ]
                    )
                );
            }
        }
    }

    /**
     * @param DOMNode $sub_chapter
     * @param CCDABag $bag
     *
     * @throws Exception
     */
    protected function handleSubChapter(DOMNode $sub_chapter, CCDABag $bag): void
    {
        $sub_results = $this->cda_dom_document->queryFromTemplateId(
            'entry',
            self::ENTRY_FR_RESULTATS_EXAMENS_DE_BIOLOGIE_MEDICALE,
            $sub_chapter
        );

        foreach ($sub_results as $result) {
            $this->handleResultsExam(
                $result,
                CCDABag::merge(
                    [
                        self::ENTRY_FR_RESULTATS_EXAMENS_DE_BIOLOGIE_MEDICALE => $result,
                    ],
                    $bag
                )
            );
        }
    }

    /**
     * @param DOMNode      $result
     * @param DOMNode|null $chapter
     * @param DOMNode|null $sub_chapter
     *
     * @throws Exception
     */
    protected function handleResultsExam(DOMNode $examen, CCDABag $bag): void
    {
        // mapping Examen
        if (null === $observation_examen = $this->repository->handleExamen($examen, $bag)) {
            return;
        }

        // keep examen in bag
        $bag->set(get_class($observation_examen), $observation_examen);

        // handle meta (participant / validator / authors / laboratory /subject_non_human)
        $this->handleObservationMeta($observation_examen, $examen, $bag);

        // handle image
        $this->handleImages($observation_examen, $examen);

        // handle comments
        $this->handleComments($observation_examen, $examen, $bag);

        // Prelevement
        $prelevements = $this->cda_dom_document->queryFromTemplateId(
            "act/entryRelationship/procedure",
            self::ENTRY_FR_PRELEVEMENT,
            $examen
        );

        // intégration des prélévement sur l'examen
        if ($prelevements->count() > 0) {
            $this->handlePrelevements(
                $observation_examen,
                CCDABag::merge([self::ENTRY_FR_PRELEVEMENT => $prelevements], $bag)
            );
        }

        // Batterie examens
        $batteries = $this->cda_dom_document->queryFromTemplateId(
            'act/entryRelationship/organizer',
            self::ENTRY_FR_BATTERIE_EXAMENS_BIOLOGIE_MEDICALE,
            $examen
        );

        if ($batteries->count() > 0) {
            $this->handleBatteries(
                $observation_examen,
                CCDABag::merge(
                    [self::ENTRY_FR_BATTERIE_EXAMENS_BIOLOGIE_MEDICALE => $batteries],
                    $bag
                )
            );
        }

        // Isolat
        $isolats = $this->cda_dom_document->queryFromTemplateId(
            'act/entryRelationship/organizer',
            self::ENTRY_FR_ISOLAT_MICROBIOLOGIQUE,
            $examen
        );

        if ($isolats->count() > 0) {
            $this->handleIsolats(
                $observation_examen,
                CCDABag::merge([self::ENTRY_FR_ISOLAT_MICROBIOLOGIQUE => $isolats], $bag)
            );
        }

        // Results
        $results = $this->cda_dom_document->queryFromTemplateId(
            'act/entryRelationship/observation',
            self::ENTRY_FR_RESULTAT_EXAMEN_PERTINENT,
            $examen
        );

        if ($results->count() > 0) {
            $this->handleResults(
                $observation_examen,
                CCDABag::merge([self::ENTRY_FR_RESULTAT_EXAMEN_PERTINENT => $results], $bag)
            );
        }
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $entry
     *
     * @return int nb elements treated
     * @throws Exception
     */
    protected function handleImages(CStoredObject $context, DOMNode $entry): int
    {
        $path   = $context instanceof CObservationResultExamen ? 'act/entryRelationship' : 'component';
        $images = $this->cda_dom_document->queryFromTemplateId(
            "$path/observationMedia",
            self::ENTRY_FR_IMAGE_ILLUSTRATIVE,
            $entry
        );

        if ($images->count() === 0) {
            return 0;
        }

        $nb_treated = 0;
        foreach ($images as $image) {
            if (!$this->handleImage($context, $image)) {
                continue;
            }

            $nb_treated += 1;
        }

        return $nb_treated;
    }

    /**
     * @param CStoredObject $context
     * @param DOMNode       $image
     *
     * @return CFile|null
     * @throws Exception
     */
    protected function handleImage(CStoredObject $context, DOMNode $image): ?CFile
    {
        if (!$file = $this->repository->handleImage($context, $image)) {
            return null;
        }

        return $file;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $entry
     * @param CCDABag                                                                                        $bag
     *
     * @return int nb elements treated
     * @throws Exception
     */
    protected function handleComments(CStoredObject $context, DOMNode $entry, CCDABag $bag): int
    {
        $path     = $context instanceof CObservationResultExamen ? 'act/entryRelationship' : 'component';
        $comments = $this->cda_dom_document->queryFromTemplateId(
            "$path/act",
            self::ENTRY_FR_COMMENTAIRE,
            $entry
        );

        if ($comments->count() === 0) {
            return 0;
        }

        $nb_treated = 0;
        foreach ($comments as $comment) {
            // mapping
            if (!$this->handleComment($context, $comment, $bag)) {
                continue;
            }

            $nb_treated += 1;
        }

        return $nb_treated;
    }

    /**
     * @param CStoredObject $context
     * @param DOMNode       $comment
     * @param CCDABag       $bag
     *
     * @return CObservationResultComment|null
     * @throws Exception
     */
    protected function handleComment(CStoredObject $context, DOMNode $comment, CCDABag $bag): ?CObservationResultComment
    {
        if (!$observation_comment = $this->repository->handleComment($context, $comment, $bag)) {
            return null;
        }

        return $observation_comment;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResult $context
     * @param CCDABag                                                               $bag
     *
     * @return int nb elements treated
     */
    protected function handlePrelevements(CStoredObject $context, CCDABag $bag): int
    {
        /** @var DOMNodeList $prelevements */
        $prelevements = $bag->get(self::ENTRY_FR_PRELEVEMENT);
        if ($prelevements->count() === 0) {
            return 0;
        }

        $nb_treated = 0;
        foreach ($prelevements as $prelevement) {
            if (!$this->handlePrelevement($context, $prelevement, $bag)) {
                continue;
            }

            $nb_treated += 1;
        }

        return $nb_treated;
    }

    /**
     * @param CStoredObject $context
     * @param DOMNode       $prelevement
     * @param CCDABag       $bag
     *
     * @return CObservationResultPrelevement|null
     * @throws Exception
     */
    protected function handlePrelevement(
        CStoredObject $context,
        DOMNode $prelevement,
        CCDABag $bag
    ): ?CObservationResultPrelevement {
        if (!$observation_prelevement = $this->repository->handlePrelevement($context, $prelevement, $bag)) {
            return null;
        }

        return $observation_prelevement;
    }

    /**
     * @param CObservationResultExamen|CObservationResultIsolat $context
     * @param CCDABag                                           $bag
     *
     * @return int nb elements treated
     * @throws Exception
     */
    protected function handleBatteries(CStoredObject $context, CCDABag $bag): int
    {
        /** @var DOMNodeList $batteries */
        $batteries = $bag->get(self::ENTRY_FR_BATTERIE_EXAMENS_BIOLOGIE_MEDICALE);
        if ($batteries->count() === 0) {
            return 0;
        }

        $nb_treated = 0;
        foreach ($batteries as $battery) {
            // mapping
            if (!$this->handleBattery($context, $battery, $bag)) {
                continue;
            }

            $nb_treated += 1;
        }

        return $nb_treated;
    }

    /**
     * @param CObservationResultExamen|CObservationResultIsolat $context
     * @param DOMNode                                           $battery
     * @param CCDABag                                           $bag
     *
     * @return CObservationResultBattery|null
     * @throws Exception
     */
    protected function handleBattery(CStoredObject $context, DOMNode $battery, CCDABag $bag): ?CObservationResultBattery
    {
        if (!$observation_battery = $this->repository->handleBattery($context, $battery, $bag)) {
            return null;
        }

        // keep battery
        $battery_bag = CCDABag::merge([get_class($observation_battery) => $observation_battery], $bag);

        // handle meta
        $this->handleObservationMeta($observation_battery, $battery, $battery_bag);

        // handle prelevement
        $prelevements = $this->cda_dom_document->queryFromTemplateId(
            "component/procedure",
            self::ENTRY_FR_PRELEVEMENT,
            $battery
        );

        // integration des prelevement sur la battery
        if ($prelevements->count() > 0) {
            $this->handlePrelevements(
                $observation_battery,
                CCDABag::merge([self::ENTRY_FR_PRELEVEMENT => $prelevements], $bag)
            );
        }

        // handle result
        $results = $this->cda_dom_document->queryFromTemplateId(
            "component/observation",
            self::ENTRY_FR_RESULTAT_EXAMEN_PERTINENT,
            $battery
        );
        if ($results->count() > 0) {
            $this->handleResults(
                $observation_battery,
                CCDABag::merge([self::ENTRY_FR_RESULTAT_EXAMEN_PERTINENT => $results], $bag)
            );
        }

        // handle images
        $this->handleImages($observation_battery, $battery);

        // handle comments
        $this->handleComments($observation_battery, $battery, $bag);

        return $observation_battery;
    }

    /**
     * @param CObservationResultExamen $examen
     * @param CCDABag                  $bag
     *
     * @return int nb items treated
     * @throws Exception
     */
    protected function handleIsolats(CObservationResultExamen $examen, CCDABag $bag): int
    {
        /** @var DOMNodeList $isolats */
        $isolats = $bag->get(self::ENTRY_FR_ISOLAT_MICROBIOLOGIQUE);
        if ($isolats->count() === 0) {
            return 0;
        }

        $nb_treated = 0;
        foreach ($isolats as $isolat) {
            //mapping
            if (!$this->handleIsolat($examen, $isolat, $bag)) {
                continue;
            }

            $nb_treated += 1;
        }

        return $nb_treated;
    }

    /**
     * @param CObservationResultExamen $examen
     * @param DOMNode                  $isolat
     * @param CCDABag                  $bag
     *
     * @return CObservationResultIsolat|null
     * @throws Exception
     */
    protected function handleIsolat(
        CObservationResultExamen $examen,
        DOMNode $isolat,
        CCDABag $bag
    ): ?CObservationResultIsolat {
        if (!$observation_isolat = $this->repository->handleIsolat($examen, $isolat, $bag)) {
            return null;
        }

        // keep isolat
        $isolat_bag = CCDABag::merge([get_class($observation_isolat) => $observation_isolat], $bag);

        // handle batteries
        $batteries = $this->cda_dom_document->queryFromTemplateId(
            "component/organizer",
            self::ENTRY_FR_BATTERIE_EXAMENS_BIOLOGIE_MEDICALE,
            $isolat
        );

        if ($batteries->count() > 0) {
            $this->handleBatteries(
                $observation_isolat,
                CCDABag::merge([self::ENTRY_FR_BATTERIE_EXAMENS_BIOLOGIE_MEDICALE => $batteries], $isolat_bag)
            );
        }

        // handle results
        $results = $this->cda_dom_document->queryFromTemplateId(
            "component/observation",
            self::ENTRY_FR_RESULTAT_EXAMEN_PERTINENT,
            $isolat
        );

        if ($results->count() > 0) {
            $this->handleResults(
                $observation_isolat,
                CCDABag::merge([self::ENTRY_FR_RESULTAT_EXAMEN_PERTINENT => $results], $isolat_bag)
            );
        }

        // handle meta
        $this->handleObservationMeta($observation_isolat, $isolat, $isolat_bag);

        // handle images
        $this->handleImages($observation_isolat, $isolat);

        // handle comments
        $this->handleComments($observation_isolat, $isolat, $bag);

        return $observation_isolat;
    }

    /**
     * @param CObservationResultExamen|CObservationResultIsolat|CObservationResultBattery $context
     * @param CCDABag                                                                     $bag
     *
     * @return int
     * @throws Exception
     */
    protected function handleResults(CStoredObject $context, CCDABag $bag)
    {
        /** @var DOMNodeList $results */
        $results = $bag->get(self::ENTRY_FR_RESULTAT_EXAMEN_PERTINENT);
        if ($results->count() === 0) {
            return 0;
        }

        $nb_treated = 0;
        foreach ($results as $result) {
            if (!$this->handleResult($context, $result, $bag)) {
                continue;
            }

            $nb_treated += 1;
        }

        return $nb_treated;
    }

    /**
     * @param CStoredObject $context
     * @param DOMNode       $result
     * @param CCDABag       $bag
     *
     * @return CObservationResult|null
     * @throws Exception
     */
    protected function handleResult(CStoredObject $context, DOMNode $result, CCDABag $bag): ?CObservationResult
    {
        if (!$observation_result = $this->repository->handleResult($context, $result, $bag)) {
            return null;
        }

        // keep Observation result
        $result_bag = CCDABag::merge([get_class($observation_result) => $observation_result], $bag);

        // handle meta
        $this->handleObservationMeta($observation_result, $result, $result_bag);

        // images
        $this->handleImages($observation_result, $result);

        // comments
        $this->handleComments($observation_result, $result, $bag);

        return $observation_result;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $entry
     * @param CCDABag                                                                                        $bag
     *
     * @throws Exception
     */
    protected function handleObservationMeta(CStoredObject $context, DOMNode $entry, CCDABag $bag)
    {
        $path = $context instanceof CObservationResultExamen ? 'act/' : '';
        // handle subject_id
        if ($context instanceof CObservationResult) {
            if ($subject = $bag->get(CObservationResultSubject::class)) {
                $context->observation_result_subject_id = $subject->_id;
                if ($msg = $context->store()) {
                    $this->report->addItemFailed($context, $msg);
                }
            }
        } else {
            // search subject non humain
            $subject_not_human = $this->cda_dom_document->queryFromTemplateId(
                $path . 'subject',
                self::ENTRY_FR_SUJET_NON_HUMAIN,
                $entry
            );
            // search patient with subject non human
            if ($subject_not_human->count() === 0) {
                $subject_not_human = $this->cda_dom_document->queryFromTemplateId(
                    $path . 'subject',
                    self::ENTRY_FR_PATIENT_AVEC_SUJET_NON_HUMAIN,
                    $entry
                );
            }

            if ($subject_not_human->count() > 0) {
                if ($observation_subject = $this->handleSubject($entry, $bag)) {
                    $bag->set(get_class($observation_subject), $observation_subject);
                }
            }
        }

        // handle laboratoires executants
        $labo_nodes = $this->cda_dom_document->queryFromTemplateId(
            $path . "performer",
            self::ENTRY_FR_LABORATOIRE_EXECUTANT,
            $entry
        );

        foreach ($labo_nodes as $labo_node) {
            $this->handleLaboExecutant($context, $labo_node, $bag);
        }

        // handle authors
        $author_nodes = $this->cda_dom_document->queryFromTemplateId(
            $path . "author",
            self::ENTRY_CI_SIS_AUTHOR,
            $entry
        );

        foreach ($author_nodes as $author_node) {
            $this->handleParticipantAuthor($context, $author_node, $bag);
        }

        $participant_nodes = $this->cda_dom_document->queryFromTemplateId(
            $path . 'participant',
            self::ENTRY_FR_PARTICIPANT,
            $entry
        );

        /** @var DOMNode $participant_node */
        foreach ($participant_nodes as $participant_node) {
            if (!$type_node = $participant_node->attributes->getNamedItem('typeCode')) {
                continue;
            }
            switch ($type_node->textContent) {
                // handle validators
                case 'AUTHEN':
                    $this->handleParticipantValidator($context, $participant_node, $bag);
                    break;

                // handle responsible
                case 'RESP':
                    $this->handleParticipantResponsible($context, $participant_node, $bag);
                    break;

                // handle Dispositif automatique
                case 'DEV':
                    $this->handleParticipantAutomate($context, $participant_node, $bag);
                    break;

                default:
                    // nothing
            }
        }
    }

    /**
     * @return CRepositoryCRBio
     */
    private function getDefaultRepository(): CRepositoryCRBio
    {
        return new CRepositoryCRBio($this, $this->cda_dom_document);
    }

    /**
     * @param DOMNode $subject
     * @param CCDABag $bag
     *
     * @return CObservationResultSubject|null
     * @throws Exception
     */
    protected function handleSubject(DOMNode $subject, CCDABag $bag): ?CObservationResultSubject
    {
        return $this->repository->handleSubject($subject, $bag);
    }

    /**
     * @param CStoredObject $context
     * @param DOMNode       $labo_node
     * @param CCDABag       $bag
     *
     * @return CObservationResultPerformer|null
     * @throws Exception
     */
    protected function handleLaboExecutant(
        CStoredObject $context,
        DOMNode $labo_node,
        CCDABag $bag
    ): ?CObservationResultPerformer {
        return $this->repository->handleLaboExecutant($context, $labo_node, $bag);
    }

    /**
     * @param CStoredObject $context
     * @param DOMNode       $author_node
     * @param CCDABag       $bag
     *
     * @return null
     */
    protected function handleParticipantAuthor(CStoredObject $context, DOMNode $author_node, CCDABag $bag)
    {
        return $this->repository->handleParticipantAuthor($context, $author_node, $bag);
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $participant_node
     * @param CCDABag                                                                                        $bag
     *
     * @return CObservationResponsibleObserver|null
     */
    protected function handleParticipantValidator(
        CStoredObject $context,
        DOMNode $participant_node,
        CCDABag $bag
    ): ?CObservationResponsibleObserver {
        if ($responsible = $this->repository->handleParticipantValidator($context, $participant_node, $bag)) {
            $bag->set(get_class($responsible), $responsible);
        }

        return $responsible;
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $participant_node
     * @param CCDABag                                                                                        $bag
     *
     * @return CCDAMetaParticipant|null
     */
    protected function handleParticipantResponsible(
        CStoredObject $context,
        DOMNode $participant_node,
        CCDABag $bag
    ): ?CCDAMetaParticipant {
        return $this->repository->handleParticipantResponsible($context, $participant_node, $bag);
    }

    /**
     * @param CObservationResultExamen|CObservationResultBattery|CObservationResultIsolat|CObservationResult $context
     * @param DOMNode                                                                                        $participant_node
     * @param CCDABag                                                                                        $bag
     *
     * @return CCDAMetaParticipant|null
     */
    protected function handleParticipantAutomate(
        CStoredObject $context,
        DOMNode $participant_node,
        CCDABag $bag
    ): ?CObservationResultAutomaticDevice {
        return $this->repository->handleParticipantAutomate($context, $participant_node, $bag);
    }
}
