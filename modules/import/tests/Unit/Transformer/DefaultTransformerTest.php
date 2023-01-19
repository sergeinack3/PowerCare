<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Tests\Unit\Transformer;

use Ox\Import\Framework\Entity\Affectation;
use Ox\Import\Framework\Entity\Consultation;
use Ox\Import\Framework\Entity\ExternalReferenceStash;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Entity\Medecin;
use Ox\Import\Framework\Entity\Operation;
use Ox\Import\Framework\Entity\Patient;
use Ox\Import\Framework\Entity\PlageConsult;
use Ox\Import\Framework\Entity\Sejour;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Tests\Unit\GeneratorEntityTrait;
use Ox\Import\Framework\Transformer\DefaultTransformer;
use Ox\Import\GenericImport\Tests\Fixtures\GenericImportFixtures;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;

class DefaultTransformerTest extends OxUnitTestCase
{
    use GeneratorEntityTrait;

    /**
     * @var DefaultTransformer
     */
    private $default_transformer;

    /**
     * @var ExternalReferenceStash
     */
    private $external_reference_stash;

    public function setUp(): void
    {
        $this->default_transformer = new DefaultTransformer();

        $this->external_reference_stash = $this->createMock(ExternalReferenceStash::class);

        $this->external_reference_stash->method('getMbIdByExternalId')->willReturn(22);
    }

    public function testTransformUser(): void
    {
        /** @var User $user */
        $user = $this->generateExternalUser();
        $user = $this->default_transformer->transformUser($user);

        $this->assertEquals('user_user_name', $user->user_username);
        $this->assertEquals('user_first_name', $user->user_first_name);
        $this->assertEquals('user_last_name', $user->user_last_name);
        $this->assertEquals('m', $user->user_sexe);
        $this->assertEquals('2020-12-12', $user->user_birthday);
        $this->assertEquals('user_email@email.com', $user->user_email);
        $this->assertEquals('0102030405', $user->user_phone);
        $this->assertEquals('0504030201', $user->user_mobile);
        $this->assertEquals('1 rue du user', $user->user_address1);
        $this->assertEquals('17000', $user->user_zip);
        $this->assertEquals('user_city', $user->user_city);
        $this->assertEquals('user_country', $user->user_country);
        $this->assertInstanceOf(CUser::class, $user);
    }

    public function testTransformPatient(): void
    {
        /** @var Patient $patient */
        $patient = $this->generateExternalPatient();
        $patient = $this->default_transformer->transformPatient($patient, $this->external_reference_stash);

        $this->assertEquals("patientNom", $patient->nom);
        $this->assertEquals("patientPrenom", $patient->prenom);
        $this->assertEquals("2020-12-12", $patient->naissance);
        $this->assertEquals('patientNomJeuneFille', $patient->nom_jeune_fille);
        $this->assertEquals('patientProfession', $patient->profession);
        $this->assertEquals('patientEmail@test.com', $patient->email);
        if ($patient->tel) {
            $this->assertEquals('0102030405', $patient->tel);
        }
        if ($patient->tel2) {
            $this->assertEquals('0102030405', $patient->tel2);
        }
        if ($patient->tel_autre) {
            $this->assertEquals('0102030405', $patient->tel_autre);
        }
        $this->assertEquals('1 rue du patient', $patient->adresse);
        $this->assertEquals('17000', $patient->cp);
        $this->assertEquals('patientVille', $patient->ville);
        $this->assertEquals('patientPays', $patient->pays);
        //        $this->assertEquals('patientMatricule', $patient->matricule);
        $this->assertEquals('m', $patient->sexe);
        $this->assertEquals('m', $patient->civilite);
        $this->assertEquals(22, $patient->medecin_traitant);
        $this->assertEquals(1, $patient->ald);
        $this->assertInstanceOf(CPatient::class, $patient);
    }

