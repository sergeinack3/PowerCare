<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Import\Rpps\Services\CPersonneExerciceService;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSpecCPAM;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CSourceSMTP;

class CMediusersLegacyController extends CLegacyController
{
    /**
     * View to edit a mediuser
     *
     * @return void
     * @throws Exception
     */
    public function viewEditMediuser(): void
    {
        $this->checkPermRead();

        $user_id                = CView::get("user_id", "ref class|CMediusers", true);
        $medecin_id             = CView::get("medecin_id", "ref class|CMedecin");
        $personne_exercice_id   = CView::get("personne_exercice_id", "ref class|CPersonneExercice");
        $no_association         = CView::get("no_association", "bool default|0");
        $ldap_user_actif        = CView::get("ldap_user_actif", "bool default|1");
        $ldap_user_deb_activite = CView::get("ldap_user_deb_activite", "date");
        $ldap_user_fin_activite = CView::get("ldap_user_fin_activite", "date");
        CView::checkin();

        // Récupération des fonctions
        $group = CGroups::loadCurrent();
        if ($group->_id) {
            $functions = $group->loadFunctions();

            // Liste des Etablissements
            $groups = CMediusers::loadEtablissements(PERM_READ);
        } else {
            // Cas du admin qui n'a pas de mediuser, et donc pas de group_id
            $function = new CFunctions();

            $where     = [
                "actif" => "='1'",
            ];
            $functions = $function->loadListWithPerms(PERM_READ, $where);

            // Liste des Etablissements
            $group  = new CGroups();
            $groups = $group->loadList();
        }

        $functions = [];
        foreach ($groups as $_group) {
            $functions[$_group->_id] = $_group->loadFunctions();
        }

        // Récupération du user à ajouter/editer
        $object = new CMediusers();

        if ($no_association) {
            $object->user_id = $user_id;
            $object->updateFormFields();
            $object->_user_id     = $user_id;
            $object->_id          = null;
            $object->actif        = $ldap_user_actif;
            $object->deb_activite = $ldap_user_deb_activite;
            $object->fin_activite = $ldap_user_fin_activite;
        } else {
            $object->load($user_id);
            $object->loadRefFunction();
            $object->loadRefProfile();
        }

        if (!$object->_id && $medecin_id) {
            $medecin = new CMedecin();
            $medecin->load($medecin_id);
            if ($medecin->_id) {
                $split_name               = explode(' ', $medecin->nom);
                $object->_user_username   = substr($medecin->prenom, 0, 1) . $split_name[0];
                $object->_user_last_name  = $medecin->nom;
                $object->_user_first_name = $medecin->prenom;
                $object->_user_sexe       = $medecin->sexe;
                $object->function_id      = $medecin->function_id;
                $object->_user_email      = $medecin->email;
                $object->_user_phone      = $medecin->tel;
                $object->rpps             = $medecin->rpps;
                $object->mail_apicrypt    = $medecin->email_apicrypt;
                $object->mssante_address  = $medecin->mssante_address;
            }
        }

        // CPersonneExercice
        if ($personne_exercice_id) {
            $object->fillMediuserFromPersonneExercice($personne_exercice_id);

            if ($user_id) {
                $object->_is_rpps_link_personne_exercice = false;
            }
        }

        $object->loadNamedFile("identite.jpg");
        $object->loadNamedFile("signature.jpg");

        $medecins_back = $object->loadBackRefs('medecin', 'nom, prenom, cp', 1);

        // Savoir s'il est relié au LDAP
        if (isset($object->_ref_user)) {
            $object->_ref_user->isLDAPLinked();
        }

        $object->loadRefMainUser();
        $object->loadRefsSecondaryUsers();
        $object->loadRefBanque();

        // Récupération des disciplines
        $discipline  = new CDiscipline();
        $disciplines = $discipline->loadList();

        // Récupération des profils
        $profile           = new CUser();
        $profile->template = 1;
        /** @var CUser[] $profiles */
        $profiles = $profile->loadMatchingList();

        // Creation du tableau de profil en fonction du type
        $tabProfil = [];
        foreach ($profiles as $profil) {
            $tabProfil[$profil->user_type][] = $profil->_id;
        }

        $tag = false;
        if ($object->_id) {
            $tag = CIdSante400::getMatch($object->_class, CMediusers::getTagSoftware(), null, $object->_id)->id400;

            $object->checkRPPSMediuserLinked();
            $object->loadRefBanque();
        }

        $password_spec = $object->_specs['_user_password'];
        $description   = $password_spec->getLitteralDescription();
        $description   = str_replace("'_user_username'", $object->_user_username, $description);
        $description   = explode('. ', $description);
        array_shift($description);
        $description = array_filter($description);

        $exchange_source         = new CSourceSMTP();
        $exchange_source->name   = 'system-message';
        $exchange_source->active = 1;
        $exchange_source->loadMatchingObject();
        $exchange_source = $exchange_source->_id;

        CMbArray::naturalSort(CUser::$types);

        $password_spec_builder  = $object->getPasswordSpecBuilder();
        $weak_prop              = $password_spec_builder->getWeakSpec()->getProp();
        $strong_prop            = $password_spec_builder->getStrongSpec()->getProp();
        $ldap_prop              = $password_spec_builder->getLDAPSpec()->getProp();
        $admin_prop             = $password_spec_builder->getAdminSpec()->getProp();
        $password_configuration = $password_spec_builder->getConfiguration();

        $activation_token = null;

        if ($object->_id && !$object->_ref_user->isLDAPLinked()) {
            $activation_token = AntiCsrf::prepare()
                ->addParam('user_id', $object->_id)
                ->addParam('type', ['token', 'email'])
                ->addParam('email')
                ->getToken();
        }

        $this->renderSmarty("inc_edit_mediuser", [
            "activation_token"       => $activation_token,
            "weak_prop"              => $weak_prop,
            "strong_prop"            => $strong_prop,
            "ldap_prop"              => $ldap_prop,
            "admin_prop"             => $admin_prop,
            "password_configuration" => $password_configuration,
            "tabProfil"              => $tabProfil,
            "utypes"                 => CUser::$types,
            "ps_types"               => CUser::$ps_types,
            "object"                 => $object,
            "profiles"               => $profiles,
            "disciplines"            => $disciplines,
            "spec_cpam"              => CSpecCPAM::getList(),
            "tag_mediuser"           => CMediusers::getTagMediusers($group->_id),
            "is_admin"               =>
                (CAppUI::$user->isAdmin()
                    || CUser::get(
                        CAppUI::$instance->user_id
                    )->isSuperAdmin()),
            "is_admin_module"        => CModule::getCanDo("admin")->admin,
            "is_robot"               => $object->isRobot(),
            "tag"                    => $tag,
            "groups"                 => $groups,
            "functions"              => $functions,
            "description"            => $description,
            "exchange_source"        => $exchange_source,
            "medecin_id"             => $medecin_id,
            "medecins_back"          => $medecins_back,
            // AppFine-specific template vars
            "current_user"           => CMediusers::get(),
        ]);
    }

