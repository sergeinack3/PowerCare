<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import\MovieDb;

use JsonSerializable;
use Ox\Mediboard\Files\CFile;

/**
 * Representation of an image which can be serialized to build a JSON:API resource.
 */
class MovieDbImage implements JsonSerializable
{
    /** @var string */
    private $file_type;

    /** @var string */
    private $image;

    public function __construct(string $file_type, string $image)
    {
        $this->file_type = $file_type;
        $this->image     = $image;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'         => null,
            'type'       => CFile::RESOURCE_TYPE,
            'attributes' => [
                'file_type'       => $this->file_type,
                '_base64_content' => $this->image,
            ],
        ];
    }
}