    public function testTransformMedecin(): void
    {
        /** @var Medecin $medecin */
        $medecin = $this->generateExternalMedecin();
        $medecin = $this->default_transformer->transformMedecin($medecin);

        $this->assertEquals('medecin_nom', $medecin->nom);
        $this->assertEquals('medecin_prenom', $medecin->prenom);
        $this->assertEquals('m', $medecin->sexe);
        $this->assertEquals('dr', $medecin->titre);
        $this->assertEquals('medecin@email.com', $medecin->email);
        $this->assertEquals('generaliste', $medecin->disciplines);
        $this->assertEquals('0102030405', $medecin->tel);
        $this->assertEquals('0102030405', $medecin->tel_autre);
        $this->assertEquals('1 rue du medecin', $medecin->adresse);
        $this->assertEquals('17000', $medecin->cp);
        $this->assertEquals('medecin_ville', $medecin->ville);
        $this->assertInstanceOf(Cmedecin::class, $medecin);
    }

    public function testTransformPlageConsult(): void
    {
        /** @var Plageconsult $plage_consult */
        $plage_consult = $this->generateExternalPlageConsult(true);
        $plage_consult = $this->default_transformer->transformPlageconsult(
            $plage_consult,
            $this->external_reference_stash
        );

        $this->assertEquals('2020-12-12', $plage_consult->date);
        $this->assertEquals('00:15:00', $plage_consult->freq);
        $this->assertEquals('08:00:00', $plage_consult->debut);
        $this->assertEquals('18:00:00', $plage_consult->fin);
        $this->assertEquals('consultation', $plage_consult->libelle);
        $this->assertEquals(22, $plage_consult->chir_id);
        $this->assertInstanceOf(CPlageconsult::class, $plage_consult);
    }

    public function testTransformConsultation(): void
    {
        /** @var Consultation $consultation */
        $consultation = $this->generateExternalConsultation();
        $consultation = $this->default_transformer->transformConsultation(
            $consultation,
            $this->external_reference_stash
        );

        $this->assertEquals('20:20:20', $consultation->heure);
        $this->assertEquals(2, $consultation->duree);
        $this->assertEquals('motif_consultation', $consultation->motif);
        $this->assertEquals('rques_consultation', $consultation->rques);
        $this->assertEquals('examen_consultation', $consultation->examen);
        $this->assertEquals('traitement_consultation', $consultation->traitement);
        $this->assertEquals('histoire_maladie', $consultation->histoire_maladie);
        $this->assertEquals('conclusion_consultation', $consultation->conclusion);
        $this->assertEquals('resultats', $consultation->resultats);
        $this->assertEquals(22, $consultation->plageconsult_id);
        $this->assertEquals(22, $consultation->patient_id);
        $this->assertInstanceOf(CConsultation::class, $consultation);
    }

    public function testTransformSejour(): void
    {
        /** @var Sejour $sejour */
        $sejour = $this->generateExternalSejour();
        $sejour = $this->default_transformer->transformSejour(
            $sejour,
            $this->external_reference_stash
        );

        $this->assertEquals('comp', $sejour->type);
        $this->assertEquals('2020-12-12 20:20:20', $sejour->entree_prevue);
        $this->assertEquals('2020-12-12 20:20:20', $sejour->entree_reelle);
        $this->assertEquals('2020-12-12 20:30:30', $sejour->sortie_prevue);
        $this->assertEquals('2020-12-12 20:30:30', $sejour->sortie_reelle);
        $this->assertEquals('libelle', $sejour->libelle);
        $this->assertEquals(22, $sejour->patient_id);
        $this->assertEquals(22, $sejour->praticien_id);
        // TODO : A ajouter quand transformSejour sera modifié
        //        $this->assertEquals(22, $sejour->group_id);
        $this->assertInstanceOf(CSejour::class, $sejour);
    }

    public function testTransformFileWithoutRef(): void
    {
        /** @var File $file */
        $file = $this->generateExternalFileWithoutRef();
        $file = $this->default_transformer->transformFile(
            $file,
            $this->external_reference_stash
        );

        $this->assertEquals('2020-12-12 20:20:20', $file->file_date);
        $this->assertEquals('file_name', $file->file_name);
        $this->assertEquals('file_type', $file->file_type);
        $this->assertEquals(22, $file->author_id);
        $this->assertInstanceOf(CFile::class, $file);
    }

    public function testTransformFileWithRefSejour(): void
    {
        /** @var File $file */
        $file = $this->generateExternalFileWithRefSejour();
        $file = $this->default_transformer->transformFile(
            $file,
            $this->external_reference_stash
        );

        $this->assertEquals(22, $file->object_id);
    }

