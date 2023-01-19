<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories;

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\Transversaux\CDAEntryFRSimpleObservation;
use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Interop\InteropResources\valueset\CANSValueSet;

class CDASectionFRResultatsEvenementsLDLSES extends CDASectionFRResultatsEvenements
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.163.1';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesSimpleObservations(CCDAPOCD_MT000040_Section $section): void
    {
        // Entry - FR Modalite Entree [1..1]
        $this->setEntryFRModaliteEntree($section);

        // Entry - FR Modalite Sortie [1..1]
        $this->setEntryFRModaliteSortie($section);

        // Entry - FR Synthèse médicale du séjour [1..1]
        $this->setEntryFRSyntheseMedicale($section);

        // Entry - FR Evenement Indesirable Pendant Hospitalisation [0..1]
        $this->setEntryFREvenementIndesirable($section);

        // Entry - FR Recherche-de-micro organismes s multirésistants ou émergents effectuée [1..1]
        $this->setEntryFRRechercheMicroOrganismes($section);

        // Entry - FR Identification de micro organismes multirésistants [0..1]
        $this->setEntryFRTransfusionProduitSanguins($section);

        // Entry - FR Accidents Transfusionnels [0..1]
        $this->setEntryFRAccidentTransfusionnel($section);

        // Entry - FR Administration de Dérivés du Sang [1..1]
        $this->setEntryFRAdministrationDerivesSang($section);

        // Entry - FR Evenements Indésirable suite à l'Administration de dérivés du sang [0..1]
        $this->setEntryFREvtIndeAdministrationDerivesSang($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntriesPatientTransfer(CCDAPOCD_MT000040_Section $section): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntriesProblems(CCDAPOCD_MT000040_Section $section): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Synthèse médicale du séjour');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $sejour = $this->factory->targetObject;

        $content = $this->fetchSmarty(
            'Components/Sections/ANS/OtherConditionHistories/fr_resultats_evenements_ldlses',
            [
                'sejour' => $sejour,
            ]
        );

        CCDADocTools::setText($section, $content);
    }

    /**
     * Entry - FR Modalite Entree [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFRModaliteEntree(CCDAPOCD_MT000040_Section $section): void
    {
        $sejour             = $this->factory->targetObject;
        $values_mode_entree = CANSValueSet::loadEntries(
            'modaliteEntree',
            $sejour->mode_entree ? CMbArray::get(
                CCDAFactory::$mapping_mode_entree_jdv,
                $sejour->mode_entree
            ) : 'GEN-092',
        );
        $options            = [];

        // Code
        $options['code_CD'] = CCDADocTools::prepareCodeCD(
            'ORG-070',
            '1.2.250.1.213.1.1.4.322',
            'Modalité d\'entrée',
            CCDAFactory::TA_ASIP
        );

        // Status code
        $options['status_code'] = 'completed';

        // Code value
        $options['code_value'] = CCDADocTools::prepareCodeCE(
            CMbArray::get($values_mode_entree, 'code'),
            CMbArray::get($values_mode_entree, 'codeSystem'),
            CMbArray::get($values_mode_entree, 'displayName'),
            CCDAFactory::TA_ASIP
        );

        // Entry FR-Modalite-entree
        $simple_observation = new CDAEntryFRSimpleObservation($this->factory, $options);
        // Conformity FR-Modalite-entree
        $simple_observation->addTemplateIds('1.2.250.1.213.1.1.3.48.6');

        // Text Reference
        $simple_observation->addText("#" . CCDAFactory::MODALITE_ENTREE, true);

        // EffectiveTime
        $simple_observation->addEffectiveTime(CMbDT::format($sejour->entree, CMbDT::ISO_DATE));

        // build Entry
        $section->appendEntry($simple_observation->buildEntry());
    }


    /**
     * Entry - FR Modalite Sortie [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFRModaliteSortie(CCDAPOCD_MT000040_Section $section): void
    {
        $sejour             = $this->factory->targetObject;
        $values_mode_sortie = CANSValueSet::loadEntries(
            'modaliteSortie',
            $sejour->mode_sortie ? CMbArray::get(
                CCDAFactory::$mapping_mode_sortie_jdv,
                $sejour->mode_sortie
            ) : 'GEN-092',
        );
        $options            = [];

        // Code
        $options['code_CD'] = CCDADocTools::prepareCodeCD(
            'ORG-074',
            '1.2.250.1.213.1.1.4.322',
            'Modalité de sortie',
            CCDAFactory::TA_ASIP
        );

        // Status code
        $options['status_code'] = 'completed';

        // Code value
        $options['code_value'] = CCDADocTools::prepareCodeCE(
            CMbArray::get($values_mode_sortie, 'code'),
            CMbArray::get($values_mode_sortie, 'codeSystem'),
            CMbArray::get($values_mode_sortie, 'displayName'),
            CCDAFactory::TA_ASIP
        );

        // Entry FR-Modalite-sortie
        $simple_observation = new CDAEntryFRSimpleObservation($this->factory, $options);
        // Conformity FR-Modalite-sortie
        $simple_observation->addTemplateIds('1.2.250.1.213.1.1.3.48.7');

        // Text Reference
        $simple_observation->addText("#" . CCDAFactory::MODALITE_SORTIE, true);

        // EffectiveTime
        $simple_observation->addEffectiveTime(CMbDT::format($sejour->sortie, CMbDT::ISO_DATE));

        // build Entry
        $section->appendEntry($simple_observation->buildEntry());
    }


    /**
     * Entry - FR Synthèse médicale du séjour [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFRSyntheseMedicale(CCDAPOCD_MT000040_Section $section): void
    {
        $sejour  = $this->factory->targetObject;
        $options = [];

        // Code
        $options['code_CD'] = CCDADocTools::prepareCodeCD(
            'MED-142',
            '1.2.250.1.213.1.1.4.322',
            'Synthèse médicale',
            CCDAFactory::TA_ASIP
        );

        // Status code
        $options['status_code'] = 'completed';

        // Code value (Textuelle value)
        $text_value = new CCDAST();
        $text_value->setData($sejour->libelle . ". " . $sejour->rques);
        $options['code_value'] = $text_value;

        // Entry FR-Synthese-medicale-sejour
        $simple_observation = new CDAEntryFRSimpleObservation($this->factory, $options);
        // Conformity FR-Synthese-medicale-sejour
        $simple_observation->addTemplateIds('1.2.250.1.213.1.1.3.48.9');

        // Text Reference
        $simple_observation->addText("#" . CCDAFactory::SYNTHESE, true);

        // EffectiveTime
        $simple_observation->addEffectiveTime(CMbDT::date());

        // build Entry
        $section->appendEntry($simple_observation->buildEntry());
    }


    /**
     * Entry - FR Recherche-de-micro organismes s multirésistants ou émergents effectuée [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFRRechercheMicroOrganismes(CCDAPOCD_MT000040_Section $section): void
    {
        // Todo a dev
        $options = [];

        // Code
        $options['code_CD'] = CCDADocTools::prepareCodeCD(
            'MED-309',
            '1.2.250.1.213.1.1.4.322',
            'Recherche de microorganismes multi-résistants ou émergents effectuée',
            CCDAFactory::TA_ASIP
        );

        // Status code
        $options['status_code'] = 'completed';

        // Code value (BL)
        $text_value = new CCDABL();
        $text_value->setValue('false');
        $options['code_value'] = $text_value;

        // Entry FR-Recherche-de-micro-organismes
        $simple_observation = new CDAEntryFRSimpleObservation($this->factory, $options);
        // Conformity FR-Recherche-de-micro-organismes
        $simple_observation->addTemplateIds('1.2.250.1.213.1.1.3.48.8');

        // Text Reference
        $simple_observation->addText("#" . CCDAFactory::RECHERCHE_MICRO_MULTI, true);

        // EffectiveTime
        $simple_observation->addEffectiveTime(CMbDT::date());

        // build Entry
        $section->appendEntry($simple_observation->buildEntry());
    }

    /**
     * Entry - FR Identification de micro organismes multirésistants [0..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFRTransfusionProduitSanguins(CCDAPOCD_MT000040_Section $section): void
    {
        // Todo a dev
        $options = [];

        // Code
        $options['code_CD'] = CCDADocTools::prepareCodeCD(
            'MED-145',
            '1.2.250.1.213.1.1.4.322',
            'Transfusion de produits sanguins',
            CCDAFactory::TA_ASIP
        );

        // Status code
        $options['status_code'] = 'completed';

        // Code value (BL)
        $text_value = new CCDABL();
        $text_value->setValue('false');
        $options['code_value'] = $text_value;

        // Entry FR-Transfusion-de-produits-sanguins
        $simple_observation = new CDAEntryFRSimpleObservation($this->factory, $options);
        // Conformity FR-Transfusion-de-produits-sanguins
        $simple_observation->addTemplateIds('1.2.250.1.213.1.1.3.48.10');

        // Text Reference
        $simple_observation->addText("#" . CCDAFactory::TRANSFU, true);

        // EffectiveTime
        $simple_observation->addEffectiveTime(CMbDT::date());

        // build Entry
        $section->appendEntry($simple_observation->buildEntry());
    }


    /**
     * Entry - FR Accidents Transfusionnels [0..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFRAccidentTransfusionnel(CCDAPOCD_MT000040_Section $section): void
    {
        // Todo a dev
        $options = [];

        // Code
        $options['code_CD'] = CCDADocTools::prepareCodeCD(
            'MED-147',
            '1.2.250.1.213.1.1.4.322',
            'Administration de dérivés du sang',
            CCDAFactory::TA_ASIP
        );

        // Status code
        $options['status_code'] = 'completed';

        // Code value (BL)
        $text_value = new CCDABL();
        $text_value->setValue('false');
        $options['code_value'] = $text_value;

        // Entry FR-Accidents-transfusionnels
        $simple_observation = new CDAEntryFRSimpleObservation($this->factory, $options);
        // Conformity FR-Accidents-transfusionnels
        $simple_observation->addTemplateIds('1.2.250.1.213.1.1.3.48.2');

        // Text Reference
        $simple_observation->addText("#" . CCDAFactory::ADMI_SANG, true);

        // EffectiveTime
        $simple_observation->addEffectiveTime(CMbDT::date());

        // build Entry
        $section->appendEntry($simple_observation->buildEntry());
    }

    /**
     * Entry - FR Administration de Dérivés du Sang [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFRAdministrationDerivesSang(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * Entry - FR Evenements Indésirable suite à l'Administration de dérivés du sang [0..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFREvtIndeAdministrationDerivesSang(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * Entry - FR Evenement Indesirable Pendant Hospitalisation [0..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntryFREvenementIndesirable(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
