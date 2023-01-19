<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\CDAComponent;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Entry;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Organizer;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Procedure;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_SubstanceAdministration;

/**
 * Class CDAEntry
 *
 * @package Ox\Interop\Cda\Components\Entries
 */
abstract class CDAEntry extends CDAComponent
{
    /** @var CCDAPOCD_MT000040_Entry */
    protected $entry;

    /** @var CCDAClasseCda */
    protected $entry_content;

    /** @var string */
    protected $id;

    /**
     * CCDAEntry constructor.
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $this->entry = new CCDAPOCD_MT000040_Entry();

        $this->id = $this->generateUUID();
    }

    /**
     * Build component and set it on Entry component
     *
     * @return CCDAPOCD_MT000040_Entry
     * @throws CCDAException
     */
    final public function buildEntry(): CCDAPOCD_MT000040_Entry
    {
        $entry = $this->entry;
        $entry_content = $this->entry_content = $this->build();
        $class = get_class($entry_content);
        switch ($class) {
            case CCDAPOCD_MT000040_Act::class:
                /** @var CCDAPOCD_MT000040_Act $entry_content */
                $entry->setAct($entry_content);
                break;

            case CCDAPOCD_MT000040_SubstanceAdministration::class:
                /** @var CCDAPOCD_MT000040_SubstanceAdministration $entry_content */
                $entry->setSubstanceAdministration($entry_content);
                break;

            case CCDAPOCD_MT000040_Procedure::class:
                /** @var CCDAPOCD_MT000040_Procedure $entry_content */
                $entry->setProcedure($entry_content);
                break;

            case CCDAPOCD_MT000040_Observation::class:
                /** @var $entry_content CCDAPOCD_MT000040_Observation */
                $entry->setObservation($entry_content);
                break;

            case CCDAPOCD_MT000040_Organizer::class:
                /** @var $entry_content CCDAPOCD_MT000040_Organizer */
                $entry->setOrganizer($entry_content);
                break;

            default:
                throw new CCDAException(
                    "class '$class' not supported, you should be implement it in CDAEntry::buildEntry"
                );
        }

        return $entry;
    }

    /**
     * Build component
     *
     * @return CCDAClasseCda
     */
    public function build(): CCDAClasseBase
    {
        $entry_content = $this->entry_content;
        // Template ids
        $this->setTemplateIds($entry_content);

        // Id
        $this->setId($entry_content);

        // build content
        $this->buildContent($entry_content);

        return $this->entry_content;
    }

    /**
     * Build content of component
     *
     * @param CCDAClasseCda $entry_content
     *
     * @return void
     */
    abstract protected function buildContent(CCDAClasseCda $entry_content): void;


    /**
     * Set Template ids on component
     */
    protected function setTemplateIds(CCDAClasseCda $entry_content): void
    {
        CCDADocTools::addTemplatesId($entry_content, $this->getTemplateIds());
    }

    /**
     * Set id on component
     *
     * @param CCDAClasseCda $entry_content
     */
    protected function setId(CCDAClasseCda $entry_content): void
    {
        if (method_exists($entry_content, 'appendId')) {
            CCDADocTools::setId($entry_content, $this->id);
        }
    }
}
