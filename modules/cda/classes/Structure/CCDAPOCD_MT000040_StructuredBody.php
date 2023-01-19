<?php

/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMAct;

/**
 * POCD_MT000040_StructuredBody Class
 */
class CCDAPOCD_MT000040_StructuredBody extends CCDARIMAct
{

    /**
     * @var CCDAPOCD_MT000040_Component3
     */
    public $component = [];

    /**
     * Setter confidentialityCode
     *
     * @param CCDACE $inst CCDACE
     *
     * @return void
     */
    function setConfidentialityCode(CCDACE $inst)
    {
        $this->confidentialityCode = $inst;
    }

    /**
     * Getter confidentialityCode
     *
     * @return CCDACE
     */
    function getConfidentialityCode()
    {
        return $this->confidentialityCode;
    }

    /**
     * Setter languageCode
     *
     * @param CCDACS $inst CCDACS
     *
     * @return void
     */
    function setLanguageCode(CCDACS $inst)
    {
        $this->languageCode = $inst;
    }

    /**
     * Getter languageCode
     *
     * @return CCDACS
     */
    function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_Component3 $inst CCDAPOCD_MT000040_Component3
     *
     * @return void
     */
    function appendComponent(CCDAPOCD_MT000040_Component3 $inst)
    {
        array_push($this->component, $inst);
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListComponent()
    {
        $this->component = [];
    }

    /**
     * Getter component
     *
     * @return CCDAPOCD_MT000040_Component3[]
     */
    function getComponent()
    {
        return $this->component;
    }

    /**
     * Assigne classCode à DOCBODY
     *
     * @return void
     */
    function setClassCode()
    {
        $actClass = new CCDAActClass();
        $actClass->setData("DOCBODY");
        $this->classCode = $actClass;
    }

    /**
     * Getter classCode
     *
     * @return CCDAActClass
     */
    function getClassCode()
    {
        return $this->classCode;
    }

    /**
     * Assigne moodCode à EVN
     *
     * @return void
     */
    function setMoodCode()
    {
        $actMood = new CCDAActMood();
        $actMood->setData("EVN");
        $this->moodCode = $actMood;
    }

    /**
     * Getter moodCode
     *
     * @return CCDAActMood
     */
    function getMoodCode()
    {
        return $this->moodCode;
    }

    /**
     * Retourne les propriétés
     *
     * @return array
     */
    function getProps()
    {
        $props                        = parent::getProps();
        $props["typeId"]              = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
        $props["confidentialityCode"] = "CCDACE xml|element max|1";
        $props["languageCode"]        = "CCDACS xml|element max|1";
        $props["component"]           = "CCDAPOCD_MT000040_Component3 xml|element min|1";
        $props["classCode"]           = "CCDAActClass xml|attribute fixed|DOCBODY";
        $props["moodCode"]            = "CCDAActMood xml|attribute fixed|EVN";

        return $props;
    }

    /**
     * Fonction permettant de tester la classe
     *
     * @return array
     */
    function test()
    {
        $tabTest = parent::test();

        /**
         * Test avec un component3 correct
         */

        $comp = new CCDAPOCD_MT000040_Component3();
        $sec  = new CCDAPOCD_MT000040_Section();
        $sec->setClassCode();
        $comp->setSection($sec);
        $this->appendComponent($comp);
        $tabTest[] = $this->sample("Test avec un component correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un classCode correct
         */

        $this->setClassCode();
        $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un moodCode correct
         */

        $this->setMoodCode();
        $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un languageCode incorrect
         */

        $cs = new CCDACS();
        $cs->setCode(" ");
        $this->setLanguageCode($cs);
        $tabTest[] = $this->sample("Test avec un languageCode incorrect", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un languageCode correct
         */

        $cs->setCode("TEST");
        $this->setLanguageCode($cs);
        $tabTest[] = $this->sample("Test avec un languageCode correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un confidentialityCode incorrect
         */

        $ce = new CCDACE();
        $ce->setCode(" ");
        $this->setConfidentialityCode($ce);
        $tabTest[] = $this->sample("Test avec un confidentialityCode incorrect", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un confidentialityCode correct
         */

        $ce->setCode("TEST");
        $this->setConfidentialityCode($ce);
        $tabTest[] = $this->sample("Test avec un confidentialityCode correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        return $tabTest;
    }
}
