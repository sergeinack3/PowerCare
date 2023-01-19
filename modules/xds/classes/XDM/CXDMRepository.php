<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\XDM;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Interop\Cda\CCDARepository;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\Eai\CReport;
use Ox\Interop\Xds\CXDSRepository;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\CXDSAssociation;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Interop\Xds\Transformer\XDSTransformer;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

class CXDMRepository
{
    /** @var CCDARepository */
    private $repo_cda;

    /** @var CXDSRepository */
    private $repo_xds;

    /** @var CXDSSubmissionSet */
    private $submission_set;

    /** @var string */
    private $type_cda;

    /** @var string */
    private $type_xds;

    /** @var CMbObject */
    private $context;

    /** @var CFile */
    private $cda_file;

    public function __construct(CMbObject $context, string $type_cda, string $type_xds)
    {
        $this->type_cda = $type_cda;
        $this->type_xds = $type_xds;
        $this->context  = $context;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->repo_cda->hasErrors();
    }

    /**
     * @return CReport
     */
    public function getReport(): CReport
    {
        return $this->repo_cda->getReport();
    }

    /**
     * @return CXDSSubmissionSet
     */
    public function getSubmissionSet(): CXDSSubmissionSet
    {
        return $this->submission_set;
    }

    /**
     * @return CCDARepository
     */
    public function getRepoCda(): CCDARepository
    {
        return $this->repo_cda;
    }

    /**
     * @param array $options_cda
     * @param array $options_xds
     *
     * @return CFile|null
     * @throws CMbException
     */
    public function generateXDM(array $options_cda = [], array $options_xds = []): ?CFile
    {
        // generate CDA
        $this->repo_cda = $this->generateCDA($options_cda);

        // We use target defined by cda (CConsultAnesth ==> CConsultation)
        $this->context = $this->repo_cda->getFactory()->targetObject;

        // La validation du CDA
        // $repo_cda->validate();

        // En cas d'erreur sur le CDA, on ne va pas plus loin
        if ($this->repo_cda->hasErrors()) {
            return null;
        }

        // generate XDS
        $this->submission_set = $this->generateXDS($options_xds);

        // Création du ZIP IHE XDM
        return $this->createZip();
    }

    /**
     * @param array $options_cda
     *
     * @return CCDARepository
     * @throws CMbException
     * @throws Exception
     */
    private function generateCDA(array $options_cda): CCDARepository
    {
        $this->repo_cda = $repo = new CCDARepository($this->type_cda, $this->context);
        $repo->setOptions($options_cda);

        if (!$this->cda_file) {
            $repo->getContentCda();
        }

        return $repo;
    }

    /**
     * @param array $options_xds
     *
     * @return CXDSSubmissionSet
     * @throws CMbException
     * @throws CXDSException
     */
    private function generateXDS(array $options_xds): CXDSSubmissionSet
    {
        $cda_file = $this->getFileCDA();

        $submission_set = new CXDSSubmissionSet($this->type_xds);
        if ($receiver = CMbArray::get($options_xds, 'receiver')) {
            $submission_set->_receiver = $receiver;
        }
        $submission_set->fill($this->context);

        $document_entry      = CXDSDocumentEntry::fromDocument($cda_file);
        $document_entry->URI = $cda_file->file_name;

        $submission_set->addDocumentEntry($document_entry);
        $submission_set->addPatientId();

        // Remplacement de fichier
        if ($doc_uuid = CMbArray::get($options_xds, 'doc_uuid')) {
            $old_document = new CXDSDocumentEntry();
            $old_document->entryUUID = $doc_uuid;
            $submission_set->addAssociation($document_entry, $old_document, CXDSAssociation::TYPE_REPLACE);
        }

        return $submission_set;
    }

    /**
     * Set file CDA
     *
     * @param CFile $file_cda
     *
     * @return void
     */
    public function setFileCDA(CFile $file_cda): void
    {
        $this->cda_file = $file_cda;
    }

    /**
     * Return the cda file
     *
     * @return CFile
     * @throws CMbException
     */
    public function getFileCDA(): CFile
    {
        if (!$this->cda_file) {
            $cda_file = $this->repo_cda->getFileCDA();
            if (!$cda_file->object_class || !$cda_file->object_id || !$cda_file->file_name || !$cda_file->type_doc_dmp) {
                throw new CCDAException('CFile CDA incorrect');
            }

            $cda_file->file_name = $this->formatFilename($cda_file->file_name);

            // try to retrieve document was already exist with same name
            $file               = new CFile();
            $file->object_class = $cda_file->object_class;
            $file->object_id    = $cda_file->object_id;
            $file->file_name    = $cda_file->file_name;
            $file->type_doc_dmp = $cda_file->type_doc_dmp;
            $file->file_type    = $cda_file->file_type;
            $file->file_category_id = $cda_file->file_category_id;

            $file->loadMatchingObject();
            if ($file->_id) {
                $cda_file->_id = $file->_id;
                $cda_file->completeField();
            }

            // save CFile ==> submission lot
            if ($msg = $cda_file->store()) {
                $this->repo_cda->getReport()->addData(
                    "Impossible d'enregistrer le CDA : $msg",
                    CItemReport::SEVERITY_ERROR
                );
                throw new CMbException($msg);
            }

            $this->cda_file = $cda_file;
        }

        return $this->cda_file;
    }

