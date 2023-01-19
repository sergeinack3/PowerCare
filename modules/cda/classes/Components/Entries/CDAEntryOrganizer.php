<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Core\CMbArray;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Rim\CCDARIMRole;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component4;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Organizer;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_RelatedSubject;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Subject;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Snomed\CSnomed;

/**
 * Class CDAEntryOrganizer
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
class CDAEntryOrganizer extends CDAEntry
{
    protected CONST TEMPLATE_IDS_SUBJECT = ['1.2.250.1.213.1.1.3.60', '1.3.6.1.4.1.19376.1.5.3.1.4.15.2'];

    protected CONST TEMPLATE_IDS_OBSERVATION = ['2.16.840.1.113883.10.20.1.22', '1.3.6.1.4.1.19376.1.5.3.1.4.13', '1.3.6.1.4.1.19376.1.5.3.1.4.13.3'];

    /** @var CCDARIMAct */
    protected $entry_content;

    /** @var CAntecedent[] */
    protected $antecedents;

    protected $family_link;

    /** @var string */
    protected $status_code;

    /** @var CCDAPOCD_MT000040_Subject */
    protected $subject;

    /** @var string */
    protected $class_code = 'CLUSTER';

    /** @var string */
    protected $mood_code = 'EVN';

    /**
     * CDAEntryOrganizer constructor.
     *
     * @param CCDAFactory $factory
     * @param array       $options
     */
    public function __construct(CCDAFactory $factory, string $family_link, array $antecedents, array $options = [])
    {
        parent::__construct($factory);

        $this->entry_content = new CCDAPOCD_MT000040_Organizer();
        $this->antecedents   = $antecedents;
        $this->family_link   = $family_link;

        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * @return CCDARIMAct
     */
    public function build(): CCDAClasseBase
    {
        /** @var CCDARIMAct $entry_content */
        $entry_content =  parent::build();

        // Code
        $this->setCode($entry_content);

        // Text
        $this->setText($entry_content);

        // Title
        $this->setTitle($entry_content);

        // StatusCode
        $this->setStatusCode($entry_content);

        // Subject
        $this->setSubject($entry_content);

        return $entry_content;
    }

    /**
     * @param CCDARIMAct $entry_content
     */
    protected function buildContent(CCDAClasseCda $organizer): void
    {
        $organizer->setClassCode($this->class_code);
        $organizer->setMoodCode($this->mood_code);
    }

    /**
     * Set code on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setCode(CCDARIMAct $entry_content): void
    {
        // to implement in sub classes
    }

    /**
     * Set Title on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setTitle(CCDARIMAct $entry_content): void
    {
        // to implement in sub classes
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
     * @param CCDAPOCD_MT000040_Organizer $organizer
     */
    protected function setSubject(CCDARIMAct $organizer): void
    {
        $subject = new CCDAPOCD_MT000040_Subject();

        $subject->setTypeCode('SBJ');

        foreach (self::TEMPLATE_IDS_SUBJECT as $_template_id) {
            $ii = new CCDAII();
            $ii->setRoot($_template_id);
            $subject->appendTemplateId($ii);
        }

        // RelatedSubject
        $related_subject = new CCDAPOCD_MT000040_RelatedSubject();
        $related_subject->setClassCode('PRS');

        $family_link = CMbArray::get(CAntecedent::$mappingFamilyLink, $this->family_link);

        $entries = CANSValueSet::loadEntries('familyLink', $family_link ?: 'FAMMEMB');

        CCDADocTools::setValueCodeCECIM10(
            $related_subject, CMbArray::get($entries, 'code'),
            CMbArray::get($entries, 'codeSystem'),
            CMbArray::get($entries, 'displayName'),
            '#'. $this->family_link,
            CMbArray::get($entries, 'codeSystemName')
        );

        $subject->setRelatedSubject($related_subject);
        $this->entry_content->setSubject($subject);

        // Components
        $this->addComponents();
    }

    protected function addComponents() {
        foreach ($this->antecedents as $_antecedent) {
            $component = new CCDAPOCD_MT000040_Component4();
            $component->setTypeCode('COMP');

            $observation = new CCDAPOCD_MT000040_Observation();
            $observation->setClassCode('OBS');
            $observation->setMoodCode('EVN');

            // TemplateIds
            foreach (self::TEMPLATE_IDS_OBSERVATION as $_template_id) {
                $ii = new CCDAII();
                $ii->setRoot($_template_id);
                $observation->appendTemplateId($ii);
            }

            CCDADocTools::setCodeCD($observation, "G-1009", "1.2.250.1.213.2.12", "Diagnostic", "SNOMED 3.5");
            CCDADocTools::setStatusCode($observation, 'completed');

            CCDADocTools::setValueCodeCECIM10(
                $observation, 'G-1009',
                '1.2.250.1.213.2.12',
                'Diagnostic',
                null,
                'SNOMED 3.5'
            );

            CCDADocTools::setTextWithReference($observation, '#'.$_antecedent->_guid);

            $codes_snomed = $_antecedent->loadRefsCodesSnomed();
            $code_snomed = reset($codes_snomed);
            CCDADocTools::addCodeSnomed($observation, $code_snomed->code, CSnomed::$oid_snomed,
                $code_snomed->libelle, '#'. $_antecedent->_guid);

            $component->setObservation($observation);

            $this->entry_content->appendComponent($component);
        }
    }

    /**
     * Set Text on component
     *
     * @param CCDARIMRole $entry_content
     */
    protected function setText(CCDARIMAct $entry_content): void
    {
        // to implement in sub classes
    }
}

