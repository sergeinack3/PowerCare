<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit;

use DateTime;
use Ox\Import\Framework\Entity\Affectation;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\Operation;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Entity\User;
use Ox\Import\GenericImport\Tests\Fixtures\GenericImportFixtures;

/**
 * Description
 */
trait GeneratorEntityTrait
{
    public function generateExternalUser(): EntityInterface
    {
        return User::fromState(
            [
                'external_id' => 33,
                'username'    => 'user_user_name',
                'first_name'  => 'user_first_name',
                'last_name'   => 'user_last_name',
                'gender'      => 'm',
                'birthday'    => new DateTime('2020-12-12 20:20:20'),
                'email'       => 'user_email@email.com',
                'phone'       => '0102030405',
                'mobile'      => '0504030201',
                'address'     => '1 rue du user',
                'zip'         => '17000',
                'city'        => 'user_city',
                'country'     => 'user_country',
            ]
        );
    }

    public function generateExternalPatient(): EntityInterface
    {
        return Patient::fromState(
            [
                'external_id'     => 33,
                'nom'             => "patientNom",
                'prenom'          => 'patientPrenom',
                'naissance'       => new DateTime('2020-12-12 20:20:20'),
                'nom_jeune_fille' => 'patientNomJeuneFille',
                'profession'      => 'patientProfession',
                'email'           => 'patientEmail@test.com',
                'tel'             => '0102030405',
                'tel2'            => '0102030405',
                'tel_autre'       => '0102030405',
                'adresse'         => '1 rue du patient',
                'cp'              => '17000',
                'ville'           => 'patientVille',
                'pays'            => 'patientPays',
                'sexe'            => 'm',
                'civilite'        => 'm',
                'ald'             => '1',
            ]
        );
    }


    public function generateExternalMedecin(): EntityInterface
    {
        $medecin = Medecin::fromState(
            [
                'external_id' => 33,
                'nom'         => 'medecin_nom',
                'prenom'      => 'medecin_prenom',
                'sexe'        => 'm',
                'titre'       => 'dr',
                'email'       => 'medecin@email.com',
                'disciplines' => 'generaliste',
                'tel'         => '0102030405',
                'tel_autre'   => '0102030405',
                'adresse'     => '1 rue du medecin',
                'cp'          => '17000',
                'ville'       => 'medecin_ville',
            ]
        );

        return $medecin;
    }

    public function generateExternalPlageConsult(bool $withMedecin = false)
    {
        if ($withMedecin) {
            return PlageConsult::fromState(
                [
                    'external_id' => 33,
                    'date'        => new DateTime('2020-12-12 15:15:15'),
                    'freq'        => new DateTime('00:15:00'),
                    'debut'       => new DateTime('2020-12-12 08:00:00'),
                    'fin'         => new DateTime('2020-12-12 18:00:00'),
                    'libelle'     => 'consultation',
                    'chir_id'     => 33,
                ]
            );
        }

        return PlageConsult::fromState(
            [
                'external_id' => 33,
                'date'        => new DateTime('2020-12-12 15:15:15'),
                'freq'        => new DateTime('2020-12-12 00:15:00'),
                'debut'       => new DateTime('2020-12-12 08:00:00'),
                'fin'         => new DateTime('2020-12-12 18:00:00'),
                'libelle'     => 'consultation',
            ]
        );
    }

    public function generateExternalConsultation(): EntityInterface
    {
        return Consultation::fromState(
            [
                'external_id'      => 33,
                'heure'            => new DateTime('2020/12/12 20:20:20'),
                'duree'            => 2,
                'motif'            => 'motif_consultation',
                'rques'            => 'rques_consultation',
                'examen'           => 'examen_consultation',
                'traitement'       => 'traitement_consultation',
                'histoire_maladie' => 'histoire_maladie',
                'conclusion'       => 'conclusion_consultation',
                'resultats'        => 'resultats',
                'plageconsult_id'  => 22,
                'patient_id'       => 22,
            ]
        );
    }

