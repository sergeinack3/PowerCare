<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse;

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbSecurity;
use Ox\Core\CRequest;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class Utils
 *
 * @package Ox\Mediboard\Jfse
 */
final class Utils
{
    /** @var Utils */
    private static $instance;

    /** @var Cache Allow to test the ApiClient */
    private $resident_uid_cache;

    /** @var Cache Allow to test the ApiClient */
    private $jfse_user_id_cache;

    /**
     * Utils constructor.
     */
    private function __construct()
    {
    }

    /**
     * Instantiate the ResidentUid cache object if necessary, and returns it
     *
     * @return Cache
     */
    public function getResidentUidCache(): Cache
    {
        if (!$this->resident_uid_cache) {
            $user = CMediusers::get();
            $this->resident_uid_cache = new Cache(
                'Jfse',
                "ResidentUid-{$user->_guid}",
                Cache::INNER_OUTER,
                300
            );
        }

        return $this->resident_uid_cache;
    }

    /**
     * Set the resident uid cache (for testing purpose)
     *
     * @param Cache $cache An optional injection of the cache object, for testing purpose
     *
     * @return void
     */
    public function setResidentUidCache(Cache $cache = null): void
    {
        $this->resident_uid_cache = $cache;
    }

    /**
     * Instantiate the JfseuserId cache object if necessary, and returns it
     *
     * @return Cache
     */
    public function getJfseUserIdCache(): Cache
    {
        if (!$this->jfse_user_id_cache) {
            $this->jfse_user_id_cache = new Cache('Jfse', "JfseUserId", Cache::INNER);
        }

        return $this->jfse_user_id_cache;
    }

    /**
     * Set the jfse user id uid cache (for testing purpose)
     *
     * @param Cache $cache An optional injection of the cache object, for testing purpose
     *
     * @return void
     */
    public function setJfseUserIdCache(Cache $cache = null): void
    {
        $this->jfse_user_id_cache = $cache;
    }

    /**
     * Returns the singleton instance of the class
     *
     * @return static
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns the id of the user in Jfse
     *
     * @return int
     * @todo Implements the system that get the user from the user end request, and the way to configure the users Jfse
     *       ids
     *
     */
    public static function getJfseUserId(): int
    {
        $id = 0;
        if (self::getInstance()->getJfseUserIdCache()->exists()) {
            $id = self::getInstance()->getJfseUserIdCache()->get();
        }

        return $id;
    }

    /**
     * Set the jfse user id in the cache
     */
    public static function setJfseUserId(int $id): void
    {
        self::getInstance()->getJfseUserIdCache()->put($id);
    }

    /**
     * Set the jfse user id linked to the given CMediusers in the cache
     *
     * @param CMediusers $mediuser
     */
    public static function setJfseUserIdFromMediuser(CMediusers $mediuser = null): void
    {
        if (!$mediuser || !$mediuser->getPerm(PERM_EDIT)) {
            $mediuser = CMediusers::get();
        }

        $jfse_user = CJfseUser::getFromMediuser($mediuser);
        $id        = ($jfse_user) ? $jfse_user->jfse_id : null;

        if ($id) {
            self::getInstance()->getJfseUserIdCache()->put($id);
        }
    }

    /**
     * Set the jfse user id, linked to the practitioner of the given consultation, in the cache
     *
     * @param CConsultation $consultation
     */
    public static function setJfseUserIdFromConsultation(CConsultation $consultation): void
    {
        self::setJfseUserIdFromMediuser($consultation->loadRefPraticien());
    }

    /**
     * Set the jfse user id, linked to the practitioner of the invoice with the given jfse invoice id, in the cache
     *
     * @param int $invoice_id
     */
    public static function setJfseUserIdFromInvoiceId(int $invoice_id): void
    {
        $invoice = CJfseInvoice::getFromJfseId($invoice_id);
        if ($invoice->_id) {
            self::setJfseUserIdFromMediuser($invoice->loadConsultation()->loadRefPraticien());
        }
    }

    /**
     * Some API methods needs a valid JfseUser, even if they are not directly related to the invoicing
     * (like getAdri for example)
     * In those cases, this method can be used to get a valid jfse user id to call the method
     *
     * @param CGroups $group
     *
     * @return void
     */
    public static function setJfseUserIdFromGroup(CGroups $group): void
    {
        $jfse_user = new CJfseUser();
        $jfse_user->loadObject([
            'functions_mediboard.group_id' => " = {$group->_id}"
        ], 'jfse_id ASC', 'jfse_user_id', [
            'users_mediboard' => 'users_mediboard.user_id = jfse_users.mediuser_id',
            'functions_mediboard' => 'functions_mediboard.function_id = users_mediboard.function_id'
        ]);

        if ($jfse_user->jfse_id) {
            self::getInstance()->getJfseUserIdCache()->put($jfse_user->jfse_id);
        }
    }

    /**
     * Returns the Jfse resident uid
     *
     * @return string|null
     */
    public static function getResidentUid(): ?string
    {
        $uid = null;
        if (self::getInstance()->getResidentUidCache()->exists()) {
            $uid = self::getInstance()->getResidentUidCache()->get();
        }

        return $uid;
    }

    /**
     * Sets the Jfse resident uid in the inner cache
     *
     * @param string $uid The resident uid
     *
     * @return void
     */
    public static function setResidentUid(string $uid): void
    {
        self::getInstance()->getResidentUidCache()->put($uid);
    }

    /**
     * Returns the name of the group
     *
     * @return string
     * @todo See how the group name is relevant in Jfse, and how it is linked to the users
     *
     */
    public static function getGroupName(): string
    {
        //    return utf8_encode(CGroups::get()->text);
        return 'TEST';
    }

    /**
     * Returns the editor's name
     *
     * @return string
     */
    public static function getEditorName(): string
    {
        return strtoupper(CAppUI::gconf('jfse API editorName'));
    }

    /**
     * Returns the editor's key
     * The editor key must be unique for users
     *
     * @return string
     */
    public static function getEditorKey(): string
    {
        /* We use MD5 because there are no security concerns about this value, it is used to distinguish users */
        return CMbSecurity::hash(CMbSecurity::MD5, CMediusers::get()->_guid);
    }

    public static function getOperatingSystem(): string
    {
        $os = 'WINDOWS';
        if (
            array_key_exists('HTTP_USER_AGENT', $_SERVER)
            && preg_match('/macintosh|mac os x/i', $_SERVER['HTTP_USER_AGENT'])
        ) {
            $os = 'MAC';
        }

        return $os;
    }

    /**
     * Sets a flag in the inner cache used to know if JfseExceptions must return a JsonResponse instead of a
     * SmartyResponse
     *
     * @param bool $json_expected true if a JsonResponse is expected
     *
     * @return void
     */
    public static function setJsonResponseExpectedFlag(bool $json_expected): void
    {
        $cache = new Cache('Jfse', 'JSON_RESPONSE_EXPECTED', Cache::INNER);
        $cache->put($json_expected);
    }

    /**
     * @return bool
     */
    public static function isJsonResponseExpected(): bool
    {
        $cache = new Cache('Jfse', 'JSON_RESPONSE_EXPECTED', Cache::INNER);

        return $cache->get() ?? false;
    }

    /**
     * Casts a value to integer if not **FALSY**.
     * The value "0" will therefore return **NULL**
     *
     * @param mixed $val
     *
     * @return int|null
     */
    public static function toIntOrNull($val): ?int
    {
        if (!$val) {
            return null;
        }

        return (int)$val;
    }

    public static function dateToJfseFormat(string $date): string
    {
        return substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);
    }
}
