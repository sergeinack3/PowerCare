<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Import\CMbCSVObjectImport;
use Ox\Interop\Eai\CSpecialtyAsip;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CBanque;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectationUfSecondaire;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Sante400\CIdSante400;

class CCSVImportMediusers extends CMbCSVObjectImport
{
    public const MAX_LINES = 10000;

    /** @var string[] */
    public const HEADERS = [
        'nom',
        'prenom',
        'username',
        'password',
        'type',
        'fonction',
        'profil',
        'adeli',
        'rpps',
        'spec_cpam',
        'discipline',
        'activite',
        'idex',
        'acces_local',
        'actif',
        'ufm',
        'main_user',
        'secteur',
        'pratique_tarifaire',
        'ccam_context',
        'num_astreinte',
        'num_astreinte_autre',
        'ufsecondaire',
        'code_asip',
        'astreinte',
        'commentaires',
        'cps',
        'mail_apicrypt',
        'mssante_address',
        'sexe',
        'force_change_pw',
        'initials',
        'user_mail',
        'user_phone',
        'internal_phone',
        'ldap_id',
        'color',
        'deb_activite',
        'fin_activite',
        'use_bris_de_glace',
        'cab',
        'conv',
        'zisd',
        'ik',
        'titres',
        'compte',
        'banque_name',
        'mode_tp_acs',
        'allow_change_password',
    ];

    /** @var array */
    protected $results = [];

    /** @var array */
    protected $unfound = [];

    protected $errors = [];

    /** @var array */
    protected $count_created = [];

    /** @var array */
    protected $count_found = [];

    /** @var int */
    protected $dryrun;

    /** @var int */
    protected $update;

    /** @var array */
    protected $line;

    /**
     * @inheritdoc
     */
    public function __construct(
        $file_path,
        $dryrun,
        $update,
        $start = 0,
        $step = 100,
        $profile = CCSVFile::PROFILE_EXCEL
    ) {
        parent::__construct($file_path, $start, $step, $profile);
        $this->dryrun = $dryrun;
        $this->update = $update;
    }

