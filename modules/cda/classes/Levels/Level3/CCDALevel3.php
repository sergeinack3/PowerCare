<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level3;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

class CCDALevel3 extends CCDAFactory
{
    /** @var int */
    public const LEVEL = 3;

    /** @var string */
    public const NAME_DOC = '';

    /** @var string  */
    public const LANGUAGE = 'fr-FR';

    /** @var string  */
    public const CODE_LOINC = '';

    /** @var COperation|CConsultAnesth|CConsultation|CSejour */
    public $mbObject;

    /**
     * @return CConsultation|CSejour|COperation
     */
    protected function determineTarget(): CStoredObject
    {
        $target_object = $this->mbObject;
        if ($target_object instanceof CConsultAnesth) {
            $target_object = $target_object->loadRefConsultation();
        }

        return $target_object;
    }

    /**
     * @return CMediusers
     * @throws Exception
     */
    protected function determineAuthor(): CMediusers
    {
        return $this->practicien;
    }

    public function extractData()
    {
        // elements sould be declared before
        $this->version       = 1;
        $this->langage       = self::LANGUAGE;
        $this->date_creation = CMbDT::dateTime();
        $this->date_author   = 'now';

        // parent call
        parent::extractData();

        // elements should be declared after
        $this->mediaType = "application/xml";
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    protected function prepareCode(): array
    {
        // Par défaut, on prend les jeux de valeurs ASIP, DMP ou XDS
        if (!$this::CODE_LOINC) {
            throw new CCDAException('CCDAException-error-code loinc missing');
        }

        // Pour un CDA structuré avec un code LOINC
        return CLoinc::getTypeCode($this::CODE_LOINC);
    }

    /**
     * @return string
     */
    protected function prepareNom(): string
    {
        $path_exploded = explode('\\', get_class($this));

        return CAppUI::tr(end($path_exploded));
    }

    /**
     * @return string
     */
    protected function getConfidentiality(): string
    {
        return 'N'; // normal
    }

    /**
     * @param string $content_cda
     *
     * @return CFile
     * @throws Exception
     */
    protected function getFile(string $content_cda): CFile
    {
        $file = parent::getFile($content_cda);
        $file->file_name = $this::NAME_DOC;

        // Seulement un fichier par contexte
        $file->loadMatchingObject();

        $file->_file_name_cda = CAppUI::tr(get_class($this));
        $file->author_id      = CAppUI::$instance->user_id;

        // todo gestion ici des masquage patient / praticien & representants_legaux

        $file->fillFields();

        return $file;
    }
}