    /**
     * @return CFile
     * @throws Exception
     */
    private function createZip(): CFile
    {
        $dir = CAppUI::getTmpPath("XDM_VSM-" . md5(uniqid("", true)));
        CMbPath::forceDir($dir);
        CMbPath::forceDir("$dir/IHE_XDM");
        CMbPath::forceDir("$dir/IHE_XDM/SUBSET01");

        // content CDA
        $file_cda = $this->getFileCDA();

        // content xds
        $xds_content = XDSTransformer::serialize($this->submission_set);

        // file perhaps don't stored but content is set on it
        $content = $file_cda->getContent() ?: $file_cda->getBinaryContent();
        file_put_contents("$dir/INDEX.HTM", $this->createFileIndex($this->context));
        file_put_contents("$dir/README.TXT", $this->createFileReadMe($this->context, $file_cda));
        file_put_contents("$dir/IHE_XDM/SUBSET01/METADATA.XML", $xds_content);
        foreach ($this->getSubmissionSet()->documents as $_document) {
            file_put_contents("$dir/IHE_XDM/SUBSET01/$_document->URI", $content);
        }

        $zip_path = "$dir.zip";
        CMbPath::zip($dir, $zip_path);

        CMbPath::remove($dir);

        $file_zip = new CFile();
        $context  = $this->context;
        if ($context instanceof CDocumentItem) {
            $context = $context->loadTargetObject();
        }
        $file_zip->setObject($context);
        $file_zip->file_name = "IHE_XDM.ZIP";
        $file_zip->file_type = "application/zip";
        $file_zip->loadMatchingObject();

        if (!$file_zip->_id) {
            $charset                      = array_merge(range('a', 'f'), range(0, 9));
            $filename_length              = CFile::FILENAME_LENGTH;
            $file_zip->file_real_filename = CMbSecurity::getRandomAlphaNumericString($charset, $filename_length);
        }
        $file_zip->file_date = "now";

        $file_zip->setContent(file_get_contents($zip_path));
        //$file_zip->doc_size = strlen(file_get_contents($zip_path));

        if ($msg = $file_zip->store()) {
            CAppUI::stepAjax("Erreur dans l'enregistrement du fichier", UI_MSG_ERROR);
        }

        return $file_zip;
    }

    /**
     * Format filename for ISO 9660 niveau 1
     *
     * @param string $filename
     * @param string $extension
     *
     * @return string
     */
    public function formatFilename(string $filename, string $default_ext = "xml"): string
    {
        $filename = CMbPath::getFilename(CMbString::removeDiacritics($filename));

        // keep extension
        if (!$extension = CMbPath::getExtension($filename)) {
            $extension = $default_ext;
        }
        $extension = strtoupper($extension);

        // delete spaces
        $filename  = str_replace(" ", '_', strtoupper($filename));

        // delete all special characters
        $filename  = preg_replace('/[^A-Za-z0-9\_]/', '', $filename);

        // keep only 8 first characters
        $filename  = substr($filename, 0, 8);

        // sanitize end of filename
        $filename  = rtrim($filename, '_');

        return "$filename.$extension";
    }

    /**
     * Generation du fichier Index.htm qui sera contenu dans le ZIP IHE_XDM
     *
     * @return string
     */
    private function createFileIndex(CMbObject $context)
    {
        if ($context instanceof CFile) {
            $context = $context->loadTargetObject();
        }

        $groups = null;
        if ($context instanceof CCodable) {
            $groups = $context->loadRefPraticien()->loadRefFunction()->loadRefGroup();
        }

        if (!$groups) {
            $groups = CGroups::loadCurrent();
        }

        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
      <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
      <html>
        Emetteur : $groups->_name ($groups->tel)<br><br>
        Voir le fichier <a href=\"README.TXT\">ReadMe</a>
      </html>";

        return $content;
    }

    /**
     * Generation du fichier ReadMe.txt qui sera contenu dans le ZIP IHE_XDM
     *
     * @param CMbObject $object object
     * @param CFile     $vsm    vsm
     *
     * @return string
     * @throws Exception
     */
    private function createFileReadMe(CMbObject $object, CFile $vsm)
    {
        if ($object instanceof CFile) {
            $object = $object->loadTargetObject();
        }

        $groups = $praticien = null;
        if ($object instanceof CCodable) {
            $groups    = $object->loadRefPraticien()->loadRefFunction()->loadRefGroup();
            $praticien = $object->loadRefPraticien();
        } elseif ($object instanceof CDocumentItem) {
            $praticien = $object->loadRefAuthor();
            if (($target = $object->loadTargetObject()) && $target instanceof CCodable) {
                $groups = $target->loadRefPraticien()->loadRefFunction()->loadRefGroup();
            }
        }

        if (!$groups) {
            $groups = CGroups::loadCurrent();
        }

        if (!$praticien) {
            $praticien = CMediusers::get();
        }

        $name    = CAppUI::conf("product_name");
        $version = CApp::getVersion()->toArray()["version"];

        $content = "
Emetteur :
========
	. Nom : $praticien->_view
	. Organisme : $groups->_name 
	. Adresse : $groups->adresse, $groups->cp $groups->ville
	. Téléphone : $groups->tel


Application de l'émetteur :
=========================
	. Nom : $name
	. Version : $version
	. Editeur : OpenXtrem

Instructions :
============
	. Consultez les fichiers reçus par messagerie sécurisée de santé dans votre logiciel de professionnel de santé.


Arborescence :
============
	README.TXT
	INDEX.HTM
	+ IHE_XDM
			+ SUBSET01
					METADATA.XML
					$vsm->file_name";

        return $content;
    }
}
