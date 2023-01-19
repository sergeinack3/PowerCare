<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

/**
 * CFile error report class in order to troubleshoot CFile issues
 */
class CReadFile extends CMbObject
{
    /** @var int */
    public $file_read_id;

    /** @var string */
    public $object_class;

    /** @var int */
    public $object_id;

    /** @var string */
    public $datetime;

    /** @var int */
    public $user_id;

    /**
     * @inheritdoc
     */
    public function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = "files_read";
        $spec->key   = "file_read_id";

        return $spec;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    public function getProps(): array
    {
        $props                 = parent::getProps();
        $props['object_class'] = 'enum list|CFile|CCompteRendu';
        $props['object_id']    = 'ref notNull class|CMbObject meta|object_class back|read_files';
        $props['datetime']     = 'dateTime notNull';
        $props['user_id']      = 'ref class|CUser notNull back|read_files';

        return $props;
    }

    public static function getUnread(array $contexts): array
    {
        $notReadFiles = [];

        /** @var CMbObject $context */
        foreach ($contexts as $context) {
            $context->loadRefsDocItems();

            $notReadFiles[$context->_id] = [];

            CStoredObject::massCountBackRefs($context->_ref_documents, 'read_files');
            CStoredObject::massCountBackRefs($context->_ref_files, 'read_files');

            foreach ([$context->_ref_documents, $context->_ref_files] as $docitems_by_class) {
                foreach ($docitems_by_class as $docitem) {
                    if (!$docitem->_count['read_files']) {
                        $notReadFiles[$context->_id][] = $docitem;
                    }
                }
            }
        }

        return $notReadFiles;
    }
}
