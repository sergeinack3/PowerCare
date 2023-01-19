<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Entities\ViewModels;

use Exception;
use Ox\Core\CModelObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Medimail\CMedimailAttachment;
use Ox\Mediboard\Messagerie\Entities\MessagerieEntity;
use Ox\Mediboard\Messagerie\Exceptions\MessagerieLinkException;
use Ox\Mediboard\Mssante\CMSSanteMailAttachment;

/**
 * Show a messaging attachment while waiting for a refactoring to
 * unify the different attachment (Mailiz / Medimail) formats available in mediboard.
 */
final class MessagerieAttachmentLinkView extends CModelObject
{
    /** @var string $name Item name */
    public string $name;

    /** @var string $file_extension Item name */
    public string $file_extension;

    /** @var string $icon Item icon */
    public string $icon;

    /** @var string $size Item size */
    public string $size;

    /** @var string $object_class Object class ref */
    public string $object_class;

    /** @var int $object_id Object id ref */
    public int $object_id;

    /** @var int $file_id File id */
    public int $file_id;

    /** @var int|null $category_id Category id ref */
    public ?int $category_id = null;

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['name']           = 'str notNull';
        $props['file_extension'] = 'str notNull';
        $props['icon']           = 'str notNull';
        $props['size']           = 'str notNull';
        $props['object_class']   = 'str notNull';
        $props['object_id']      = 'num notNull';
        $props['file_id']        = 'num notNull';
        $props['category_id']    = 'num';

        return $props;
    }

    /**
     * Get the icon associated with the file type
     *
     * @param string|null $type File type
     *
     * @return string
     */
    public function getIconByType(?string $type): string
    {
        switch ($type) {
            case 'text':
                return 'mdi-text-box';
            case 'image':
                return 'mdi-image';
            case 'pdf':
                return 'mdi-file-pdf-box';
            case 'word':
                return 'mdi-file-word-box';
            case 'excel':
                return 'mdi-file-excel-box';
            default:
                return 'mdi-file';
        }
    }

    /**
     * Get a readable size
     *
     * @param int $bytes File size
     * @param int $dec   Display decimals
     *
     * @return string
     */
    public function getReadableSize(int $bytes, int $dec = 2): string
    {
        $size   = ['o', 'ko', 'Mo', 'Go', 'To', 'Po', 'Eo', 'Zo', 'Yo'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }

    /**
     * Return a instance of MessagerieAttachmentLink
     *
     * @param string $name         Item name
     * @param string $extension    Item extension
     * @param int    $object_id    Item object identifier
     * @param string $object_class Item object class
     * @param CFile  $file         Item file
     *
     * @return static
     */
    public static function createFromData(
        string $name,
        string $extension,
        int $object_id,
        string $object_class,
        CFile $file
    ): self {
        $view_model                 = new self();
        $view_model->name           = $name;
        $view_model->file_extension = ".$extension";
        $view_model->object_id      = $object_id;
        $view_model->object_class   = $object_class;
        $view_model->file_id        = $file->_id;
        $view_model->icon           = $view_model->getIconByType($file->_file_type);
        $view_model->size           = $view_model->getReadableSize($file->doc_size);

        return $view_model;
    }
}