    /**
     * @inheritdoc
     *
     * @throws CMbException
     */
    public function import()
    {
        $this->openFile();

        if (($count = ($this->countLines() - 1)) > self::MAX_LINES) {
            throw new CMbException('CCSVImportMediusers-Error-Too many lines', $count, self::MAX_LINES);
        }

        $this->setColumnNames();

        $this->current_line = 0;
        while ($this->line = $this->readAndSanitizeLine()) {
            $this->current_line++;

            if (!$this->line['nom']) {
                $this->errors[] = ['mediusers-import-lastname-mandatory-line%d', $this->current_line];
                continue;
            }

            $this->results[$this->current_line] = $this->line;

            $user     = $this->getUser($this->line);
            $mediuser = ($user->_id) ? $user->loadRefMediuser() : null;

            $function = $this->getFunction($this->line['fonction'], $this->line['type']);
            if (!$mediuser || !$mediuser->_id) {
                if (($mediuser = $this->createNewMediuser($user, $function)) === null) {
                    continue;
                }
            } elseif ($function && $function->_id && $this->update) {
                $mediuser->function_id = $function->_id;
            }

            if ($mediuser->_user_type !== $user->user_type && $this->update && $user->user_type) {
                $mediuser->_user_type = $user->user_type;
            }

            if (!is_numeric($mediuser->_user_type) || !array_key_exists($mediuser->_user_type, CUser::$types)) {
                $this->unfound["user_type"][$mediuser->_user_type] = true;
            }

            $mediuser->adeli
                = (($this->update || !$mediuser->adeli) && $this->line['adeli']) ? $this->line['adeli'] : null;
            $mediuser->rpps
                = (($this->update || !$mediuser->rpps) && $this->line['rpps']) ? $this->line['rpps'] : null;

            if ($mediuser->_id && (!$this->update || $this->dryrun)) {
                $this->results[$this->current_line]['found'] = true;
                if (!$this->dryrun) {
                    continue;
                }
            }

            $mediuser->activite
                = (isset($this->line['activite']) && $this->line['activite'] !== '') ? $this->line['activite'] : null;

            $mediuser->actif
                              = (isset($this->line['actif']) && $this->line['actif'] !== '') ? $this->line['actif'] : null;
            $mediuser->remote = (isset($this->line['acces_local']) && $this->line['acces_local'] !== '')
                ? $this->line['acces_local'] : null;

            $mediuser = $this->setMainUser($mediuser);

            // Password
            if (!$mediuser->_id) {
                // On force la regénération du mot de passe
                if (CAppUI::conf("instance_role") == "prod" && !CAppUI::conf("admin LDAP ldap_connection")) {
                    $mediuser->_force_change_password = true;
                }

                $user_username = $mediuser->_user_username;
                $mediuser->makeUsernamePassword($this->line["prenom"], $this->line["nom"]);
                $mediuser->_user_username = $user_username;
            }

            if (trim($this->line["password"])) {
                $mediuser->_user_password = trim($this->line["password"]);
            } else {
                $mediuser->_user_password = null;
            }

            //Profil
            if ($profile_name = $this->line['profil']) {
                $mediuser = $this->checkProfile($mediuser, $profile_name);
            }

            if ($spec_cpam_code = $this->line['spec_cpam']) {
                $mediuser = $this->getSpecCPAM($mediuser, $spec_cpam_code);
            }

            if ($discipline_name = $this->line["discipline"]) {
                $mediuser = $this->getDiscipline($mediuser, $discipline_name);
            }

            if ($this->line["secteur"]) {
                $mediuser->secteur = trim($this->line['secteur']);
            }

            if ($this->line["pratique_tarifaire"]) {
                $mediuser->pratique_tarifaire = trim($this->line['pratique_tarifaire']);
            }

            if ($this->line["ccam_context"]) {
                $mediuser->ccam_context = trim($this->line['ccam_context']);
            }

            if ($this->line["num_astreinte"]) {
                $mediuser->_user_astreinte = trim($this->line["num_astreinte"]);
            }

            if ($this->line["num_astreinte_autre"]) {
                $mediuser->_user_astreinte_autre = trim($this->line["num_astreinte_autre"]);
            }

            if ($this->line["code_asip"]) {
                $asip_code       = new CSpecialtyAsip();
                $asip_code->code = trim($this->line["code_asip"]);
                $asip_code->loadMatchingObjectEsc();

                if ($asip_code->_id) {
                    $mediuser->other_specialty_id = $asip_code->_id;
                }
            }

            if ($this->line["astreinte"]) {
                $mediuser->astreinte = trim($this->line["astreinte"]);
            }

            if ($this->line["commentaires"]) {
                $mediuser->commentaires = trim($this->line["commentaires"]);
            }

            if ($this->line["cps"]) {
                $mediuser->cps = trim($this->line["cps"]);
            }

            if ($this->line["mail_apicrypt"]) {
                $mediuser->mail_apicrypt = trim($this->line["mail_apicrypt"]);
            }

            if ($this->line["mssante_address"]) {
                $mediuser->mssante_address = trim($this->line["mssante_address"]);
            }

            if ($this->line["sexe"]) {
                $mediuser->_user_sexe = trim($this->line["sexe"]);
            }

            if ($this->line["force_change_pw"]) {
                $mediuser->_force_change_password = trim($this->line["force_change_pw"]);
            }

            if ($this->line["initials"]) {
                $mediuser->initials = trim($this->line["initials"]);
            }

            if ($this->line["user_mail"]) {
                $mediuser->_user_email = trim($this->line["user_mail"]);
            }

            if ($this->line["user_phone"]) {
                $mediuser->_user_phone = trim($this->line["user_phone"]);
            }

            if ($this->line["internal_phone"]) {
                $mediuser->_internal_phone = trim($this->line["internal_phone"]);
            }

            if ($this->line["color"]) {
                $mediuser->color = trim($this->line["color"]);
            }

            if ($this->line["deb_activite"]) {
                $mediuser->deb_activite = trim($this->line["deb_activite"]);
            }

            if ($this->line["fin_activite"]) {
                $mediuser->fin_activite = trim($this->line["fin_activite"]);
            }

            if ($this->line["use_bris_de_glace"]) {
                $mediuser->use_bris_de_glace = trim($this->line["use_bris_de_glace"]);
            }

            if ($this->line["cab"]) {
                $mediuser->cab = trim($this->line["cab"]);
            }

            if ($this->line["conv"]) {
                $mediuser->conv = trim($this->line["conv"]);
            }

            if ($this->line["zisd"]) {
                $mediuser->zisd = trim($this->line["zisd"]);
            }

            if ($this->line["ik"]) {
                $mediuser->ik = trim($this->line["ik"]);
            }

            if ($this->line["titres"]) {
                $mediuser->titres = trim($this->line["titres"]);
            }

            if ($this->line["compte"]) {
                $mediuser->compte = trim($this->line["compte"]);
            }

            if ($banque_name = $this->line["banque_name"]) {
                $mediuser = $this->getBanqueId($mediuser, $banque_name);
            }

            if ($this->line["mode_tp_acs"]) {
                $mediuser->mode_tp_acs = trim($this->line["mode_tp_acs"]);
            }

            if ($this->line["allow_change_password"]) {
                $mediuser->_allow_change_password = trim($this->line["allow_change_password"]);
            }

            if ($this->dryrun) {
                if ($mediuser->_id) {
                    $this->incrementCount($this->count_found, 'CMediusers');
                } else {
                    $this->incrementCount($this->count_created, 'CMediusers');
                }

                continue;
            }

            $mediuser->unescapeValues();

            $new = $mediuser->_id ? false : true;

            if (!$new && !$this->update) {
                continue;
            }

            if ($msg = $mediuser->store()) {
                $this->errors[]                              = [$this->current_line . ' : ' . $msg];
                $this->results[$this->current_line]["error"] = $msg;

                continue;
            }

            if ($new) {
                $this->incrementCount($this->count_created, 'CMediusers');
            } else {
                $this->incrementCount($this->count_found, 'CMediusers');
            }

            $mediuser->insFunctionPermission();
            $mediuser->insGroupPermission();
            $this->results[$this->current_line]["result"]   = 0;
            $this->results[$this->current_line]["username"] = $mediuser->_user_username;
            $this->results[$this->current_line]["password"] = $mediuser->_user_password;

            $group_id = CGroups::loadCurrent()->_id;

            if (isset($this->line['ufm']) && ($ufms = $this->line['ufm'])) {
                $this->getUFMs($ufms, $group_id, $mediuser);
            }

            if ($this->line['idex']) {
                $this->addIdex($mediuser, $group_id);
            }

            if ($this->line['ldap_id']) {
                $this->addLdapTag($this->line['ldap_id'], $mediuser->loadRefUser());
            }

            if ($this->line['ufsecondaire']) {
                $this->addSecondaryUf($mediuser, $group_id);
            }
        }

        $this->csv->close();
    }

