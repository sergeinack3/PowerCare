<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Personnel\Tests\Fixtures;

use Exception;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CAffectationPersonnel;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class AffectationPersonnelFixtures extends Fixtures
{
    public const TAG_AFFECTATION = 'affectation_personnel';

    public const TAG_PERSONNEL = 'personnel';

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     * @throws Exception
     */
    public function load(): void
    {
        $personnel = $this->generatePersonnel(self::TAG_PERSONNEL);

        $plage = $this->generatePlageOp($personnel->loadRefUser());

        $this->generateAffectationPersonnel(self::TAG_AFFECTATION, $personnel, $plage);
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generatePersonnel(string $tag): CPersonnel
    {
        $user = $this->getUser(false);

        $personnel          = CPersonnel::getSampleObject();
        $personnel->user_id = $user->_id;
        $this->store($personnel, $tag);

        return $personnel;
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function generatePlageOp(CMediusers $praticien): CPlageOp
    {
        $bloc           = CBlocOperatoire::getSampleObject();
        $bloc->group_id = $praticien->loadRefFunction()->group_id;

        $this->store($bloc);

        /** @var CSalle $salle */
        $salle          = CSalle::getSampleObject();
        $salle->bloc_id = $bloc->_id;

        $this->store($salle);

        $plage           = CPlageOp::getSampleObject();
        $plage->chir_id  = $praticien->_id;
        $plage->debut    = $plage->debut_reference = '08:00:00';
        $plage->fin      = $plage->fin_reference = '18:00:00';
        $plage->salle_id = $salle->_id;

        $this->store($plage);

        return $plage;
    }

    /**
     * @throws FixturesException
     */
    private function generateAffectationPersonnel(string $tag, CPersonnel $personnel, CPlageOp $plage): void
    {
        $affectation               = new CAffectationPersonnel();
        $affectation->personnel_id = $personnel->_id;
        $affectation->object_class = $plage->_class;
        $affectation->object_id    = $plage->_id;

        $this->store($affectation, $tag);
    }
}
