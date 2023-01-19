<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_additionalLocator;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_buildingNumberSuffix;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_careOf;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_censusTract;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_city;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_country;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_county;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_delimiter;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_deliveryAddressLine;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_deliveryInstallationArea;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_deliveryInstallationQualifier;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_deliveryInstallationType;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_deliveryMode;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_deliveryModeIdentifier;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_direction;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_houseNumber;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_houseNumberNumeric;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_postalCode;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_postBox;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_precinct;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_state;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_streetAddressLine;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_streetName;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_streetNameBase;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_streetNameType;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_unitID;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_unitType;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_delimiter;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_family;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_given;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_prefix;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_suffix;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDAADXP;
use Ox\Interop\Cda\Datatypes\Base\CCDAANY;
use Ox\Interop\Cda\Datatypes\Base\CCDAAnyNonNull;
use Ox\Interop\Cda\Datatypes\Base\CCDABIN;
use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDABN;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACR;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDACV;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAEN;
use Ox\Interop\Cda\Datatypes\Base\CCDAENXP;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAMO;
use Ox\Interop\Cda\Datatypes\Base\CCDAON;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQR;
use Ox\Interop\Cda\Datatypes\Base\CCDAREAL;
use Ox\Interop\Cda\Datatypes\Base\CCDARTO_QTY_QTY;
use Ox\Interop\Cda\Datatypes\Base\CCDASC;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;
use Ox\Interop\Cda\Datatypes\Base\CCDASXCM_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Base\CCDAthumbnail;
use Ox\Interop\Cda\Datatypes\Base\CCDATN;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Base\CCDAURL;
use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Base;
use Ox\Tests\OxUnitTestCase;

class DatatypeBaseTest extends OxUnitTestCase
{
    public function testCCDADatatypeBase(): void
    {
        $CCDA_Datatype_Base = new CCDA_Datatype_Base();

        // Test avec une valeur null, document invalide
        $this->assertFalse($CCDA_Datatype_Base->validate());
    }

