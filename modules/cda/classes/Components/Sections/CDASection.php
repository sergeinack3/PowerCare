<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\CDAComponent;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASection extends CDAComponent
{
    /** @var CDASection[]  */
    protected $sub_sections = [];

    /** @var CCDAPOCD_MT000040_Section */
    protected $section;

    /** @var string */
    protected $id;

    /**
     * CCDASection constructor.
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $this->section = new CCDAPOCD_MT000040_Section();

        $this->id = $this->generateUUID();
    }

    /**
     * Build component Section
     *
     * @return CCDAPOCD_MT000040_Section
     */
    final public function build(): CCDAClasseBase
    {
        $section = $this->section;

        // template ids
        $this->setTemplateIds($section);

        // add ID on section
        $this->setId($section);

        // Code
        $this->setCode($section);

        // Text
        $this->setText($section);

        // Title
        $this->setTitle($section);

        // build entries
        $this->buildEntries($section);

        return $section;
    }

    /**
     * Build all entries of section
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // to implement in classes which extends of self
    }

    /**
     * Set id of section
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setId(CCDAPOCD_MT000040_Section $section): void
    {
        $root = new CCDAII();
        $root->setRoot($this->id);
        $section->setId($root);
    }

    /**
     * Set templates id on section
     *
     * @param CCDAPOCD_MT000040_Section $section
     *
     * @return array
     */
    protected function setTemplateIds(CCDAPOCD_MT000040_Section $section): array
    {
        $template_ids = $this->getTemplateIds();

        return CCDADocTools::addTemplatesId($section, $template_ids);
    }

    /**
     * Set code of section
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {

    }

    /**
     * Set Text on section
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {

    }

    /**
     * Set Title on section
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {

    }
}