    /**
     * @throws Exception
     */
    protected function addIdex(CMediusers $mediuser, int $group_id): void
    {
        $all_idex = explode(',', $this->line['idex']);
        foreach ($all_idex as $_idex) {
            $idex_parts = explode('|', $_idex);

            $idex               = new CIdSante400();
            $idex->object_class = $mediuser->_class;
            $idex->object_id    = $mediuser->_id;
            $idex->id400        = $idex_parts[0];

            $idex->tag = CMediusers::getTagMediusers($group_id);
            if (isset($idex_parts[1])) {
                $idex->tag = $idex_parts[1];
            }

            $idex->loadMatchingObjectEsc();

            if ($idex->_id) {
                $this->unfound["idex"][$_idex] = true;
                continue;
            } elseif ($msg = $idex->store()) {
                $this->errors[] = [$this->current_line . " : " . $msg];
            }
        }
    }

    private function incrementCount(array &$count, string $shortclass): void
    {
        if (isset($count[$shortclass])) {
            $count[$shortclass]++;
        } else {
            $count[$shortclass] = 1;
        }
    }

    private function addLdapTag(?string $ldap_id, CUser $user): void
    {
        if (!$ldap_id) {
            return;
        }

        $user->ldap_uid = $ldap_id;
        $user->store();
    }

    /**
     * @param CMediusers $mediuser
     * @param int        $group_id
     *
     * @throws Exception
     */
    protected function addSecondaryUf($mediuser, $group_id): void
    {
        $all_ufs = explode('|', $this->line['ufsecondaire']);

        foreach ($all_ufs as $_uf) {
            $uf           = new CUniteFonctionnelle();
            $uf->group_id = $group_id;
            $uf->code     = $_uf;

            $uf->loadMatchingObjectEsc();

            if (!$uf || !$uf->_id) {
                $this->errors[] = [$this->current_line . " : L'unité fonctionnelle $_uf n'existe pas."];
                continue;
            }

            $affectation = new CAffectationUfSecondaire();
            $affectation->setObject($mediuser);
            $affectation->uf_id = $uf->_id;
            $affectation->loadMatchingObjectEsc();

            if (!$affectation->_id) {
                if ($msg = $affectation->store()) {
                    $this->errors[] = [$this->current_line . ' : ' . $msg];
                } else {
                    $this->incrementCount($this->count_created, 'CAffectationUfSecondaire');
                }
            } else {
                $this->incrementCount($this->count_found, 'CAffectationUfSecondaire');
            }
        }
    }

