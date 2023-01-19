<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level3\ANS;


use Exception;
use Ox\Core\CMbObject;
use Ox\Interop\Cda\Documents\CCDADocumentCDA;
use Ox\Interop\Cda\Documents\CCDADocumentLDLEES;

/**
 * Class CCDALdlEes
 *
 * @package Ox\Interop\Cda\Levels\Level3\ANS
 */
class CCDALdlEes extends CCDAANS
{
    /** @var string */
    public const TYPE = self::TYPE_LDL_EES;

    /** @var string */
    public const TYPE_DOC = '2.16.840.1.113883.6.1^18761-7';

    /** @var string */
    public const CODE_JDV = 'urn:asip:ci-sis:ldl-ees:2017';

    /** @var string */
    public const NAME_DOC = 'LDLEES.XML';

    /** @var string */
    public const CODE_LOINC = '18761-7';

    /**
     * CCDALdlEes constructor.
     *
     * @param CMbObject $mbObject
     */
    public function __construct(CMbObject $mbObject)
    {
        parent::__construct($mbObject);
    }

    public function extractData()
    {
        parent::extractData();

        // LDL-EES templated ID
        $this->templateId[] = $this->createTemplateID('1.2.250.1.213.1.1.1.21');
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
        return new CCDADocumentLDLEES($this);
    }
}
