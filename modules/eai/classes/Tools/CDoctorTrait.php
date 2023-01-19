<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Tools;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * trait CDoctorTrait
 * Doctor utilities EAI
 */
trait CDoctorTrait
{
    /**
     * Get doctor ID
     *
     * @param array               $doctors Doctors
     * @param CMediusers|CMedecin $object  CMediusers or CMedecin
     * @param bool                $create
     * @param string              $aauid   Assigning authority universal id MB
     *
     */
    public function getDoctorID(
        array $doctors,
        CMbObject $object,
        int $group_id,
        bool $create = true,
        string $aauid = null
    ): ?int {
        // Load by RPPS
        if ($rpps = CMbArray::get($doctors, 'RPPS')) {
            $object = $object->loadFromRPPS($rpps, false, $group_id);
            if ($object && $object->_id) {
                return $object->_id;
            }
        }

        // Load by RI
        if ($id = CMbArray::get($doctors, 'RI')) {
            // Notre propre RI
            if ($aauid && (CMbArray::get($doctors, 'assigning_authority_universal_id') == $aauid)) {
                return $id;
            }

            // Recherche du praticien par son idex
            $idex = CIdSante400::getMatch($object->_class, $this->_ref_sender->_tag_mediuser, $id);
            if ($idex->_id) {
                return $idex->object_id;
            }
        }

        $first_name = CMbArray::get($doctors, 'first_name');
        $last_name  = CMbArray::get($doctors, 'last_name');

        // On fini par la recherche par son nom/prénom / ADELI
        if ($object instanceof CMedecin) {
            $object->prenom = $first_name;
            $object->nom    = $last_name;

            $object->loadMatchingObjectEsc();
            // Dans le cas où il n'est pas connu dans MB on le créé
            if (!$object->_id) {
                $object->store();
            }

            return $object->_id;
        } elseif ($object instanceof CMediusers) {
            $object->_user_first_name = $first_name;
            $object->_user_last_name  = $last_name;

            $ds = $object->getDS();

            $ljoin                        = [];
            $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

            $where                                 = [];
            $where["functions_mediboard.group_id"] = " = '$group_id'";

            // Load by ADELI
            if ($adeli = CMbArray::get($doctors, 'ADELI')) {
                $where[] = $ds->prepare("adeli = %", $adeli);

                // Dans le cas où le praticien recherché par son ADELI est multiple
                if ($object->countList($where, null, $ljoin) > 1) {
                    $ljoin["users"] = "users_mediboard.user_id = users.user_id";
                    $where[]        = $ds->prepare("users.user_last_name = %", $last_name);
                }

                $object->loadObject($where, null, null, $ljoin);

                if ($object->_id) {
                    return $object->_id;
                }
            }

            $ljoin                        = [];
            $ljoin["users_mediboard"]     = "users.user_id = users_mediboard.user_id";
            $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

            $where                                 = [];
            $where["functions_mediboard.group_id"] = " = '$group_id'";
            $where[]                               = $ds->prepare("users.user_first_name = %", $first_name);
            $where[]                               = $ds->prepare("users.user_last_name = %", $last_name);

            $user = new CUser();
            if ($user->loadObject($where, "users.user_id ASC", null, $ljoin)) {
                return $user->_id;
            }

            $object->_user_first_name = $first_name;
            $object->_user_last_name  = $last_name;

            if ($create) {
                return $this->createDoctor($object, $group_id);
            }

            return null;
        }

        return null;
    }

    /**
     * Create mediuser
     *
     * @param CMediusers $mediuser
     * @param int        $group_id
     *
     * @return int
     */
    public function createDoctor(CMediusers $mediuser, int $group_id): ?int
    {
        $function           = new CFunctions();
        $function->text     = CAppUI::conf("hl7 importFunctionName");
        $function->group_id = $group_id;
        $function->loadMatchingObjectEsc();
        if (!$function->_id) {
            $function->type            = "cabinet";
            $function->compta_partagee = 0;
            $function->color           = "ffffff";
            $function->store();
        }
        $mediuser->function_id    = $function->_id;
        $mediuser->_user_username = CMbFieldSpec::randomString(
            array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z')),
            20
        );
        $mediuser->_user_password = CMbSecurity::getRandomPassword();
        $mediuser->_user_type     = 13; // Medecin
        $mediuser->actif          = CAppUI::conf("hl7 doctorActif") ? 1 : 0;

        $user                  = new CUser();
        $user->user_last_name  = $mediuser->_user_last_name;
        $user->user_first_name = $mediuser->_user_first_name;
        // On recherche par le seek
        $users = $user->seek("$user->user_last_name $user->user_first_name");
        if (count($users) === 1) {
            $user = reset($users);
            $user->loadRefMediuser();
            $mediuser = $user->_ref_mediuser;
        } else {
            // Dernière recherche si le login est déjà existant
            $user                = new CUser();
            $user->user_username = $mediuser->_user_username;
            if ($user->loadMatchingObject()) {
                // On affecte un username aléatoire
                $mediuser->_user_username .= rand(1, 10);
            }

            $mediuser->store();
        }

        return $mediuser->_id;
    }
}
