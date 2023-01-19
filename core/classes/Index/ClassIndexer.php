<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Index;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CObjectIndexer;
use Ox\Core\CStoredObject;
use Ox\Core\Locales\Translator;
use Ox\Core\Module\CModule;

/**
 * Class building the indexer and can be used for searching in this index
 */
class ClassIndexer
{
    private CObjectIndexer $indexer;

    private Translator $translator;

    /**
     * Construct a new object indexer with specific name and version
     * @throws Exception
     */
    public function __construct()
    {
        $this->translator = new Translator();
        $this->indexer    = new CObjectIndexer(
            $this->getName(),
            CClassMap::getSN(ClassMetadata::class),
            $this->getVersion(),
            function () {
                return $this->build();
            },
            null
        );
    }

    /**
     * Search method for classes indexer
     *
     * @param string $keywords
     * @param bool   $to_array
     *
     * @return ClassMetadata[]
     * @throws Exception
     */
    public function search(string $keywords, bool $to_array = false): array
    {
        $classes = [];
        foreach ($this->indexer->search($keywords) as $_res) {
            $class     = ClassMetadata::fromString($_res['_id'], $_res['body']);
            $classes[] = ($to_array) ? $class->toArray() : $class;
        }

        return $classes;
    }

    /**
     * Building content for object indexer by reading data from classmap
     * @throws Exception
     */
    protected function build(): array
    {
        // Read includes/classmap.php content and return an array
        $classes = CClassMap::getInstance()->getClassChildren(CStoredObject::class, false, true);

        $objects = [];
        foreach ($classes as $_class) {
            $class       = (array)CClassMap::getInstance()->getClassMap($_class);
            $class['id'] = uniqid();
            $module      = $class['module'];
            $table       = $class['table'];

            if ($module !== null && $table !== null && $this->getActiveModule($module)) {
                $objects[] = ClassMetadata::fromArray($class, $this->translator);
            }
        }

        return $objects;
    }

    /**
     * Return the name for building indexer.
     * The name depends on current user locale (fr-classes / en-classes...).
     */
    protected function getName(): string
    {
        return "{$this->translator->getCurrentLocale()}-classes";
    }

    /**
     * Return all or only one active module name
     * @return CModule|CModule[]|null
     */
    protected function getActiveModule(?string $module_name = null)
    {
        return CModule::getActive($module_name);
    }

    /**
     * Return an hash generated with all active modules.
     * Version will change if a module have been disabled/deleted or enabled.
     */
    protected function getVersion(): string
    {
        return CModule::getModulesSignature(true);
    }
}
