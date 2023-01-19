<?php
/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Entity;

use Exception;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CStoredObject;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Description
 */
abstract class CAbstractExternalRppsObject extends CStoredObject
{
    public const TYPE_IDENTIFIANT_ADELI = '0';
    public const TYPE_IDENTIFIANT_RPPS  = '8';

    /** @var int */
    public $type_identifiant;

    /** @var string */
    public $identifiant;

    /** @var string */
    public $identifiant_national;

    /** @var string */
    public $nom;

    /** @var string */
    public $prenom;

    /** @var string */
    public $version;

    /** @var bool */
    public $synchronized;

    /** @var bool */
    public $error;

    /**
     * @inheritDoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->dsn      = CExternalMedecinBulkImport::DSN;
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['type_identifiant']     = 'enum list|0|8 notNull';
        $props['identifiant']          = 'str notNull';
        $props['identifiant_national'] = 'str notNull';
        $props['nom']                  = 'str';
        $props['prenom']               = 'str';
        $props['version']              = 'str notNull';
        $props['synchronized']         = 'bool default|0';
        $props['error']                = 'bool default|0';

        return $props;
    }

    /**
     * @param bool $sync
     *
     * @return int|null
     * @throws Exception
     */
    public function getTotalLines(bool $sync = false): ?int
    {
        $ds = $this->getDS();

        $where = [
            'synchronized' => $ds->prepare("= ?", ($sync) ? '1' : '0'),
        ];

        return $this->countList($where);
    }

    /**
     * @param CMedecin $medecin
     *
     * @return string|CMedecin
     */
    abstract public function synchronize(?CMedecin $medecin = null);
}
