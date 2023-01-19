<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Mediboard\System\CModuleAction;
use Ox\Mediboard\System\Cron\CCronJobLog;
use Symfony\Component\HttpFoundation\Request;

/**
 * Temporary view access token
 */
class CViewAccessToken extends CMbObject
{
    use RequestHelperTrait;

    /** @var string */
    public const RESOURCE_TYPE = "token";

    /** @var string */
    public const FIELDSET_IDENTIFIERS = 'identifiers';

    /** @var string */
    public const FIELDSET_USAGE = 'usage';

    /** @var string */
    public const FIELDSET_VALIDATOR = 'validator';

    /** @var string[] */
    private const ACTION_KEYS = ['a', 'tab', 'dialog', 'ajax', 'raw', 'wsdl', 'info'];

    /** @var int */
    public const MINIMUM_HASH_LENGTH = 6;

    /** @var int */
    public const DEFAULT_HASH_LENGTH = 7;

    /** @var int Coefficient for combinatorics limit */
    private const COMBINATORICS_COEFF = 1000000;

    /** @var int */
    public $view_access_token_id;

    /** @var string */
    public $label;

    /** @var string */
    public $hash;

    /** @var int */
    public $module_action_id;

    /** @var int */
    public $user_id;

    /** @var string */
    public $params;

    /** @var string */
    public $routes_names;

    /** @var bool */
    public $restricted;

    /** @var string */
    public $datetime_start;

    /** @var string */
    public $datetime_end;

    /** @var int */
    public $max_usages;

    /** @var string */
    public $first_use;

    /** @var string */
    public $latest_use;

    /** @var int */
    public $total_use;

    /** @var bool Can the token be purged? */
    public $purgeable;

    /** @var string CTokenValidator classname */
    public $validator;

    /** @var CUser */
    public $_ref_user;

    /** @var string */
    public $_url;

    /** @var array */
    public $_params;

    /** @var array */
    public $_mean_usage_duration;

    // Form fields
    /** @var string */
    public $_min_validity_date;

    /** @var string */
    public $_max_validity_date;

    /** @var string */
    public $_min_usage_date;

    /** @var string */
    public $_max_usage_date;

    /** @var array Validity duration */
    public $_validity_duration;

    /** @var string */
    public $_module;

    /** @var string */
    public $_action;

    /** @var int */
    public $_hash_length;

    /** @var array Validator classes */
    public $_validators = [];

    /** @var string[] Allowed route names */
    public $_allowed_routes = [];

