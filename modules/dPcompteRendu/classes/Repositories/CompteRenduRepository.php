<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu\Repositories;

use Exception;
use Ox\Core\CPDODataSource;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Repository to fetch CSampleMovie objects.
 */
class CompteRenduRepository
{
    private array $where = [];
    /** @var CPDODataSource|CSQLDataSource */
    private              $ds;
    private CCompteRendu $cr;

    public function __construct()
    {
        $this->cr = new CCompteRendu();
        $this->ds = $this->cr->getDS();
    }

    /**
     * @param CMediusers[] $users
     *
     * @return int
     * @throws Exception
     */
    public function countUnsigned(array $users = []): int
    {
        $this->addWhereSigned(false);

        $this->addWhereSignatoryIdIn(array_column($users, '_id'));

        return $this->cr->countList($this->where);
    }

    private function addWhereSigned(bool $signed): void
    {
        if (!isset($this->where['compte_rendu.signe'])) {
            $this->where['compte_rendu.valide'] = $this->ds->prepare(
                '= ?' . ($signed ? '' : ' OR compte_rendu.valide IS NULL'),
                $signed ? '1' : '0'
            );
        }
    }

    private function addWhereSignatoryId(CMediusers $user): void
    {
        if (!isset($this->where['compte_rendu.signataire_id'])) {
            $this->where['compte_rendu.signataire_id'] = $this->ds->prepare('= ?', $user->_id);
        }
    }


    public function addWhereSignatoryIdIn(array $users_id): void
    {
        if (!isset($this->where['compte_rendu.signataire_id'])) {
            $this->where['compte_rendu.signataire_id'] = $this->ds->prepareIn($users_id);
        }
    }
}
