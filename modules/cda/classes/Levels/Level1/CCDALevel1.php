<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level1;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\CCdaTools;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Handle\CCDAHandleLevel1;
use Ox\Interop\Xds\Factory\CXDSFactory;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

class CCDALevel1 extends CCDAFactory
{
    /** @var int */
    public const LEVEL = 1;

    /** @var CFile|CCompteRendu */
    public $mbObject;

    /** @var string */
    public $file_path;

    /**
     * @return CCDAHandleLevel1
     */
    public function getHandle(): ?CCDAHandle
    {
        return new CCDAHandleLevel1();
    }

    /**
     * @return CSejour|CConsultation|COperation
     * @throws Exception
     */
    protected function determineTarget(): CStoredObject
    {
        $classes_available = [CSejour::class, CConsultation::class, COperation::class];

        $target_object = $this->mbObject->loadTargetObject();
        // File -> CompteRundu -> Codable
        if ($target_object instanceof CCompteRendu) {
            $target_object = $target_object->loadTargetObject();
        }

        // when ccompte rendu antesth
        if ($target_object instanceof CConsultAnesth) {
            $target_object = $target_object->loadRefConsultation();
        }

        if (!in_array(get_class($target_object), $classes_available)) {
            throw CCDAException::invalidCoherenceFactoryParameters();
        }

        return $target_object;
    }

    /**
     * @return CMediusers
     * @throws Exception
     */
    protected function determineAuthor(): CMediusers
    {
        $author = $this->mbObject->loadRefAuthor();
        if (!$author->isPraticien()) {
            $author = $this->practicien;
        }

        return $author;
    }

    /**
     * @return null[]
     */
    protected function prepareServiceEvent(): array
    {
        $target_object = $this->targetObject;
        $service       = [
            "nullflavor" => null,
            "executant"  => $target_object->loadRefPraticien(),
        ];

        switch (get_class($target_object)) {
            case CSejour::class:
                /** @var CSejour $target_object CSejour */
                $service["time_start"] = $target_object->entree;
                $service["time_stop"]  = $target_object->sortie;
                break;
            case CConsultation::class:
                /** @var CConsultation $target_object */
                $service["time_start"] = $target_object->_datetime;
                $service["time_stop"]  = $target_object->_date_fin;
                break;

            case COperation::class:
                /** @var COperation $target_object */
                $service["time_start"] = $target_object->_datetime_best;
                $service["time_stop"]  = $target_object->_datetime_reel_fin;
                break;
        }

        return $service;
    }

    /**
     * @throws CMbException
     */
    public function extractData()
    {
        // elements should be declared before
        $doc_item      = $this->mbObject;
        $this->version = $doc_item->_version;
        $this->langage = $doc_item->language;
        //Récupération du dernier log qui correspond à la date de création de cette version
        $last_log            = $doc_item->loadLastLog();
        $this->date_creation = $last_log->date;
        $this->date_author   = $last_log->date;

        // parent call
        parent::extractData();

        // elements should be declared after

        //Confirmité IHE XSD-SD => contenu non structuré
        $this->templateId[] = $this->createTemplateID("1.3.6.1.4.1.19376.1.2.20", "IHE XDS-SD");

        //Génération du PDF
        $this->generatePdf();
    }

    /**
     * @throws CMbException
     */
    protected function generatePdf(): void
    {
        if ($this->mbObject instanceof CFile) {
            [$path, $mediaType] = $this->generatePdfFromFile();
        } else {
            [$path, $mediaType] = $this->generatePdfFromCompteRendu();
        }

        $this->file_path = $path;
        $this->mediaType = $mediaType;
    }

    /**
     * @throws CMbException
     */
    private function generatePdfFromFile(): array
    {
        /** @var CFile $docItem */
        $docItem   = $this->mbObject;
        $path      = $docItem->_file_path;
        $mediaType = $docItem->file_type;
        switch ($docItem->file_type) {
            case "application/pdf":
                $path = CCdaTools::generatePDFA($docItem->_file_path);
                break;
            case "image/jpeg":
            case "image/jpg":
                $mediaType = "image/jpeg";
                break;
            case "application/rtf":
                $mediaType = "text/rtf";
                break;
            default:
                if ($this::TYPE === self::TYPE_DMP || $this::TYPE === self::TYPE_ZEPRA) {
                    $type = $this::TYPE;
                    throw new CMbException("$type-msg-Document type authorized in $type|pl");
                } else {
                    throw new CMbException("XDS-msg-Document type authorized in XDS|pl");
                }
        }

        return [$path, $mediaType];
    }

    /**
     * @return array
     * @throws CMbException
     */
    private function generatePdfFromCompteRendu(): array
    {
        /** @var CCompteRendu $compte_rendu */
        $compte_rendu = $this->mbObject;
        if ($msg = $compte_rendu->makePDFpreview(1, 0)) {
            throw new CMbException($msg);
        }
        $file = $compte_rendu->_ref_file;
        $path = CCdaTools::generatePDFA($file->_file_path);

        return [$path, 'application/pdf'];
    }

    /**
     * @param CXDSFactory $xds
     */
    public function initializeXDS(CXDSFactory $xds): void
    {
        parent::initializeXDS($xds);

        $xds->repositoryUniqueId = true;
    }

    /**
     * @return string
     */
    protected function prepareNom(): string
    {
        $docItem = $this->mbObject;
        if ($docItem instanceof CCompteRendu) {
            return $docItem->nom;
        }

        /** @var CFile $docItem */
        if (isset($docItem->_file_name_cda) && $docItem->_file_name_cda) {
            return $docItem->_file_name_cda;
        }

        return CMbPath::getFilename($docItem->file_name);
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function prepareCode(): array
    {
        $docItem = $this->mbObject;
        $type    = [];
        if ($docItem->type_doc_dmp) {
            $type = explode("^", $docItem->type_doc_dmp);
        }

        // Par défaut, on prend les jeux de valeurs ASIP, DMP ou XDS
        return $this->valueset_factory::getTypeCode(CMbArray::get($type, 1));
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getConfidentiality(): string
    {
        return $this->mbObject->private ? "R" : "N";
    }

    /**
     * @param string $content_cda
     * @param int    $file_category_id
     *
     * @return CFile
     * @throws Exception
     */
    protected function getFile(string $content_cda): CFile
    {
        $file                                = parent::getFile($content_cda);
        $filename                            = $this->mbObject instanceof CFile
            ? CMbPath::getFilename($this->mbObject->file_name)
            : $this->mbObject->nom;
        $file->file_name                     = "$filename.xml";
        $file->_file_name_cda                = CAppUI::tr(get_class($this));
        $file->author_id                     = CAppUI::$instance->user_id;
        $file->type_doc_dmp                  = $this->mbObject->type_doc_dmp;
        $file->masquage_patient              = $this->mbObject->masquage_patient;
        $file->masquage_praticien            = $this->mbObject->masquage_praticien;
        $file->masquage_representants_legaux = $this->mbObject->masquage_representants_legaux;
        $file->fillFields();

        return $file;
    }
}
