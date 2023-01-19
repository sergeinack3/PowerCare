<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Unit;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Import\Rpps\CExternalMedecinSync;
use Ox\Import\Rpps\Entity\CDiplomeAutorisationExercice;
use Ox\Import\Rpps\Entity\CMssanteInfos;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Import\Rpps\Entity\CSavoirFaire;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Description
 */
class CExternalMedecinSyncTest extends OxUnitTestCase
{
    /** @var CSQLDataSource */
    private $ds;

    public function setUp(): void
    {
        parent::setUp();

        $this->ds = CSQLDataSource::get(CExternalMedecinBulkImport::DSN, true);
        if (!$this->ds) {
            $this->markTestSkipped('Datasource RPPS is not available');
        }
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     * @throws CImportMedecinException
     */
    public function testSynchronizeSomeMedecinsWithMutexAlreadyPuted(): void
    {
        $sync = new CExternalMedecinSync();
        $this->invokePrivateMethod($sync, 'putMutex');

        $this->expectExceptionMessage('Mutex is already in use');
        $sync->synchronizeSomeMedecins();

        $this->invokePrivateMethod($sync, 'releaseMutex');
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testLoadMedecinsToSyncWithRpps(): void
    {
        $medecins_ok  = $this->createMedecins(5, 1);
        $medecins_nok = $this->createMedecins(5);

        $rpps_codes = CMbArray::pluck($medecins_ok, 'rpps');

        $sync = new CExternalMedecinSync();

        /** @var CMedecin[] $medecins */
        $medecins = $this->invokePrivateMethod($sync, 'loadMedecinsToSync', 'rpps', $rpps_codes);

        $ids_medecins_ok      = CMbArray::pluck($medecins_ok, '_id');
        $ids_medecins_nok     = CMbArray::pluck($medecins_nok, '_id');
        $ids_medecins_to_sync = CMbArray::pluck($medecins, '_id');

        foreach ($ids_medecins_ok as $id_medecins_ok) {
            $this->assertContains(
                $id_medecins_ok,
                $ids_medecins_to_sync,
                'Error : Tout les medecins devant être présents n\'y sont pas.'
            );
        }

        foreach ($ids_medecins_nok as $id_medecins_nok) {
            $this->assertNotContains(
                $id_medecins_nok,
                $ids_medecins_to_sync,
                'Error : Des médecins sont présents alors qu\'ils ne devraient pas.'
            );
        }
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testRandomLoadMedecinsToSync(): void
    {
        $this->markTestSkipped('Must ref this test in order to really try the syncronization');
        $medecins_ok  = $this->createMedecins(5, 1);
        $medecins_nok = $this->createMedecins(5);

        $sync = new CExternalMedecinSync();

        /** @var CMedecin[] $medecins */
        $medecins = $this->invokePrivateMethod($sync, 'loadRandomMedecinsToSync', 10);

        $ids_medecins_ok      = CMbArray::pluck($medecins_ok, '_id');
        $ids_medecins_nok     = CMbArray::pluck($medecins_nok, '_id');
        $ids_medecins_to_sync = CMbArray::pluck($medecins, '_id');

        dump([$ids_medecins_ok, $ids_medecins_to_sync]);

        foreach ($ids_medecins_ok as $id_medecins_ok) {
            $this->assertContains(
                $id_medecins_ok,
                $ids_medecins_to_sync,
                'Error : Tout les medecins devant être présents n\'y sont pas.'
            );
        }

        foreach ($ids_medecins_nok as $id_medecins_nok) {
            $this->assertNotContains(
                $id_medecins_nok,
                $ids_medecins_to_sync,
                'Error: Des médecins sont présents alors qu\'ils ne devraient pas.'
            );
        }
    }

    /**
     * @throws TestsException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testLoadPersonnesExercices(): void
    {
        $medecins = $this->createMedecins(5, 1);

        $sync = new CExternalMedecinSync();

        $filtered_ids = array_filter(CMbArray::pluck($medecins, 'rpps'));

        $personnes_exercices = $sync->loadExternalObjectsFromIds($filtered_ids, new CPersonneExercice());

        $ids_medecins = CMbArray::pluck($medecins, 'rpps');

        foreach ($ids_medecins as $id_medecins) {
            $this->assertTrue(isset($personnes_exercices[$id_medecins]), 'error tout les medecins n\'y sont pas');
        }
    }

    /**
     * @throws Exception
     */
    private function createPersonsExercice(
        int $count,
        bool $sync = false,
        bool $err = false,
        string $type = '0',
        string $code = null
    ): void {
        $persons = [];
        for ($i = 0; $i < $count; $i++) {
            $person                       = new CPersonneExercice();
            $person->type_identifiant     = $type;
            $person->identifiant          = $code;
            $person->identifiant_national = $type . $code;
            $person->version              = CMbDT::date();
            $person->code_profession      = '1';
            $person->libelle_profession   = 'Test';

            $person->synchronized = ($sync) ? '1' : '0';
            $person->error        = ($err) ? '1' : '0';

            if ($msg = $person->store()) {
                $this->fail($msg);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function createMedecins(
        int $count,
        int $actif = 0
    ): array {
        $medecins = [];
        for ($i = 0; $i < $count; $i++) {
            $unique = CMbString::createLuhn(random_int(1000000000, 9999999999));

            $this->createPersonsExercice(1, false, false, '0', $unique);

            $medecin                      = new CMedecin();
            $medecin->nom                 = 'Nom-' . $unique;
            $medecin->prenom              = 'Prenom-' . $unique;
            $medecin->type                = 'medecin';
            $medecin->rpps                = $unique;
            $medecin->import_file_version = CMbDT::date('-1 DAY');
            $medecin->actif               = $actif;

            if ($msg = $medecin->store()) {
                $this->fail($msg);
            }

            $medecins[] = $medecin;
        }

        return $medecins;
    }

    /**
     * @throws Exception
     */
    private function createMedecinFromAdeli(string $code): CMedecin
    {
        $medecin = $this->createMedecinFrom($code, 'adeli');

        $exercice_place               = new CExercicePlace();
        $exercice_place->id_technique = uniqid();
        if ($msg = $exercice_place->store()) {
            $this->fail($msg);
        }

        $medecin_exercice_place                    = new CMedecinExercicePlace();
        $medecin_exercice_place->medecin_id        = $medecin->_id;
        $medecin_exercice_place->exercice_place_id = $exercice_place->_id;
        $medecin_exercice_place->adeli             = $code;
        if ($msg = $medecin_exercice_place->store()) {
            $this->fail($msg);
        }

        return $medecin;
    }

    /**
     * @throws Exception
     */
    private function createMedecinFrom(string $code, string $field): CMedecin
    {
        $medecin = new CMedecin();

        if ($field === 'adeli') {
            $medecin->loadByAdeli($code);
        } elseif ($field === 'rpps') {
            $medecin->loadFromRPPS($code);
        }

        if (!$medecin->_id) {
            $medecin->nom      = 'Test';
            $medecin->type     = 'medecin';
            $medecin->{$field} = $code;
            if ($msg = $medecin->store()) {
                $this->fail($msg);
            }
        }

        return $medecin;
    }

    /**
     * @throws Exception
     */
    private function createMssante(string $code, string $type): CMssanteInfos
    {
        $mssante                       = new CMssanteInfos();
        $mssante->type_identifiant     = $type;
        $mssante->identifiant          = $code;
        $mssante->identifiant_national = $type . $code;
        $mssante->version              = CMbDT::date();
        $mssante->type_bal             = 'PER';
        $mssante->email                = 'test@mssante.com';

        if ($msg = $mssante->store()) {
            $this->fail($msg);
        }

        return $mssante;
    }

    /**
     * @throws Exception
     */
    private function initTables(): void
    {
        $import = new CExternalMedecinBulkImport();
        $import->createSchema();
    }
}
