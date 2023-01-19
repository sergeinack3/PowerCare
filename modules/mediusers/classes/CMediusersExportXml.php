<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Closure;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Import\CMbObjectExport;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CPreferences;
use ZipArchive;

/**
 * Description
 */
class CMediusersExportXml
{
    public const MEDIUSER_BW = [
        "CMediusers" => [
            'identifiants',
            'ufs',
        ],
    ];

    public const MEDIUSER_FWD = [
        "CMediusers"                     => [
            "user_id",
            "discipline_id",
            "function_id",
            "other_specialty_id",
            "spec_cpam_id",
        ],
        "CAffectationUniteFonctionnelle" => [
            "uf_id",
        ],
        "CUser"                          => [
            "profile_id",
        ],
        "CFunctions"                     => [
            "group_id",
        ],
    ];

    public const TARIFS = [
        "CMediusers" => [
            "tarifs",
        ],
        "CGroups"    => [
            "tarif_group",
        ],
        "CFunctions" => [
            "tarifs",
        ],
    ];

    public const MEDIUSER_PLA_CONS = ['CMediusers' => 'plages_consult'];

    public const USER_PERM_BACK = ['CUser' => ['permissions_module', 'permissions_objet']];

    public const USER_PERM_FWD = ['CPermModule' => ['mod_id']];

    public const USER_PREF = ['CUser' => ['preferences']];

    public const DIR_TEMP = "/tmp/export_mediusers";

    /**
     * @var array
     */
    public $config;

    /**
     * @var CSQLDataSource
     */
    public $ds;

    /**
     * @var CGroups
     */
    public $group;
    /**
     * @var string
     */
    private $date;

    /**
     * @var int
     */
    private $etab_id;
    /**
     * @var int|null
     */
    private $function_id;
    /**
     * @var bool|null
     */
    private $profile;
    /**
     * @var bool|null
     */
    private $perms;
    /**
     * @var bool|null
     */
    private $prefs;
    /**
     * @var bool|null
     */
    private $default_prefs;
    /**
     * @var bool|null
     */
    private $perms_functionnal;
    /**
     * @var bool|null
     */
    private $tarification;
    /**
     * @var bool|null
     */
    private $planning;
    /**
     * @var array
     */
    private $backrefs_tree = [];
    /**
     * @var array
     */
    private $fwdrefs_tree = [];
    /**
     * @var string
     */
    private $root_dir;

    public function __construct(
        ?int $etab_id = null,
        ?int $function_id = null,
        ?bool $profile = false,
        ?bool $perms = false,
        ?bool $prefs = false,
        ?bool $default_prefs = false,
        ?bool $perms_functionnal = false,
        ?bool $tarification = false,
        ?bool $planning = false
    ) {
        $this->etab_id           = $etab_id;
        $this->function_id       = $function_id;
        $this->profile           = $profile;
        $this->perms             = $perms;
        $this->prefs             = $prefs;
        $this->default_prefs     = $default_prefs;
        $this->perms_functionnal = $perms_functionnal;
        $this->tarification      = $tarification;
        $this->planning          = $planning;
        $this->ds                = CSQLDataSource::get('std');
        $this->date              = CMbDT::format(null, "%y-%m-%d");
        $this->root_dir          = CAppUI::conf("root_dir");
    }

    public function exportMediusers(): ?array
    {
        if (!$this->checkPerm()) {
            return ['access-forbidden', UI_MSG_ERROR];
        }

        $this->constructTrees();

        if ($this->profile) {
            $query = $this->getProfileQuery();
        } else {
            $query = $this->getUsersQuery();
        }

        $mediusers_ids = CMbArray::pluck($this->ds->loadList($query->makeSelect()), 'user_id');

        $nb_mediusers = ($mediusers_ids) ? count($mediusers_ids) : 0;
        if ($nb_mediusers == 0) {
            return ['CMediusers-nb-to-export', UI_MSG_OK, $nb_mediusers];
        }

        $objects = $this->getObjects($mediusers_ids);

        $filter_callback = $this->getFilterCallback($this->prefs, $this->perms_functionnal);

        $dir_zip_temp = $this->writeFiles($objects, $filter_callback);

        $file_name_sanitize = $this->sanitizeFileName();
        $this->download($dir_zip_temp, "$file_name_sanitize.zip");

        return null;
    }

