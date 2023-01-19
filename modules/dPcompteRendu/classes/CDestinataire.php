<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\MedecinFieldService;

/**
 * Permet une forme de publi-postage pour les documents produits dans Mediboard
 * Cette classe n'est pas un MbObject et les objets ne sont pas enregistrés en base
 */
class CDestinataire implements IShortNameAutoloadable
{
    public const ADDRESS_TYPE_MAIL = 'mail';
    public const ADDRESS_TYPE_APICRYPT = 'apicrypt';
    public const ADDRESS_TYPE_MSSANTE = 'mssante';

    public $nom;
    public $adresse;
    public $cpville;
    public $email;
    public $confraternite;      // used for medecin
    public $tag;
    public $civilite_nom;
    public $object_guid;
    public $medecin_exercice_place_id;

    /** @var string Current user starting formula */
    public $starting_formula;

    /** @var string Current user closing formula */
    public $closing_formula;

    public $tutoiement;

    static $destByClass = [];
    static $_patient    = null;

    /** @var CMedecin */
    public $_ref_medecin;

    /**
     * Constructeur standard
     *
     * @param ?string $tag Tag par défaut, optionnel
     */
    public function __construct(string $tag = null)
    {
        $this->tag = $tag;
    }

    public static function getFromPatient(CPatient $patient, string $tag = 'patient'): self
    {
        $dest               = new self($tag);
        $dest->nom          = "{$patient->nom} {$patient->prenom}";
        $dest->adresse      = $patient->adresse;
        $dest->cpville      = "{$patient->cp} {$patient->ville}";
        $dest->email        = $patient->email;
        $dest->civilite_nom = ucfirst($patient->_civilite_long) . " {$dest->nom}";
        $dest->object_guid  = $patient->_guid;

        return $dest;
    }

    public static function getFromPatientAssure(CPatient $patient, string $tag = 'assure'): self
    {
        $dest               = new self($tag);
        $dest->nom          = "{$patient->assure_nom} {$patient->assure_prenom}";
        $dest->adresse      = $patient->assure_adresse;
        $dest->cpville      = "{$patient->assure_cp} {$patient->assure_ville}";
        $dest->civilite_nom = $patient->_assure_civilite_long . " {$dest->nom}";
        $dest->object_guid  = $patient->_guid;
        $dest->email        = '';

        return $dest;
    }

    public static function getFromCorrespondantPatient(CCorrespondantPatient $correspondant, string $tag = null): self
    {
        $dest               = new self($tag);
        $dest->tag          = $correspondant->relation;
        $dest->nom          = $correspondant->prenom
            ? "{$correspondant->nom} {$correspondant->prenom}" : $correspondant->nom;
        $dest->adresse      = $correspondant->adresse;
        $dest->cpville      = "$correspondant->cp $correspondant->ville";
        $dest->civilite_nom = "$correspondant->nom";
        $dest->email        = $correspondant->email;
        $dest->object_guid  = $correspondant->_guid;

        return $dest;
    }

    public static function getFromCMedecin(
        CMedecin $medecin,
        string $tag = null,
        string $address_type = self::ADDRESS_TYPE_MAIL,
        int $user_id = null
    ): self {
        $medecin->getExercicePlaces();
        $medecin->loadSalutations($user_id);

        if (is_null($medecin->_medecin_exercice_place)) {
            $medecin->_medecin_exercice_place = new CMedecinExercicePlace();
        }

        if (
            !$medecin->_medecin_exercice_place->_id
            && !$medecin->adresse
            && count($medecin->_ref_medecin_exercice_places)
        ) {
            $medecin->_medecin_exercice_place = reset($medecin->_ref_medecin_exercice_places);
        }

        $medecin_service = new MedecinFieldService(
            $medecin,
            $medecin->_medecin_exercice_place
        );

        $dest                            = new self($tag);
        $dest->confraternite             = $medecin->_confraternite;
        $dest->nom                       = $medecin->_view;
        $dest->adresse                   = $medecin_service->getAdresse();
        $dest->cpville                   = "{$medecin_service->getCP()} {$medecin_service->getVille()}";
        $dest->civilite_nom              = $medecin->_longview;
        $dest->object_guid               = $medecin->_guid;
        $dest->medecin_exercice_place_id = $medecin->_medecin_exercice_place->_id;
        $dest->_ref_medecin              = $medecin;
        $dest->starting_formula          = $medecin->_starting_formula;
        $dest->closing_formula           = $medecin->_closing_formula;
        $dest->tutoiement                = $medecin->_tutoiement;

        switch ($address_type) {
            case self::ADDRESS_TYPE_APICRYPT:
                $dest->email = $medecin->email_apicrypt;
                break;
            case self::ADDRESS_TYPE_MSSANTE:
                $dest->email = $medecin_service->getMssanteAddress();
                break;
            case self::ADDRESS_TYPE_MAIL:
            default:
                $dest->email = $medecin_service->getEmail();
        }

        return $dest;
    }

    public static function getFromMediuser(
        CMediusers $user,
        string $tag = 'praticien',
        string $address_type = self::ADDRESS_TYPE_MAIL
    ): self {
        $dest              = new self($tag);
        $dest->nom         = "Dr {$user->_user_last_name} {$user->_user_first_name}";
        $dest->object_guid = $user->_guid;

        $user->loadRefFunction();
        $dest->adresse = $user->_ref_function->adresse;
        $dest->cpville = "{$user->_ref_function->cp} {$user->_ref_function->ville}";

        switch ($address_type) {
            case 'apicrypt':
                $dest->email = $user->mail_apicrypt;
                break;
            case 'mssante':
                $dest->email = $user->mssante_address;
                break;
            case 'mail':
            default:
                $dest->email = $user->_user_email;
        }

        return $dest;
    }
}
