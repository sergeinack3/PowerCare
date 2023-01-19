<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\CarePath;

use Ox\Mediboard\Jfse\JfseEnum;

/**
 * @method static static EMERGENCY()
 * @method static static REFERRING_PHYSICIAN()
 * @method static static NEW_RP()
 * @method static static RP_SUBSTITUTE()
 * @method static static ORIENTED_BY_RP()
 * @method static static ORIENTED_BY_NRP()
 * @method static static RECENTLY_INSTALLED_RP()
 * @method static static POOR_MEDICALIZED_ZONE()
 * @method static static SPECIFIC_DIRECT_ACCESS()
 * @method static static OUT_OF_RESIDENCY()
 * @method static static NOT_SPECIFIC_ACCESS()
 * @method static static NON_COMPLIANCE_CARE_PATH()
 */
final class CarePathEnum extends JfseEnum
{
    /** @var string Urgence */
    private const EMERGENCY = 'U';

    /** @var string Medecin traitant (aka 'RP') */
    private const REFERRING_PHYSICIAN = 'T';

    /** @var string Nouveau medecin traitant */
    private const NEW_RP = 'N';

    /** @var string Medecin traitant de substitution */
    private const RP_SUBSTITUTE = 'R';

    /** @var string Oriente par un medecin traitant */
    private const ORIENTED_BY_RP = 'O';

    /** @var string Oriente par un medecin autre que le medecin traitant (Not Reffering Physician aka NRP) */
    private const ORIENTED_BY_NRP = 'M';

    /** @var string Generaliste recemment installe */
    private const RECENTLY_INSTALLED_RP = 'J';

    /** @var string Medecin installe en zone sous medicalisee */
    private const POOR_MEDICALIZED_ZONE = 'B';

    /** @var string Acces direct spécifique */
    private const SPECIFIC_DIRECT_ACCESS = 'D';

    /** @var string Hors residence habituelle */
    private const OUT_OF_RESIDENCY = 'H';

    /** @var string Hors acces spécifique */
    private const NOT_SPECIFIC_ACCESS = 'S1';

    /** @var string Non-respect du parcours / autre medecin */
    private const NON_COMPLIANCE_CARE_PATH = 'S2';
}
