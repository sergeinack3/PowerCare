<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Tests\Unit;

use Exception;
use Ox\Core\CFileParser;
use Ox\Tests\OxUnitTestCase;

/**
 * Test the parsing of image files containing test
 */
class CFileParserTest extends OxUnitTestCase
{
    protected const TIKA_UNAVAILABLE_MESSAGE = 'Tika server is unavailable';

    /** @var CFileParser|null  */
    protected static $parser = null;

    /**
     * @return void
     */
    public static function setUpBeforeClass() :void
    {
        try {
            self::$parser = new CFileParser();
        } catch (Exception $e) {
            /* Do nothing */
        }
    }

    /**
     * @return void
     */
    public function setUp() :void
    {
        parent::setUp();

        if (!self::$parser instanceof CFileParser) {
            $this->markTestSkipped(self::TIKA_UNAVAILABLE_MESSAGE);
        }
    }

    /**
     * @group schedules
     * @dataProvider metaDataProvider
     *
     * @param string $filename
     * @param string $title
     * @param string $language
     * @param string $contentType
     * @return void
     * @throws Exception
     */
    public function testGetMetadata(string $filename, string $title, string $language, string $contentType): void
    {
        $metadata = self::$parser->getMetadata($filename);

        $this->assertEquals($title, $metadata->title);
        $this->assertEquals($language, $metadata->meta->{'language'});
        $this->assertEquals($contentType, $metadata->meta->{'Content-Type'});
    }

    /**
     * @group schedules
     * @dataProvider contentDataProvider
     *
     * @param string $filename
     * @param string $content
     * @return void
     * @throws Exception
     */
    public function testGetContent(string $filename, string $content): void
    {
        $this->assertEquals(
            $content,
            self::$parser->getContent($filename)
        );
    }

    /**
     * @return array
     */
    public function metaDataProvider(): array
    {
        return [
            [
                __DIR__ . '/../Resources/Parser/sample-fr.png',
                'sample-fr',
                'fr',
                'image/png',
            ],
        ];
    }

    /**
     * @return array
     */
    public function contentDataProvider(): array
    {
        return [
            [
                __DIR__ . '/../Resources/Parser/sample-fr.png',
                "Exemple de texte pour Apache Tika avec Tesseract OCR"
            ],
            [
                __DIR__ . '/../Resources/Parser/sample-id-card-old-bottom-fr.png',
                "IDFRABERTHIER<<<<<<<<<<<<<<<<<92C001\n8806923102858CORINNE<<<<<<<6512068F8"
            ],
            [
                __DIR__ . '/../Resources/Parser/sample-id-card-new-bottom-lq-fr.png',
                "IDFRAX4RTBPFW4G<<<<<<<<<EL<E<<\n9007138F3002119FRA<<<<<<<<<<<6\nMARTIN<<MAELYS<GAELLE<MARIE<<<"
            ],
        ];
    }
}
