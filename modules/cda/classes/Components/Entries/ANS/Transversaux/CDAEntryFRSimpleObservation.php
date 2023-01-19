<?php

namespace Ox\Interop\Cda\Components\Entries\ANS\Transversaux;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\Transversaux\CDAEntrySimpleObservation;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;

/**
 * Class CDAEntryFRSimpleObservation
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\Transversaux
 */
class CDAEntryFRSimpleObservation extends CDAEntrySimpleObservation
{
    /** @var string */
    public const  TEMPLATE_ID = '1.2.250.1.213.1.1.3.48';

    /** @var CCDACD */
    protected $code_CD;

    /** @var string */
    protected $text_content;

    /** @var bool */
    protected $is_text_content_reference = false;

    /** @var string */
    protected $class_code = 'OBS';

    /** @var string */
    protected $mood_code = 'EVN';

    /** @var string */
    protected $date_low;
    /** @var string */
    protected $date_high;
    /** @var string */
    protected $date_nullflavor;

    /** @var string */
    protected $status_code;

    /** @var CCDACD|string */
    protected $code_value;

    /**
     * CDAEntryFRSimpleObservation constructor.
     *
     * @param CCDAFactory $factory
     * @param array       $options
     */
    public function __construct(CCDAFactory $factory, array $options = [])
    {
        parent::__construct($factory);

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     *
     * @return void
     */
    protected function buildContent(CCDAClasseCda $observation): void
    {
        $observation->setClassCode($this->class_code);
        $observation->setMoodCode($this->mood_code);

        // EffectiveTime
        $this->setEffectiveTime($observation);

        // RepeatNumber
        $this->setRepeatNumber($observation);

        // Value
        $this->setValue($observation);

        // InterpretationCode
        $this->setInterpretationCode($observation);

        // MethodCode
        $this->setMethodCode($observation);

        // TargetSideCode
        $this->setTargetSideCode($observation);

        // Author
        $this->setAuthor($observation);
    }

    /**
     * @param string $content
     * @param bool   $is_reference
     */
    public function addText(string $content, bool $is_reference = false): void
    {
        $this->text_content = $content;
        $this->is_text_content_reference = $is_reference;
    }

    /**
     * @param string|null $date_low
     * @param string|null $date_high
     * @param string|null $nullflavor
     */
    public function addEffectiveTime(?string $date_low, ?string $date_high = null, ?string $nullflavor = null): void
    {
        $this->date_low        = $date_low;
        $this->date_high       = $date_high;
        $this->date_nullflavor = $nullflavor;
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setCode(CCDARIMAct $observation): void
    {
        if (!$this->code_CD) {
            return;
        }

        $observation->setCode($this->code_CD);
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setText(CCDARIMAct $observation): void
    {
        if (!$this->text_content) {
            return;
        }

        if ($this->is_text_content_reference) {
            CCDADocTools::setTextWithReference($observation, $this->text_content);
        } else {
            CCDADocTools::setText($observation, $this->text_content);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setStatusCode(CCDARIMAct $observation): void
    {
        if (!$this->status_code) {
            return;
        }

        CCDADocTools::setStatusCode($observation, $this->status_code);
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setRepeatNumber(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setEffectiveTime(CCDAPOCD_MT000040_Observation $observation): void
    {
        $ivl_ts = new CCDAIVL_TS();
        if ($this->date_low && $this->date_high) {
            CCDADocTools::setLowAndHighTime($observation, $this->date_low, $this->date_high);
        } elseif ($this->date_low) {
            $ivl_ts->setValue($this->date_low);
            $observation->setEffectiveTime($ivl_ts);
        } elseif ($this->date_nullflavor) {
            $ivl_ts->setNullFlavor($this->date_nullflavor);
            $observation->setEffectiveTime($ivl_ts);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setValue(CCDAPOCD_MT000040_Observation $observation): void
    {
        if (!$this->code_value) {
            return;
        }

        $observation->appendValue($this->code_value);
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setMethodCode(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setInterpretationCode(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setTargetSideCode(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setAuthor(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not implemented
    }
}
