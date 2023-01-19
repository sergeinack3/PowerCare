<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\CDAEntryOrganizer;
use Ox\Interop\Cda\Components\Sections\ANS\SousSections\CDASectionFRFacteursDeRisques;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Patients\CAntecedent;

class CDASectionFRAntecedentsFamiliaux extends CDASectionFRFacteursDeRisques
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.15';

    /** @var CAntecedent[] */
    protected $antecedents = [];

    /**
     * CDASectionFRAntecedentsMedicaux constructor.
     *
     * @param CCDAFactory $factory
     *
     * @throws \Exception
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $patient         = $this->factory->patient;
        $dossier_medical = $patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return;
        }

        /** @var CAntecedent[] $antecedents */
        $antecedents = $dossier_medical->loadRefsAntecedentsOfType("fam");

        $antecedents_ok = [];

        foreach ($antecedents as $_antecedent) {
            if (!$_antecedent->date || !$_antecedent->family_link) {
                continue;
            }

            if (!$_antecedent->loadRefsCodesSnomed()) {
                continue;
            }
            $antecedents_ok[$_antecedent->family_link][] = $_antecedent;
        }

        $this->antecedents = $antecedents_ok;
    }

    /**
     * Build all entries of section
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        foreach ($this->antecedents as $_family_link => $_antecedents_by_family) {
            $options            = [];

            // Status code
            $options['status_code'] = 'completed';

            // Entry Organizer
            $organizer = new CDAEntryOrganizer($this->factory, $_family_link, $_antecedents_by_family, $options);
            $organizer->addTemplateIds('2.16.840.1.113883.10.20.1.23');
            $organizer->addTemplateIds('1.3.6.1.4.1.19376.1.5.3.1.4.15');
            $organizer->addTemplateIds('1.2.250.1.213.1.1.3.59');

            // build Entry
            $section->appendEntry($organizer->buildEntry());
        }
    }

    /**
     * Entries - FR Liste des problemes [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntryProblemConcern(CCDAPOCD_MT000040_Section $section): void
    {
    }

    public static function check(CCDAFactory $factory): bool
    {
        $dossier_medical = $factory->patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return false;
        }

        /** @var CAntecedent $_antecedent */
        $antecedents = $dossier_medical->loadRefsAntecedentsOfType("fam");
        foreach ($antecedents as $_antecedent) {
            if (!$_antecedent->date) {
                continue;
            }

            if (!$_antecedent->family_link) {
                continue;
            }

            // Récupération des codes Snomed sur l'antécédent (on prend le premier code Snomed) => si on en a pas, next
            $_antecedent->loadBackRefs("atcd_snomed");
            if (!$_antecedent->loadRefsCodesSnomed()) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '10157-6');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Antécédents familiaux');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $content = $this->fetchSmarty(
            'Components/Sections/ANS/OtherConditionHistories/fr_antecedent_familiaux',
            [
                'antecedents' => $this->antecedents,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}