    /**
     * View the filter to search doctors in the directory
     *
     * @return void
     * @throws Exception
     */
    public function viewFilterSearchDoctors(): void
    {
        $this->checkPermRead();

        $user_id = CView::get("user_id", "ref class|CMediusers");
        $rpps    = CView::get("rpps", "str");
        CView::checkin();

        $mediuser = CMediusers::findOrNew($user_id);
        $mediuser->loadRefUser();


        $mediuser->rpps = $rpps;

        if ($user_id && !$mediuser->_id) {
            CAppUI::stepAjax('CMediusers.none', UI_MSG_ERROR);
        }

        $this->renderSmarty("vw_search_medecin", [
            "mediuser" => $mediuser,
        ]);
    }

    /**
     * Unlink the mediuser from health directory
     *
     * @return void
     * @throws Exception
     */
    public function unlinkMediuserFromHealthDirectory(): void
    {
        $this->checkPermRead();

        $user_id = CView::post("user_id", "ref class|CMediusers");
        CView::checkin();

        $mediuser = CMediusers::findOrNew($user_id);

        $idex_personne_exercice = CIdSante400::getMatch(
            $mediuser->_class,
            CPersonneExercice::TAG_RPPS_IDENTIFIANT_STRUCTURE,
            null,
            $mediuser->_id
        );

        if ($idex_personne_exercice->_id) {
            $idex_personne_exercice->delete();
            CAppUI::stepAjax(
                CAppUI::tr("CMediusers-msg-This user has been unlinked from the health directory"),
                UI_MSG_OK
            );
        }

        $this->rip();
    }

