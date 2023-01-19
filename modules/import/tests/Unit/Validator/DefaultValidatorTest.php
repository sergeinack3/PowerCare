<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit\Validator;

use DateTime;
use Ox\Core\CMbDT;
use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\Entity\Constante;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Tests\Unit\GeneratorEntityTrait;
use Ox\Import\Framework\Validator\DefaultValidator;
use Ox\Tests\OxUnitTestCase;

class DefaultValidatorTest extends OxUnitTestCase
{
    use GeneratorEntityTrait;

    private const MAPPING = [
        Patient::class => 'validatePatient',
    ];
    /**
     * @var DefaultValidator
     */
    private $default_validator;

    private $external_user;

    public function setUp(): void
    {
        $this->default_validator = new DefaultValidator();

        $this->external_user = $this->createMock(User::class);
    }


    /**
     * @param string $external_class
     * @param array  $state
     *
     * @dataProvider getValidObjectProvider
     *
     * @config [CConfiguration] dPpatients CPatient addr_patient_mandatory 0
     * @config [CConfiguration] dPpatients CPatient cp_patient_mandatory 0
     * @config [CConfiguration] dPpatients CPatient tel_patient_mandatory 0
     *
     */
    public function testEntityValidationIsOK(string $external_class, array $state): void
    {
        /** @var EntityInterface $external_entity */
        $external_entity = $external_class::fromState($state);
        $violation       = $external_entity->validate($this->default_validator);

        $this->assertNull($violation);
    }

    /**
     * @dataProvider getFailObjectProvider
     *
     * Unset Config
     *
     * @config [CConfiguration] dPpatients CPatient addr_patient_mandatory 0
     * @config [CConfiguration] dPpatients CPatient cp_patient_mandatory 0
     * @config [CConfiguration] dPpatients CPatient tel_patient_mandatory 0
     */
    public function testEntityValidationIsKO(string $external_class, array $state): void
    {
        /** @var EntityInterface $external_class */
        $external_entity = $external_class::fromState($state);
        $violation       = $external_entity->validate($this->default_validator);

        $this->assertInstanceOf(SpecificationViolation::class, $violation);
    }

    public function getValidObjectProvider(): array
    {
        $provider = [];
        $provider = array_merge($provider, ["user valide" => $this->generateUser()]);
        $provider = array_merge($provider, ["Medecin valide" => $this->generateMedecin()]);
        $provider = array_merge($provider, ["PlageConsult valide" => $this->generatePlageConsult()]);
        $provider = array_merge($provider, ["Consultation valide" => $this->generateConsultation()]);
        $provider = array_merge($provider, ["Sejour valide" => $this->generateSejour()]);
        $provider = array_merge($provider, ["File valide" => $this->generateFile()]);
        $provider = array_merge(
            $provider,
            ["Constant valide" => $this->generateConstante(['taille' => 120, 'poids' => 75])]
        );

        return $provider;
    }

    public function getFailObjectProvider(): array
    {
        $provider = [];
        $provider = array_merge($provider, $this->getNotValidUser());
        $provider = array_merge($provider, $this->getNotValidPatient());
        $provider = array_merge($provider, $this->getNotValidMedecin());
        $provider = array_merge($provider, $this->getNotValidPlageConsult());
        $provider = array_merge($provider, $this->getNotValidConsultation());
        $provider = array_merge($provider, $this->getNotValidSejour());
        $provider = array_merge($provider, $this->getNotValidFile());
        $provider = array_merge($provider, $this->getNotValidConstante());

        return $provider;
    }

    public function getFailObjectConfProvider(): array
    {
        $provider = [];
        $provider = array_merge($provider, $this->getNotValidPatientWithConf());
        $provider = array_merge($provider, $this->getNotValidMedecinWithConf());

        return $provider;
    }

