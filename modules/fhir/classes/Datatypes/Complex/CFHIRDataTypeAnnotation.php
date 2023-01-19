<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;

/**
 * FHIR data type
 */
class CFHIRDataTypeAnnotation extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Annotation';

    /**
     * @var CFHIRDataTypeChoice
     * @choice authorReference
     * @choice authorString
     */
    public $author;

    /** @var CFHIRDataTypeDateTime */
    public $time;

    /** @var CFHIRDataTypeMarkdown */
    public $text;

    /**
     * @param string $text
     *
     * @return CFHIRDataTypeAnnotation
     */
    public function setText(string $text): CFHIRDataTypeAnnotation
    {
        $this->text = new CFHIRDataTypeMarkdown($text);

        return $this;
    }
}
