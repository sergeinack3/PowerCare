<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level3\ANS;

use Exception;
use Ox\Interop\Cda\Documents\CCDADocumentCDA;
use Ox\Interop\Cda\Documents\CCDADocumentLDLSES;
use Ox\Interop\Cda\Handle\CCDAHandle;
use Ox\Interop\Cda\Handle\Level3\ANS\CCDAHandleCRLDLSES;

/**
 * Class CCDALdlSes
 *
 * @package Ox\Interop\Cda\Levels\Level3\ANS
 */
class CCDALdlSes extends CCDAANS
{
    /** @var string */
    public const TYPE = self::TYPE_LDL_SES;

    /** @var string */
    public const TYPE_DOC = '2.16.840.1.113883.6.1^11490-0';

    /** @var string */
    public const CODE_JDV = 'urn:asip:ci-sis:ldl-ses:2017';

    /** @var string */
    public const NAME_DOC = 'LDLSES.XML';

    /** @var string */
    public const CODE_LOINC = '11490-0';

    /**
     * @return CCDAHandleCRLDLSES
     */
    public function getHandle(): ?CCDAHandle
    {
        return new CCDAHandleCRLDLSES();
    }

    public function extractData()
    {
        parent::extractData();

        // LDL-SES templated ID
        $this->templateId[] = $this->createTemplateID('1.2.250.1.213.1.1.1.29', '2020.01');
    }

    /**
     * @return array
     *
     * @throws Exception
     */
    protected function prepareServiceEvent(): array
    {
        $service = parent::prepareServiceEvent();
        $service["type_code"]  = $this::TYPE;
        $service["code"]       = 'IMP';
        $service["nullflavor"] = "";
        $service["oid"]        = '2.16.840.1.113883.5.4';
        $service["libelle"]    = 'Hospitalisation';

        return $service;
    }

    /**
     * @return CCDADocumentCDA
     */
    public function getDocumentCDA(): CCDADocumentCDA
    {
        return new CCDADocumentLDLSES($this);
    }
}
