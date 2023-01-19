<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\PlansOfCare;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\PlansOfCare\CDAEntryAdvancedDirectiveObservation;
use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

/**
 * Class CDAEntryFRDirectiveAnticipee
 *
 * @package Ox\Interop\Cda\Components\Entry\ANS\PlansOfCare
 */
class CDAEntryFRDirectiveAnticipee extends CDAEntryAdvancedDirectiveObservation
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.54';

    /** * @var CInjection */
    private $injection;

    /** @var CCDAIVL_TS */
    private $effectiveTime;

    /** @var bool */
    private $value;

    /** @var CCDACD */
    private $code;

    /** @var string */
    private $text;

    /**
     * CDAEntryFRTraitement constructor.
     *
     * @param CCDAFactory $factory
     * @param CPrescriptionLineMedicament $prescription_line
     */
    public function __construct(CCDAFactory $factory, CInjection $injection)
    {
        parent::__construct($factory);

        $this->injection = $injection;
    }

    /**
     * @param bool $value
     *
     * @return CDAEntryFRDirectiveAnticipee
     */
    public function setValue(bool $value): CDAEntryFRDirectiveAnticipee
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return CDAEntryFRDirectiveAnticipee
     */
    public function setEntityText(string $text): CDAEntryFRDirectiveAnticipee
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param CCDAIVL_TS|string $effectiveTime
     *
     * @return CDAEntryFRDirectiveAnticipee
     */
    public function setEffectiveTime($effectiveTime): CDAEntryFRDirectiveAnticipee
    {
        if (is_string($effectiveTime)) {
            $effectiveTime = CCDADocTools::prepareLowTime($effectiveTime);
            $effectiveTime = CCDADocTools::prepareHighTime(null, $effectiveTime, 'NA');
        }

        if ($effectiveTime instanceof CCDAIVL_TS) {
            $this->effectiveTime = $effectiveTime;
        }

        return $this;
    }

    /**
     * @param string|CCDACD $code object CCDACD or loinc code
     * @param string|null   $alt_libelle
     *
     * @return $this
     */
    public function setEntityCode($code, string $alt_libelle = null): self
    {
        if (is_string($code)) {
            $loinc = CLoinc::get($code);

            $code = CCDADocTools::prepareCodeCE(
                $loinc->code,
                $loinc::$oid_loinc,
                $alt_libelle ?: $loinc->libelle_fr,
                $loinc::$name_loinc,
            );
        }

        if ($code instanceof CCDACD) {
            $this->code = $code;
        }

        return $this;
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $entry_content
     */
    protected function buildContent(CCDAClasseCda $entry_content): void
    {
        if (!$this->injection->_id) {
            return;
        }

        // Effective time
        if ($this->effectiveTime) {
            $entry_content->setEffectiveTime($this->effectiveTime);
        }

        // Value
        if ($this->value !== null) {
            $value = new CCDABL();
            $value->setValue("$this->value");
            $entry_content->appendValue($value);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $entry_content
     */
    protected function setCode(CCDARIMAct $entry_content): void
    {
        if (!$this->code) {
            $entry_content->setCode($this->code);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $entry_content
     */
    protected function setStatusCode(CCDARIMAct $entry_content): void
    {
        CCDADocTools::setStatusCode($entry_content, 'completed');
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $entry_content
     */
    protected function setText(CCDARIMAct $entry_content): void
    {
        if ($this->text) {
            CCDADocTools::setTextWithReference($entry_content, $this->text);
        }
    }
}
