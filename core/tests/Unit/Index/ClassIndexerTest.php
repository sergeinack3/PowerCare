<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Index;

use Exception;
use Ox\Core\Index\ClassIndexer;
use Ox\Core\Index\ClassMetadata;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ClassIndexerTest extends OxUnitTestCase
{
    protected ClassIndexer  $class_indexer;
    protected ClassMetadata $class_metadata_1;
    protected ClassMetadata $class_metadata_2;
    protected MockObject    $mock;

    protected string $id_1         = "indextest01";
    protected string $sn_1         = "CPatient";
    protected string $tab_1        = "patients";
    protected string $mod_1        = "dPpatients";
    protected string $class_trad_1 = "Patient";
    protected string $mod_trad_1   = "Dossier patient";
    protected string $field_1      = "patient_id";

    protected string $id_2         = "indextest02";
    protected string $sn_2         = "CTaskingTicketDescription";
    protected string $tab_2        = "tasking_ticket_description";
    protected string $mod_2        = "releaseNotes";
    protected string $class_trad_2 = "Note de version";
    protected string $mod_trad_2   = "Notes de version";
    protected string $field_2      = "tasking_ticket_description_id";

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->class_indexer    = new ClassIndexer();
        $this->class_metadata_1 = new ClassMetadata(
            $this->id_1,
            $this->sn_1,
            $this->tab_1,
            $this->mod_1,
            $this->class_trad_1,
            $this->mod_trad_1,
            $this->field_1,
        );
        $this->class_metadata_2 = new ClassMetadata(
            $this->id_2,
            $this->sn_2,
            $this->tab_2,
            $this->mod_2,
            $this->class_trad_2,
            $this->mod_trad_2,
            $this->field_2,
        );

        $this->mock = $this->getMock();
    }

    /**
     * @throws ReflectionException
     * @throws TestsException
     */
    public function testBuild(): void
    {
        $created = $this->invokePrivateMethod($this->mock, 'build');

        $this->assertEquals([$this->class_metadata_1, $this->class_metadata_2], $created);
    }

    public function testSearch(): void
    {
        $classes = $this->mock->search($this->sn_1);

        /** @var ClassMetadata $_class */
        foreach ($classes as $_class) {
            if ($_class->getShortname() !== $this->class_metadata_1->getShortname()) {
                continue;
            }
            $this->assertEquals($this->class_metadata_1->getShortname(), $_class->getShortname());
        }
    }

    private function getMock(): MockObject
    {
        $indexer = (new self())->getMockBuilder(ClassIndexer::class)
            ->onlyMethods(['getActiveModule', 'build'])
            ->getMock();
        $indexer->expects($this->any())->method('getActiveModule')->willReturn(
            [$this->mod_1, $this->mod_2]
        );
        $indexer->expects($this->any())->method('build')->willReturn(
            [$this->class_metadata_1, $this->class_metadata_2]
        );

        return $indexer;
    }
}
