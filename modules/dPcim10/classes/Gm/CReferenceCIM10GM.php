<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cim10\Gm;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Mediboard\Cim10\CCodeCIM10;

/**
 * Represent a text reference to a CIM10 code or chapter
 */
class CReferenceCIM10GM implements IShortNameAutoloadable
{
    /** @var int The id of the reference */
    public $id;

    /** @var int The id of the note where the reference is */
    public $note_id;

    /** @var int The id of the referenced object */
    public $code_id;

    /** @var string The type of the referenced object (code or category) */
    public $code_type;

    /** @var string The text of the reference */
    public $text;

    /** @var string The usage of the referenced object (dagger, asterisk or optional) */
    public $usage;

    /**
     * CReferenceCIM10GM constructor.
     *
     * @param array $data The data to set the object
     */
    public function __construct($data = [])
    {
        foreach ($data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->$field = $value;
            }
        }
    }

    /**
     * Load the note with the given id
     *
     * @param int $id The note id
     *
     * @return CReferenceCIM10GM
     */
    public static function get($id)
    {
        $ds = CCodeCIM10::getDS();

        $data = $ds->loadHash($ds->prepare("SELECT * FROM `references_gm` WHERE `id` = ?1;", $id));
        if (!$data) {
            return null;
        }

        return new self($data);
    }

    /**
     * Load the references for the given note
     *
     * @param CNoteCIM10GM $note The note
     *
     * @return CReferenceCIM10GM[]
     */
    public static function getFor($note)
    {
        $ds = CCodeCIM10::getDS();

        $query = $ds->prepare("SELECT * FROM `references_gm` WHERE `note_id` = ?1;", $note->id);

        $results = $ds->loadList($query);

        $references = [];
        foreach ($results as $result) {
            $references[] = new self($result);
        }

        return $references;
    }
}
