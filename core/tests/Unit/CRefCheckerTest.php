<?php
/**
 * @package Mediboard\\${Module}
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbFieldSpecFact;
use Ox\Core\CMbObject;
use Ox\Core\FieldSpecs\CRefChecker;
use Ox\Core\FieldSpecs\CRefCheckerException;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Tests\Unit\Models\CFoo;
use Ox\Interop\Eai\CExchangeTransportLayer;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CRefCheckerTest
 *
 * @package Ox\Core\Tests\Unit
 */
class CRefCheckerTest extends OxUnitTestCase
{

    const CLASS_ABSTRACT     = 'CExchangeTransportLayer';
    const CLASS_MODEL        = 'CExchangeHTTP';
    const CLASS_MODEL_RANDOM = 'CNote';
    const CLASS_NOT_STORABLE = 'CFoo';

    public function setUp(): void
    {
        parent::setUp();
        if (!class_exists('CFoo')) {
            class_alias(CFoo::class, 'CFoo');
        }
    }

    /**
     * @dataProvider checkClassiqueProvider
     *
     * @param bool     $failed
     * @param CRefSpec $spec
     *
     * @throws Exception
     */
    function testCheckClassique($failed, CRefSpec $spec)
    {
        $ref_check = new CRefChecker($spec);

        if ($failed) {
            $this->expectException(CRefCheckerException::class);
            $ref_check->check();
        } else {
            $this->assertTrue($ref_check->check());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    function checkClassiqueProvider()
    {
        return [
            // failed
            //[true, $this->makeSpec('object_id', 'ref class|CMbObject')],
            [true, $this->makeSpec('object_id', 'ref class|CStoredObject')],
            [true, $this->makeSpec('object_id', 'ref class|CFooBar')],
            [true, $this->makeSpec('object_id', 'ref class|' . static::CLASS_NOT_STORABLE)],
            //[true, $this->makeSpec('object_id', 'ref class|' . static::CLASS_ABSTRACT)],
            // succes
            [false, $this->makeSpec('object_id', 'ref class|' . static::CLASS_MODEL)],
        ];
    }


    /**
     * @dataProvider checkMetaProvider
     *
     * @param bool         $failed
     * @param CRefSpec     $ref_spec
     * @param CMbFieldSpec $meta_spec
     */
    function testCheckMeta($failed, CRefSpec $ref_spec, CMbFieldSpec $meta_spec)
    {
        $ref_check = new CRefChecker($ref_spec, $meta_spec);

        if ($failed) {
            $this->expectException(CRefCheckerException::class);
            $ref_check->check();
        } else {
            $this->assertTrue($ref_check->check());
        }
    }


    /**
     * @return array
     * @throws Exception
     */
    function checkMetaProvider()
    {
        return [
            // failed
            [
                true,
                $this->makeSpec('object_id', 'ref class|' . static::CLASS_NOT_STORABLE . ' meta|object_class'),
                $this->makeSpec('object_class', 'enum list|' . static::CLASS_MODEL),
            ],
            [
                true,
                $this->makeSpec('object_id', 'ref class|' . static::CLASS_ABSTRACT . ' meta|object_class'),
                $this->makeSpec('object_class', 'enum list|' . static::CLASS_MODEL_RANDOM),
            ],
            // succes
            [
                false,
                $this->makeSpec('object_id', 'ref class|CStoredObject meta|object_class'),
                $this->makeSpec('object_class', 'enum list|' . static::CLASS_MODEL),
            ],
            [
                false,
                $this->makeSpec('object_id', 'ref class|' . static::CLASS_ABSTRACT . ' meta|object_class'),
                $this->makeSpec('object_class', 'enum list|' . static::CLASS_MODEL),
            ],
        ];
    }


    /**
     * @param string $prop
     * @param string $class
     * @param string $field
     *
     * @return CMbFieldSpec
     * @throws Exception
     */
    function makeSpec($field, $prop)
    {
        return CMbFieldSpecFact::getSpecWithClassName('CtestUnit', $field, $prop);
    }
}