    private function getFilterCallback(bool $prefs, bool $perms_functionnal): Closure
    {
        return function (CStoredObject $object) use ($prefs, $perms_functionnal) {
            if ($object instanceof CPreferences) {
                if ($object->value === '' || $object->value === null) {
                    return false;
                }

                if ($perms_functionnal && !$prefs && $object->restricted == '0') {
                    return false;
                }

                if (!$perms_functionnal && $prefs && $object->restricted == '1') {
                    return false;
                }
            }

            if ($object instanceof CPermObject) {
                if ($object->object_id !== null) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * @return mixed
     */
    private function getGroup(): CGroups
    {
        return $this->group = CGroups::get($this->etab_id);
    }

    private function checkPerm(): bool
    {
        $group = $this->getGroup();

        if (!$group->getPerm(PERM_READ)) {
            return false;
        }

        return true;
    }

    private function constructTrees(): array
    {
        if (!$this->profile) {
            $this->backrefs_tree = self::MEDIUSER_BW;

            $this->fwdrefs_tree = self::MEDIUSER_FWD;

            if ($this->tarification) {
                $tarifs = self::TARIFS;

                $this->backrefs_tree = array_merge_recursive($this->backrefs_tree, $tarifs);
            }

            if ($this->planning) {
                $this->backrefs_tree = array_merge_recursive($this->backrefs_tree, self::MEDIUSER_PLA_CONS);
            }
        }


        if ($this->perms) {
            $this->backrefs_tree = array_merge_recursive($this->backrefs_tree, self::USER_PERM_BACK);

            $this->fwdrefs_tree = array_merge_recursive($this->fwdrefs_tree, self::USER_PERM_FWD);
        }

        if ($this->prefs || $this->perms_functionnal) {
            $this->backrefs_tree = array_merge_recursive($this->backrefs_tree, self::USER_PREF);
        }

        return [$this->backrefs_tree, $this->fwdrefs_tree];
    }

    private function getUsersQuery(): CRequest
    {
        $query = new CRequest();

        $query->addSelect('M.user_id');
        $query->addTable(['users_mediboard M', 'functions_mediboard F', 'users U']);

        /** @var CGroups $group */
        $group = $this->getGroup();
        $where = [
            'U.template'    => "= '0'",
            'U.user_id'     => '= M.user_id',
            'M.function_id' => '= F.function_id',
            'F.group_id'    => $this->ds->prepare('= ?', $group->group_id),
            'M.actif'       => "= '1'",
        ];

        if ($this->function_id) {
            $where['F.function_id'] = $this->ds->prepare('= ?', $this->function_id);
        }

        $query->addWhere($where);
        $query->addOrder('M.user_id ASC');

        return $query;
    }

    private function getProfileQuery(): CRequest
    {
        $query = new CRequest();

        $query->addSelect('user_id');
        $query->addTable('users');
        $where = [
            'template' => "= '1'",
        ];
        $query->addWhere($where);
        $query->addOrder('user_id ASC');

        return $query;
    }

    private function getObjects(array $mediusers_ids): ?array
    {
        if ($this->profile) {
            $_user = new CUser();

            return $_user->loadAll($mediusers_ids);
        } else {
            $mediuser = new CMediusers();

            return $mediuser->loadAll($mediusers_ids);
        }
    }

    private function writeFiles(?array $objects, Closure $filter_callback): string
    {
        $archive = new ZipArchive();

        $temp_dir     = $this->root_dir . self::DIR_TEMP;
        $dir_zip_temp = $this->createDir($temp_dir);

        if (($code = $archive->open($dir_zip_temp, ZipArchive::OVERWRITE)) !== true) {
            throw new CMbException('Cannot open archive ' . $dir_zip_temp . '. Error code : ' . $code);
        }

        $files_names = [];
        /** @var CUser $_mediuser */
        foreach ($objects as $_mediuser) {
            try {
                $export               = new CMbObjectExport($_mediuser, $this->backrefs_tree);
                $export->empty_values = false;
                $export->setFilterCallback($filter_callback);
                $export->setForwardRefsTree($this->fwdrefs_tree);

                if ($this->profile) {
                    $export = $this->checkPermOrPref($export, $_mediuser);
                }

                $xml = $export->toDOM()->saveXML();
                if ($this->profile) {
                    $user_name = utf8_encode($_mediuser->user_username);
                } else {
                    $user_name = utf8_encode($_mediuser->_user_username);
                }

                if ($xml) {
                    $file_name = "$temp_dir/$user_name.xml";
                    if (file_put_contents($file_name, $xml) !== false) {
                        $files_names[] = $file_name;
                    }
                    $archive->addFile($file_name, "$user_name.xml");
                }
            } catch (CMbException $e) {
                $e->stepAjax(UI_MSG_ERROR);
            }
        }

        if ($this->default_prefs) {
            $file_name = $this->exportDefaultPref($temp_dir);
            if ($file_name !== null) {
                $archive->addFile($file_name, 'Default_prefs.xml');
                $files_names [] = $file_name;
            }
        }
        $archive->close();

        foreach ($files_names as $file_name) {
            unlink($file_name);
        }

        return $dir_zip_temp;
    }

    private function exportDefaultPref(string $temp_dir): ?string
    {
        $preference  = new CPreferences();
        $preferences = $preference->loadList(['user_id' => 'IS NULL']);

        $file_name = null;

        if ($preferences) {
            $export               = new CMbObjectExport(reset($preferences));
            $export->empty_values = false;
            $xml                  = $export->objectListToDOM($preferences)->saveXML();
            $file_name            = "$temp_dir/Default_prefs.xml";
            file_put_contents($file_name, $xml);

            CAppUI::stepAjax('CPreferences-default-nb-exported', UI_MSG_OK, count($preferences));
        }

        return $file_name;
    }

    private function addPerm(CMbObjectExport $export, CUser $_mediuser): CMbObjectExport
    {
        $hash_mods = $_mediuser->getPermModulesHash();
        $export->addHash('CPermModule', $hash_mods);

        $hash_objects = $_mediuser->getPermObjectHash();
        $export->addHash('CPermObject', $hash_objects);

        return $export;
    }

    private function addPrefs(CMbObjectExport $export, CUser $_mediuser): CMbObjectExport
    {
        $hash_prefs = $_mediuser->getPrefsHash();
        $export->addHash('CPreferences', $hash_prefs);

        return $export;
    }

    private function checkPermOrPref(CMbObjectExport $export, CUser $_mediuser): CMbObjectExport
    {
        if ($this->perms) {
            $export = $this->addPerm($export, $_mediuser);
        }

        if ($this->prefs || $this->perms_functionnal) {
            $export = $this->addPrefs($export, $_mediuser);
        }

        return $export;
    }

    protected function download(string $dir_zip_temp, string $file_name): void
    {
        ob_end_clean();

        // Génération des en-têtes CSV
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Type: application/zip;charset=ISO-8859-1');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');

        // Envoi des en-têtes
        ob_flush();
        flush();
        echo file_get_contents($dir_zip_temp);

        unlink($dir_zip_temp);
    }

    private function createDir(string $temp_dir): string
    {
        if (is_dir($temp_dir)) {
            CMbPath::emptyDir($temp_dir);
        }

        CMbPath::forceDir($temp_dir);
        $dir_sanitize = $this->sanitizeFileName();
        $dir_zip_temp = "$temp_dir/$dir_sanitize.zip";

        if (file_exists($dir_zip_temp)) {
            CMbPath::remove($dir_zip_temp);
        }

        if (!touch($dir_zip_temp)) {
            throw new CMbException('Cannot create file ' . $dir_zip_temp);
        }

        return $dir_zip_temp;
    }

    private function sanitizeFileName(): string
    {
        return CMbString::removeDiacritics(
            utf8_encode(str_replace(' ', '_', "export-{$this->group->text}-$this->date"))
        );
    }
}
