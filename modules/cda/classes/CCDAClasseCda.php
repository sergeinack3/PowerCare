<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDANullFlavor;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_InfrastructureRoot_typeId;

/**
 * CCDAClasseBase Class
 */
class CCDAClasseCda extends CCDAClasseBase
{

    /**
     * @var CCDAII
     */
    public $typeId;

    /**
     * @var CCDANullFlavor
     */
    public $nullFlavor;

    /**
     * @var CCDACS
     */
    public $realmCode;

    /**
     * @var CCDAII
     */
    public $templateId = [];

    /**
     * Getter typeID
     *
     * @return CCDAII
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Assigne typeID au valeurs par défaut
     *
     * @return void
     */
    public function setTypeId()
    {
        $typeID       = new CCDAPOCD_MT000040_InfrastructureRoot_typeId();
        $this->typeId = $typeID;
    }

    /**
     * Getter realmCode
     *
     * @return CCDACS
     */
    public function getRealmCode(): CCDACS
    {
        return $this->realmCode;
    }

    /**
     * Ajoute un realmCode dans le tableau
     *
     * @param string $realmCode Realm code
     *
     * @return void
     */
    public function setRealmCode(string $realmCode): void
    {
        $cs = new CCDACS();
        $cs->setCode($realmCode);

        $this->realmCode = $cs;
    }

    /**
     * Getter templateId
     *
     * @return CCDAII[]
     */
    function getTemplateID()
    {
        return $this->templateId;
    }

    /**
     * Getter nullFlavor
     *
     * @return CCDANullFlavor
     */
    public function getNullFlavor()
    {
        return $this->nullFlavor;
    }

    /**
     * Setter nullFlavor
     *
     * @param String $nullFlavor String
     *
     * @return void
     */
    public function setNullFlavor($nullFlavor)
    {
        if (!$nullFlavor) {
            $this->nullFlavor = null;

            return;
        }
        $null = new CCDANullFlavor();
        $null->setData($nullFlavor);
        $this->nullFlavor = $null;
    }

    /**
     * Retourne le nom de la classe
     *
     * @return String
     */
    function getNameClass()
    {
        $name = CClassMap::getSN($this);
        $part = substr($name, strpos($name, "_") + 1);
        $part = str_replace("_", ".", $part);
        $name = substr_replace($name, $part, strpos($name, "_") + 1);
        $name = substr($name, 4);

        return $name;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props               = [];
        $props["realmCode"]  = "CCDACS xml|element";
        $props["typeId"]     = "CCDAPOCD_MT00040_InfrastructureRoot_typeId xml|element max|1";
        $props["templateId"] = "CCDAII xml|element";
        $props["nullFlavor"] = "CCDANullFlavor xml|attribute";

        return $props;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListRealmCode()
    {
        $this->realmCode = [];
    }

    /**
     * Ajoute un templateId dans le tableau
     *
     * @param CCDAII $templateId CCDAII
     *
     * @return void
     */
    function appendTemplateId($templateId)
    {
        array_push($this->templateId, $templateId);
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListTemplateId()
    {
        $this->templateId = [];
    }
}