    private function getNotValidUser(): array
    {
        return [
            "user with extId null"          => $this->generateUser(['external_id' => null]),
            "user with username null"       => $this->generateUser(["username" => null]),
            "user with long username"       => $this->generateUser(["username" => $this->completeString(81)]),
            "user with long first_name"     => $this->generateUser(["first_name" => $this->completeString(51)]),
            "user without last_name"        => $this->generateUser(["last_name" => null]),
            "user with long last_name"      => $this->generateUser(["last_name" => $this->completeString(51)]),
            "user with gender invalide"     => $this->generateUser(["gender" => 'r']),
            "user with gender double"       => $this->generateUser(["gender" => 'ff']),
            "user with birthday no DT"      => $this->generateUser(["birthday" => CMbDT::date()]),
            "user with email bad format"    => $this->generateUser(["email" => "@.m"]),
            "user with email bad finish"    => $this->generateUser(["email" => "toto@test.c"]),
            "user with email finish long"   => $this->generateUser(["email" => "toto@test.commo"]),
            "user with email bad begin"     => $this->generateUser(["email" => "@test.com"]),
            "user with email bad diacr"     => $this->generateUser(["email" => "/é%ù@test.com"]),
            "user with email bad no @"      => $this->generateUser(["email" => "tototest.com"]),
            "user with email bad middle"    => $this->generateUser(["email" => "toto@.com"]),
            "user with email with space"    => $this->generateUser(["email" => "toto  @test.com"]),
            "user with email with no point" => $this->generateUser(["email" => "toto@testcom"]),
            "user with email to long"       => $this->generateUser(
                ["email" => $this->completeString(256) . "@test.com"]
            ),
            "user with phone to long"       => $this->generateUser(["phone" => "010203040506"]),
            "user with phone to short"      => $this->generateUser(["phone" => "010203040"]),
            "user with mobile to long"      => $this->generateUser(["mobile" => "010203040506"]),
            "user with mobile to short"     => $this->generateUser(["mobile" => "010203040"]),
            "user with long address"        => $this->generateUser(["address" => $this->completeString(256)]),
            "user with long city"           => $this->generateUser(["city" => $this->completeString(31)]),
            "user with long zip"            => $this->generateUser(["zip" => $this->completeString(6)]),
            "user with short zip"           => $this->generateUser(["zip" => $this->completeString(4)]),
            "user with long country"        => $this->generateUser(["country" => $this->completeString(31)]),
        ];
    }

    private function generateUser(array $attributes = []): array
    {
        $user = array_merge(
            [
                'external_id' => 11,
                'username'    => 'toto',
                'last_name'   => 'toto',
            ],
            $attributes
        );

        return ["base" => User::class, "user" => $user];
    }

