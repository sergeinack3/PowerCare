<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use DOMNode;
use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Parser\CCDAParserFactory;
use Ox\Interop\Eai\CItemReport;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CCDAPOCD_HD000040
 * CDA InfrastructureRoot
 */
class CCDAPOCD_HD000040 extends CCDADomDocument
{
    private const CDA_EXTENSION = 'xml';
    public const  CDA_PREFIX    = 'CDA-';

    /**
     * @inheritdoc
     */
    public function handle(): ?string
    {
        $this->setTitle();

        $this->report = new CCDAReport('Report ' . $this->getTitle());

        // Parsing
        $parser = CCDAParserFactory::parseDocument($this);

        // keep parsing error in report
        if ($parsing_exception = $parser->getException()) {
            $this->report->addData($parsing_exception->getMessage(), CItemReport::SEVERITY_ERROR);
        }

        if (!$parser->hasError() && ($handle_object = $parser->getHandleObject())) {
            $handle_object->setReport($this->report);

            // handle document cda
            $handle_object->handle($parser->getCDADomDocument());

            // Modification of the original document, we will cancel the original document
            $this->cancelOriginalDocument($parser);

            // Record CDA file
            $this->storeCDAFile($parser);
        }

        // Record report
        $this->makeReport();

        // Store exchange
        $this->storeExchange();

        return null;
    }

    /**
     * Cancel original document
     *
     * @param CCDAParserFactory $parser Parser
     *
     * @return void
     */
    private function cancelOriginalDocument(CCDAParserFactory $parser): void
    {
        if (!$relatedDocumentId = $parser->getHandleObject()->getMeta()->relatedDocumentId) {
            return;
        }
        $file_orginal = new CFile();
        $idex         = CIdSante400::getMatch(
            'CFile',
            $this->_ref_sender->_self_tag,
            $relatedDocumentId
        );
        if ($idex->_id) {
            $file_orginal->load($idex->object_id);
        }
        $file_orginal->annule = 1;
        if ($msg = $file_orginal->store()) {
            $this->report->addItemFailed($file_orginal, $msg);
        } else {
            $this->report->addItemsStored($file_orginal);
        }

        $file_cda_orginal = new CFile();
        $idex_cda         = CIdSante400::getMatch(
            'CFile',
            $this->_ref_sender->_self_tag,
            self::CDA_PREFIX . $relatedDocumentId
        );
        if ($idex_cda->_id) {
            $file_cda_orginal->load($idex_cda->object_id);
        }

        $file_cda_orginal->annule = 1;
        // oxlabo not send cancel of document (already done by replacing)
        $file_cda_orginal->_no_synchro_eai = true;
        if ($msg = $file_cda_orginal->store()) {
            $this->report->addItemFailed($file_cda_orginal, $msg);
            return;
        }

        $this->report->addItemsStored($file_cda_orginal);
    }

    /**
     * Store CDA file
     *
     * @param CCDAParserFactory $parser Parser
     *
     * @return void
     * @throws CCDAException|Exception
     */
    private function storeCDAFile(CCDAParserFactory $parser): void
    {
        $file_traceability = new CFileTraceability();
        $target            = null;
        if ($handle_factory = $parser->getHandleObject()) {
            $target = $handle_factory->getTargetObject();
        }

        if (!$target || !$target->_id) {
            // Soit on va lier le fichier CDA au patient si l'expéditeur accepte l'enregistrement
            // d'un fichier sur le patient
            if ($this->_ref_sender->_ref_config_cda->assigning_cda_to_patient) {
                $target = $handle_factory->getPatient();
            }
            // Soit directement sur une trace pour un rattachement auto. / si on ne retrouve pas le patient
            if (!$target || !$target->_id) {
                // Create trace HUB Documentary
                $file_traceability = $this->createFileTraceability($parser);

                $this->getReport()->addData(
                    CCDAException::noTargetToSaveFile()->getMessage(),
                    CItemReport::SEVERITY_WARNING
                );

                // La target devient la trace
                $target = $file_traceability;
            }
        }

        // Create CDA file
        $file = new CFile();
        $idex = CIdSante400::getMatch(
            'CFile',
            $this->_ref_sender->_self_tag,
            self::CDA_PREFIX . $handle_factory->getMeta()->id
        );
        if ($idex->_id) {
            $file->load($idex->object_id);
        }
        $file->file_name = self::CDA_PREFIX . $this->getTitle() . '.' . self::CDA_EXTENSION;
        $file->setObject($target);
        $file->fillFields();
        $file->file_date    = $handle_factory->getMeta()->effectiveTime;
        $file->type_doc_dmp = $this->getTypeDoc();
        $file->file_type    = CMbPath::guessMimeType(null, self::CDA_EXTENSION);
        $file->private      = 0;
        $file->setContent($this->msg_xml);

        // dispatch before file store
        if ($handle_factory = $parser->getHandleObject()) {
            $handle_factory->dispatch($file, CCDAHandle::EVENT_BEFORE_STORE_FILE);
        }

        if ($msg = $file->store()) {
            unlink($file->_file_path);

            throw CCDAException::errorStoreCDAFile($msg);
        }

        // dispatch after file is stored
        if ($handle_factory = $parser->getHandleObject()) {
            $handle_factory->dispatch($file, CCDAHandle::EVENT_AFTER_STORE_FILE);
        }

        // On va créer la trace avec un lien vers le CFile
        if (!$file_traceability->_id) {
            $file_traceability = $this->createFileTraceability($parser, $file);
        }

        $this->report->addItemsStored($file);

        $idex->object_id = $file->_id;
        if ($msg = $idex->store()) {
            $this->report->addItemFailed($idex, $msg);
        }

        $this->report->addItemsStored($idex);

        // Update trace with file
        $this->updateFileTraceability($file_traceability, $file);
    }

