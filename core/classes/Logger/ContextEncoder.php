<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Logger;

use Ox\Core\CModelObject;

/**
 * Encode/Decode log context and cast CModelObject
 */
class ContextEncoder
{
    /** @var array|object */
    private $context;

    /**
     * @param array|object $context
     */
    public function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * Cast object to array|string
     * Encode from Windows-1252 to UTF-8
     **
     * @return array|object $context
     */
    public function encode()
    {
        // cast
        array_walk_recursive($this->context, function (&$item): void {
            if (is_object($item)) {
                $class = get_class($item);
                $item  = $item instanceof CModelObject ? [
                    $class => $item->getPlainFields(),
                ] : [$class => get_object_vars($item)];
            } elseif (is_resource($item)) {
                $type = get_resource_type($item);
                $item = ['resource' => $type];
            }
        });

        // encode
        array_walk_recursive($this->context, function (&$item): void {
            if ($item !== null) {
                $item = mb_convert_encoding($item, 'UTF-8', 'Windows-1252');
            }
        });

        return $this->context;
    }

    /**
     * Decode from UTF-8 to Windows-1252
     *
     * @return array|object $context
     */
    public function decode()
    {
        if (!$this->context || !is_array($this->context)) {
            return $this->context;
        }

        array_walk_recursive($this->context, function (&$item): void {
            $item = mb_convert_encoding($item, 'Windows-1252', 'UTF-8');
        });

        return $this->context;
    }
}
