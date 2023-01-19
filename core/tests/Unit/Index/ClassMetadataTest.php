<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Index;

use Ox\Core\Index\ClassMetadata;
use Ox\Core\Locales\Translator;
use Ox\Tests\OxUnitTestCase;

class ClassMetadataTest extends OxUnitTestCase
{
    protected ClassMetadata $class_metadata;

    protected Translator $translator;

    protected string $id         = "indextest01";
    protected string $sn         = "CPatient";
    protected string $tab        = "patients";
    protected string $mod        = "dPpatients";
    protected string $class_trad = "Patient";
    protected string $mod_trad   = "Dossier patient";
    protected string $field      = "patient_id";

    protected function setUp(): void
    {
        parent::setUp();
        $this->translator     = new Translator();
        $this->class_metadata = new ClassMetadata(
            $this->id,
            $this->sn,
            $this->tab,
            $this->mod,
            $this->class_trad,
            $this->mod_trad,
            $this->field
        );
    }

    public function testFromArray(): void
    {
        $expected = [
            "id"         => $this->class_metadata->getId(),
            "short_name" => $this->class_metadata->getShortname(),
            "table"      => $this->class_metadata->getTableName(),
            "module"     => $this->class_metadata->getModule(),
            "key"        => $this->class_metadata->getFieldName(),
        ];

        $this->assertEquals(
            $this->sn,
            $this->class_metadata->fromArray($expected, $this->translator)->getShortname()
        );
    }

    public function testToString(): void
    {
        $this->assertEquals(
            "{$this->sn} | {$this->tab} | {$this->field} | {$this->mod} | {$this->class_trad} | {$this->mod_trad}",
            (string)$this->class_metadata
        );
    }

    public function testFromString(): void
    {
        $string = "{$this->sn} | {$this->tab} | {$this->field}"
            . " | {$this->mod} | {$this->class_trad} | {$this->mod_trad}";

        $this->assertEquals(
            $this->class_metadata,
            $this->class_metadata->fromString($this->id, $string)
        );
    }

    public function testGetIndexableData(): void
    {
        $expected = [
            "title" => $this->sn,
            "_id"   => $this->class_metadata->getId(),
            "body"  => (string)$this->class_metadata,
        ];

        $this->assertEquals(
            $expected,
            $this->class_metadata->getIndexableData()
        );
    }
}
