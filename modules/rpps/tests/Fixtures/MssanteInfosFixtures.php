<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Tests\Fixtures;

use Exception;
use Ox\Core\CMbString;
use Ox\Core\CModelObjectException;
use Ox\Import\Rpps\Entity\CAbstractExternalRppsObject;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Mediboard\Patients\CExercicePlace;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CMedecinExercicePlace;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Class MssanteInfosFixtures
 * @package Ox\Import\Rpps\Tests\Fixtures;
 */
class MssanteInfosFixtures extends Fixtures implements GroupFixturesInterface
{
    /** @var string */
    public const REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITH_EXERCICE_PLACE    = 'mssante_infos_with_ep';
    public const REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITHOUT_EXERCICE_PLACE = 'mssante_infos_without_ep';
    public const REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITH_DUPLICATE         = 'mssante_infos_with_duplicate';

    /**
     * @throws Exception
     */
    public function load(): void
    {
        $medecin = CMedecin::getSampleObject();
        $this->store($medecin);

        $medecin_exercice_place_without_exercice_place                  = CMedecinExercicePlace::getSampleObject();
        $medecin_exercice_place_without_exercice_place->mssante_address = 'mail@mssantemail.com';
        $medecin_exercice_place_without_exercice_place->medecin_id      = $medecin->_id;
        $this->store(
            $medecin_exercice_place_without_exercice_place,
            self::REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITHOUT_EXERCICE_PLACE
        );

        $medecin_exercice_place_with_exercice_place                    = CMedecinExercicePlace::getSampleObject();
        $medecin_exercice_place_with_exercice_place->medecin_id        = $medecin->_id;
        $medecin_exercice_place_with_exercice_place->exercice_place_id = self::generateExercicePlaceWithFinej(
            '123456789'
        )->_id;
        $this->store(
            $medecin_exercice_place_with_exercice_place,
            self::REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITH_EXERCICE_PLACE
        );

        $medecin_exercice_place_with_duplicate                    = CMedecinExercicePlace::getSampleObject();
        $medecin_exercice_place_with_duplicate->medecin_id        = $medecin->_id;
        $medecin_exercice_place_with_duplicate->exercice_place_id = self::generateExercicePlaceWithFinej(
            '122334455'
        )->_id;

        $mep_addresses = implode("\n", ['jean.dupont@aquitaine.mssante.fr', 'jean.dupont@aquitaine.mssante.fr']);

        $medecin_exercice_place_with_duplicate->mssante_address = $mep_addresses;
        $this->store(
            $medecin_exercice_place_with_duplicate,
            self::REF_MSSANTE_INFOS_MEDECIN_EXERCICE_PLACE_WITH_DUPLICATE
        );
    }

    /**
     * @return array
     */
    public static function getGroup(): array
    {
        return ['rpps'];
    }

    /**
     * @param string $finej
     *
     * @return CExercicePlace
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function generateExercicePlaceWithFinej(string $finej): CExercicePlace
    {
        $exercice_place                   = CExercicePlace::getSampleObject();
        $exercice_place->finess_juridique = $finej;
        $this->store($exercice_place);

        return $exercice_place;
    }
}
