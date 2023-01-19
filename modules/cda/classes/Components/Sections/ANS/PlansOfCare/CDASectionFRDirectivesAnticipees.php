<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\PlansOfCare;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\PlansOfCare\CDAEntryFRDirectiveAnticipee;
use Ox\Interop\Cda\Components\Sections\IHE\PlansOfCare\CDASectionCodedAdvanceDirectives;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACR;
use Ox\Interop\Cda\Datatypes\Base\CCDACV;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

/**
 * Class CDAEntryFRDirectivesAnticipees
 *
 * @package Ox\Interop\Cda\Components\Sections\ANS\PlansOfCare
 */
class CDASectionFRDirectivesAnticipees extends CDASectionCodedAdvanceDirectives
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.157';

    /** * @var CInjection */
    private $injection;

    /**
     * CDAEntryFRTraitement constructor.
     *
     * @param CCDAFactory                 $factory
     * @param CPrescriptionLineMedicament $prescription_line
     */
    public function __construct(CCDAFactory $factory, CInjection $injection)
    {
        parent::__construct($factory);

        $this->injection = $injection;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     *
     * @throws \Ox\Interop\Cda\Exception\CCDAException
     */
    protected function buildDirectives(CCDAPOCD_MT000040_Section $section)
    {
        $code_entry = $this->getRefusedCode();

        $entry = (new CDAEntryFRDirectiveAnticipee($this->factory, $this->injection))
            ->setEffectiveTime($this->injection->injection_date)
            ->setEntityText("#" . $this->injection->_guid)
            ->setValue(true)
            ->setEntityCode($code_entry)
            ->buildEntry();
        $section->appendEntry($entry);
    }

    /**
     * @return CCDACD
     */
    protected function getRefusedCode(): CCDACD
    {
        $entries = CANSValueSet::loadEntries('directiveAnticipee', 'MED-297');
        $code_cd = CCDADocTools::prepareCodeCD(
            CMbArray::get($entries, 'code'),
            CMbArray::get($entries, 'codeSystem'),
            CMbArray::get($entries, 'displayName'),
            CMbArray::get($entries, 'codeSystemName')
        );

        $name = new CCDACV();
        $name->setCode('Z28.2');
        $name->setDisplayName('Refus de vaccination');
        $name->setCodeSystem('2.16.840.1.113883.6.3');
        $name->setCodeSystemName('CIM-10');

        $cr = new CCDACR();
        $cr->setName($name);
        $code_cd->setQualifier($cr);

        return $code_cd;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeCE($section, '42348-3', CLoinc::$oid_loinc, 'Directives anticipées', CLoinc::$name_loinc);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Directives anticipées');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     *
     * @throws \Exception
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $injection    = $this->injection;
        $vaccinations = $this->injection->loadRefVaccinations();

        $content = $this->fetchSmarty(
            'Components/Sections/ANS/PlansOfCare/fr_directives_anticipees',
            [
                'injection'        => $injection,
                'vaccination_name' => CAppUI::tr(
                    'CInjection-msg-refused vaccination' . (count($vaccinations) > 1 ? '|pl' : ''),
                    implode(',', CMbArray::pluck($vaccinations, 'type'))
                ),
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}
