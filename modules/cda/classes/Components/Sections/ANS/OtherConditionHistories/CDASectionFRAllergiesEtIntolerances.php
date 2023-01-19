<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories;

use Exception;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories\CDAEntryFRListeAllergiesEtIntolerances;
use Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories\CDASectionAllergiesAndOtherAdverseReactions;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Patients\CAntecedent;

class CDASectionFRAllergiesEtIntolerances extends CDASectionAllergiesAndOtherAdverseReactions
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.137';

    /** @var CAntecedent[] */
    protected $allergies = [];

    /**
     * CDAEntryFRAllergiesEtTolerances constructor.
     *
     * @param CCDAFactory $factory
     *
     * @throws Exception
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $patient         = $factory->patient;
        $dossier_medical = $patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return;
        }

        $allergies = $dossier_medical->loadRefsAntecedentsOfType("alle");

        // On ajoute que les antécédents qui ont une date de début et une date de fin
        $this->allergies = array_filter($allergies, function ($allergy) {
            return $allergy->date;
        });
    }

    /**
     * Entries - FR Liste des allergies et intolerances [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesAllergyIntoleranceConcern(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - FR Liste des allergies et intolerances [1..*]
        $entry = (new CDAEntryFRListeAllergiesEtIntolerances($this->factory, $this->allergies))->buildEntry();

        $section->appendEntry($entry);
    }

    /**
     * @param CCDAFactory $factory
     *
     * @return bool
     */
    public static function check(CCDAFactory $factory): bool
    {
        $dossier_medical = $factory->patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return false;
        }

        $allergies = $dossier_medical->loadRefsAntecedentsOfType("alle");

        /** @var CAntecedent $_allergy */
        foreach ($allergies as $_allergy) {
            // On ajoute que les antécédents qui ont une date de début et une date de fin
            if (!$_allergy->date) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '48765-2');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Allergies, effet indésirables, alertes');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $content = $this->fetchSmarty(
            'Components/Sections/ANS/OtherConditionHistories/fr_allergies_et_intolerances',
            [
                'allergies' => $this->allergies,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}