    public function generateExternalSejour(): EntityInterface
    {
        return Sejour::fromState(
            [
                'external_id'   => 33,
                'type'          => 'comp',
                'entree_prevue' => new DateTime('2020/12/12 20:20:20'),
                'entree_reelle' => new DateTime('2020/12/12 20:20:20'),
                'sortie_prevue' => new DateTime('2020/12/12 20:30:30'),
                'sortie_reelle' => new DateTime('2020/12/12 20:30:30'),
                'libelle'       => 'libelle',
                'patient_id'    => 22,
                'praticien_id'  => 22,
                'group_id'      => 22,
            ]
        );
    }

    public function generateExternalFileWithRefSejour(): EntityInterface
    {
        return File::fromState(
            [
                'external_id'  => 33,
                'file_date'    => new DateTime('2020/12/12 20:20:20'),
                'file_name'    => 'file_name',
                'file_type'    => 'file_type',
                'file_content' => 'file_content',
                'author_id'    => 22,
                'sejour_id'    => 22,
            ]
        );
    }

    public function generateExternalFileWithRefConsultation(): EntityInterface
    {
        return File::fromState(
            [
                'external_id'     => 33,
                'file_date'       => new DateTime('2020/12/12 20:20:20'),
                'file_name'       => 'file_name',
                'file_type'       => 'file_type',
                'file_content'    => 'file_content',
                'author_id'       => 22,
                'consultation_id' => 22,
            ]
        );
    }

    public function generateExternalFileWithRefPatient(): EntityInterface
    {
        return File::fromState(
            [
                'external_id'  => 33,
                'file_date'    => new DateTime('2020/12/12 20:20:20'),
                'file_name'    => 'file_name',
                'file_type'    => 'file_type',
                'file_content' => 'file_content',
                'author_id'    => 22,
                'patient_id'   => 22,
            ]
        );
    }

    public function generateExternalFileWithoutRef(): EntityInterface
    {
        return File::fromState(
            [
                'external_id'  => 33,
                'file_date'    => new DateTime('2020/12/12 20:20:20'),
                'file_name'    => 'file_name',
                'file_type'    => 'file_type',
                'file_content' => 'file_content',
                'author_id'    => 22,
            ]
        );
    }

    public function generateExternalAffectation(
        string $nom_service,
        string $nom_lit,
        string $code_uf,
        string $mode_entree,
        string $mode_sortie
    ): EntityInterface {
        return Affectation::fromState(
            [
                'external_id' => 33,
                'sejour_id'   => 22,
                'nom_service' => $nom_service,
                'nom_lit'     => $nom_lit,
                'code_uf'     => $code_uf,
                'mode_entree' => $mode_entree,
                'mode_sortie' => $mode_sortie,
                'entree'      => new DateTime('2020/12/12 20:20:20'),
                'sortie'      => new DateTime('2020/12/12 20:30:30'),
            ]
        );
    }

    public function generateExternalAffectationWithRefsIncorrect(string $nom_service): EntityInterface
    {
        return Affectation::fromState(
            [
                'external_id' => 33,
                'sejour_id'   => 22,
                'nom_service' => $nom_service,
                'nom_lit'     => "totototo",
                'code_uf'     => "totototo",
                'mode_entree' => "totototo",
                'mode_sortie' => "totototo",
                'entree'      => new DateTime('2020/12/12 20:20:20'),
                'sortie'      => new DateTime('2020/12/12 20:30:30'),
            ]
        );
    }

    public function generateExternalAffectationWithoutRefs(string $nom_service): EntityInterface
    {
        return Affectation::fromState(
            [
                'external_id' => 33,
                'sejour_id'   => 22,
                'nom_service' => $nom_service,
                'entree'      => new DateTime('2020/12/12 20:20:20'),
                'sortie'      => new DateTime('2020/12/12 20:30:30'),
            ]
        );
    }

    public function generateExternalOperation(): EntityInterface
    {
        return Operation::fromState(
            [
                'external_id' => 33,
                'sejour_id'   => 22,
                'chir_id'     => 22,
                'cote'        => 'gauche',
                'date_time'   => '2020-12-12 10:10:10',
                'libelle'     => 'Lorem ipsum',
                'examen'      => 'Lorem ipsum',
            ]
        );
    }
}
