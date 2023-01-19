<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level3\ANS;

use Exception;
use Ox\Interop\Cda\Documents\CCDADocumentCDA;
use Ox\Interop\Cda\Documents\CCDADocumentVaccinationNote;
use Ox\Mediboard\Loinc\CLoinc;

/**
 * Class CCDAVsm
 *
 * @package Ox\Interop\Cda\Levels\Level3\ANS
 */
class CCDAVaccinationNote extends CCDAANS
{
    /** @var string */
    public const TYPE = self::TYPE_VACCINATION_NOTE;

    /** @var string */
    public const TYPE_DOC = '2.16.840.1.113883.6.1^87273-9';

    /** @var string */
    public const CODE_JDV = 'urn:asip:ci-sis:vac-note:2021';

    /** @var string */
    public const NAME_DOC = 'VAC-NOTE.XML';

    /** @var string */
    public const CODE_LOINC = '87273-9';

    public function extractData()
    {
        parent::extractData();

        // conformité PCC Immunization Content
       $this->templateId[] =  $this->createTemplateID('1.3.6.1.4.1.19376.1.5.3.1.1.18.1.2');

        // conformité Note de vaccination
        $this->templateId[] =  $this->createTemplateID('1.2.250.1.213.1.1.1.46', '2021.01');
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function prepareCode(): array
    {
        return $this->valueset_factory::getTypeCode(self::CODE_LOINC);
    }

    /**
     * Dans le cas du CDA VACCINATION-NOTE, le code dans documentationOf/serviceEvent est fixe Code LOINC : 87273-9
     *
     * @return array
     *
     * @throws Exception
     */
    public function prepareServiceEvent(): array
    {
        $service = parent::prepareServiceEvent();

        $service["type_code"]  = $this::TYPE;
        $service["code"]       = self::CODE_LOINC;
        $service["nullflavor"] = "";
        $service["oid"]        = CLoinc::$oid_loinc;
        $service["libelle"]    = $this->prepareNom();

        return $service;
    }

    /**
     * @return CCDADocumentCDA
     */
    public function getDocumentCDA(): CCDADocumentCDA
    {
        return new CCDADocumentVaccinationNote($this);
    }
}