    /**
     * @inheritDoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec                  = parent::getSpec();
        $spec->table           = 'view_access_token';
        $spec->key             = 'view_access_token_id';
        $spec->uniques['hash'] = ['hash'];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['hash']             = 'str notNull fieldset|default';
        $props['module_action_id'] = 'ref class|CModuleAction back|access_tokens fieldset|extra';
        $props['label']            = 'str seekable fieldset|default';

        // Behaviour
        $props['user_id']      = 'ref notNull class|CUser back|tokens fieldset|identifiers';
        $props['params']       = 'text notNull fieldset|extra';
        $props['routes_names'] = 'text fieldset|extra';
        $props['restricted']   = 'bool notNull default|0 fieldset|extra';

        // Validity
        $props['datetime_start'] = 'dateTime notNull fieldset|default';
        $props['datetime_end']   = 'dateTime moreThan|datetime_start fieldset|default';
        $props['max_usages']     = 'num min|1 fieldset|default';

        // Stats
        $props['first_use']            = 'dateTime loggable|0 fieldset|usage';
        $props['latest_use']           = 'dateTime loggable|0 fieldset|usage';
        $props['total_use']            = 'num loggable|0 fieldset|usage';
        $props['_mean_usage_duration'] = '';

        // Validator
        $props['validator'] = 'str fieldset|validator';

        // Purge
        $props['purgeable'] = 'bool notNull default|0 fieldset|extra';

        // Search
        $props['_min_validity_date'] = 'dateTime';
        $props['_max_validity_date'] = 'dateTime moreThan|_min_validity_date';
        $props['_min_usage_date']    = 'dateTime';
        $props['_max_usage_date']    = 'dateTime moreThan|_min_usage_date';

        $props['_hash_length'] = 'num min|' . self::MINIMUM_HASH_LENGTH;

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = $this->label;

        $this->setModuleAction();

        $this->_hash_length = $this->computeHashLength();

        if ($this->total_use > 1) {
            $duration = CMbDT::friendlyDuration(
                (strtotime($this->latest_use) - strtotime($this->first_use)) / $this->total_use
            );

            $this->_mean_usage_duration = $duration;
        }

        $this->isValid();
        $this->getParams();

        if ($this->routes_names) {
            $this->_allowed_routes = array_map('trim', explode("\n", $this->routes_names));
        }
    }

    /**
     * @inheritdoc
     */
    public function store(): ?string
    {
        $this->completeField('datetime_start', 'label');

        // Default starting date is now
        $this->datetime_start = ($this->datetime_start) ?: CMbDT::dateTime();

        // Set the module_action_id according to given parameters
        $this->module_action_id = $this->getModuleActionId();
        $this->setModuleAction();

        // Set the token label according to module / action translation
        if (!$this->label && $this->_module && $this->_action) {
            $this->label = CAppUI::tr("module-{$this->_module}-court")
                . ' > '
                . CAppUI::tr("mod-{$this->_module}-tab-{$this->_action}");
        }

        if (!$this->checkUserIsAllowed()) {
            return 'CViewAccessToken-error-Unable to create a token associated to a secondary user';
        }

        // Setting default hash length if no hash nor datetime_end
        if (!$this->_hash_length && !$this->hash && !$this->datetime_end) {
            $this->setDefaultHashLength();
        }

        // Hash provided, we check charset validity
        if ($this->hash) {
            $this->_hash_length = $this->computeHashLength();

            if (!$this->hashIsAllowed($this->hash)) {
                return sprintf('CViewAccessToken-error-Invalid hash, characters not allowed: %s', $this->hash);
            }
        }

        // We compute validity duration
        if ($this->datetime_end && !$this->_hash_length) {
            $this->_hash_length = $this->computeHashLengthForValidityDuration(
                CMbDT::durationSecond(CMbDT::dateTime(), $this->datetime_end)
            );
        }

        // Provided hash length is sufficient
        if ($this->_hash_length < self::MINIMUM_HASH_LENGTH) {
            return sprintf(
                'CViewAccessToken-error-Hash must be composed of at least %d characters',
                self::MINIMUM_HASH_LENGTH
            );
        }

        // Validity date must be compliant to hash length specifications
        $validity_date = $this->computeMinValidityDateForHashLength();

        if ($this->datetime_end) {
            if ($validity_date && ($this->datetime_end > $validity_date)) {
                return sprintf('CViewAccessToken-error-Token can have a maximum validity date of %s', $validity_date);
            }
        } else {
            $this->datetime_end = $validity_date;
        }

        if (!$this->_id) {
            // On creation, if custom hash is provided, token must be restricted
            if ($this->hash && !$this->restricted) {
                return 'CViewAccessToken-error-Token with user defined hash must be restricted';
            }

            // Generate hash according to required hash length
            $this->hash = ($this->hash) ?: $this->generateHash();

            $this->purgeSome();
        }

        return parent::store();
    }

    /**
     * Check whether the token user is allowed
     *
     * @return bool
     */
    private function checkUserIsAllowed(): bool
    {
        try {
            $token_user = CUser::findOrFail($this->user_id);
        } catch (Exception $e) {
            return false;
        }

        if ($token_user->isSecondary()) {
            return false;
        }

        return true;
    }

    /**
     * Tell whether a given hash is allowed
     * Todo: Refactor when base64 will not be allowed anymore
     *
     * @param string $hash
     *
     * @return bool
     */
    private function hashIsAllowed(string $hash): bool
    {
        return (CMbString::isBase58($hash) || CMbString::isBase64($hash));
    }

