<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\Medications;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\Product\CDAEntryFRProduitDeSante;
use Ox\Interop\Cda\Components\Entries\IHE\Medications\CDAEntryMedications;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_PQ;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAPIVL_TS;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Consumable;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_SubstanceAdministration;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Patients\CDossierMedical;

/**
 * Class CDAEntryFRTraitement
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\Medications
 */
class CDAEntryFRTraitement extends CDAEntryMedications
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.42';

    /** @var CPrescriptionLineMedicament|null */
    protected $prescription_line;

    /** @var CDossierMedical */
    protected $dossier_medical;

    /**
     * CDAEntryFRTraitement constructor.
     *
     * @param CCDAFactory                 $factory
     * @param CPrescriptionLineMedicament $prescription_line
     */
    public function __construct(CCDAFactory $factory, ?CPrescriptionLineMedicament $prescription_line)
    {
        parent::__construct($factory);

        if ($prescription_line) {
            $prescription_line->loadRefsPrises();
        }

        // set lines
        $this->prescription_line = $prescription_line;

        // Ajout du templateId en fonction du mode d'administration
        $this->addTemplateIds("1.3.6.1.4.1.19376.1.5.3.1.4.7.1");

        $this->dossier_medical = $this->factory->patient->loadRefDossierMedical();
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     *
     * @return void
     */
    protected function buildContent(CCDAClasseCda $substanceAdministration): void
    {
        // set code entry
        $this->entry->setTypeCode("DRIV");

        // Ajout substanceAdministration
        $substanceAdministration->setClassCode();
        $substanceAdministration->setMoodCode("EVN");

        // EffectiveTime (début et fin de traitement)
        $this->setEffectiveTime($substanceAdministration);

        // EffectiveTime (fréquence d'administration) and Dose quantity
        $this->setDoseAndFrequenceAdministration($substanceAdministration);

        // EntryRelationship - FR Produit de santé [1..1]
        $this->buildConsumable($substanceAdministration);
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    protected function setEffectiveTime(CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration): void
    {
        $prescription_line = $this->prescription_line;
        // EffectiveTime (début et fin de traitement)
        if (!$prescription_line) {
            // EffectiveTime (début et fin de traitement)
            CCDADocTools::addLowAndHighTime($substanceAdministration, null, null, 'NA', 'NA');

            return;
        }

        if ($prescription_line->debut && $prescription_line->fin && $prescription_line->debut == $prescription_line->fin) {
            // Pour une prise ponctuelle (date début == date fin), il faut mettre la date dans <low> et 'NA' dans <high>
            CCDADocTools::addLowAndHighTime($substanceAdministration, $prescription_line->debut, null, null, 'NA');
        } else {
            CCDADocTools::addLowAndHighTime(
                $substanceAdministration,
                $prescription_line->debut,
                $prescription_line->fin,
                !$prescription_line->debut ? 'UNK' : null,
                !$prescription_line->fin ? 'UNK' : null
            );
        }
    }

    /**
     * Add frequency administration
     *
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration element
     * @param CPrescriptionLineMedicament               $prescription_line       prescription line
     *
     * @return void
     */
    private function setDoseAndFrequenceAdministration(
        CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
    ): void {
        if (!$prescription_line = $this->prescription_line) {
            return;
        }

        $frequence          = false;
        $moment_nb          = 0;
        $frequence_nb_fois  = 0;
        $frequence_quantity = 0;
        $unite_frequence    = "";
        $unit               = null;
        foreach ($prescription_line->_ref_prises as $_prise) {
            if ($_prise->unite_fois) {
                $frequence = true;

                $frequence_nb_fois  = $frequence_nb_fois + $_prise->nb_fois;
                $frequence_quantity = $frequence_quantity + $_prise->quantite;
                $unite_frequence    = $_prise->unite_fois;
            } else {
                $moment_nb = $moment_nb + $_prise->quantite;
            }

            // On récupère l'unité de la dernière poso (c'est sensé être la même pour toutes les posologies)
            $unit = $_prise->_libelle_unite_prescription;
        }

        $pivlTs = new CCDAPIVL_TS();
        // On met à la position 1 pour pouvoir valuer correctement le xsi:type lors de la génération du XML
        $pivlTs->position = 1;

        // Si on a la date de début, on met la phase
        if ($prescription_line->debut) {
            $ivlTs = new CCDAIVL_TS();

            // Low time
            $ivxbL = new CCDAIVXB_TS();
            $ivxbL->setValue($prescription_line->debut);
            $ivlTs->setLow($ivxbL);

            $pivlTs->setPhase($ivlTs);
        }

        // Ajout de la periode
        $cdaPQ = new CCDAPQ();

        if ($frequence) {
            $cdaPQ->setValue($frequence_nb_fois);
            $cdaPQ->setUnit($unite_frequence == "semaine" ? "wk" : "d");
        } else {
            $cdaPQ->setValue($moment_nb);
            $cdaPQ->setUnit("d");
        }
        $pivlTs->setPeriod($cdaPQ);

        $pivlTs->setOperator("A");
        $substanceAdministration->appendEffectiveTime($pivlTs);

        // Ajout de la dose
        $ivlPQ    = new CCDAIVL_PQ();
        $min_dose = $max_dose = $frequence ? $frequence_quantity : $moment_nb;

        if ($unit) {
            if ($unit == "comprimés") {
                // basé sur UCUM
                $unit = "{tbl}";
            } // pour les unités dénombrables => pas d'unité à mettre
            elseif ($unit != "ml" || $unit != "mg") {
                $unit = null;
            }
        }

        // Low dose and unit
        $ivxbL = new CCDAIVXB_PQ();
        $ivxbL->setValue($min_dose);
        $ivxbL->setUnit($unit);
        $ivlPQ->setLow($ivxbL);

        // High dose and unit
        $ivxbL = new CCDAIVXB_PQ();
        $ivxbL->setValue($max_dose);
        $ivxbL->setUnit($unit);
        $ivlPQ->setHigh($ivxbL);

        $substanceAdministration->setDoseQuantity($ivlPQ);
    }

    /**
     * EntryRelationship - FR Produit de santé [1..1]
     *
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    private function buildConsumable(CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration): void
    {
        $consumable = new CCDAPOCD_MT000040_Consumable();
        $consumable->setTypeCode();

        $fallback_none_treatment = $produit = $text_reference = null;
        if (!$this->prescription_line) {
            $fallback_none_treatment = function ($manufacturedMaterial) {
                // none traitement
                $code_empty = new CCDACE();
                $code_empty->setCode('MED-273');
                $code_empty->setCodeSystem("1.2.250.1.213.1.1.4.322");
                $code_empty->setDisplayName('Aucun traitement');
                $code_empty->setCodeSystemName("TA-ASIP");

                $text_observation           = new CCDAED();
                $text_reference_observation = new CCDATEL();
                $text_reference_observation->setValue('#' . CCDAFactory::NONE_TREATMENT);
                $text_observation->setReference($text_reference_observation);
                $code_empty->setOriginalText($text_observation);

                $manufacturedMaterial->setCode($code_empty);
            };
        } else {
            $produit        = $this->prescription_line->loadRefProduit();
            $text_reference = $this->prescription_line->_guid;
        }

        $manufacturedProduct = (new CDAEntryFRProduitDeSante($this->factory, $produit))
            ->setAlternativeCodeMaterial($fallback_none_treatment)
            ->setTextReference($text_reference)
            ->build();

        $consumable->setManufacturedProduct($manufacturedProduct);

        $substanceAdministration->setConsumable($consumable);
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    protected function setStatusCode(CCDARIMAct $substanceAdministration): void
    {
        CCDADocTools::setStatusCode($substanceAdministration, "completed");
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    protected function setText(CCDARIMAct $substanceAdministration): void
    {
        // empty prescription
        if (!$this->prescription_line) {
            CCDADocTools::setTextWithReference($substanceAdministration, "#" . CCDAFactory::NONE_TREATMENT);

            return;
        }

        CCDADocTools::setTextWithReference($substanceAdministration, "#" . $this->prescription_line->_guid);
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    protected function setCode(CCDARIMAct $substanceAdministration): void
    {
        // Ajout du code MED-273 si on sait qu'il n'y aucun traitement
        if ($this->dossier_medical->absence_traitement) {
            $code = new CCDACD();
            $code->setCode('MED-273');
            $code->setCodeSystem("1.2.250.1.213.1.1.4.322");
            $code->setDisplayName('Aucun traitement');
            $code->setCodeSystemName("TA-ASIP");
            $substanceAdministration->setCode($code);
        }
    }
}