    /**
     * @param CMediusers $mediuser Mediuser
     *
     * @return CMediusers
     */
    public function setMainUser($mediuser)
    {
        if (isset($this->line['main_user']) && $this->line['main_user']) {
            $main_user                = new CUser();
            $main_user->user_username = $this->line['main_user'];
            $main_user->loadMatchingObjectEsc();

            if ($main_user && $main_user->_id) {
                $mediuser->main_user_id = $main_user->_id;
            }
        }

        return $mediuser;
    }

    /**
     * Get a CUser from CSV
     *
     * @throws Exception
     */
    private function getUser(array $line): CUser
    {
        $user                  = new CUser();
        $user->user_last_name  = (isset($line['nom']) && $line['nom']) ? $line['nom'] : null;
        $user->user_first_name = (isset($line['prenom']) && $line['prenom']) ? $line['prenom'] : null;

        if (!$user->user_last_name && !$user->user_first_name) {
            return $user;
        }

        $user->user_username =
            $line['username'] ?: CMbString::lower(substr($user->user_first_name, 0, 1) . $user->user_last_name);

        $user->loadMatchingObjectEsc();

        if (!$user->user_type || ($this->update && $line['type'])) {
            $current_user = CUser::get();

            // Do not allow to put an admin user if current user is not admin
            if ($line['type'] == 1 && $current_user->user_type !== '1') {
                $user->user_type = 14;
            } else {
                $user->user_type = $line['type'];
            }
        }

        return $user;
    }

    /**
     * Get a new CMediusers from CSV
     *
     * @param CUser      $user     User from CSV
     * @param CFunctions $function Function of the mediuser
     *
     * @return CMediusers
     */
    private function createNewMediuser(CUser $user, ?CFunctions $function): ?CMediusers
    {
        if (!$function || !$function->_id) {
            $this->errors[] = [
                $this->current_line . ' : ' . CAppUI::tr(
                    'CMediusers-Error-Function is mandatory for a new user'
                ),
            ];
            $this->results[$this->current_line]['error']
                            = CAppUI::tr('CMediusers-Error-Function is mandatory for a new user');

            return null;
        }

        if (!trim($this->line['password'])) {
            $this->errors[] = [
                $this->current_line . ' : ' . CAppUI::tr(
                    'CUser-Error-Password is mandatory for a new user'
                ),
            ];

            $this->results[$this->current_line]['error']
                = CAppUI::tr('CUser-Error-Password is mandatory for a new user');

            return null;
        }

        $mediuser              = new CMediusers();
        $mediuser->function_id = $function->_id;

        if (!$user->_id) {
            $mediuser->_user_last_name  = $user->user_last_name;
            $mediuser->_user_first_name = $user->user_first_name;
            $mediuser->_user_username   = $user->user_username;
            $mediuser->_user_type       = $user->user_type;
        } else {
            $mediuser->user_id        = $user->_id;
            $mediuser->_user_username = $user->user_username;
        }

        return $mediuser;
    }

    /**
     * Set the discipline field to a mediuser
     *
     * @param CMediusers $mediuser        Mediuser to set discipline
     * @param string     $discipline_name Discipline to set
     *
     * @return CMediusers
     */
    public function getDiscipline(CMediusers $mediuser, string $discipline_name): CMediusers
    {
        $discipline       = new CDiscipline();
        $discipline->text = strtoupper($discipline_name);
        $discipline->loadMatchingObject();
        if ($discipline->_id) {
            $mediuser->discipline_id = $discipline->_id;
        } else {
            $this->unfound["discipline_name"][$discipline_name] = true;
        }

        return $mediuser;
    }

    /**
     * Set the banque_id field to a mediuser
     *
     * @param CMediusers $mediuser    Mediuser to set banque
     * @param string     $banque_name Banque to set
     *
     * @return CMediusers
     * @throws Exception
     */
    public function getBanqueId(CMediusers $mediuser, string $banque_name): CMediusers
    {
        $banque      = new CBanque();
        $banque->nom = $banque_name;
        $banque->loadMatchingObject();
        if ($banque->_id) {
            $mediuser->banque_id = $banque->_id;
        } else {
            $this->unfound["banque_name"][$banque_name] = true;
        }

        return $mediuser;
    }

