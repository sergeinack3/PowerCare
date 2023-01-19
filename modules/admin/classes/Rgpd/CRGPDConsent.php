<?php

/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Rgpd;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbPath;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbServer;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\System\CSourceSMTP;

/**
 * A proof of consent is:
 * 1. What we consent to (file enclosure)
 * 2. The moment         (acceptance or refusal datetime)
 * 3. Who agrees         (target object)
 */
final class CRGPDConsent extends CMbObject
{
    public const STATUS_TO_GENERATE = 1;
    public const STATUS_TO_SEND     = 2;
    public const STATUS_SENT        = 3;
    public const STATUS_READ        = 4;
    public const STATUS_ACCEPTED    = 5;
    public const STATUS_REFUSED     = 6;

    public const STATUSES = [
        self::STATUS_TO_GENERATE,
        self::STATUS_TO_SEND,
        self::STATUS_SENT,
        self::STATUS_READ,
        self::STATUS_ACCEPTED,
        self::STATUS_REFUSED,
    ];

    // TAG: UNSIGNED SMALLINT, do not exceed 65535
    // Should be used in order to identify treatments
    public const TAG_DATA            = 1;
    public const TAG_APPFINE         = 2;
    public const TAG_TERRESANTE      = 3;
    public const TAG_SISRA           = 4;
    public const TAG_DMP             = 5;
    public const TAG_MSSANTE_PATIENT = 6;
    public const TAG_MSSANTE_PRO     = 7;

    public const TAGS = [
        self::TAG_DATA,
        self::TAG_APPFINE,
    ];

    public const TAGS_PATIENT = [
        self::TAG_TERRESANTE,
        self::TAG_DMP,
        self::TAG_MSSANTE_PATIENT,
        self::TAG_MSSANTE_PRO,
    ];

    /** @var string */
    public const RESOURCE_TYPE = "rgpd";

    /** @var int Primary key */
    public $rgpd_consent_id;

    /** @var string Additional tag in order to precise context */
    public $tag;

    /** @var string Object status */
    public $status;

    /** @var string Date and time of consent generation */
    public $generation_datetime;

    /** @var string Date and time of consent proposal */
    public $send_datetime;

    /** @var string Date and time of consent ack */
    public $read_datetime;

    /** @var string Date and time of consent acceptance */
    public $acceptance_datetime;

    /** @var string Date and time of consent refusal */
    public $refusal_datetime;

    /** @var string Proof file checksum (SHA256 hash) */
    public $proof_hash;

    /** @var string Last error */
    public $last_error;

    /** @var int CGroups ID */
    public $group_id;

    // References
    /** @var CFile Consent file */
    public $_ref_consent_file;

    /** @var CGroups */
    public $_ref_group;

    /** @var CRGPDManager */
    public $_manager;

    // Meta

    /** @var int */
    public $object_id;

    /** @var string */
    public $object_class;

    /** @var CMbObject */
    public $_ref_object;

    // Form fields
    /** @var string */
    public $_status;

    /** @var string */
    public $_min_generation_datetime;

    /** @var string */
    public $_min_send_datetime;

    /** @var string */
    public $_min_read_datetime;

    /** @var string */
    public $_min_acceptance_datetime;

    /** @var string */
    public $_min_refusal_datetime;

    /** @var string */
    public $_max_generation_datetime;

    /** @var string */
    public $_max_send_datetime;

    /** @var string */
    public $_max_read_datetime;

    /** @var string */
    public $_max_acceptance_datetime;

    /** @var string */
    public $_max_refusal_datetime;

    /** @var string */
    public $_object_class;

    /** @var bool */
    public $_last_error;

    /** @var string */
    public $_first_name;

    /** @var string */
    public $_last_name;

    /** @var string */
    public $_birth_date;

