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
 * Represents a note in CIM10 GM
 */
class CNoteCIM10GM implements IShortNameAutoloadable
{
    /** @var int The note's id */
    public $id;

    /** @var int The note's owner */
    public $owner_id;

    /** @var string The type of the owner (category or code) */
    public $owner_type;

    /** @var string The type of the note */
    public $type;

    /** @var string The text of the note */
    public $content;

    /** @var CReferenceCIM10GM[] The references contained in the note */
    public $_references;

    /** @var int The note's parent */
    public $parent_id;

    /** @var CNoteCIM10GM[] The note's children */
    public $_children;

    /** @var array The list of types */
    public static $types = [
        'coding-hint',
        'definition',
        'exclusion',
        'inclusion',
        'introduction',
        'note',
        'preferred',
        'preferredLong',
        'text',
    ];

    /**
     * CNoteCIM10GM constructor.
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

        if ($this->content) {
            $this->content = nl2br(ucfirst($this->content));
        }

        if ($this->id) {
            $this->loadChildren();
            $this->loadReferences();
        }
    }

    /**
     * Load the children of the note
     *
     * @return void
     */
    public function loadChildren()
    {
        $ds = CCodeCIM10::getDS();

        $this->_children = [];

        $results = $ds->loadList($ds->prepare("SELECT id FROM notes_gm WHERE parent_id = ?1;", $this->id));
        foreach ($results as $result) {
            $this->_children[] = self::get($result['id']);
        }

        $children_content = '';
        foreach ($this->_children as $child) {
            $children_content .= "<li>{$child->content}</li>";
        }

        $this->content = str_replace('[children]', $children_content, $this->content);
    }

    /**
     * Load the references of the note
     *
     * @return void
     */
    public function loadReferences()
    {
        $this->_references = CReferenceCIM10GM::getFor($this);

        if ($this->_references) {
            foreach ($this->_references as $reference) {
                if (strpos($this->content, "<a class=\"cim10-code\" data-id=\"{$reference->id}\">") !== false) {
                    if ($reference->code_type == 'code') {
                        $code = CCodeCIM10GM::getCode($reference->code_id);
                    } else {
                        $code = CCategoryCIM10GM::getCode($reference->code_id);
                    }

                    $pattern     = "#(<a class=\"cim10-code\" data-id=\"{$reference->id}\")>#";
                    $replacement = " $1 href=\"#\" onclick=\"CIM.showCode('{$code}');\">{$reference->text}";
                    switch ($reference->usage) {
                        case 'asterisk':
                            $replacement .= "&ast;";
                            break;
                        case 'dagger':
                            $replacement .= "&dagger;";
                            break;
                        case 'optional':
                            $replacement .= "?";
                            break;
                        default:
                    }

                    $this->content = preg_replace($pattern, $replacement, $this->content);
                }
            }
        }
    }

    /**
     * Load the note with the given id
     *
     * @param int $id The note id
     *
     * @return CNoteCIM10GM
     */
    public static function get($id)
    {
        $ds = CCodeCIM10::getDS();

        $data = $ds->loadHash($ds->prepare("SELECT * FROM `notes_gm` WHERE `id` = ?1;", $id));
        if (!$data) {
            return null;
        }

        return new self($data);
    }

    /**
     * Load the notes of the given type, for the given owner
     *
     * @param CCategoryCIM10GM|CCodeCIM10GM $owner The owner
     * @param string                        $type  The type of the notes to return
     *
     * @return CNoteCIM10GM[]
     */
    public static function getFor($owner, $type = null)
    {
        $notes = [];
        if (
            !$owner instanceof CCategoryCIM10GM && !$owner instanceof CCodeCIM10GM && ($type && !in_array(
                $type,
                self::$types
            ))
        ) {
            return $notes;
        }

        $ds         = CCodeCIM10::getDS();
        $owner_type = $owner instanceof CCategoryCIM10GM ? 'chapter' : 'code';

        if ($type) {
            $query = "SELECT * FROM `notes_gm` WHERE `owner_id` = ?1 AND `owner_type` = ?2 AND `type` = ?3 AND `parent_id` IS NULL;";
            $query = $ds->prepare($query, $owner->id, $owner_type, $type);
        } else {
            $query = "SELECT * FROM `notes_gm` WHERE `owner_id` = ?1 AND `owner_type` = ?2 AND `parent_id` IS NULL;";
            $query = $ds->prepare($query, $owner->id, $owner_type);
        }

        $results = $ds->loadList($query);
        foreach ($results as $result) {
            $notes[] = new self($result);
        }

        return $notes;
    }

    /**
     * Load a single note of the given type, for the given owner
     *
     * @param CCategoryCIM10GM|CCodeCIM10GM $owner The owner
     * @param string                        $type  The type of the notes to return
     *
     * @return CNoteCIM10GM
     */
    public static function getSingleFor($owner, $type = null)
    {
        $note = null;
        if (
            !$owner instanceof CCategoryCIM10GM && !$owner instanceof CCodeCIM10GM && ($type && !in_array(
                $type,
                self::$types
            ))
        ) {
            return $note;
        }

        $ds         = CCodeCIM10::getDS();
        $owner_type = $owner instanceof CCategoryCIM10GM ? 'chapter' : 'code';

        if ($type) {
            $query = "SELECT * 
        FROM `notes_gm` 
        WHERE `owner_id` = ?1 AND `owner_type` = ?2 AND `type` = ?3 AND `parent_id` IS NULL 
        ORDER BY `id` LIMIT 0, 1;";
            $query = $ds->prepare($query, $owner->id, $owner_type, $type);
        } else {
            $query = "SELECT * 
        FROM `notes_gm` 
        WHERE `owner_id` = ?1 AND `owner_type` = ?2 AND `parent_id` IS NULL 
        ORDER BY `id` LIMIT 0, 1;";
            $query = $ds->prepare($query, $owner->id, $owner_type);
        }

        $result = $ds->loadHash($query);
        if ($result) {
            $note = new self($result);
        }

        return $note;
    }
}
