<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\RelevantStudies;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\Components\Sections\IHE\RelevantStudies\CDASectionResults;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionFRResultatsExamensNonCode extends CDASectionResults
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.150';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '30954-2');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Points de vigilance');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $patient = $this->factory->patient;
        $dossier_medical = $patient->loadRefDossierMedical();

        $content = $this->fetchSmarty(
            'Components/Sections/ANS/RelevantStudies/fr_resultats_examens_non_code',
            [
                'dossier_medical' => $dossier_medical,
            ]
        );

        CCDADocTools::setText($section, $content);

        $point_vigilance = $dossier_medical->_id && $dossier_medical->points_attention ? $dossier_medical->points_attention : 'Aucun point de vigilance pour le patient';

        $text_content = "<table><tbody><tr><td>$point_vigilance</td></tr></tbody></table>";
        CCDADocTools::setText($section, $text_content);
    }
}