    /**
     * Count the number of consent by status
     *
     * @return array
     * @throws CRGPDException
     */
    public static function getCountByStatus(): array
    {
        try {
            $consent = new static();

            $request = new CRequest();
            $request->addSelect('status, COUNT(*) AS total');
            $request->addTable($consent->getSpec()->table);
            $request->addGroup('status');

            $total  = array_fill_keys(self::getStatuses(), 0);
            $result = $consent->getDS()->loadHashList($request->makeSelect());

            foreach ($result as $_status => $_total) {
                $total[$_status] = $_total;
            }

            return $total;
        } catch (Exception $e) {
            throw new CRGPDException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'rgpd_consent';
        $spec->key   = 'rgpd_consent_id';

        $spec->uniques['object_tag'] = ['object_class', 'object_id', 'tag', 'group_id'];

        return $spec;
    }

    /**
     * Get allowed statuses
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return self::STATUSES;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $compliant_classes = CRGPDManager::getCompliantClasses();

        $props                        = parent::getProps();
        $props['tag']                 = 'enum list|' . implode('|', self::getUsageTags()) . ' notNull fieldset|default';
        $props['status']              = 'enum list|' . implode(
            '|',
            self::getStatuses()
        ) . ' default|1 notNull fieldset|default';
        $props['generation_datetime'] = 'dateTime fieldset|default';
        $props['send_datetime']       = 'dateTime fieldset|default';
        $props['read_datetime']       = 'dateTime fieldset|default';
        $props['acceptance_datetime'] = 'dateTime fieldset|default';
        $props['refusal_datetime']    = 'dateTime fieldset|default';
        $props['object_id']           = 'ref notNull class|CStoredObject meta|object_class back|rgpd_consents fieldset|default';
        $props['object_class']        = 'enum list|' . implode('|', $compliant_classes) . ' notNull fieldset|default';
        $props['proof_hash']          = 'str length|64 show|0 fieldset|extra';
        $props['last_error']          = 'str show|0 fieldset|extra';
        $props['group_id']            = 'ref class|CGroups back|related_rgpd_consents fieldset|default';

        $props['_status']                  = 'set list|' . implode('|', self::getStatuses());
        $props['_min_generation_datetime'] = 'dateTime';
        $props['_min_send_datetime']       = 'dateTime';
        $props['_min_read_datetime']       = 'dateTime';
        $props['_min_acceptance_datetime'] = 'dateTime';
        $props['_min_refusal_datetime']    = 'dateTime';
        $props['_max_generation_datetime'] = 'dateTime';
        $props['_max_send_datetime']       = 'dateTime';
        $props['_max_read_datetime']       = 'dateTime';
        $props['_max_acceptance_datetime'] = 'dateTime';
        $props['_max_refusal_datetime']    = 'dateTime';
        $props['_object_class']            = 'set list|' . implode('|', $compliant_classes);
        $props['_last_error']              = 'set list|1|0';
        $props['_first_name']              = 'str';
        $props['_last_name']               = 'str';
        $props['_birth_date']              = 'birthDate';

        return $props;
    }

    /**
     * Get authorised tags
     *
     * @return array
     */
    public static function getUsageTags(): array
    {
        return self::TAGS;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = CAppUI::tr("{$this->_class}.status.{$this->status}");

        if ($date = $this->getStatusRelatedDate()) {
            $this->_view = '[' . CMbDT::dateToLocale($date) . '] ' . $this->_view;
        }

        if ($this->group_id) {
            $this->setManager(new CRGPDManager($this->group_id));
        }
    }

    /**
     * Get consent date according to its status
     *
     * @return string|null
     */
    public function getStatusRelatedDate(): ?string
    {
        switch ($this->status) {
            case self::STATUS_TO_SEND:
                return $this->generation_datetime;

            case self::STATUS_SENT:
                return $this->send_datetime;

            case self::STATUS_ACCEPTED:
                return $this->acceptance_datetime;

            case self::STATUS_REFUSED:
                return $this->refusal_datetime;

            default:
                return null;
        }
    }

    /**
     * @return CRGPDManager
     */
    public function getManager(): CRGPDManager
    {
        return $this->_manager;
    }

    /**
     * Sets the GDPR Manager
     *
     * @param CRGPDManager $manager
     *
     * @return void
     */
    public function setManager(CRGPDManager $manager): void
    {
        $this->_manager = $manager;
    }

    /**
     * Set the last error during sending
     *
     * @param string $error
     *
     * @return $this
     */
    public function setLastError(string $error): self
    {
        $this->last_error = $error;

        return $this;
    }

    /**
     * @param bool $cached
     *
     * @return CGroups|null
     * @throws CRGPDException
     */
    public function loadRefGroup($cached = true): ?CGroups
    {
        try {
            return $this->_ref_group = $this->loadFwdRef('group_id', $cached);
        } catch (Exception $e) {
            throw new CRGPDException($e->getMessage());
        }
    }

    /**
     * Checks if proof file is stored
     *
     * @return bool
     */
    public function checkProofFile(): bool
    {
        $file = ($this->_ref_consent_file && $this->_ref_consent_file->_id) ? $this->_ref_consent_file : $this->loadProofFile(
        );

        if (!$file || !$file->_id) {
            return false;
        }

        static $cache = [];

        if (isset($cache[$file->_id])) {
            $hash = $cache[$file->_id];
        } elseif ($hash = CMbSecurity::hash(CMbSecurity::SHA256, $file->getBinaryContent())) {
            $cache[$file->_id] = $hash;
        }

        return ($file->doc_size > 0 && $this->proof_hash === $hash);
    }

    /**
     * Loads consent file
     *
     * @return CFile
     */
    public function loadProofFile(): CFile
    {
        return $this->_ref_consent_file = $this->loadNamedFile($this->getProofFileName());
    }

    /**
     * @return string
     */
    public function getProofFileName(): string
    {
        return $this->_manager->getProofFileName();
    }

    /**
     * Tells if consent is approved
     *
     * @return bool
     */
    public function isOK(): bool
    {
        if (!$this->_id) {
            return false;
        }

        return ($this->isAccepted()/* && $this->checkProofFile()*/);
    }

    /**
     * Tells if consent has been accepted
     *
     * @return bool
     */
    public function isAccepted(): bool
    {
        return ($this->status == self::STATUS_ACCEPTED);
    }

    /**
     * Tells if consent is generated
     *
     * @return bool
     */
    public function isGenerated(): bool
    {
        return ($this->status > self::STATUS_TO_GENERATE);
    }

    /**
     * Tells if consent must be send
     *
     * @return bool
     */
    public function isToSend(): bool
    {
        return ($this->status == self::STATUS_TO_SEND);
    }

    /**
     * Tells if ask for consent is sent
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return ($this->status > self::STATUS_TO_SEND);
    }

    /**
     * Tells if ask for consent is read
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return ($this->status > self::STATUS_SENT);
    }

    /**
     * Tells if consent has been refused
     *
     * @return bool
     */
    public function isRefused(): bool
    {
        return ($this->status == self::STATUS_REFUSED);
    }

    /**
     * Generate a consent token
     *
     * @return CViewAccessToken|null
     * @throws CRGPDException
     */
    public function getResponseToken(): ?CViewAccessToken
    {
        if (!$this->_id) {
            return null;
        }

        $user_id = $this->getRGPDUserID();

        if (!$user_id) {
            throw new CRGPDException('CRGPDConsent-error-User is not configured.');
        }

        $token             = new CViewAccessToken();
        $token->user_id    = $user_id;
        $token->purgeable  = 1;
        $token->restricted = 1;
        $token->params     = "m=admin\na=token_collect_rgpd_consent&object_id={$this->_id}&dialog=1";
        $token->validator  = 'CRGPDTokenValidator';

        if ($token->loadMatchingObject()) {
            if (!$token->isValid()) {
                $token->datetime_end = CMbDT::dateTime('+3 months');
            }

            if ($msg = $token->store()) {
                throw new CRGPDException($msg);
            }

            return $token;
        }

        $date                  = CMbDT::dateTime();
        $token->datetime_start = $date;
        $token->datetime_end   = CMbDT::dateTime('+3 months', $date);

        if ($msg = $token->store()) {
            throw new CRGPDException($msg);
        }

        return $token;
    }

    /**
     * @return int
     */
    private function getRGPDUserID(): int
    {
        return $this->_manager->getRGPDUserID();
    }

    /**
     * Marks a consent as generated
     *
     * @param null $date Date
     *
     * @return $this
     * @throws CRGPDException
     */
    public function markAsGenerated(?string $date = null): self
    {
        $this->markAs('generated', $date);
        $this->makeProofFile();

        return $this;
    }

    /**
     * Marks a consent according to given action
     *
     * @param string $status Action
     * @param null   $date   Date
     *
     * @return $this
     * @throws CRGPDException
     */
    private function markAs(?string $status, ?string $date = null): self
    {
        $date = ($date) ?: CMbDT::dateTime();

        switch ($status) {
            case 'generated':
                $this->status              = self::STATUS_TO_SEND;
                $this->generation_datetime = $date;
                break;

            case 'sent':
                $this->status        = self::STATUS_SENT;
                $this->send_datetime = $date;
                break;

            case 'read':
                $this->status        = self::STATUS_READ;
                $this->read_datetime = $date;
                break;

            case 'accepted':
                $this->status              = self::STATUS_ACCEPTED;
                $this->acceptance_datetime = $date;
                $this->refusal_datetime    = '';
                break;

            case 'refused':
                $this->status              = self::STATUS_REFUSED;
                $this->refusal_datetime    = $date;
                $this->acceptance_datetime = '';
                break;

            default:
                return $this;
        }

        if ($msg = $this->store()) {
            throw new CRGPDException($msg);
        }

        if ($this->canUpdateProofFile()) {
            $this->updateProofFile();
        }

        return $this;
    }

    /**
     * Tell if proof file can be modified
     *
     * @param CFile|null $file
     *
     * @return bool
     */
    public function canUpdateProofFile(CFile $file = null): bool
    {
        $file = ($file && $file->_id) ? $file : $this->loadProofFile();

        return (
            (
                strtolower(
                    $file->file_type
                ) === 'text/plain')
            && ($file->author_id && $file->author_id == $this->getRGPDUserID())
        );
    }

    /**
     * Update proof file metadata
     *
     * @param CFile|null $file
     *
     * @return void
     * @throws CRGPDException
     */
    public function updateProofFile(CFile $file = null): void
    {
        $file = ($file && $file->_id) ? $file : $this->loadProofFile();

        if (!$file || !$file->_id) {
            throw new CRGPDException('CRGPD-error-Cannot find proof file');
        }

        if (!$this->canUpdateProofFile()) {
            return;
        }

        $content = $file->getBinaryContent();
        $date    = CMbDT::dateTime();

        $context = $this->loadTargetObject();
        $address = CMbServer::getRemoteAddress();
        $ip_addr = ($address && $address['client']) ? $address['client'] : null;

        $content .= <<<SOF


########## UPDATE ##########
DATE: {$date}, CONTEXT: {$context}, IP ADDR: {$ip_addr}
generation_date: {$this->generation_datetime}, send_date: {$this->send_datetime}, read_date: {$this->read_datetime}, acceptance_date: {$this->acceptance_datetime}, refusal_date: {$this->refusal_datetime},
last hash: {$this->proof_hash}
SOF;

        $file->setContent($content);
        if ($msg = $file->store()) {
            throw new CRGPDException('CRGPD-error-Cannot update proof file');
        }

        $this->computeFileHash($file);

        return;
    }

    /**
     * @param bool $cache
     *
     * @return mixed
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject(bool $cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * Compute proof file hash
     *
     * @param CFile|null $file
     *
     * @return void
     * @throws CRGPDException
     */
    public function computeFileHash(CFile $file = null): void
    {
        $file = ($file && $file->_id) ? $file : $this->loadProofFile();

        if (!$file || !$file->_id) {
            throw new CRGPDException('CRGPD-error-Cannot find proof file');
        }

        $this->setFileHash(CMbSecurity::hash(CMbSecurity::SHA256, $file->getBinaryContent()));
    }

    /**
     * Set the proof file hash
     *
     * @param string $hash
     *
     * @return void
     * @throws CRGPDException
     */
    public function setFileHash(string $hash): void
    {
        $this->proof_hash = $hash;

        if ($msg = $this->store()) {
            throw new CRGPDException($msg);
        }
    }

    /**
     * Creates the proof file of consent
     *
     * @return CFile
     * @throws CRGPDException
     */
    public function makeProofFile(): CFile
    {
        $file = $this->loadProofFile();

        if (!$file || !$file->_id) {
            $file = new CFile();
            $file->setObject($this);
        }

        $file->file_name = $this->getProofFileName();
        $file->file_type = CMbPath::guessMimeType($this->getProofFileName());
        $file->author_id = $this->getRGPDUserID();
        $file->file_date = CMbDT::dateTime();
        $file->fillFields();

        $proof = $this->fetchRGPDDocument();

        $file->setContent($proof);
        //$file->doc_size = strlen($proof);

        if ($msg = $file->store()) {
            throw new CRGPDException($msg);
        }

        $this->computeFileHash($file);
        $this->updateProofFile();

        return $file;
    }

    /**
     * Produce the RGPD document (proof) as a CFile content
     *
     * @return string
     * @throws CRGPDException
     */
    public function fetchRGPDDocument(): string
    {
        $turn_off_fetch = CApp::getTurnOffFetch();
        CApp::turnOnFetch();
        switch ($this->object_class) {
            case 'CUser':
                $user = $this->loadTargetObject();

                // AppFine treatment
                if ($user->user_type == 22) {
                    $source = CApp::fetch(
                        'appFine',
                        'vw_rgpd_document',
                        ['dialog' => '1', 'object_class' => $this->object_class]
                    );
                } else {
                    $source = CApp::fetch(
                        'admin',
                        'vw_rgpd_document',
                        ['dialog' => '1', 'object_class' => $this->object_class]
                    );
                }
                break;

            default:
                $source = CApp::fetch(
                    'admin',
                    'vw_rgpd_document',
                    ['dialog' => '1', 'object_class' => $this->object_class]
                );
        }

        if ($turn_off_fetch) {
            CApp::turnOffFetch();
        }

        $content = trim(CMbString::htmlToText($source));

        if (!$content) {
            throw new CRGPDException('CRGPD-error-Proof file is empty');
        }

        return $content;
    }

    /**
     * Marks a consent as sent
     *
     * @param null $date Date
     *
     * @return $this
     * @throws CRGPDException
     */
    public function markAsSent(?string $date = null): self
    {
        return $this->markAs('sent', $date);
    }

    /**
     * Marks a consent as read
     *
     * @param null $date Date
     *
     * @return $this
     * @throws CRGPDException
     */
    public function markAsRead(?string $date = null): self
    {
        return $this->markAs('read', $date);
    }

    /**
     * Marks a consent as accepted
     *
     * @param null $date Date
     *
     * @return $this
     * @throws CRGPDException
     */
    public function markAsAccepted(?string $date = null): self
    {
        return $this->markAs('accepted', $date);
    }

    /**
     * Marks a consent as refused
     *
     * @param null $date Date
     *
     * @return $this
     * @throws CRGPDException
     */
    public function markAsRefused(?string $date = null): self
    {
        return $this->markAs('refused', $date);
    }

    /**
     * Get email notification subject
     *
     * @return string
     */
    public function getEmailSubject(): string
    {
        return $this->_manager->getEmailSubject();
    }

    /**
     * @return CSourceSMTP|null
     */
    public function getRGPDSource(): ?CSourceSMTP
    {
        return $this->_manager->getRGPDSource();
    }

    /**
     * @return string
     * @throws CRGPDException
     */
    public function getEmailBody(): ?string
    {
        if (
            $cr = CCompteRendu::getSpecialModel(
                CGroups::get($this->group_id),
                $this->object_class,
                $this->getSpecialModelName()
            )
        ) {
            $cr->loadContent();

            $source = $cr->generateDocFromModel(null, $cr->header_id, $cr->footer_id);

            $cr->user_id        = null;
            $cr->function_id    = null;
            $cr->group_id       = null;
            $cr->content_id     = null;
            $cr->doc_size       = null;
            $cr->creation_date  = null;
            $cr->fields_missing = null;
            $cr->version        = null;
            $cr->author_id      = null;

            $object = $this->loadTargetObject();

            $_cr = new CCompteRendu();
            $_cr->cloneFrom($cr);
            $_cr->setObject($object);
            $_cr->modele_id = $cr->_id;
            $_cr->_source   = $source;

            $template_manager           = new CTemplateManager();
            $template_manager->isModele = false;
            $template_manager->document = $source;

            $object->fillTemplate($template_manager);
            $template_manager->applyTemplate($_cr);

            return $_cr->_source = $template_manager->document;
        }

        return $this->fetchRGPDDocument();
    }

    /**
     * @return string
     */
    private function getSpecialModelName(): string
    {
        return $this->_manager->getSpecialModelName();
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object): void
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    public function loadRefsFwd(): void
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }
}
