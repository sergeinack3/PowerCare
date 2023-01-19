<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\Medications;

use Ox\Core\CMbDT;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\Medications\CDAEntryFRVaccination;
use Ox\Interop\Cda\Components\Sections\IHE\Medications\CDASectionImmunizations;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Medicament\IMedicamentProduit;

/**
 * Class CDASectionFRVaccinations
 *
 * @package Ox\Interop\Cda\Components\Sections\HL7\Medications
 */
class CDASectionFRVaccinations extends CDASectionImmunizations
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.147';

    /** @var CInjection[] */
    private $injections;

    /**
     * CDASectionFRVaccinations constructor.
     *
     * @param CCDAFactory  $factory
     * @param CInjection[] $injections
     */
    public function __construct(CCDAFactory $factory, array $injections)
    {
        parent::__construct($factory);

        $this->injections = $injections;
    }

    /**
     * Entries - FR-Traitement [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesVaccinations(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - FR-Vaccination [1..*]
        foreach ($this->injections as $injection) {
            $entry = (new CDAEntryFRVaccination($this->factory, $injection))->buildEntry();
            $section->appendEntry($entry);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Note de vaccination');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, "11369-6", "Historique des vaccinations");
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $injection      = reset($this->injections);
        $injection_date = CMbDT::format($injection->injection_date, "%d/%m/%Y");
        $vaccinations   = $injection->loadRefVaccinations();

        /** @var IMedicamentProduit $product */
        $product = $injection->loadRefProduit();

        $content = $this->fetchSmarty(
            'Components/Sections/ANS/Medications/fr_vaccinations',
            [
                'injection_date' => $injection_date,
                'product'        => $product,
                'injection'      => $injection,
                'vaccinations'   => $vaccinations,
            ]
        );

        // set text on section
        CCDADocTools::setText($section, $content);
    }
}
