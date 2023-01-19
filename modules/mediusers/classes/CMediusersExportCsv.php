<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
class CMediusersExportCsv
{
    public const OPTIONNAL_FIELDS = [
        'ldap',
        'last_auth',
    ];


    private const MASS_LOAD_FW = [
        'other_specialty_id',
        'function_id',
        'discipline_id',
        'main_user_id',
        'user_id',
    ];

    private const MASS_LOAD_BACK = [
        'identifiants',
        'ufs_secondaires',
    ];

    /** @var CGroups */
    private $group;

    /** @var CCSVFile */
    private $writer;

    /** @var string */
    private $tmp_file_path;

    /** @var string */
    private $file_name;

    /** @var CSQLDataSource */
    private $ds;

    /** @var array */
    private $additionnal_fields = [];

    public function __construct(?CGroups $group = null, ?CSQLDataSource $ds = null)
    {
        $this->group = $group ?: CGroups::loadCurrent();
        $this->ds    = $ds ?: CSQLDataSource::get('std');
    }

    public function export(): void
    {
        $this->group->needsRead();

        $mediusers = $this->loadUsersToExport();
        $this->massLoadData($mediusers);

        $this->initWriter();

        /** @var CMediusers $_user */
        foreach ($mediusers as $_mediuser) {
            // Skip profile or undefined types
            if ($_mediuser->_ref_user->template === '1' || $_mediuser->_user_type === '0') {
                continue;
            }

            $line = $this->buildLine($_mediuser);
            $this->writeLine($line);
        }

        $this->streamFile();
        $this->endExport();
    }

    private function loadUsersToExport(): array
    {
        $mediuser = new CMediusers();

        return $mediuser->loadList(
            ['functions_mediboard.group_id' => $this->ds->prepare('= ?', $this->group->_id),],
            null,
            null,
            null,
            ['functions_mediboard' => 'functions_mediboard.function_id = users_mediboard.function_id']
        );
    }

    private function initWriter(): void
    {
        $date            = CMbDT::date();
        $this->file_name = "export-utilisateurs-{$this->group->text}-{$date}";

        $this->tmp_file_path = rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp/$this->file_name";

        $fp           = fopen($this->tmp_file_path, 'w+');
        $this->writer = new CCSVFile($fp);
        $headers      = $this->getHeaders();

        if ($this->additionnal_fields) {
            $headers = array_merge($headers, array_keys($this->additionnal_fields));
        }

        $this->writer->setColumnNames($headers);
        $this->writeLine($headers);
    }

    private function getHeaders(): array
    {
        return CCSVImportMediusers::HEADERS;
    }

    private function massLoadData(array $users): void
    {
        foreach (self::MASS_LOAD_FW as $_fw) {
            CStoredObject::massLoadFwdRef($users, $_fw);
        }

        foreach (self::MASS_LOAD_BACK as $_back) {
            CStoredObject::massLoadBackRefs($users, $_back);
        }
    }