    private function getNotValidPatient(): array
    {
        return [
            "patient with extId null"          => $this->generatePatient(['external_id' => null]),
            "patient without nom"              => $this->generatePatient(["nom" => null]),
            "patient with nom to long"         => $this->generatePatient(["nom" => $this->completeString(256)]),
            "patient without prenom"           => $this->generatePatient(["prenom" => null]),
            "patient with prenom to long"      => $this->generatePatient(
                ["prenom" => $this->completeString(256)]
            ),
            "patient with to old naissance"    => $this->generatePatient(
                ["naissance" => new DateTime('1849-12-31')]
            ),
            "patient with to young naissance"  => $this->generatePatient(["naissance" => new DateTime('+1 Day')]),
            "patient with no naissance"        => $this->generatePatient(["naissance" => null]),
            "patient with no \DateTime"        => $this->generatePatient(
                ["naissance" => CMbDT::date('2020/12/12')]
            ),
            "patient with profession to long " => $this->generatePatient(
                ["profession" => $this->completeString(256)]
            ),
            "patient with email bad format"    => $this->generatePatient(["email" => "@.m"]),
            "patient with email bad finish"    => $this->generatePatient(["email" => "toto@test.c"]),
            "patient with email finish long"   => $this->generatePatient(["email" => "toto@test.commo"]),
            "patient with email bad begin"     => $this->generatePatient(["email" => "@test.com"]),
            "patient with email bad diacr"     => $this->generatePatient(["email" => "/é%ù@test.com"]),
            "patient with email bad no @"      => $this->generatePatient(["email" => "tototest.com"]),
            "patient with email bad middle"    => $this->generatePatient(["email" => "toto@.com"]),
            "patient with email with space"    => $this->generatePatient(["email" => "toto  @test.com"]),
            "patient with email with no point" => $this->generatePatient(["email" => "toto@testcom"]),
            "patient with email to long"       => $this->generatePatient(
                ["email" => $this->completeString(256) . "@test.com"]
            ),
            "patient with tel letter"          => $this->generatePatient(["tel" => $this->completeString(10)]),
            "patient with tel to long"         => $this->generatePatient(["tel" => "010203040506"]),
            "patient with tel to short"        => $this->generatePatient(["tel" => "010203040"]),
            "patient with tel2 letter"         => $this->generatePatient(["tel2" => $this->completeString(10)]),
            "patient with tel2 to long"        => $this->generatePatient(["tel2" => "010203040506"]),
            "patient with tel2 to short"       => $this->generatePatient(["tel2" => "010203040"]),
            "patient with tel_autre letter"    => $this->generatePatient(
                ["tel_autre" => $this->completeString(10)]
            ),
            "patient with tel_autre to long"   => $this->generatePatient(["tel_autre" => "010203040506"]),
            "patient with tel_autre to short"  => $this->generatePatient(["tel_autre" => "010203040"]),
            "patient with matricule letter"    => $this->generatePatient(
                ["matricule" => $this->completeString(15)]
            ),
            "patient with matricule to long"   => $this->generatePatient(
                ["matricule" => $this->completeNumber(16)]
            ),
            "patient with matricule to short"  => $this->generatePatient(
                ["matricule" => $this->completeNumber(10)]
            ),
            "patient with civilite bad"        => $this->generatePatient(["civilite" => "a"]),
        ];
    }

    public function getNotValidPatientWithConf(): array
    {
        return [
            "patient with adresse null"            => $this->generatePatientConf(["adresse" => null]),
            "patient with ville null"              => $this->generatePatientConf(["ville" => null]),
            "patient with ville to long"           => $this->generatePatientConf(
                ["ville" => $this->completeString(256)]
            ),
            "patient with pays null"               => $this->generatePatientConf(["pays" => null]),
            "patient with pays to long"            => $this->generatePatientConf(
                ["pays" => $this->completeString(256)]
            ),
            "patient with cp to short"             => $this->generatePatientConf(
                ["cp" => $this->completeString(4)]
            ),
            "patient with cp to long"              => $this->generatePatientConf(
                ["cp" => $this->completeString(6)]
            ),
            "patient with nom_jeune_fille to long" => $this->generatePatientConf(
                ["nom_jeune_fille" => $this->completeString(256)]
            ),
            "patient with nom_jeune_fille null"    => $this->generatePatientConf(["nom_jeune_fille" => null]),
            "patient with sexe null"               => $this->generatePatientConf(["sexe" => null]),
            "patient with sexe bad"                => $this->generatePatientConf(["sexe" => 'a']),
            "patient with sexe m +njf conf"        => $this->generatePatientConf(["sexe" => 'm']),
        ];
    }

    private function generatePatient(array $attributes = []): array
    {
        $patient = array_merge(
            [
                'external_id' => 33,
                'nom'         => 'toto',
                'prenom'      => 'toto',
                'naissance'   => new DateTime('2000-12-12'),
                'sexe'        => "f",
            ],
            $attributes
        );

        return ["base" => Patient::class, "patient" => $patient];
    }