    /**
     * Import CAffectationUniteFonctionnelle
     *
     * @param string     $ufms     CUniteFonctionnelle-code separated by |
     * @param int        $group_id Group id
     * @param CMediusers $mediuser Mediuser to link to the ufm
     *
     * @return void
     */
    public function getUFMs($ufms, $group_id, $mediuser)
    {
        if (!$ufms) {
            return;
        }

        $_ufms = explode('|', $ufms);
        foreach ($_ufms as $_ufm) {
            $ufm           = new CUniteFonctionnelle();
            $ufm->type     = 'medicale';
            $ufm->group_id = $group_id;
            $ufm->code     = $_ufm;
            $ufm->loadMatchingObjectEsc();

            if (!$ufm->_id) {
                $this->unfound["ufm"][$_ufm] = true;
                $this->errors[]              =
                    [$this->current_line . ' : ' . CAppUI::tr('mediusers-import-ufm-not-exists%s', $_ufm)];
                continue;
            }

            $ufm_link        = new CAffectationUniteFonctionnelle();
            $ufm_link->uf_id = $ufm->_id;
            $ufm_link->setObject($mediuser);
            $ufm_link->loadMatchingObjectEsc();

            if (!$ufm_link->_id) {
                if ($msg = $ufm_link->store()) {
                    $this->errors[] = [$this->current_line . ' : ' . $msg];
                } else {
                    $this->incrementCount($this->count_created, 'CAffectationUniteFonctionnelle');
                }
            } else {
                $this->incrementCount($this->count_found, 'CAffectationUniteFonctionnelle');
            }
        }
    }

    /**
     * Set the spec CPAM to the mediuser
     *
     * @param CMediusers $mediuser       Mediuser to set spec CPAM
     * @param string     $spec_cpam_code Code of the spec CPAM
     *
     * @return CMediusers
     */
    public function getSpecCPAM($mediuser, $spec_cpam_code)
    {
        $spec_cpam = CSpecCPAM::get($spec_cpam_code);
        if ($spec_cpam->_id) {
            $mediuser->spec_cpam_id = $spec_cpam->_id;
        } else {
            $this->unfound["spec_cpam_code"][$spec_cpam_code] = true;
        }

        return $mediuser;
    }

    /**
     * Get a function (create it if necessary) and return it
     *
     * @return CFunctions|null
     */
    private function getFunction(?string $function_name, string $type): ?CFunctions
    {
        if (!$function_name) {
            return null;
        }

        $group_id = CGroups::loadCurrent()->_id;
        // Fonction
        $function           = new CFunctions();
        $function->group_id = $group_id;
        $function->text     = $function_name;
        $function->loadMatchingObjectEsc();
        if (!$function->_id) {
            if (in_array($type, ["3", "4", "13"])) {
                $function->type = "cabinet";
            } else {
                $function->type = "administratif";
            }
            $function->color                     = "ffffff";
            $function->compta_partagee           = 0;
            $function->consults_events_partagees = 1;
            $function->unescapeValues();
            $msg = $function->store();
            if ($msg) {
                CAppUI::stepAjax($msg, UI_MSG_ERROR);
                $this->results[$this->current_line]["error"]    = $msg;
                $this->results[$this->current_line]["username"] = "";
                $this->results[$this->current_line]["password"] = "";

                return null;
            }
        }

        return $function;
    }

    /**
     * Check if a profile exists and set the user profile to it
     *
     * @param CMediusers $mediuser     Mediusers to import
     * @param string|int $profile_name Name of the profile to search
     */
    private function checkProfile(CMediusers $mediuser, $profile_name): CMediusers
    {
        $profile = new CUser();

        if ((int)$profile_name) {
            $profile->load($profile_name);
        } elseif (is_string($profile_name)) {
            $profile->user_username = $profile_name;
            $profile->template      = 1;
            $profile->loadMatchingObject();
        }

        if ($profile->_id) {
            $mediuser->_profile_id = $profile->_id;
        } else {
            $this->unfound['profile_name'][$profile_name] = true;
        }

        return $mediuser;
    }

    /**
     * @inheritdoc
     */
    function sanitizeLine($line)
    {
        if (!$line) {
            return '';
        }

        $line = array_map('addslashes', array_map('trim', parent::sanitizeLine($line)));
        foreach (static::HEADERS as $_info) {
            if (!array_key_exists($_info, $line)) {
                $line[$_info] = '';
            }
        }

        return $line;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return array
     */
    public function getUnfound()
    {
        return $this->unfound;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getCreated(): array
    {
        return $this->count_created;
    }

    public function getFound(): array
    {
        return $this->count_found;
    }
}