    /**
     * Tell whether a token hash can be used in short-url form (without token= legacy syntax)
     *
     * @return string|null
     * @throws Exception
     */
    public static function getShortURLTokenHash(): ?string
    {
        if (count($_GET) !== 1) {
            return null;
        }

        $possibly_token_hash = array_key_first($_GET);

        if (!CMbString::isBase58($possibly_token_hash) && !CMbString::isBase64($possibly_token_hash)) {
            return null;
        }

        if (mb_strlen($possibly_token_hash) < self::MINIMUM_HASH_LENGTH) {
            return null;
        }

        $token = self::getByHash($possibly_token_hash);

        if ($token && $token->_id) {
            return $token->hash;
        }

        return null;
    }

    /**
     * Set module and action
     *
     * @return void
     */
    private function setModuleAction(): void
    {
        $params = $this->getParams();

        if (isset($params['m'])) {
            $this->_module = $params['m'];

            $action = array_intersect(self::ACTION_KEYS, array_keys($params));
            if ($action) {
                $this->_action = $params[reset($action)];
            }
        }
    }

    /**
     * Set the default wanted hash length
     *
     * @return void
     */
    public function setDefaultHashLength(): void
    {
        $this->_hash_length = self::DEFAULT_HASH_LENGTH;
    }

    /**
     * @return int
     */
    private function computeHashLength(): int
    {
        return mb_strlen($this->hash);
    }

    /**
     * @return string|null
     */
    private function computeMinValidityDateForHashLength(): ?string
    {
        $combinatorics = CMbString::getBase58CombinatoricsLimit($this->_hash_length);

        // Floating number (long hashes with big combinatorics floating notation)
        $c = $combinatorics / self::COMBINATORICS_COEFF;

        // Return the minimal allowed validity date
        if ($c < (10 * CMbDT::SECS_PER_YEAR)) {
            $c = intval($c);

            return CMbDT::dateTime("+{$c} seconds", $this->datetime_start);
        }

        return null;
    }