    public function generatePatientConf(array $attributes = []): array
    {
        $patient = array_merge(
            [
                'external_id'     => 33,
                'nom'             => 'toto',
                'prenom'          => 'toto',
                'naissance'       => new DateTime('2000-12-12'),
                'sexe'            => "f",
                'adresse'         => "1 rue du patient",
                'ville'           => "ville du patient",
                'cp'              => "17000",
                'nom_jeune_fille' => "tata",
            ],
            $attributes
        );

        return ["base" => Patient::class, "patient" => $patient];
    }

    private function getNotValidMedecin(): array
    {
        return [
            "medecin without extId"            => $this->generateMedecin(['external_id' => null]),
            "medecin with nom null"            => $this->generateMedecin(['nom' => null]),
            "medecin with nom to long"         => $this->generateMedecin(['nom' => $this->completeString(256)]),
            "medecin with prenom long"         => $this->generateMedecin(
                ['prenom' => $this->completeString(256)]
            ),
            "medecin with bad sexe"            => $this->generateMedecin(['sexe' => 'aaa']),
            "medecin with bad titre"           => $this->generateMedecin(['titre' => 'a']),
            "medecin with email bad format"    => $this->generateMedecin(["email" => "@.m"]),
            "medecin with email bad finish"    => $this->generateMedecin(["email" => "toto@test.c"]),
            "medecin with email finish long"   => $this->generateMedecin(["email" => "toto@test.commo"]),
            "medecin with email bad begin"     => $this->generateMedecin(["email" => "@test.com"]),
            "medecin with email bad diacr"     => $this->generateMedecin(["email" => "/é%ù@test.com"]),
            "medecin with email bad no @"      => $this->generateMedecin(["email" => "tototest.com"]),
            "medecin with email bad middle"    => $this->generateMedecin(["email" => "toto@.com"]),
            "medecin with email with space"    => $this->generateMedecin(["email" => "toto  @test.com"]),
            "medecin with email with no point" => $this->generateMedecin(["email" => "toto@testcom"]),
            "medecin with email to long"       => $this->generateMedecin(
                ["email" => $this->completeString(256) . "@test.com"]
            ),
            "medecin with tel letter"          => $this->generateMedecin(["tel" => "dzadazdazd"]),
            "medecin with tel to long"         => $this->generateMedecin(["tel" => "010203040506"]),
            "medecin with tel to short"        => $this->generateMedecin(["tel" => "010203040"]),
            "medecin with tel_autre letter"    => $this->generateMedecin(["tel_autre" => "dzadazdazd"]),
            "medecin with tel_autre to long"   => $this->generateMedecin(["tel_autre" => "010203040506"]),
            "medecin with tel_autre to short"  => $this->generateMedecin(["tel_autre" => "010203040"]),
            "medecin with ville to long"       => $this->generateMedecin(["ville" => $this->completeString(256)]),
            "medecin with adeli character"     => $this->generateMedecin(['adeli' => 'aaaa a a aaa']),
            "medecin with adeli to long"       => $this->generateMedecin(['adeli' => $this->completeNumber(10)]),
            "medecin with adeli to short"      => $this->generateMedecin(['adeli' => $this->completeNumber(8)]),
            "medecin with rpps character"      => $this->generateMedecin(['rpps' => 'aaaa a a aaa']),
            "medecin with rpps to long"        => $this->generateMedecin(['rpps' => $this->completeNumber(12)]),
            "medecin with rpps to short"       => $this->generateMedecin(['rpps' => $this->completeNumber(10)]),
        ];
    }

    public function getNotValidMedecinWithConf(): array
    {
        return [
            "medecin with adresse null" => $this->generateMedecinConf(['adresse' => null]),
            "medecin with cp to short"  => $this->generateMedecinConf(["cp" => $this->completeString(4)]),
            "medecin with cp to long"   => $this->generateMedecinConf(["cp" => $this->completeString(6)]),
            "medecin with tel null"     => $this->generateMedecinConf(['tel' => null]),
            "medecin with ville null"   => $this->generateMedecinConf(['ville' => null]),
        ];
    }

