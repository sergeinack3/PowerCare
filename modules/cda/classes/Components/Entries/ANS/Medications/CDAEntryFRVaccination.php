<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\Medications;

use Exception;
use Ox\Core\CAppUI;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\Product\CDAEntryFRProduitDeSante;
use Ox\Interop\Cda\Components\Entries\ANS\Transversaux\CDAEntryFRCommentaire;
use Ox\Interop\Cda\Components\Entries\IHE\Medications\CDAEntryImmunization;
use Ox\Interop\Cda\Components\Meta\CDAMetaAuthor;
use Ox\Interop\Cda\Datatypes\Base\CCDASXCM_TS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_PQ;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Consumable;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_EntryRelationship;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ManufacturedProduct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_SubstanceAdministration;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

/**
 * Class CDAEntryFRVaccination
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\Medications
 */
class CDAEntryFRVaccination extends CDAEntryImmunization
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.45';

    /** @var CInjection */
    protected $injection;

    /**
     * CDAEntryFRTraitement constructor.
     *
     * @param CCDAFactory                 $factory
     * @param CPrescriptionLineMedicament $prescription_line
     */
    public function __construct(CCDAFactory $factory, CInjection $injection)
    {
        parent::__construct($factory);

        // set lines
        $this->injection = $injection;
    }

    /**
     * @param CCDAClasseCda $entry_content
     *
     * @throws Exception
     */
    protected function setId(CCDAClasseCda $entry_content): void
    {
        $this->id = $this->injection->_id;
        $root     = CAppUI::conf('mb_oid');

        CCDADocTools::setId($entry_content, $root, $this->id);
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     *
     * @return void
     */
    protected function buildContent(CCDAClasseCda $substanceAdministration): void
    {
        if (!$this->injection->isVaccinated()) {
            return;
        }

        // Ajout substanceAdministration
        $substanceAdministration->setClassCode();
        $substanceAdministration->setMoodCode('EVN');
        $substanceAdministration->setNegationInd('false');

        // status
        $this->setStatusCode($substanceAdministration);

        // EffectiveTime
        $this->setEffectiveTime($substanceAdministration);

        // routeCode
        // not used

        // approachSiteCode
        // not used

        // doseQuantity
        $this->setDoseQuantity($substanceAdministration);

        // Consumable - FR Produit de santé [1..1]
        $this->buildConsumable($substanceAdministration);

        // Prescription
        // not used

        // Rang de la vaccination
        // not used

        // Performer [0..1]
        $this->setPerformer($substanceAdministration);

        // author [1..1]
        $this->setAuthor($substanceAdministration);

        // Réaction observée suite au vaccin
        // not used

        // Dose d antigène reçue
        // not used

        // Commentaire
        $this->setCommponentComment($substanceAdministration);
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
    protected function setCode(CCDARIMAct $substanceAdministration): void
    {
        CCDADocTools::setCodeCD(
            $substanceAdministration,
            'IMMUNIZ',
            '2.16.840.1.113883.5.4',
            'Vaccination sans autre précision',
            'ActSubstanceAdministrationImmunizationCode'
        );
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    protected function setEffectiveTime(CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration): void
    {
        $effective_time = new CCDASXCM_TS();
        $effective_time->setValue($this->injection->injection_date);
        $substanceAdministration->appendEffectiveTime($effective_time);
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    protected function setText(CCDARIMAct $substanceAdministration): void
    {
        CCDADocTools::setTextWithReference($substanceAdministration, "#" . $this->injection->_guid);
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

        $produit                      = $this->injection->loadRefProduit();
        $builder_manufactured_product = new CDAEntryFRProduitDeSante($this->factory, $produit);

        // name of product
        if (!$produit) {
            $builder_manufactured_product->setNameCode($this->injection->speciality);
        }

        // number lot
        if ($this->injection->hasBatch()) {
            $builder_manufactured_product->setNumberLot($this->injection->batch);
        }

        /** @var CCDAPOCD_MT000040_ManufacturedProduct $manufactured_product */
        $manufactured_product = $builder_manufactured_product->build();
        $consumable->setManufacturedProduct($manufactured_product);

        $substanceAdministration->setConsumable($consumable);
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    private function setDoseQuantity(CCDAClasseCda $substanceAdministration)
    {
        $ivlpq = new CCDAIVL_PQ();
        $ivlpq->setValue('1');
        $substanceAdministration->setDoseQuantity($ivlpq);
    }

    /**
     * @param CCDAPOCD_MT000040_SubstanceAdministration $substanceAdministration
     */
    private function setCommponentComment(CCDAClasseCda $substanceAdministration)
    {
        if (!$this->injection->remarques) {
            return;
        }

        $text_reference = $this->injection->_guid . '-comment';

        /** @var CCDAPOCD_MT000040_Act $commentaire */
        $commentaire = (new CDAEntryFRCommentaire($this->factory, $text_reference))->build();

        $entry_relationship = new CCDAPOCD_MT000040_EntryRelationship();
        $entry_relationship->setTypeCode('SUBJ');
        $entry_relationship->setInversionInd('true');
        $entry_relationship->setAct($commentaire);
        $substanceAdministration->appendEntryRelationship($entry_relationship);
    }

    /**
     * Permet d?indiquer la personne ayant réalisé la vaccination
     *
     * @param CCDAPOCD_MT000040_Act $substanceAdministration
     */
    private function setPerformer(CCDAClasseCda $substanceAdministration): void
    {
        // not used
    }

    /**
     * Permet d'indiquer la personne la personne indiquant que la vaccination a bien été réalisée
     *
     * @param CCDAPOCD_MT000040_Act $substanceAdministration
     */
    private function setAuthor(CCDAClasseCda $substanceAdministration): void
    {
        $mediusers = $this->factory->practicien;
        $author    = (new CDAMetaAuthor($this->factory, $mediusers))->build();

        $substanceAdministration->appendAuthor($author);
    }
}
