<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components;

use Ox\Core\CMbSecurity;
use Ox\Core\CSmartyDP;
use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\CDAEntry;
use Ox\Interop\Cda\Components\Sections\CDASection;

/**
 * Class CDAComponent
 *
 * @package Ox\Interop\Cda\Components
 */
abstract class CDAComponent
{
    /** @var string */
    public const TEMPLATE_ID = '';

    /** @var CCDAFactory */
    protected $factory;

    /** @var array  */
    protected $template_ids = [];

    /**
     * CDAComponent constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Get all template ids from hierarchy component from constant TEMPLATE_ID
     *
     * @return string[]
     */
    public function getTemplateIds(): array
    {
        $template_ids = $this->template_ids;
        $ignore_classes = [self::class, CDASection::class, CDAEntry::class];
        foreach (class_parents($this) as $class) {
            if (in_array($class, $ignore_classes)) {
                continue;
            }

            /** @var CDASection $class */
            if ($template_id = $class::TEMPLATE_ID) {
                $template_ids[] = $template_id;
            }
        }

        $template_ids[] = $this::TEMPLATE_ID;

        return array_unique($template_ids);
    }

    /**
     * Add Template id on component
     *
     * @param string|string[] $ids
     *
     * @return array
     */
    public function addTemplateIds($ids): array
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $template_ids = array_merge($this->template_ids, $ids);

        return $this->template_ids = array_unique($template_ids);
    }

    /**
     * Build component
     *
     * @return CDAEntry|CDASection
     */
    abstract public function build(): CCDAClasseBase;

    /**
     * Generate Universally unique identifier (UUID)
     *
     * @return string
     */
    protected function generateUUID(): string
    {
        return mb_strtoupper(CMbSecurity::generateUUID());
    }

    /**
     * Fetch smarty template
     *
     * @param string      $tpl
     * @param array       $varriables
     * @param string|null $module
     *
     * @return string
     */
    protected function fetchSmarty(string $tpl, array $varriables = [], string $module = 'modules/cda'): string
    {
        $smarty = new CSmartyDP($module);
        foreach ($varriables as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $smarty->assign($key, $value);
        }

        return $smarty->fetch($tpl);
    }
}
