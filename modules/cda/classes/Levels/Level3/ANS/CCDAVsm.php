<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level3\ANS;

use Exception;
use Ox\Interop\Cda\Documents\CCDADocumentCDA;
use Ox\Interop\Cda\Documents\CCDADocumentVSM;
use Ox\Mediboard\Loinc\CLoinc;

/**
 * Class CCDAVsm
 *
 * @package Ox\Interop\Cda\Levels\Level3\ANS
 */
class CCDAVsm extends CCDAANS
{
    /** @var string */
    public const TYPE = self::TYPE_VSM;

    /** @var string */
    public const TYPE_DOC = '1.2.250.1.213.1.1.4.12^SYNTH';

    /** @var string */
    public const CODE_JDV = 'urn:asip:ci-sis:vsm:2012';

    /** @var string */
    public const NAME_DOC = 'SYNTH.XML';

    /** @var string */
    public const CODE_LOINC = 'SYNTH';



    /**
     * @return array
     * @throws Exception
     */
    protected function prepareCode(): array
    {
        return $this->valueset_factory::getTypeCode(self::CODE_LOINC);
    }

    public function extractData()
    {
        parent::extractData();

        // VSM templated ID
        $this->templateId[] = $this->createTemplateID('1.2.250.1.213.1.1.1.13', 'Synthèse médicale');
    }

    /**
     * Dans le cas du CDA VSM, le code dans documentationOf/serviceEvent est fixe Code LOINC : 34117-2
     *
     * @return array
     *
     * @throws Exception
     */
    public function prepareServiceEvent(): array
    {
        $service = parent::prepareServiceEvent();

        $service["type_code"]  = $this::TYPE;
        $service["code"]       = CLoinc::$code_vsm;
        $service["nullflavor"] = "";
        $service["oid"]        = CLoinc::$oid_loinc;
        // On fixe le libellé en dur parce que le code LOINC est utilisé avec 2 libelles différents
        $service["libelle"] = 'Historique et clinique';

        return $service;
    }

    /**
     * @return string
     */
    protected function prepareNom(): string
    {
        return $this::NAME_DOC;
    }

    /**
     * @return CCDADocumentCDA
     */
    public function getDocumentCDA(): CCDADocumentCDA
    {
        return new CCDADocumentVSM($this);
    }
}