    public function testCCDAAD(): void
    {
        $CCDAAD = new CCDAAD();

        // Test avec des données, document valide
        $CCDAAD->setData("test");
        $this->assertTrue($CCDAAD->validate());

        // Test avec un use incorrect
        $CCDAAD->setUse(["TESTTEST"]);
        $this->assertFalse($CCDAAD->validate());

        // Test avec use correct
        $CCDAAD->setUse(["TMP"]);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un isNotOrdered incorrect
        $CCDAAD->setIsNotOrdered("TESTTEST");
        $this->assertFalse($CCDAAD->validate());

        // Test avec un isNotOrdered correct
        $CCDAAD->setIsNotOrdered("true");
        $this->assertTrue($CCDAAD->validate());

        // Test avec un delimiter correct
        $adxp = new CCDA_adxp_delimiter();
        $CCDAAD->append("delimiter", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec deux delimiter correct
        $adxp = new CCDA_adxp_delimiter();
        $CCDAAD->append("delimiter", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un country correct
        $adxp = new CCDA_adxp_country();
        $CCDAAD->append("country", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un state correct
        $adxp = new CCDA_adxp_state();
        $CCDAAD->append("state", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un county correct
        $adxp = new CCDA_adxp_county();
        $CCDAAD->append("county", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un city correct
        $adxp = new CCDA_adxp_city();
        $CCDAAD->append("city", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un postalCode correct
        $adxp = new CCDA_adxp_postalCode();
        $CCDAAD->append("postalCode", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un streetAddressLine correct
        $adxp = new CCDA_adxp_streetAddressLine();
        $CCDAAD->append("streetAddressLine", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un houseNumber correct
        $adxp = new CCDA_adxp_houseNumber();
        $CCDAAD->append("houseNumber", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un houseNumberNumeric correct
        $adxp = new CCDA_adxp_houseNumberNumeric();
        $this->assertTrue($CCDAAD->validate());

        // Test avec un direction correct
        $adxp = new CCDA_adxp_direction();
        $CCDAAD->append("direction", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un streetName correct
        $adxp = new CCDA_adxp_streetName();
        $CCDAAD->append("streetName", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un streetNameBase correct
        $adxp = new CCDA_adxp_streetNameBase();
        $CCDAAD->append("streetNameBase", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un streetNameType correct
        $adxp = new CCDA_adxp_streetNameType();
        $CCDAAD->append("streetNameType", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un additionalLocator correct
        $adxp = new CCDA_adxp_additionalLocator();
        $CCDAAD->append("additionalLocator", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un unitID correct
        $adxp = new CCDA_adxp_unitID();
        $CCDAAD->append("unitID", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un unitType correct
        $adxp = new CCDA_adxp_unitType();
        $CCDAAD->append("unitType", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un careOf correct
        $adxp = new CCDA_adxp_careOf();
        $CCDAAD->append("careOf", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un censusTract correct
        $adxp = new CCDA_adxp_censusTract();
        $CCDAAD->append("censusTract", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un deliveryAddressLine correct
        $adxp = new CCDA_adxp_deliveryAddressLine();
        $CCDAAD->append("deliveryAddressLine", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un deliveryInstallationType correct
        $adxp = new CCDA_adxp_deliveryInstallationType();
        $CCDAAD->append("deliveryInstallationType", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un deliveryInstallationArea correct
        $adxp = new CCDA_adxp_deliveryInstallationArea();
        $CCDAAD->append("deliveryInstallationArea", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un deliveryInstallationQualifier correct
        $adxp = new CCDA_adxp_deliveryInstallationQualifier();
        $CCDAAD->append("deliveryInstallationQualifier", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un deliveryMode correct
        $adxp = new CCDA_adxp_deliveryMode();
        $CCDAAD->append("deliveryMode", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un deliveryModeIdentifier correct
        $adxp = new CCDA_adxp_deliveryModeIdentifier();
        $CCDAAD->append("deliveryModeIdentifier", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un buildingNumberSuffix correct
        $adxp = new CCDA_adxp_buildingNumberSuffix();
        $CCDAAD->append("buildingNumberSuffix", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un postBox correct
        $adxp = new CCDA_adxp_postBox();
        $CCDAAD->append("postBox", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un precinct correct
        $adxp = new CCDA_adxp_precinct();
        $CCDAAD->append("precinct", $adxp);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un useablePeriod correct
        $useable = new CCDASXCM_TS();
        $useable->setValue(CMbDT::dateTime());
        $CCDAAD->resetListdata("useablePeriod");
        $CCDAAD->append("useablePeriod", $useable);
        $this->assertTrue($CCDAAD->validate());

        // Test avec un useablePeriod incorrect
        $useable = new CCDASXCM_TS();
        $this->expectException(Exception::class);
        $useable->setValue("TESTEST");
    }

    public function testCCDAADXP(): void
    {
        $CCDAADXP = new CCDAADXP();

        // Test avec un parttype incorrect
        $CCDAADXP->setPartType("TEstTEst");
        $this->assertFalse($CCDAADXP->validate());

        // Test avec un parttype correct
        $CCDAADXP->setPartType("ZIP");
        $this->assertTrue($CCDAADXP->validate());
    }

    public function testCCDAANY(): void
    {
        $CCDAANY = new CCDAANY();

        // Test avec les valeurs null
        $this->assertTrue($CCDAANY->validate());

        // Test avec un nullFlavor incorrect
        $CCDAANY->setNullFlavor("TESTEST");
        $this->assertFalse($CCDAANY->validate());

        // Test avec un nullFlavor correct
        $CCDAANY->setNullFlavor("NP");
        $this->assertTrue($CCDAANY->validate());
    }

    public function testCCDAAnyNonNull(): void
    {
        $CCDAAnyNonNull = new CCDAAnyNonNull();

        $CCDAAnyNonNull->setNullFlavor("TESTEST");
        $this->assertFalse($CCDAAnyNonNull->validate());

        $CCDAAnyNonNull->setNullFlavor(null);
        $this->assertFalse($CCDAAnyNonNull->validate());
    }

    public function testCCDABIN(): void
    {
        $CCDABIN = new CCDABIN();

        // Test avec des données
        $CCDABIN->setData("test");
        $this->assertTrue($CCDABIN->validate());

        // Test avec une representation incorrecte
        $CCDABIN->setRepresentation("TESTTEST");
        $this->assertFalse($CCDABIN->validate());

        // Test avec une representation correcte
        $CCDABIN->setRepresentation("B64");
        $this->assertTrue($CCDABIN->validate());
    }

    public function testCCDABL(): void
    {
        $CCDABL = new CCDABL();

        // Test avec une valeur incorrecte
        $CCDABL->setValue("TESTTEST");
        $this->assertFalse($CCDABL->validate());

        // Test avec une valeur correcte
        $CCDABL->setValue("true");
        $this->assertTrue($CCDABL->validate());

        // Test avec un nullFlavor correct
        $CCDABL->setNullFlavor("NP");
        $this->assertTrue($CCDABL->validate());
    }

    public function testCCDABN(): void
    {
        $CCDABN = new CCDABN();

        $CCDABN->setValue("TESTTEST");
        $this->assertFalse($CCDABN->validate());

        $CCDABN->setValue("true");
        $this->assertTrue($CCDABN->validate());
    }

    public function testCCDACD(): void
    {
        $CCDACD = new CCDACD();

        // Test avec un code incorrect
        $CCDACD->setCode(" ");
        $this->assertFalse($CCDACD->validate());

        // Test avec un code correct
        $CCDACD->setCode("TEST");
        $this->assertTrue($CCDACD->validate());

        // Test avec un codeSystem incorrect
        $CCDACD->setCodeSystem("*");
        $this->assertFalse($CCDACD->validate());

        // Test avec un codeSystem correct
        $CCDACD->setCodeSystem("HL7");
        $this->assertTrue($CCDACD->validate());

        // Test avec un codeSystemName incorrect, null par défaut
        $CCDACD->setCodeSystemName("");
        $this->assertTrue($CCDACD->validate());

        // Test avec un codeSystemName correct
        $CCDACD->setCodeSystemName("test");
        $this->assertTrue($CCDACD->validate());

        // Test avec un codeSystemVersion incorrect, null par défaut
        $CCDACD->setCodeSystemVersion("");
        $this->assertTrue($CCDACD->validate());

        // Test avec un codeSystemVersion correct
        $CCDACD->setCodeSystemVersion("test");
        $this->assertTrue($CCDACD->validate());

        // Test avec un displayName incorrect, null par défaut
        $CCDACD->setDisplayName("");
        $this->assertTrue($CCDACD->validate());

        // Test avec un displayName correct
        $CCDACD->setDisplayName("test");
        $this->assertTrue($CCDACD->validate());

        // Test avec une translation correct sans valeur
        $translation = new CCDACD();
        $CCDACD->addTranslation($translation);
        $this->assertTrue($CCDACD->validate());

        // Test avec deux translation correct sans valeur
        $translation2 = new CCDACD();
        $CCDACD->addTranslation($translation2);
        $this->assertTrue($CCDACD->validate());

        // Test avec un qualifier incorrect
        $cr = new CCDACR();
        $cr->setInverted("TESTTEST");
        $CCDACD->setQualifier($cr);
        $this->assertFalse($CCDACD->validate());

        // Test avec un qualifier correct
        $cr->setInverted("true");
        $CCDACD->setQualifier($cr);
        $this->assertTrue($CCDACD->validate());

        // Test avec deux qualifier corrects
        $cr2 = new CCDACR();
        $cr2->setInverted("true");
        $CCDACD->setQualifier($cr2);
        $this->assertTrue($CCDACD->validate());

        // Test avec un originalText incorrect
        $ed = new CCDAED();
        $ed->setLanguage(" ");
        $CCDACD->setOriginalText($ed);
        $this->assertFalse($CCDACD->validate());

        // Test avec un originalText correct
        $ed->setLanguage("TEST");
        $CCDACD->setOriginalText($ed);
        $this->assertTrue($CCDACD->validate());
    }

    public function testCCDACE(): void
    {
        $CCDACE = new CCDACE();

        // Test avec un qualifier correct, interdit dans ce contexte
        $cr = new CCDACR();
        $cr->setInverted("true");
        $CCDACE->setQualifier($cr);
        $this->assertFalse($CCDACE->validate());
        $CCDACE->resetListQualifier();
    }

    public function testCCDACR(): void
    {
        $CCDACR = new CCDACR();

        // Test avec un inverted incorrect
        $CCDACR->setInverted(" ");
        $this->assertFalse($CCDACR->validate());

        // Test avec un inverted correct
        $CCDACR->setInverted("false");
        $this->assertTrue($CCDACR->validate());

        // Test avec un name incorrect
        $cv = new CCDACV();
        $cv->setCode(" ");
        $CCDACR->setName($cv);
        $this->assertFalse($CCDACR->validate());

        // Test avec un name correct
        $cv->setCode("test");
        $CCDACR->setName($cv);
        $this->assertTrue($CCDACR->validate());

        // Test avec une value incorrecte
        $valuetest = new CCDACD();
        $valuetest->setCode(" ");
        $CCDACR->setValue($valuetest);
        $this->assertFalse($CCDACR->validate());

        // Test avec une value correcte
        $valuetest = new CCDACD();
        $valuetest->setCode("test");
        $CCDACR->setValue($valuetest);
        $this->assertTrue($CCDACR->validate());
    }

    public function testCCDACS(): void
    {
        $CCDACS = new CCDACS();

        // Test avec un code incorrect
        $CCDACS->setCode(" ");

        // Test avec un code correct
        $CCDACS->setCode("TEST");
        $this->assertTrue($CCDACS->validate());

        // Test avec un codeSystem incorrect
        $CCDACS->setCode(null);
        $CCDACS->setCodeSystem("*");
        $this->assertFalse($CCDACS->validate());

        // Test avec un displayName incorrect
        $CCDACS->setDisplayName("test");
        $this->assertFalse($CCDACS->validate());

        // Test avec un originalText incorrect
        $CCDACS->resetListQualifier();
        $ed = new CCDAED();
        $ed->setLanguage("test");
        $CCDACS->setOriginalText($ed);
        $this->assertFalse($CCDACS->validate());
    }

    public function testCCDACV(): void
    {
        $CCDACV = new CCDACV();

        // Test avec une translation correct, interdit dans ce contexte
        $translation = new CCDACD();
        $translation->setCodeSystemName("test");
        $CCDACV->addTranslation($translation);
        $this->assertFalse($CCDACV->validate());
        $CCDACV->resetListTranslation();
    }

    public function testCCDAED(): void
    {
        $CCDAED = new CCDAED();

        // Test avec un language incorrect
        $CCDAED->setLanguage(" ");
        $this->assertFalse($CCDAED->validate());

        // Test avec un language correct
        $CCDAED->setLanguage("TEST");
        $this->assertTrue($CCDAED->validate());

        // Test avec un mediaType incorrect
        $CCDAED->setMediaType(" ");
        $this->assertFalse($CCDAED->validate());

        // Test avec un mediaType correct
        $CCDAED->setMediaType("TEST");
        $this->assertTrue($CCDAED->validate());

        // Test avec une compression incorrecte
        $CCDAED->setCompression(" ");
        $this->assertFalse($CCDAED->validate());

        // Test avec une compression correcte
        $CCDAED->setCompression("GZ");
        $this->assertTrue($CCDAED->validate());

        // Test avec un integrityCheck incorrect
        $CCDAED->setIntegrityCheck("111111111");
        $this->assertFalse($CCDAED->validate());

        // Test avec un integrityCheck correct
        $CCDAED->setIntegrityCheck("JVBERi0xLjUNCiW1tbW1DQoxIDAgb2Jq");
        $this->assertTrue($CCDAED->validate());

        // Test avec un integrityCheck incorrect
        $CCDAED->setIntegrityCheckAlgorithm("SHA-25");
        $this->assertFalse($CCDAED->validate());

        // Test avec un integrityCheck correct
        $CCDAED->setIntegrityCheckAlgorithm("SHA-256");
        $this->assertTrue($CCDAED->validate());

        // Test avec une reference incorrecte
        $tel = new CCDATEL();
        $tel->setUse(["TEST"]);
        $CCDAED->setReference($tel);
        $this->assertFalse($CCDAED->validate());

        // Test avec une reference correcte
        $tel->setUse(["MC"]);
        $CCDAED->setReference($tel);
        $this->assertTrue($CCDAED->validate());

        // Test avec un thumbnail incorrect
        $thum = new CCDAthumbnail();
        $thum->setIntegrityCheckAlgorithm("SHA-25");
        $CCDAED->setThumbnail($thum);
        $this->assertFalse($CCDAED->validate());

        // Test avec un thumbnail correct
        $thum->setIntegrityCheckAlgorithm("SHA-256");
        $CCDAED->setThumbnail($thum);
        $this->assertTrue($CCDAED->validate());
    }

    public function testCCDAEN(): void
    {
        $CCDAEN = new CCDAEN();

        // Test avec des données
        $CCDAEN->setData("test");
        $this->assertTrue($CCDAEN->validate());

        // Test avec un use incorrect
        $CCDAEN->setUse(["TESTTEST"]);
        $this->assertFalse($CCDAEN->validate());

        // Test avec un use correct
        $CCDAEN->setUse(["C"]);
        $this->assertTrue($CCDAEN->validate());

        // Test avec un validTime correct
        $valid = new CCDAIVL_TS();
        $valid->setValue(CMbDT::dateTime());
        $CCDAEN->setValidTime($valid);
        $this->assertTrue($CCDAEN->validate());

        // Test avec un delimiter correct
        $enxp = new CCDA_en_delimiter();
        $CCDAEN->append("delimiter", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec deux delimiter corrects
        $enxp = new CCDA_en_delimiter();
        $CCDAEN->append("delimiter", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec un prefix correct
        $enxp = new CCDA_en_prefix();
        $CCDAEN->append("prefix", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec deux prefix corrects
        $enxp = new CCDA_en_prefix();
        $CCDAEN->append("prefix", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec un suffix correct
        $enxp = new CCDA_en_suffix();
        $CCDAEN->append("suffix", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec deux suffix correct
        $enxp = new CCDA_en_suffix();
        $CCDAEN->append("suffix", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec un family correct
        $enxp = new CCDA_en_family();
        $CCDAEN->append("family", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec deux family corrects
        $enxp = new CCDA_en_family();
        $CCDAEN->append("family", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec un given correct
        $enxp = new CCDA_en_given();
        $CCDAEN->append("given", $enxp);
        $this->assertTrue($CCDAEN->validate());

        // Test avec deux given corrects
        $enxp = new CCDA_en_given();
        $CCDAEN->append("given", $enxp);
        $this->assertTrue($CCDAEN->validate());
    }

    public function testCCDAENXP(): void
    {
        $CCDAENXP = new CCDAENXP();

        // Test avec un partType incorrect
        $CCDAENXP->setPartType("TEstTEst");
        $this->assertFalse($CCDAENXP->validate());

        // Test avec un partType correct
        $CCDAENXP->setPartType("FAM");
        $this->assertTrue($CCDAENXP->validate());

        // Test avec un qualifier incorrect
        $CCDAENXP->setQualifier(["TESTTEST"]);
        $this->assertFalse($CCDAENXP->validate());

        // Test avec un qualifier correct
        $CCDAENXP->setQualifier(["LS"]);
        $this->assertTrue($CCDAENXP->validate());
    }

    public function testCCDAII(): void
    {
        $CCDAII = new CCDAII();

        // Test avec un root incorrect
        $CCDAII->setRoot("4TESTTEST");
        $this->assertFalse($CCDAII->validate());

        // Test avec un root correcte
        $CCDAII->setRoot("1.2.4.5");
        $this->assertTrue($CCDAII->validate());

        // Test avec un extension correcte
        $CCDAII->setExtension("HL7");
        $this->assertTrue($CCDAII->validate());

        // Test avec un assigningAuthorityName incorrecte, null par défaut
        $CCDAII->setAssigningAuthorityName("");
        $this->assertTrue($CCDAII->validate());

        // Test avec un assigningAuthorityName correct
        $CCDAII->setAssigningAuthorityName("HL7");
        $this->assertTrue($CCDAII->validate());

        // Test avec un displayable incorrect
        $CCDAII->setDisplayable("TESTTEST");
        $this->assertFalse($CCDAII->validate());

        // Test avec un displayable correct
        $CCDAII->setDisplayable("true");
        $this->assertTrue($CCDAII->validate());
    }

    public function testCCDAINT(): void
    {
        $CCDAINT = new CCDAINT();

        // Test avec une valeur incorrecte
        $CCDAINT->setValue("10.25");
        $this->assertFalse($CCDAINT->validate());

        // Test avec une valeur correcte
        $CCDAINT->setValue("10");
        $this->assertTrue($CCDAINT->validate());
    }

    public function testCCDAIVL_TS(): void
    {
        $CCDAIVL_TS = new CCDAIVL_TS();

        // Test avec un low correct
        $xbts = new CCDAIVXB_TS();
        $xbts->setValue(CMbDT::dateTime());
        $CCDAIVL_TS->setLow($xbts);
        $this->assertTrue($CCDAIVL_TS->validate());

        // Test avec un high correct
        $hi = new CCDAIVXB_TS();
        $hi->setValue(CMbDT::dateTime());
        $CCDAIVL_TS->setHigh($hi);
        $this->assertTrue($CCDAIVL_TS->validate());

        // Test avec un width incorrecte, séquence incorrect
        $pq = new CCDAPQ();
        $pq->setValue("test");
        $CCDAIVL_TS->setWidth($pq);
        $this->assertFalse($CCDAIVL_TS->validate());

        // Test avec un width correcte, séquence incorrect
        $pq->setValue("10.25");
        $CCDAIVL_TS->setWidth($pq);
        $this->assertFalse($CCDAIVL_TS->validate());

        // Test avec un high correct
        $CCDAIVL_TS->setOrder(null);
        $CCDAIVL_TS->low    = null;
        $CCDAIVL_TS->width  = null;
        $CCDAIVL_TS->center = null;
        $hi                 = new CCDAIVXB_TS();
        $hi->setValue(CMbDT::dateTime());
        $CCDAIVL_TS->setHigh($hi);
        $this->assertTrue($CCDAIVL_TS->validate());

        // Test avec un width incorrect
        $CCDAIVL_TS->high = null;
        $CCDAIVL_TS->setOrder(null);
        $pq = new CCDAPQ();
        $pq->setValue("test");
        $CCDAIVL_TS->setWidth($pq);
        $this->assertFalse($CCDAIVL_TS->validate());

        // Test avec un width correct
        $pq->setValue("10.25");
        $CCDAIVL_TS->setWidth($pq);
        $this->assertTrue($CCDAIVL_TS->validate());

        // Test avec un high correct
        $hi = new CCDAIVXB_TS();
        $hi->setValue(CMbDT::dateTime());
        $CCDAIVL_TS->setHigh($hi);
        $this->assertTrue($CCDAIVL_TS->validate());

        // Test avec un center correct
        $CCDAIVL_TS->setOrder(null);
        $CCDAIVL_TS->width = null;
        $CCDAIVL_TS->high  = null;
        $ts                = new CCDATS();
        $ts->setValue(CMbDT::dateTime());
        $CCDAIVL_TS->setCenter($ts);
        $this->assertTrue($CCDAIVL_TS->validate());

        // Test avec un width incorrect
        $pq = new CCDAPQ();
        $pq->setValue("test");
        $CCDAIVL_TS->setWidth($pq);
        $this->assertFalse($CCDAIVL_TS->validate());

        // Test avec un width correct
        $pq->setValue("10.25");
        $CCDAIVL_TS->setWidth($pq);
        $this->assertTrue($CCDAIVL_TS->validate());
    }

    public function testCCDAIVXB_TS(): void
    {
        $CCDAIVXB_TS = new CCDAIVXB_TS();

        // Test avec un inclusive incorrect
        $CCDAIVXB_TS->setInclusive("test");
        $this->assertFalse($CCDAIVXB_TS->validate());

        // Test avec un inclusive correct
        $CCDAIVXB_TS->setInclusive("true");
        $this->assertTrue($CCDAIVXB_TS->validate());
    }

    public function testCCDAMO(): void
    {
        $CCDAMO = new CCDAMO();

        // Test avec une valeur incorrecte
        $CCDAMO->setValue("test");
        $this->assertFalse($CCDAMO->validate());

        // Test avec une valeur correcte
        $CCDAMO->setValue("10.25");
        $this->assertTrue($CCDAMO->validate());

        // Test avec un currency incorrect
        $CCDAMO->setCurrency(" ");
        $this->assertFalse($CCDAMO->validate());

        // Test avec un currency correct
        $CCDAMO->setCurrency("test");
        $this->assertTrue($CCDAMO->validate());
    }

    public function testCCDAON(): void
    {
        $CCDAON = new CCDAON();

        // Test avec un family correct, interdit dans ce contexte
        $enxp = new CCDA_en_family();
        $CCDAON->append("family", $enxp);
        $this->assertFalse($CCDAON->validate());

        // Test avec un given correct, interdit dans ce contexte
        $CCDAON->resetListdata("family");
        $enxp = new CCDA_en_given();
        $CCDAON->append("given", $enxp);
        $this->assertFalse($CCDAON->validate());
    }

    public function testCCDAPQ(): void
    {
        $CCDAPQ = new CCDAPQ();

        // Test avec une valeur incorrecte
        $CCDAPQ->setValue("test");
        $this->assertFalse($CCDAPQ->validate());

        //  Test avec une valeur correcte
        $CCDAPQ->setValue("10.25");
        $this->assertTrue($CCDAPQ->validate());

        // Test avec une unit incorrecte
        $CCDAPQ->setUnit(" ");
        $this->assertFalse($CCDAPQ->validate());

        // Test avec une unit correcte
        $CCDAPQ->setUnit("test");
        $this->assertTrue($CCDAPQ->validate());

        // Test avec une translation incorrecte
        $pqr = new CCDAPQR();
        $pqr->setValue("test");
        $CCDAPQ->appendTranslation($pqr);
        $this->assertFalse($CCDAPQ->validate());

        // Test avec une translation correcte
        $pqr->setValue("10.25");
        $CCDAPQ->appendTranslation($pqr);
        $this->assertTrue($CCDAPQ->validate());
    }

    public function testCCDAPQR(): void
    {
        $CCDAPQR = new CCDAPQR();

        // Test avec une valeur incorrecte
        $CCDAPQR->setValue("test");
        $this->assertFalse($CCDAPQR->validate());

        // Test avec une valeur correcte
        $CCDAPQR->setValue("10.5");
        $this->assertTrue($CCDAPQR->validate());
    }

    public function testCCDAREAL(): void
    {
        $CCDAREAL = new CCDAREAL();

        // Test avec une valeur incorrecte
        $CCDAREAL->setValue("test");
        $this->assertFalse($CCDAREAL->validate());

        // Test avec une valeur correcte
        $CCDAREAL->setValue("10.5");
        $this->assertTrue($CCDAREAL->validate());
    }

    public function testCCDARTO_QTY_QTY(): void
    {
        $CCDARTO_QTY_QTY = new CCDARTO_QTY_QTY();

        // Test avec les valeurs null
        $this->assertFalse($CCDARTO_QTY_QTY->validate());

        // Test avec un numerator incorrect
        $num = new CCDAINT();
        $num->setValue("10.25");
        $CCDARTO_QTY_QTY->setNumerator($num);
        $this->assertFalse($CCDARTO_QTY_QTY->validate());

        // Test avec un numerator correct, séquence incorrecte
        $num->setValue("10");
        $CCDARTO_QTY_QTY->setNumerator($num);
        $this->assertFalse($CCDARTO_QTY_QTY->validate());

        // Test avec un denominator incorrect
        $num = new CCDAINT();
        $num->setValue("10.25");
        $CCDARTO_QTY_QTY->setDenominator($num);
        $this->assertFalse($CCDARTO_QTY_QTY->validate());

        // Test avec un denominator correct
        $num->setValue("15");
        $CCDARTO_QTY_QTY->setDenominator($num);
        $this->assertTrue($CCDARTO_QTY_QTY->validate());

        // Test avec un denominator correct en real
        $num = new CCDAREAL();
        $num->setValue("10.25");
        $CCDARTO_QTY_QTY->setDenominator($num);
        $this->assertTrue($CCDARTO_QTY_QTY->validate());
    }

    public function testCCDASC(): void
    {
        $CCDASC = new CCDASC();

        // Test avec un code incorrecte
        $CCDASC->setCode(" ");
        $this->assertFalse($CCDASC->validate());

        // Test avec un code correct
        $CCDASC->setCode("TEST");
        $this->assertTrue($CCDASC->validate());

        // Test avec un codeSystem incorrecte
        $CCDASC->setCodeSystem("*");
        $this->assertFalse($CCDASC->validate());

        // Test avec un codeSystem correct
        $CCDASC->setCodeSystem("HL7");
        $this->assertTrue($CCDASC->validate());

        // Test avec un codeSystemName incorrecte, null par défaut
        $CCDASC->setCodeSystemName("");
        $this->assertTrue($CCDASC->validate());

        // Test avec un codeSystemName correct
        $CCDASC->setCodeSystemName("test");
        $this->assertTrue($CCDASC->validate());

        // Test avec un codeSystemVersion incorrecte, null par défaut
        $CCDASC->setCodeSystemVersion("");
        $this->assertTrue($CCDASC->validate());

        // Test avec un codeSystemVersion correct
        $CCDASC->setCodeSystemVersion("test");
        $this->assertTrue($CCDASC->validate());

        // Test avec un displayName incorrecte, null par défaut
        $CCDASC->setDisplayName("");
        $this->assertTrue($CCDASC->validate());

        // Test avec un displayName correct
        $CCDASC->setDisplayName("test");
        $this->assertTrue($CCDASC->validate());
    }

    public function testCCDAST(): void
    {
        $CCDAST = new CCDAST();

        // Test avec une valeur correcte mais refuser dans ce contexte
        $CCDAST->setRepresentation("B64");
        $this->assertFalse($CCDAST->validate());

        // Test avec une representation correcte
        $CCDAST->setRepresentation("TXT");
        $this->assertTrue($CCDAST->validate());

        // Test avec un mediaType correct, interdit dans ce contexte
        $CCDAST->setMediaType(" ");
        $this->assertFalse($CCDAST->validate());

        // Test avec un mediaType correct
        $CCDAST->setMediaType("text/plain");
        $this->assertTrue($CCDAST->validate());

        // Test avec une compression incorrecte
        $CCDAST->setCompression(" ");
        $this->assertFalse($CCDAST->validate());

        // Test avec une compression correcte mais pas de ce contexte
        $CCDAST->setCompression("GZ");
        $this->assertFalse($CCDAST->validate());
    }

    public function testCCDASXCM_TS(): void
    {
        $CCDASXCM_TS = new CCDASXCM_TS();

        // Test avec un operator incorrect
        $CCDASXCM_TS->setOperator("TESTTEST");
        $this->assertFalse($CCDASXCM_TS->validate());

        // Test avec un operator correct
        $CCDASXCM_TS->setOperator("H");
        $this->assertTrue($CCDASXCM_TS->validate());
    }

    public function testCCDATEL(): void
    {
        $CCDATEL = new CCDATEL();

        // Test avec une useablePeriod incorrecte
        $sx = new CCDASXCM_TS();
        $sx->setOperator("TEST");
        $CCDATEL->setUseablePeriod($sx);
        $this->assertFalse($CCDATEL->validate());

        // Test avec une useablePeriod correcte
        $sx = new CCDASXCM_TS();
        $sx->setOperator("H");
        $CCDATEL->setUseablePeriod($sx);
        $this->assertTrue($CCDATEL->validate());

        // Test avec un use incorrect
        $arrayUse = ["TESTTEST"];
        $CCDATEL->setUse($arrayUse);
        $this->assertFalse($CCDATEL->validate());

        // Test avec un use correct
        $arrayUse = ["AS"];
        $CCDATEL->setUse($arrayUse);
        $this->assertTrue($CCDATEL->validate());
    }

    public function testCCDAthumbnail(): void
    {
        $CCDAthumbnail = new CCDAthumbnail();

        $thum = new CCDAthumbnail();
        $thum->setIntegrityCheckAlgorithm("SHA-256");
        $CCDAthumbnail->setThumbnail($thum);
        $this->assertFalse($CCDAthumbnail->validate());
    }

    public function testCCDATN(): void
    {
        $CCDATN = new CCDATN();

        // Test avec un family correct, interdit dans ce contexte
        $enxp = new CCDA_en_family();
        $CCDATN->append("family", $enxp);
        $this->assertFalse($CCDATN->validate());

        // Test avec un given correct, interdit dans ce contexte
        $CCDATN->resetListdata("family");
        $enxp = new CCDA_en_given();
        $CCDATN->append("given", $enxp);
        $this->assertFalse($CCDATN->validate());

        // Test avec un prefix correct, interdit dans ce contexte
        $CCDATN->resetListdata("given");
        $enxp = new CCDA_en_prefix();
        $CCDATN->append("prefix", $enxp);
        $this->assertFalse($CCDATN->validate());

        // Test avec un sufix correct, interdit dans ce contexte
        $CCDATN->resetListdata("prefix");
        $enxp = new CCDA_en_suffix();
        $CCDATN->append("suffix", $enxp);
        $this->assertFalse($CCDATN->validate());

        // Test avec un delimiter correct, interdit dans ce contexte
        $CCDATN->resetListdata("sufix");
        $enxp = new CCDA_en_delimiter();
        $CCDATN->append("delimiter", $enxp);
        $this->assertFalse($CCDATN->validate());
    }

    public function testCCDATS(): void
    {
        $CCDATS = new CCDATS();

        $CCDATS->setValue(CMbDT::dateTime());
        $this->assertTrue($CCDATS->validate());
    }

    public function testCCDAURL(): void
    {
        $CCDAURL = new CCDAURL();

        // Test avec une valeur incorrecte
        $CCDAURL->setValue(":::$:!:");
        $this->assertFalse($CCDAURL->validate());

        // Test avec une valeur correcte
        $CCDAURL->setValue("test");
        $this->assertTrue($CCDAURL->validate());
    }
}