    public function testTransformFileWithRefConsultation(): void
    {
        /** @var File $file */
        $file = $this->generateExternalFileWithRefConsultation();
        $file = $this->default_transformer->transformFile(
            $file,
            $this->external_reference_stash
        );

        $this->assertEquals(22, $file->object_id);
    }

    public function testTransformFileWithRefPatient(): void
    {
        /** @var File $file */
        $file = $this->generateExternalFileWithRefPatient();
        $file = $this->default_transformer->transformFile(
            $file,
            $this->external_reference_stash
        );

        $this->assertEquals(22, $file->object_id);
    }

    public function testTransformAffectation(): void
    {
        /** @var CService $service */
        $service = $this->getObjectFromFixturesReference(CService::class, GenericImportFixtures::TAG_SERVICE);
        /** @var CLit $lit */
        $lit     = $this->getObjectFromFixturesReference(CLit::class, GenericImportFixtures::TAG_LIT);
        /** @var CUniteFonctionnelle $uf */
        $uf      = $this->getObjectFromFixturesReference(CUniteFonctionnelle::class, GenericImportFixtures::TAG_UF);
        /** @var CModeEntreeSejour $me */
        $me      = $this->getObjectFromFixturesReference(CModeEntreeSejour::class, GenericImportFixtures::TAG_ME);
        /** @var CModeSortieSejour $ms */
        $ms      = $this->getObjectFromFixturesReference(CModeSortieSejour::class, GenericImportFixtures::TAG_MS);

        /** @var Affectation $affectation */
        $affectation = $this->generateExternalAffectation($service->nom, $lit->nom, $uf->code, $me->code, $ms->code);
        $affectation = $this->default_transformer->transformAffectation(
            $affectation,
            $this->external_reference_stash
        );



        $this->assertEquals(22, $affectation->sejour_id);
        $this->assertEquals($service->_id, $affectation->service_id);
        $this->assertEquals($lit->_id, $affectation->lit_id);
        $this->assertEquals($uf->_id, $affectation->uf_medicale_id);
        $this->assertEquals($me->_id, $affectation->mode_entree_id);
        $this->assertEquals($ms->_id, $affectation->mode_sortie_id);
        $this->assertEquals('2020-12-12 20:20:20', $affectation->entree);
        $this->assertEquals('2020-12-12 20:30:30', $affectation->sortie);
    }

    /**
     * @dataProvider transformAffectationIncorrectProvider
     */
    public function testTransformAffectationWithRefsIncorrect(Affectation $affectation): void
    {
        $affectation = $this->default_transformer->transformAffectation(
            $affectation,
            $this->external_reference_stash
        );

        $this->assertNull($affectation->lit_id);
        $this->assertNull($affectation->uf_medicale_id);
        $this->assertNull($affectation->uf_soins_id);
        $this->assertNull($affectation->uf_hebergement_id);
        $this->assertNull($affectation->mode_entree_id);
        $this->assertNull($affectation->mode_sortie_id);
    }

    public function transformAffectationIncorrectProvider(): array
    {
        /** @var CService $service */
        $service = $this->getObjectFromFixturesReference(CService::class, GenericImportFixtures::TAG_SERVICE);

        return [
            'incorrect_refs' => [$this->generateExternalAffectationWithRefsIncorrect($service->nom)],
            'without_refs' => [$this->generateExternalAffectationWithoutRefs($service->nom)],
        ];
    }

    public function testTransformOperation(): void
    {
        /** @var Operation $operation */
        $operation = $this->generateExternalOperation();
        $operation = $this->default_transformer->transformOperation(
            $operation,
            $this->external_reference_stash
        );

        $this->assertEquals(22, $operation->sejour_id);
        $this->assertEquals(22, $operation->chir_id);
        $this->assertEquals('gauche', $operation->cote);
        $this->assertEquals('2020-12-12', $operation->date);
        $this->assertEquals('10:10:10', $operation->time_operation);
        $this->assertEquals('Lorem ipsum', $operation->libelle);
        $this->assertEquals('Lorem ipsum', $operation->examen);
        $this->assertInstanceOf(COperation::class, $operation);
    }
}