    /**
     * @param int $seconds
     *
     * @return string
     */
    private function computeHashLengthForValidityDuration(int $seconds): string
    {
        return intval(ceil(log($seconds * self::COMBINATORICS_COEFF, 10) / log(58, 10)));
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateHash(): string
    {
        return CMbSecurity::getRandomBase58String($this->_hash_length);
    }

    /**
     * Get list a validator classes
     *
     * @return array
     * @throws Exception
     */
    public function getValidators(): array
    {
        return $this->_validators = CApp::getChildClasses(CTokenValidator::class, false, true);
    }

    /**
     * Load a token by it's hash
     *
     * @param string $hash The hash
     *
     * @return self
     * @throws Exception
     */
    public static function getByHash(string $hash): self
    {
        $token       = new self();
        $token->hash = $hash;
        $token->loadMatchingObjectEsc();

        return $token;
    }

    /**
     * Parse the params string to an associative array
     *
     * @return array An associative array
     */
    private function getParams(): array
    {
        $this->_params = [];

        // Parse parameters
        // Do not remove whitespaces
        $params = strtr($this->params, ["\r\n" => "&", "\n" => "&"/*, " " => ""*/]);
        parse_str($params, $this->_params);

        return $this->_params;
    }

    /**
     * Build the complete url requested by the token.
     *
     * @return string
     * @throws Exception
     */
    public function getUrl(): string
    {
        return $this->_url = CAppUI::conf("external_url") . "/?{$this->hash}";
    }

    /**
     * Get ModuleAction object
     *
     * @return CModuleAction|string
     * @throws Exception
     */
    public function getModuleActionId()
    {
        $params = $this->getParams();

        $m = CValue::read($params, 'm');

        if (!$m) {
            return null;
        }

        $a = null;

        foreach (self::ACTION_KEYS as $_key) {
            if (isset($params[$_key]) && $params[$_key] != '1') {
                if (file_exists(__DIR__ . "/../../{$m}/{$params[$_key]}.php")) {
                    $a = $params[$_key];
                    break;
                } elseif (file_exists(__DIR__ . "/../../dP{$m}/{$params[$_key]}.php")) {
                    $m = "dP{$m}";
                    $a = $params[$_key];
                    break;
                }
            }
        }

        if ($m && $a) {
            return CModuleAction::getID($m, $a);
        }

        return '';
    }

    /**
     * Tell if the token is still valid regarding the datetime_start and the TTL
     *
     * Todo: Remove from updateFormField (CUser loading)
     *
     * @return bool True if still valid
     */
    public function isValid(Request $request = null): bool
    {
        if (!$this->_id) {
            return false;
        }

        $now = CMbDT::dateTime();

        $this->_validity_duration = CMbDT::relativeDuration($this->datetime_start, $this->datetime_end);

        // Validity start
        if ($now < $this->datetime_start) {
            return false;
        }

        // Validity end
        if ($this->datetime_end && $now > $this->datetime_end) {
            return false;
        }

        // Total usage
        if ($this->max_usages && $this->total_use >= $this->max_usages) {
            return false;
        }

        if ($request !== null && !$this->checkAllowedRoutes($request)) {
            return false;
        }

        try {
            CUser::findOrFail($this->user_id);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Apply token's params to redirect the user
     *
     * @return void
     */
    public function applyParams(): void
    {
        // Save token expiration in the session
        CAppUI::$token_session    = true;
        CAppUI::$token_expiration = $this->datetime_end;
        CAppUI::$token_restricted = $this->restricted == 1;
        CAppUI::$token_id         = $this->_id;

        CValue::setSessionAbs('token_expiration', CAppUI::$token_expiration);
        CValue::setSessionAbs('token_session', true);
        CValue::setSessionAbs('token_id', CAppUI::$token_id);

        $params = $this->getParams();

        // If the token is used by a CCronJob keep the execution_id to end the job after execution
        if ($cron_log_id = CCronJobLog::getCronJobLogId()) {
            $params['execute_cron_log_id'] = $cron_log_id;
        }

        if ($this->validator) {
            /** @var CTokenValidator $validator */
            $validator = new $this->validator();
            $validator->applyParams($params);
        } else {
            if (isset($params['tab']) && empty($params['a'])) {
                $params['a'] = $params['tab'];
                unset($params['tab']);
            }

            $_GET     = [];
            $_POST    = [];
            $_REQUEST = [];

            foreach ($params as $key => $value) {
                $_GET    [$key] = $value;
                $_REQUEST[$key] = $value;
            }
        }
    }

    /**
     * Marks the token as used
     *
     * @return void
     * @throws Exception
     */
    public function useIt(): void
    {
        $this->completeField('first_use', 'total_use');

        if ($this->_id) {
            if (!$this->first_use) {
                $this->first_use = CMbDT::dateTime();
            }

            $this->latest_use = CMbDT::dateTime();

            if (!$this->total_use) {
                $this->total_use = 1;
            } else {
                $this->total_use++;
            }
        }

        $this->store();
    }

    /**
     * Get URL parameters as single line query string
     *
     * @return string
     */
    public function getQueryString(): string
    {
        $params = $this->getParams();

        if ($this->validator) {
            /** @var CTokenValidator $validator */
            $validator = new $this->validator();

            return $validator->getQueryString($params);
        }

        return http_build_query($params, null, '&');
    }

    /**
     * Load the token's user
     *
     * @param bool $cache Use object cache
     *
     * @return CUser|CStoredObject The user object
     * @throws Exception
     */
    public function loadRefUser(bool $cache = true): ?CUser
    {
        $_ref_user = $this->loadFwdRef('user_id', $cache);

        $this->_view = "{$_ref_user->_view} - {$this->label}";

        return $this->_ref_user = $_ref_user;
    }

    /**
     * Purges some CViewAccessToken according to given delay
     *
     * @return bool|resource
     * @throws Exception
     */
    public function purgeSome()
    {
        if (!$delay = CAppUI::conf('CViewAccessToken_purge_delay')) {
            return false;
        }

        $date  = CMbDT::dateTime("- {$delay} days", CMbDT::format(null, "%Y-%m-%d 00:00:00"));
        $limit = 100;

        $ds = $this->getDS();

        $request = new CRequest();
        $request->addTable($this->_spec->table);

        $query = [
            $ds->prepare('(datetime_end IS NOT NULL AND datetime_end <= ?)', $date),
            '(total_use IS NOT NULL AND total_use >= max_usages)',
        ];

        $where = [
            'purgeable' => "= '1'",
        ];

        $where[] = implode(' OR ', $query);

        $request->addWhere($where);
        $request->setLimit($limit);

        return $ds->exec($request->makeDelete());
    }

    /**
     * @param CCSVFile           $csv
     * @param CViewAccessToken[] $tokens
     *
     * @return CCSVFile
     * @throws Exception
     */
    public static function prepareCSV(CCSVFile $csv, array $tokens): CCSVFile
    {
        $header = [
            CAppUI::tr('CViewAccessToken-hash'),
            CAppUI::tr('CViewAccessToken-user_id'),
            CAppUI::tr('CViewAccessToken-label'),
            CAppUI::tr('CViewAccessToken-module_action_id-court'),
            CAppUI::tr('CViewAccessToken-params'),
            CAppUI::tr('CViewAccessToken-restricted'),
            CAppUI::tr('CViewAccessToken-purgeable'),
            CAppUI::tr('CViewAccessToken-datetime_start'),
            CAppUI::tr('CViewAccessToken-datetime_end'),
            CAppUI::tr('CViewAccessToken-first_use'),
            CAppUI::tr('CViewAccessToken-latest_use'),
            CAppUI::tr('CViewAccessToken-max_usages-court'),
            CAppUI::tr('CViewAccessToken-total_use-court'),
            CAppUI::tr('CViewAccessToken-_mean_usage_duration'),
            CAppUI::tr('CViewAccessToken-validator'),
        ];

        $csv->writeLine($header);

        foreach ($tokens as $_token) {
            $_token->loadRefUser();

            // Translate the module / action
            $_module = null;
            $_action = null;
            if (isset($_token->_fwd['module_action_id'])) {
                $_module = $_token->_fwd['module_action_id']->module;
                $_action = $_token->_fwd['module_action_id']->action;
            }

            $_module_action = null;
            if ($_module && $_action) {
                $_module_action =
                    CAppUI::tr("module-{$_module}-court")
                    . " > "
                    . CAppUI::tr("module-{$_module}-tab-{$_action}");
            }

            $line = [
                'hash'                 => $_token->hash,
                'user_id'              => ($_token->_ref_user) ? $_token->_ref_user->_view : null,
                'label'                => $_token->label,
                'module_action_id'     => $_module_action,
                'params'               => $_token->params,
                'restricted'           => $_token->restricted,
                'purgeable'            => $_token->purgeable,
                'datetime_start'       => $_token->datetime_start,
                'datetime_end'         => $_token->datetime_end,
                'first_use'            => $_token->first_use,
                'latest_use'           => $_token->latest_use,
                'max_usages'           => $_token->max_usages,
                'total_use'            => $_token->total_use,
                '_mean_usage_duration' => $_token->_mean_usage_duration['locale'],
                'validator'            => $_token->validator,
            ];

            $csv->writeLine($line);
        }

        return $csv;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function checkAllowedRoutes(Request $request): bool
    {
        // If no request or no routes_names or request is not an api request do not check routes
        if (!$this->routes_names || !$this->isRequestApi($request)) {
            return true;
        }

        return in_array($request->attributes->get('_route'), $this->_allowed_routes);
    }
}