    /**
     * View to show the doctor's list in the directory
     *
     * @return void
     * @throws Exception
     */
    public function viewDoctorsDirectory(): void
    {
        $this->checkPermRead();

        $rpps        = CView::get('rpps', 'str');
        $nom         = CView::get('nom', 'str');
        $prenom      = CView::get('prenom', 'str');
        $cp          = CView::get('cp', 'str');
        $ville       = CView::get('ville', 'str');
        $disciplines = CView::get('disciplines', 'str');
        $user_id     = CView::get('user_id', 'ref class|CMediusers');
        $start       = CView::get('start', 'num default|0');
        $step        = CView::get('step', 'num default|10');
        $type        = CView::get('type', 'enum list|close|exact');
        CView::checkin();

        $function_id = CAppUI::isCabinet() ? CMediusers::get($user_id)->function_id : null;
        $group_id    = CAppUI::isGroup() ? CMediusers::get($user_id)->loadRefFunction()->group_id : null;
        $module_rpps = CModule::getActive("rpps");

        if (!$function_id && !$module_rpps) {
            if (!$function_id && !$group_id) {
                CAppUI::stepAjax('CMedecin-search-function_id.none', UI_MSG_ERROR);
            }
        }

        $medecin = new CMedecin();
        $ds      = $medecin->getDS();

        $where      = [];
        $where_rpps = [];

        $where[] = $ds->prepare('`user_id` IS NULL OR `user_id` = ?', $user_id);

        if ($nom) {
            $where_rpps['nom'] = $where['nom'] = $ds->prepareLike("%$nom%");
        }

        if ($prenom) {
            $where_rpps['prenom'] = $where['prenom'] = $ds->prepareLike("%$prenom%");
        }

        if ($cp) {
            $where_rpps['cp'] = $where['cp'] = $ds->prepareLike("$cp%");
        }

        if ($ville) {
            $where_rpps['libelle_commune'] = $where['ville'] = $ds->prepareLike("%$ville%");
        }

        if ($disciplines) {
            $where_rpps['libelle_profession'] = $where['disciplines'] = $ds->prepareLike("%$disciplines%");
        }

        if ($rpps) {
            $where_rpps['identifiant'] = $where['rpps'] = $ds->prepareLike("$rpps%");
        }

        if ($function_id) {
            $where['function_id'] = $ds->prepare('= ?', $function_id);
        } else {
            $where['group_id'] = $ds->prepare('= ?', $group_id);
        }
        $count    = (!$type || $type == 'exact') ? $medecin->countList($where) : 0;
        $medecins = (!$type || $type == 'exact') ? $medecin->loadList($where, 'nom, prenom, cp', "$start,$step") : [];

        $where['function_id'] = 'IS NULL';

        $close_count    = (!$type || $type == 'close') ? $medecin->countList($where) : 0;
        $close_medecins = (!$type || $type == 'close') ? $medecin->loadList(
            $where,
            'nom, prenom, cp',
            "$start,$step"
        ) : [];

        // RPPS directory
        $praticiens                = [];
        $practicioner_mediuser     = [];
        $counter_personne_exercice = 0;

        if ($module_rpps) {
            $personne_exercice_service        = new CPersonneExerciceService();
            $personne_exercice_service->start = $start;
            $personne_exercice_service->where = $where_rpps;
            [$counter_personne_exercice, $praticiens] = $personne_exercice_service->searchPraticionerFromRPPS();
            $practicioner_mediuser = $personne_exercice_service->comparePraticionerMediuser(
                CMediusers::get($user_id),
                $praticiens
            );
        }

        $this->renderSmarty("inc_search_medecin", [
            "medecins"                => $medecins,
            "medecins_close"          => $close_medecins,
            "praticiens"              => $praticiens,
            "total_personne_exercice" => $counter_personne_exercice,
            "practicioner_mediuser"   => $practicioner_mediuser,
            "user_id"                 => $user_id,
            "page"                    => $start,
            "total"                   => [
                'exact' => $count,
                'close' => $close_count,
                'rpps'  => $counter_personne_exercice,
            ],
            "step"                    => $step,
            "type"                    => $type,
            "function_id"             => $function_id,
            "group_id"                => $group_id,
        ]);
    }

    /**
     * @throws Exception
     */
    public function ajax_generate_token(): void
    {
        $this->checkPerm();

        $export       = CView::post("export", "str");
        $weeks_before = CView::post("weeks_before", "num");
        $weeks_after  = CView::post("weeks_after", "num");
        $group        = CView::post("group", "enum list|0|1 default|0");
        $details      = CView::post("details", "bool");
        $anonymize    = CView::post("anonymize", "str");

        $anonymize = ($anonymize) ? '1' : '0';

        CView::checkin();

        $user_id = CMediusers::get()->_id;

        $params = [
            "m=board",
            "raw=exportIcal",
            "prat_id={$user_id}",
            "weeks_before={$weeks_before}",
            "weeks_after={$weeks_after}",
            "group={$group}",
            "details={$details}",
            "anonymize={$anonymize}",
        ];

        if ($export) {
            foreach ($export as $_export) {
                $params[] = "export[]={$_export}";
            }
        }

        $ds = CSQLDataSource::get('std');

        $token = new CViewAccessToken();

        $where = [
            'user_id'      => $ds->prepare('= ?', $user_id),
            'params'       => $ds->prepare('= ?', implode("\n", $params)),
            'restricted'   => "= '1'",
            'datetime_end' => 'IS NULL',
            'max_usages'   => 'IS NULL',
        ];

        if (!$token->loadObject($where, 'datetime_start DESC')) {
            $token->user_id    = $user_id;
            $token->params     = implode("\n", $params);
            $token->restricted = '1';
        }

        if ($msg = $token->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
        }

        $this->renderSmarty(
            'vw_generated_token',
            [
                "url" => $token->getUrl(),
            ]
        );
    }
}
