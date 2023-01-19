<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Exception;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;

/**
 * Fields replacer : used when the names of the fields change (translations by example)
 */
class CompteRenduFieldReplacer
{
    private const REPLACEMENTS = [
        self::PATIENT_NOM           => self::PATIENT_NOM_UTILISE,
        self::PATIENT_NOM_NAISSANCE => self::PATIENT_NOM_NAISSANCE_MAJ,
        self::PATIENT_PRENOM        => self::PATIENT_PRENOM_PRENOM_NAISSANCE,
    ];

    public const PATIENT_NOM         = 'Patient - nom';
    public const PATIENT_NOM_UTILISE = 'Patient - Nom utilisé';

    public const PATIENT_NOM_NAISSANCE     = 'Patient - nom de naissance';
    public const PATIENT_NOM_NAISSANCE_MAJ = 'Patient - Nom de naissance';

    public const PATIENT_PRENOM                  = 'Patient - prénom';
    public const PATIENT_PRENOM_PRENOM_NAISSANCE = 'Patient - Premier prénom de naissance';

    private const PREFIX = '[';
    private const SUFFIX = ']';

    private ?string $source;

    public function __construct(?string $source = null)
    {
        $this->source = $source;
    }

    public function getSource(): string
    {
        foreach (self::REPLACEMENTS as $search => $replace) {
            $search  = CMbString::htmlEntities(self::PREFIX . $search . self::SUFFIX);
            $replace = CMbString::htmlEntities(self::PREFIX . $replace . self::SUFFIX);

            $this->source = str_replace($search, $replace, $this->source);
        }

        return $this->source;
    }

    /**
     * @throws Exception
     */
    public function bulkReplace(): int
    {
        $request = "UPDATE compte_rendu AS cr, content_html AS ch
        SET ch.content = REPLACE(`content`, ?1, ?2)
        WHERE cr.object_id IS NULL
        AND cr.content_id = ch.content_id";

        $count = 0;

        $ds = CSQLDataSource::get('std');

        foreach (self::REPLACEMENTS as $search => $replace) {
            $search  = CMbString::htmlEntities(self::PREFIX . $search . self::SUFFIX);
            $replace = CMbString::htmlEntities(self::PREFIX . $replace . self::SUFFIX);

            $ds->exec($ds->prepare($request, $search, $replace));

            $count += $ds->affectedRows();
        }

        return $count;
    }
}