    private function buildLine(CMediusers $mediuser): array
    {
        $line = [
            'nom'                   => $mediuser->_user_last_name,
            'prenom'                => $mediuser->_user_first_name,
            'username'              => $mediuser->_user_username,
            'mdp'                   => null,
            'type'                  => $mediuser->_user_type,
            'fonction'              => $mediuser->loadRefFunction()->text,
            'profil'                => ($mediuser->loadRefProfile()) ? $mediuser->_ref_profile->user_username : null,
            'adeli'                 => $mediuser->adeli,
            'rpps'                  => $mediuser->rpps,
            'spec_cpam'             => $mediuser->spec_cpam_id,
            'discipline'            => ($mediuser->loadRefDiscipline()) ? $mediuser->_ref_discipline->text : null,
            'activite'              => $mediuser->activite,
            'identifiant'           => $this->addIdx($mediuser),
            'acces_local'           => ($mediuser->remote) ? '1' : '0',
            'actif'                 => ($mediuser->actif) ? '1' : '0',
            'ufm'                   => $this->addUfms($mediuser),
            'main_user'             => $this->addMainUser($mediuser),
            'secteur'               => $mediuser->secteur,
            'pratique_tarifaire'    => $mediuser->pratique_tarifaire,
            'ccam_context'          => $mediuser->ccam_context,
            'num_astreinte'         => $mediuser->_user_astreinte,
            'num_astreinte_autre'   => $mediuser->_user_astreinte_autre,
            'ufsecondaire'          => $this->addSecondaryUfs($mediuser),
            'code_asip'             => ($mediuser->loadRefOtherSpec()) ? $mediuser->_ref_other_spec->code : null,
            'astreinte'             => $mediuser->astreinte,
            'commentaires'          => $mediuser->commentaires,
            'cps'                   => $mediuser->cps,
            'mail_apicrypt'         => $mediuser->mail_apicrypt,
            'mssante_address'       => $mediuser->mssante_address,
            'sexe'                  => $mediuser->_user_sexe,
            'force_change_pw'       => $mediuser->_force_change_password,
            'initials'              => $mediuser->initials,
            'user_mail'             => $mediuser->_user_email,
            'user_phone'            => $mediuser->_user_phone,
            'internal_phone'        => $mediuser->_internal_phone,
            'ldap_id'               => null,
            'color'                 => $mediuser->_color,
            'deb_activite'          => $mediuser->deb_activite,
            'fin_activite'          => $mediuser->fin_activite,
            'use_bris_de_glace'     => $mediuser->use_bris_de_glace,
            'cab'                   => $mediuser->cab,
            'conv'                  => $mediuser->conv,
            'zisd'                  => $mediuser->zisd,
            'ik'                    => $mediuser->ik,
            'titres'                => $mediuser->titres,
            'compte'                => $mediuser->compte,
            'banque_name'           => ($mediuser->loadRefBanque(
            )) ? $mediuser->_ref_banque->nom : $mediuser->banque_id,
            'mode_tp_acs'           => $mediuser->mode_tp_acs,
            'allow_change_password' => $mediuser->_allow_change_password,
        ];

        if (isset($this->additionnal_fields['ldap'])) {
            $user = $mediuser->loadRefUser();

            $line['ldap'] = $user->isLDAPLinked() ? '1' : '0';
        }

        if (isset($this->additionnal_fields['last_auth'])) {
            $user = $mediuser->loadRefUser();

            $last_auth         = $user->loadRefLastAuth();
            $line['last_auth'] = ($last_auth && $last_auth->_id) ? $last_auth->datetime_login : null;
        }

        return $line;
    }

    private function addIdx(CMediusers $mediuser): ?string
    {
        if ($_idexs = $mediuser->loadBackRefs('identifiants')) {
            $all_idx = [];
            /** @var CIdSante400 $_idx */
            foreach ($_idexs as $_idx) {
                $all_idx[] = $_idx->id400 . ($_idx->tag ? '|' . $_idx->tag : '');
            }

            return implode(',', $all_idx);
        }

        return null;
    }

    private function addUfms(CMediusers $mediuser): ?string
    {
        $mediuser->loadRefsUfsMedicales();
        if ($mediuser->_ref_ufs_medicales) {
            $ufs = CMbArray::pluck($mediuser->_ref_ufs_medicales, 'code');

            return implode('|', $ufs);
        }


        return null;
    }

    private function addMainUser(CMediusers $mediuser): ?string
    {
        $main_user = $mediuser->loadRefMainUser();
        if ($main_user && $main_user->_user_username) {
            return $main_user->_user_username;
        }

        return null;
    }

    private function addSecondaryUfs(CMediusers $mediuser): ?string
    {
        $mediuser->loadRefUfMedicaleSecondaire();

        if ($mediuser->_ref_uf_medicale_secondaire) {
            $ufs = CMbArray::pluck($mediuser->_ref_uf_medicale_secondaire, 'code');

            return implode('|', $ufs);
        }

        return null;
    }

    private function writeLine(array $line)
    {
        $this->writer->writeLine($line);
    }

    private function streamFile()
    {
        $this->writer->stream($this->file_name, true);
    }

    private function endExport()
    {
        $this->writer->close();
        unlink($this->tmp_file_path);

        CApp::rip();
    }

    public function addField(string $field_name, $value): void
    {
        $this->additionnal_fields[$field_name] = $value;
    }
}
