<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\DataModels;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Jfse\Exceptions\UserManagement\UserException;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * A data model for the Jfse user, that allow to link a CMediuser to a User in Jfse
 */
final class CJfseUser extends CMbObject
{
    /** @var integer Primary key */
    public $jfse_user_id;

    /** @var int The id of the user in Jfse */
    public $jfse_id;

    /** @var int The id of the CMediusers */
    public $mediuser_id;

    /** @var int The id of the establishment linked to the user in Jfse */
    public $jfse_establishment_id;

    /** @var string The creation datetime of the user */
    public $creation;

    /** @var int The default securing mode selected by the user */
    public $securing_mode;

    /** @var CMediusers */
    public $_mediuser;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec                     = parent::getSpec();
        $spec->table              = "jfse_users";
        $spec->key                = "jfse_user_id";
        $spec->uniques['jfse_id'] = ['jfse_id'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['jfse_id']               = 'str notNull';
        $props['mediuser_id']           = 'ref class|CMediusers back|jfse_user';
        $props['jfse_establishment_id'] = 'ref class|CJfseEstablishment back|establishment';
        $props['creation']              = 'dateTime notNull default|now';
        $props['securing_mode']         = 'enum list|3|4 default|3';

        return $props;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function store(): ?string
    {
        if (!$this->_id) {
            $this->creation = CMbDT::dateTime();
        }

        return parent::store();
    }

    /**
     * @param bool $cache
     *
     * @return CMediusers
     */
    public function loadMediuser(bool $cache = true): CMediusers
    {
        if (!$this->_mediuser) {
            try {
                $this->_mediuser = $this->loadFwdRef('mediuser_id', $cache);
            } catch (Exception $e) {
                $this->_mediuser = null;
            }
        }

        return $this->_mediuser;
    }

    /**
     * @param int $mediuser_id
     *
     * @throws UserException
     *
     * @return self
     */
    public function setMediuserId(int $mediuser_id): self
    {
        try {
            $mediuser = CMediusers::findOrFail($mediuser_id);
        } catch (Exception $e) {
            throw UserException::mediuserNotFound($mediuser_id, $e);
        }

        $this->mediuser_id = $mediuser->_id;

        return $this;
    }

    public function setEstablishmentId(int $establishment_id): self
    {
        try {
            $establishment = CJfseEstablishment::findOrFail($establishment_id);
        } catch (Exception $e) {
            throw UserException::establishmentNotFound($establishment_id, $e);
        }

        $this->jfse_establishment_id = $establishment->_id;

        return $this;
    }

    /**
     * @return CJfseUser[]
     * @throws Exception
     */
    public function getAutocompleteJfseUsers(string $keywords): array
    {
        $jfse_users = array_map(
            function (CMediusers $mediuser): ?CJfseUser {
                $jfse_user = CJfseUser::getFromMediuser($mediuser);
                if ($jfse_user) {
                    $jfse_user->_mediuser = $mediuser;
                }

                return $jfse_user;
            },
            (new CMediusers())->getAutocompleteList($keywords)
        );

        return array_filter($jfse_users);
    }

    public static function isUserLinked(CMediusers $user): bool
    {
        $jfse_user              = new self();
        $jfse_user->mediuser_id = $user->_id;
        $jfse_user->loadMatchingObjectEsc();

        return isset($jfse_user->_id) && isset($jfse_user->jfse_id);
    }

    public static function getFromMediuser(CMediusers $mediuser): ?CJfseUser
    {
        $jfse_user = new self();
        $jfse_user->mediuser_id = $mediuser->_id;
        $jfse_user->loadMatchingObjectEsc();

        return ($jfse_user->_id) ? $jfse_user : null;
    }

    public static function getFromJfseId(string $jfse_id): ?CJfseUser
    {
        $jfse_user = new self();
        $jfse_user->jfse_id = $jfse_id;
        $jfse_user->loadMatchingObjectEsc();

        return ($jfse_user->_id) ? $jfse_user : null;
    }
}