    private function generateMedecin(array $attributes = []): array
    {
        $medecin = array_merge(
            [
                'external_id' => 33,
                'nom'         => 'toto',
            ],
            $attributes
        );

        return ["base" => Medecin::class, "medecin" => $medecin];
    }

    public function generateMedecinConf(array $attributes = []): array
    {
        $medecin = array_merge(
            [
                'external_id' => 33,
                'nom'         => 'toto',
                'prenom'      => 'toto',
                'naissance'   => new DateTime('2000-12-12'),
                'sexe'        => "f",
                'adresse'     => "1 rue du medecin",
                'tel'         => "0102030405",
                'ville'       => "ville du medecin",
                'cp'          => "17000",
            ],
            $attributes
        );

        return ["base" => Medecin::class, "medecin" => $medecin];
    }

    private function getNotValidPlageConsult(): array
    {
        return [
            "pl_cons with extId null"   => $this->generatePlageConsult(['external_id' => null]),
            "pl_cons with chir_id null" => $this->generatePlageConsult(['chir_id' => null]),
            "pl_cons with date null"    => $this->generatePlageConsult(['date' => null]),
            "pl_cons with date no DT"   => $this->generatePlageConsult(['date' => CMbDT::date()]),
            "pl_cons with freq null"    => $this->generatePlageConsult(['freq' => null]),
            "pl_cons with freq no DT"   => $this->generatePlageConsult(['freq' => CMbDT::time()]),
            "pl_cons with freq <5min"   => $this->generatePlageConsult(['freq' => new DateTime('00:04:59')]),
            "pl_cons with debut null"   => $this->generatePlageConsult(['debut' => null]),
            "pl_cons with debut no DT"  => $this->generatePlageConsult(['debut' => CMbDT::time()]),
            "pl_cons with fin null"     => $this->generatePlageConsult(['fin' => null]),
            "pl_cons with fin no DT"    => $this->generatePlageConsult(['fin' => CMbDT::time()]),
            "pl_cons with fin <debut"   => $this->generatePlageConsult(['fin' => new DateTime('11:59:59')]),
            "pl_cons with libelle long" => $this->generatePlageConsult(['libelle' => $this->completeString(256)]),
        ];
    }

    private function generatePlageConsult(array $attributes = []): array
    {
        $plage_consult = array_merge(
            [
                'external_id' => 33,
                'chir_id'     => 11,
                'date'        => new DateTime('1900-12-12'),
                'freq'        => new DateTime('00:05:01'),
                'debut'       => new DateTime('12:00:00'),
                'fin'         => new DateTime('13:13:13'),
            ],
            $attributes
        );

        return ["base" => PlageConsult::class, "plage_consult" => $plage_consult];
    }

    private function getNotValidConsultation(): array
    {
        return [
            "consult with extId null"      => $this->generateConsultation(['external_id' => null]),
            "consult with plage_id null"   => $this->generateConsultation(['plageconsult_id' => null]),
            "consult with heure null"      => $this->generateConsultation(['heure' => null]),
            "consult with heure not DT"    => $this->generateConsultation(['heure' => CMbDT::time()]),
            "consult with duree null"      => $this->generateConsultation(['duree' => null]),
            "consult with duree <1"        => $this->generateConsultation(['duree' => 0]),
            "consult with duree >255"      => $this->generateConsultation(['duree' => 256]),
            "consult with motif null"      => $this->generateConsultation(['motif' => null]),
            "consult with chrono string"   => $this->generateConsultation(['chrono' => 'a']),
            "consult with chrono not enum" => $this->generateConsultation(['chrono' => 1]),
        ];
    }

    private function generateConsultation(array $attributes = []): array
    {
        $consultation = array_merge(
            [
                "external_id"     => 11,
                "plageconsult_id" => 33,
                "heure"           => new DateTime('12:00:00'),
                "duree"           => 1,
                "motif"           => $this->completeString(10),
            ],
            $attributes
        );

        return ["base" => Consultation::class, "consultation" => $consultation];
    }

