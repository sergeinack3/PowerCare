<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Unit\Entity;

use Exception;
use Ox\Core\CSQLDataSource;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CPersonneExerciceTest extends OxUnitTestCase
{
    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->ds = CSQLDataSource::get(CExternalMedecinBulkImport::DSN, true);
        if (!$this->ds) {
            $this->markTestSkipped('Datasource RPPS is not available');
        }

        if (!$this->ds->hasTable('personne_exercice')) {
            // Create schema for tables to be available
            $import = new CExternalMedecinBulkImport();
            $import->createSchema();
            $this->markTestSkipped('personne_exercice table not existing');
        }
    }

    /**
     * @throws Exception
     */
    public function testSynchronizeEmpty(): void
    {
        $person_exercice = new CPersonneExercice();
        $this->assertEquals(new CMedecin(), $person_exercice->synchronize());
    }

    /**
     * @param array $person_agrs
     * @param array $medecin_args
     *
     * @dataProvider synchronizeProvider
     * @throws Exception
     */
    public function testSynchronize(array $person_agrs, array $medecin_args): void
    {
        $person_exercice = $this->buildPersonneExercice(...$person_agrs);
        $this->assertEquals($this->buildMedecin(...$medecin_args), $person_exercice->synchronize());
    }

    /**
     * @throws CImportMedecinException
     */
    public function testSynchronizeExercicePlaceException(): void
    {
        $medecin         = new CMedecin();
        $person_exercice = new CPersonneExercice();
        $this->expectExceptionMessage('CMedecin must be a valide object');
        $person_exercice->synchronizeExercicePlace($medecin, null);
    }


    public function synchronizeProvider(): array
    {
        return [
            'adeli'       => [
                [0, '9FA000288', '05.06-04 06.08'],
                [null, '0506040608'],
            ],
            'rpps'        => [
                [8, '10001667434', '05.06-04 06.08'],
                ['10001667434', '0506040608'],
            ],
            'telTooShort' => [
                [8, '10001667434', '05reee2'],
                ['10001667434', null],
            ],
            'noPrenom'    => [
                [8, '10001667434', '05.06-04 06.08', ''],
                ['10001667434', '0506040608', null],
            ],
        ];
    }

    private function buildPersonneExercice(
        int $type_id,
        string $id,
        string $tel,
        ?string $prenom = 'PRENOM-SYNCHRONIZE'
    ): CPersonneExercice {
        $person_exercice                         = new CPersonneExercice();
        $person_exercice->nom                    = 'NOM-SYNCHRONIZE';
        $person_exercice->prenom                 = $prenom;
        $person_exercice->code_profession        = 10;
        $person_exercice->cp                     = '17000';
        $person_exercice->type_identifiant       = $type_id;
        $person_exercice->identifiant            = $id;
        $person_exercice->num_voie               = '10';
        $person_exercice->libelle_type_voie      = 'Avenue';
        $person_exercice->libelle_voie           = 'du grand cygne';
        $person_exercice->mention_distrib        = 'DISTIB 3';
        $person_exercice->cedex                  = 'CEDEX 9';
        $person_exercice->tel                    = $tel;
        $person_exercice->tel2                   = '05bggg.06-0fffg4 06.08';
        $person_exercice->fax                    = '05.sdfgsdf06-  0sdfsf4 06.08';
        $person_exercice->email                  = 'emailtest@email.com';
        $person_exercice->libelle_commune        = 'La Rochelle';
        $person_exercice->libelle_categorie_pro  = 'CIVil';
        $person_exercice->code_savoir_faire      = 'CM10';
        $person_exercice->libelle_savoir_faire   = 'Test savoir faire';
        $person_exercice->code_civilite_exercice = 'DR';
        $person_exercice->code_civilite          = 'MME';
        $person_exercice->code_mode_exercice     = 'L';

        return $person_exercice;
    }

    private function buildMedecin(
        ?string $code = null,
        ?string $tel = null,
        ?string $prenom = 'Prenom-Synchronize'
    ): CMedecin {
        $medecin         = new CMedecin();
        $medecin->nom    = 'NOM-SYNCHRONIZE';
        $medecin->prenom = $prenom;

        if ($code) {
            $medecin->rpps = $code;
        }

        $medecin->titre = 'dr';
        $medecin->sexe  = 'f';

        return $medecin;
    }

    /**
     * @throws Exception
     */
    private function createExercicePlace(
        string $rs,
        string $uid,
        string $version,
        string $siret = null,
        string $siren = null,
        string $finess = null
    ): CExercicePlace {
        $place                            = new CExercicePlace();
        $place->exercice_place_identifier = $uid;
        $place->raison_sociale            = $rs;
        $place->cp                        = '17000';
        $place->rpps_file_version         = $version;
        $place->id_technique              = '123456789';
        $place->commune                   = 'La Rochelle';
        $place->siret                     = $siret;
        $place->siren                     = $siren;
        $place->finess                    = $finess;

        $place->store();

        return $place;
    }

    private function getNewPlace(
        string $rs,
        string $version,
        string $siret = null,
        string $siren = null,
        string $finess = null
    ): CMedecinExercicePlace {
        $place                    = new CMedecinExercicePlace();
        $place->raison_sociale    = $rs;
        $place->cp                = '17000';
        $place->rpps_file_version = $version;
        $place->id_technique      = '123456789';
        $place->enseigne_comm     = 'ENSEIGNE COMM TEST';
        $place->comp_destinataire = 'COM DESTINATAIRE';
        $place->comp_point_geo    = '12345';
        $place->commune           = 'La Rochelle';
        $place->pays              = 'France';
        $place->siret             = $siret;
        $place->siren             = $siren;
        $place->finess            = $finess;

        return $place;
    }
}
