<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\Product;

use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\Product\CDAEntryProduct;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAEN;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ManufacturedProduct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Material;
use Ox\Mediboard\Medicament\IMedicamentProduit;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

/**
 * Class CDAEntryFRProduitDeSante
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\Product
 */
class CDAEntryFRProduitDeSante extends CDAEntryProduct
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.43';

    /** @var IMedicamentProduit */
    private $produit;

    /** @var callable */
    private $alternative_code_material;

    /** @var string|null */
    private $text_reference;

    /** @var string|null */
    private $name_code;

    /** @var string */
    private $number_lot;

    /**
     * CDAEntryFRProduitDeSante constructor.
     *
     * @param CCDAFactory                 $factory
     * @param CPrescriptionLineMedicament $prescription_line
     */
    public function __construct(CCDAFactory $factory, IMedicamentProduit $produit = null)
    {
        parent::__construct($factory);

        $this->produit = $produit;
        if ($this->produit) {
            $this->name_code = $produit->getLibelleUCD();
        }
    }

    /**
     * @param callable|null $alternative_code_material
     *
     * @return CDAEntryFRProduitDeSante
     */
    public function setAlternativeCodeMaterial(?callable $alternative_code_material): CDAEntryFRProduitDeSante
    {
        $this->alternative_code_material = $alternative_code_material;

        return $this;
    }

    /**
     * @param CStoredObject|string $reference
     *
     * @return CDAEntryFRProduitDeSante
     */
    public function setTextReference($reference): CDAEntryFRProduitDeSante
    {
        if (is_object($reference) && $reference instanceof CStoredObject) {
            $reference = $reference->_guid;
        }

        if (is_string($reference)) {
            $this->text_reference = $reference;
        }

        return $this;
    }

    /**
     * @param string|null $name_code
     *
     * @return CDAEntryFRProduitDeSante
     */
    public function setNameCode(?string $name_code): CDAEntryFRProduitDeSante
    {
        $this->name_code = $name_code;

        return $this;
    }

    /**
     * @param string $number_lot
     *
     * @return CDAEntryFRProduitDeSante
     */
    public function setNumberLot(?string $number_lot): CDAEntryFRProduitDeSante
    {
        $this->number_lot = $number_lot;

        return $this;
    }

    /**
     * @param CCDAPOCD_MT000040_ManufacturedProduct $manufacturedProduct
     *
     * @return void
     */
    protected function buildContent(CCDAClasseCda $manufacturedProduct): void
    {
        // Material
        $manufacturedMaterial = new CCDAPOCD_MT000040_Material();

        // Code
        $this->setCodeMaterial($manufacturedMaterial);

        // name
        $this->setName($manufacturedMaterial);

        // lot number
        $this->setLotNumber($manufacturedMaterial);

        $manufacturedProduct->setManufacturedMaterial($manufacturedMaterial);
    }

    /**
     * @param CCDAPOCD_MT000040_Material $manufacturedMaterial
     */
    protected function setName(CCDAPOCD_MT000040_Material $manufacturedMaterial): void
    {
        if (!$this->name_code) {
            return;
        }

        $name = new CCDAEN();
        $name->setData($this->name_code);
        $manufacturedMaterial->setName($name);
    }

    /**
     * @param CCDAPOCD_MT000040_Material $manufacturedMaterial
     */
    protected function setCodeMaterial(CCDAPOCD_MT000040_Material $manufacturedMaterial): void
    {
        if ($function = $this->alternative_code_material) {
            $function($manufacturedMaterial);

            return;
        }

        $code_cis = $this->produit ? $this->produit->getCodeCIS() : null;
        if ($code_cis) {
            $this->setCodeCIS($manufacturedMaterial, $this->produit, $this->text_reference);
        } else {
            $code = new CCDACE();
            //$code->setData(' ');
            $manufacturedMaterial->setCode($code);
        }
    }

    /**
     * Add code CCAM on element
     *
     * @param object             $element               element
     * @param IMedicamentProduit $produit               produit
     * @param string             $content_original_text content original text
     *
     * @return void
     */
    private function setCodeCIS(CCDAPOCD_MT000040_Material $element, IMedicamentProduit $produit, ?string $content_original_text = null)
    {
        $code = new CCDACE();
        $code->setCode($produit->getCodeCIS());
        $code->setCodeSystem("1.2.250.1.213.2.3.1");
        $code->setDisplayName($produit->libelle);
        $code->setCodeSystemName("CIS");

        if ($content_original_text) {
            $text_observation           = new CCDAED();
            $text_reference_observation = new CCDATEL();
            $text_reference_observation->setValue($content_original_text);
            $text_observation->setReference($text_reference_observation);
            $code->setOriginalText($text_observation);
        }

        $this->addCodeCIP($code);
        $this->addCodeUCD($code);

        $element->setCode($code);
    }

    /**
     * @param CCDACE $code
     */
    private function addCodeCIP(CCDACE $code): void
    {
        if (!$this->produit) {
            return;
        }

        if (!$code_cip = $this->produit->getCodeCIP()) {
            return;
        }

        $libelle_cip = $this->produit->getLibelleCIP();
        $translation = new CCDACD();
        $translation->setCode($code_cip);
        $translation->setDisplayName($libelle_cip);
        $translation->setCodeSystem('1.2.250.1.213.2.3.2');
        $translation->setDisplayName('CIP');
        $code->addTranslation($translation);
    }

    /**
     * @param CCDACE $code
     */
    private function addCodeUCD(CCDACE $code): void
    {
        if (!$this->produit) {
            return;
        }

        if (!$code_ucd = $this->produit->getCodeUCD()) {
            return;
        }

        $libelle_ucd = $this->produit->getLibelleUCD();
        $translation = new CCDACD();
        $translation->setCode($code_ucd);
        $translation->setDisplayName($libelle_ucd);
        $translation->setCodeSystem('1.2.250.1.213.2.62');
        $translation->setDisplayName('UCD');
        $code->addTranslation($translation);
    }

    /**
     * @param CCDAPOCD_MT000040_Material $manufacturedMaterial
     */
    private function setLotNumber(CCDAPOCD_MT000040_Material $manufacturedMaterial)
    {
        if (!$this->number_lot) {
            return;
        }
        $lot_number = new CCDAST();
        $lot_number->setData($this->number_lot);

        $manufacturedMaterial->setLotNumberText($lot_number);
    }
}