    /**
     * Create file traceability
     *
     * @param CCDAParserFactory $parser Parser
     * @param CFile|null        $file   File
     *
     * @return void
     * @throws Exception
     */
    private function createFileTraceability(CCDAParserFactory $parser, CFile $file = null): CFileTraceability
    {
        $sender = $this->_ref_sender;

        $file_traceability                    = new CFileTraceability();
        $file_traceability->initiator         = CFileTraceability::INITIATOR_SERVER;
        $file_traceability->received_datetime = 'now';
        $file_traceability->user_id           = CMediusers::get()->_id;
        $file_traceability->actor_class       = $sender->_class;
        $file_traceability->actor_id          = $sender->_id;
        $file_traceability->group_id          = $sender->group_id;
        $file_traceability->source_name       = CFileTraceability::getSourceName($sender);
        $file_traceability->status            = "pending";
        if ($patient = $this->getPatientFromDocument()) {
            $file_traceability->NIR                   = $patient->_ins;
            $file_traceability->oid_nir               = $patient->_oid;
            $file_traceability->patient_name          = $patient->nom;
            $file_traceability->patient_birthname     = $patient->nom_jeune_fille;
            $file_traceability->patient_firstname     = $patient->prenom;
            $file_traceability->patient_date_of_birth = $patient->naissance;
            $file_traceability->patient_sexe          = $patient->sexe;
        }
        $file_traceability->datetime_object = $parser->getHandleObject()->getMeta()->effectiveTime;
        if (!$file || !$file->_id) {
            if ($msg = $file_traceability->store()) {
                $this->report->addItemFailed($file_traceability, $msg);
            }

            $this->report->addItemsStored($file_traceability);

            return $file_traceability;
        }

        $file_traceability->status = "auto";

        return $file_traceability;
    }

    /**
     * Get patient identifier
     *
     * @param DOMNode $node patientRole
     * @param string  $oid  OID
     *
     * @return string|null
     */
    public function getIdentifier(DOMNode $node, string $OID): ?string
    {
        return $this->queryAttributeNodeValue(
            "id[@root='$OID']",
            $node,
            "extension"
        );
    }

    /**
     * Update file traceability
     *
     * @param CCDAParserFactory $parser Parser
     * @param CFile             $file   File
     *
     * @return void
     * @throws Exception
     */
    private function updateFileTraceability(
        CFileTraceability $file_traceability,
        CFile $file
    ): void {
        $file_traceability->setObject($file);
        if ($msg = $file_traceability->store()) {
            $this->report->addItemFailed($file_traceability, $msg);
        }

        $this->report->addItemsStored($file_traceability);
    }

    /**
     * Store exchange
     *
     * @return void
     */
    private function storeExchange(): void
    {
        $exchange_cda                    = $this->_ref_exchange_cda;
        $exchange_cda->response_datetime = $exchange_cda->send_datetime = CMbDT::dateTime();
        $exchange_cda->store();
    }
}
