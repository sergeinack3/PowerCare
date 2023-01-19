<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Services;

use DateTimeImmutable;
use Exception;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Medimail\CMedimailAttachment;
use Ox\Mediboard\Medimail\CMedimailMessage;
use Ox\Mediboard\Messagerie\Entities\MessagerieEntity;
use Ox\Mediboard\Messagerie\Exceptions\MessagerieLinkException;
use Ox\Mediboard\Mssante\CMSSanteMail;
use Ox\Mediboard\Mssante\CMSSanteMailAttachment;

class MessagerieLinkService
{
    /**
     * Link a file from a medimail entity
     *
     * @param MessagerieEntity $attachment       Entity
     * @param int              $user_id          Owner of file
     * @param int              $context_id       Object identifier of file
     * @param string           $context_class    Object class of file
     * @param string           $file_name        Name of file
     * @param int|null         $file_category_id Category file identifier
     *
     * @return string|null
     * @throws MessagerieLinkException
     */
    public function fromMedimail(
        MessagerieEntity $attachment,
        int $user_id,
        int $context_id,
        string $context_class,
        string $file_name,
        ?int $file_category_id
    ): ?string {
        $file = $this->createLinkFile($user_id, $context_id, $context_class, $file_name, $file_category_id);

        switch (true) {
            case $attachment instanceof CMedimailMessage:
                $file->file_type = 'text/plain';
                $file->setContent($attachment->getContent());

                break;
            case $attachment instanceof CMedimailAttachment:
                $ref_file = $attachment->loadFile();

                $file->file_type = $ref_file->file_type;
                $file->setContent($ref_file->getBinaryContent());

                break;
            default:
                throw MessagerieLinkException::instanceOfObjectNotAvailable();
        }

        return $this->storeLinkFile($file);
    }

    /**
     * Link a file from a mailiz entity
     *
     * @param MessagerieEntity $attachment
     * @param int              $user_id
     * @param int              $context_id
     * @param string           $context_class
     * @param string           $file_name
     * @param int|null         $file_category_id
     *
     * @return string|null
     * @throws MessagerieLinkException
     */
    public function fromMailiz(
        MessagerieEntity $attachment,
        int $user_id,
        int $context_id,
        string $context_class,
        string $file_name,
        ?int $file_category_id
    ): ?string {
        $file = $this->createLinkFile($user_id, $context_id, $context_class, $file_name, $file_category_id);

        switch (true) {
            case $attachment instanceof CMSSanteMail:
                $ref_content = $attachment->loadRefContent();

                $file->file_type = 'text/plain';
                $file->setContent($ref_content->content);

                break;
            case $attachment instanceof CMSSanteMailAttachment:
                $ref_file = $attachment->loadRefFile();

                $file->file_type = $ref_file->file_type;
                $file->setContent($ref_file->getBinaryContent());

                break;
            default:
                throw MessagerieLinkException::instanceOfObjectNotAvailable();
        }

        return $this->storeLinkFile($file);
    }

    /**
     * Create a file from data
     *
     * @param int      $user_id          Owner of file
     * @param int      $context_id       Object identifier of file
     * @param string   $context_class    Object class of file
     * @param string   $file_name        Name of file
     * @param int|null $file_category_id Category file identifier
     *
     * @return CFile
     */
    private function createLinkFile(
        int $user_id,
        int $context_id,
        string $context_class,
        string $file_name,
        ?int $file_category_id
    ): CFile {
        $file                   = new CFile();
        $file->author_id        = $user_id;
        $file->object_id        = $context_id;
        $file->object_class     = $context_class;
        $file->file_name        = $file_name;
        $file->file_date        = (new DateTimeImmutable())->format('Y-m-d h:i:s');

        if ($file_category_id) {
            $file->file_category_id = $file_category_id;
        }

        return $file;
    }

    /**
     * Store link file
     *  Returns an error if it is not possible to store.
     *
     * @param CFile $file File to store
     *
     * @return string|null
     */
    private function storeLinkFile(CFile $file): ?string
    {
        // Fill other fields
        $file->fillFields();

        if ($msg = $file->store()) {
            return $msg;
        } else {
            return null;
        }
    }
}