    private function getNotValidSejour(): array
    {
        return [
            "sejour with extId null"          => $this->generateSejour(['external_id' => null]),
            "sejour with type null"           => $this->generateSejour(['type' => null]),
            "sejour with type not enum"       => $this->generateSejour(['type' => 'a']),
            "sejour with entree_p null"       => $this->generateSejour(['entree_prevue' => null]),
            "sejour with entree_p not DT"     => $this->generateSejour(['entree_prevue' => CMbDT::dateTime()]),
            "sejour with entree_r not DT"     => $this->generateSejour(['entree_reelle' => CMbDT::dateTime()]),
            "sejour with sortie_p null"       => $this->generateSejour(['sortie_prevue' => null]),
            "sejour with sortie_p not DT"     => $this->generateSejour(['sortie_prevue' => CMbDT::dateTime()]),
            "sejour with sortie_r not DT"     => $this->generateSejour(['sortie_reelle' => CMbDT::dateTime()]),
            "sejour with libelle > 255"       => $this->generateSejour(['libelle' => $this->completeString(256)]),
            "sejour with patient null"        => $this->generateSejour(['patient_id' => null]),
            "sejour with praticien null"      => $this->generateSejour(['praticien_id' => null]),
        ];
    }

    private function generateSejour(array $attributes = []): array
    {
        $sejour = array_merge(
            [
                "external_id"   => 11,
                "type"          => 'comp',
                "entree"        => new DateTime('2020-12-12 12:12:12'),
                "entree_prevue" => new DateTime('2020-12-12 12:12:12'),
                "sortie"        => new DateTime('2020-12-12 13:13:13'),
                "sortie_prevue" => new DateTime('2020-12-12 13:13:13'),
                "patient_id"    => 22,
                "praticien_id"  => 33,
                "group_id"      => 44,
            ],
            $attributes
        );

        return ["base" => Sejour::class, "sejour" => $sejour];
    }


    private function getNotValidFile(): array
    {
        return [
            "file with extId null"      => $this->generateFile(['external_id' => null]),
            "file with file_name null"  => $this->generateFile(['file_name' => null]),
            "file with file_name long"  => $this->generateFile(['file_name' => $this->completeString(256)]),
            "file with file_date null"  => $this->generateFile(['file_date' => null]),
            "file with file_date no DT" => $this->generateFile(['file_date' => CMbDT::dateTime()]),
            "file with file_type long"  => $this->generateFile(['file_type' => $this->completeString(256)]),
        ];
    }

    private function getNotValidConstante(): array
    {
        return [
            'const with extId null'           => $this->generateConstante(['external_id' => null]),
            'const with patient null'         => $this->generateConstante(['patient_id' => null]),
            'const with invalid datetime'     => $this->generateConstante(['datetime' => 'abcd']),
            'const with taille less than 20'  => $this->generateConstante(['taille' => 10]),
            'const with taille more than 300' => $this->generateConstante(['taille' => 500]),
            'const with poids more than 500'  => $this->generateConstante(['poids' => 1000]),
        ];
    }

    private function generateFile(array $attributes = []): array
    {
        $file = array_merge(
            [
                "external_id" => 11,
                "file_date"   => new DateTime('2020-12-12 12:12:12'),
                "file_name"   => 'file_toto',
                "author_id"   => 22,
            ],
            $attributes
        );

        return ["base" => File::class, "file" => $file];
    }

    private function generateConstante(array $attributes = []): array
    {
        $constante = array_merge(
            [
                'external_id' => uniqid(),
                'patient_id'  => 22,
            ],
            $attributes
        );

        return ['base' => Constante::class, 'constante' => $constante];
    }

    private function completeString(int $length): string
    {
        return str_pad('', $length, 'a', STR_PAD_RIGHT);
    }

    private function completeNumber(int $length): string
    {
        return str_pad('', $length, '1', STR_PAD_RIGHT);
    }
}
