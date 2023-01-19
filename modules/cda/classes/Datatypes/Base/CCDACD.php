<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * A concept descriptor represents any kind of concept usually
 * by giving a code defined in a code system.  A concept
 * descriptor can contain the original text or phrase that
 * served as the basis of the coding and one or more
 * translations into different coding systems. A concept
 * descriptor can also contain qualifiers to describe, e.g.,
 * the concept of a "left foot" as a postcoordinated term built
 * from the primary code "FOOT" and the qualifier "LEFT".
 * In exceptional cases, the concept descriptor need not
 * contain a code but only the original text describing
 * that concept.
 */
class CCDACD extends CCDAANY
{

    /**
     * The text or phrase used as the basis for the coding.
     * @var CCDAED
     */
    public $originalText;

    /**
     * Specifies additional codes that increase the
     * specificity of the primary code.
     * @var CCDACR
     */
    public $qualifier = [];

    /**
     * A set of other concept descriptors that translate
     * this concept descriptor into other code systems.
     * @var CCDACD
     */
    public $translation = [];

    /**
     * The plain code symbol defined by the code system.
     * For example, "784.0" is the code symbol of the ICD-9
     * code "784.0" for headache.
     * @var CCDA_base_cs
     */
    public $code;

    /**
     * Specifies the code system that defines the code.
     * @var CCDA_base_uid
     */
    public $codeSystem;

    /**
     * A common name of the coding system.
     * @var CCDA_base_st
     */
    public $codeSystemName;

    /**
     * If applicable, a version descriptor defined
     * specifically for the given code system.
     * @var CCDA_base_st
     */
    public $codeSystemVersion;

    /**
     * A name or title for the code, under which the sending
     * system shows the code value to its users.
     * @var CCDA_base_st
     */
    public $displayName;

    /**
     * Getter code
     *
     * @return CCDA_base_cs CCDA_base_cs code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Setter Code
     *
     * @param String $code String
     *
     * @return void
     */
    public function setCode($code)
    {
        if (!$code) {
            $this->code = null;

            return;
        }
        $cod = new CCDA_base_cs();
        $cod->setData($code);
        $this->code = $cod;
    }

    /**
     * Getter CodeSystem
     *
     * @return CCDA_base_uid
     */
    public function getCodeSystem()
    {
        return $this->codeSystem;
    }

    /**
     * Setter CodeSystem
     *
     * @param String $codeSystem String
     *
     * @return void
     */
    public function setCodeSystem($codeSystem)
    {
        if (!$codeSystem) {
            $this->codeSystem = null;

            return;
        }
        $codeSys = new CCDA_base_uid();
        $codeSys->setData($codeSystem);
        $this->codeSystem = $codeSys;
    }

    /**
     * Getter CodeSystemName
     *
     * @return CCDA_base_st
     */
    public function getCodeSystemName()
    {
        return $this->codeSystemName;
    }

    /**
     * Setter codeSystemName
     *
     * @param String $codeSystemName String
     *
     * @return void
     */
    public function setCodeSystemName($codeSystemName)
    {
        if (!$codeSystemName) {
            $this->codeSystemName = null;

            return;
        }
        $codeSysN = new CCDA_base_st();
        $codeSysN->setData($codeSystemName);
        $this->codeSystemName = $codeSysN;
    }

    /**
     * Getter CodeSystemVersion
     *
     * @return CCDA_base_st
     */
    public function getCodeSystemVersion()
    {
        return $this->codeSystemVersion;
    }

    /**
     * Setter codeSystemVersion
     *
     * @param String $codeSystemVersion String
     *
     * @return void
     */
    public function setCodeSystemVersion($codeSystemVersion)
    {
        if (!$codeSystemVersion) {
            $this->codeSystemVersion = null;

            return;
        }
        $codeSysV = new CCDA_base_st();
        $codeSysV->setData($codeSystemVersion);
        $this->codeSystemVersion = $codeSysV;
    }

    /**
     * Getter DisplayName
     *
     * @return CCDA_base_st
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Setter displayName
     *
     * @param String $displayName String
     *
     * @return void
     */
    public function setDisplayName($displayName)
    {
        if (!$displayName) {
            $this->displayName = null;

            return;
        }
        $diplay = new CCDA_base_st();
        $diplay->setData($displayName);
        $this->displayName = $diplay;
    }

    /**
     * Getter OriginalText
     *
     * @return CCDAED
     */
    public function getOriginalText()
    {
        return $this->originalText;
    }

    /**
     * Setter originalText
     *
     * @param CCDAED $originalText CCDAED
     *
     * @return void
     */
    public function setOriginalText($originalText)
    {
        $this->originalText = $originalText;
    }

    /**
     * Getter Qualifier
     *
     * @return CCDACR
     */
    public function getQualifier()
    {
        return $this->qualifier;
    }

    /**
     * Setter qualifier
     *
     * @param CCDACR $qualifier CCDACR
     *
     * @return void
     */
    public function setQualifier($qualifier)
    {
        array_push($this->qualifier, $qualifier);
    }

    /**
     * Getter Translation
     *
     * @return CCDACD
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Setter translation
     *
     * @param CCDACD $translation CCDACD
     *
     * @return void
     */
    public function addTranslation(CCDACD $translation)
    {
        array_push($this->translation, $translation);
    }

    /**
     * Efface le tableau translation
     *
     * @return void
     */
    public function resetListTranslation()
    {
        $this->translation = [];
    }

    /**
     * Efface le tableau qualifier
     *
     * @return void
     */
    public function resetListQualifier()
    {
        $this->qualifier = [];
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props                      = parent::getProps();
        $props["originalText"]      = "CCDAED xml|element max|1";
        $props["qualifier"]         = "CCDACR xml|element";
        $props["translation"]       = "CCDACD xml|element";
        $props["code"]              = "CCDA_base_cs xml|attribute";
        $props["codeSystem"]        = "CCDA_base_uid xml|attribute";
        $props["codeSystemName"]    = "CCDA_base_st xml|attribute";
        $props["codeSystemVersion"] = "CCDA_base_st xml|attribute";
        $props["displayName"]       = "CCDA_base_st xml|attribute";

        return $props;
    }
}
