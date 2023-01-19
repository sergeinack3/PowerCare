<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CAppUI;
use Ox\Core\Module\AbstractTabsRegister;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * @codeCoverageIgnore
 */
class CTabsFacturation extends AbstractTabsRegister
{
    public function registerAll(): void
    {
        if (CAppUI::gconf("dPfacturation CFactureCabinet view_bill")) {
            $this->registerFile('vw_factures_cabinet', TAB_READ);
        }

        if (CAppUI::gconf("dPfacturation CFactureEtablissement view_bill")) {
            $this->registerFile('vw_factures_etab', TAB_READ);
        }

        $this->registerFile('vw_tdb_cotation', TAB_EDIT);
        $this->registerFile('vw_compta', TAB_READ);

        if (CAppUI::gconf("dPfacturation CRetrocession use_retrocessions")) {
            $this->registerFile('vw_retrocessions', TAB_READ);
        }

        $this->registerFile('vw_tdb_facturiere', TAB_EDIT);

        $this->registerFile('vw_factureliaison_manager', TAB_EDIT, self::TAB_SETTINGS);

        if ( (CAppUI::$user instanceof CMediusers)  && (CAppUI::$user->isAdmin()
            || CAppUI::conf("dPfacturation CFactureCategory use_category_bill", CAppUI::$user->_ref_function) != "hide")
        ) {
            $this->registerFile('vw_category_facturation', TAB_EDIT, self::TAB_SETTINGS);
        }

        if (CAppUI::gconf("dPfacturation CReglement use_debiteur")) {
            $this->registerFile('', TAB_READ, self::TAB_SETTINGS);
        }

        $this->registerFile('vw_debiteurs', TAB_READ, self::TAB_SETTINGS);

        if (CAppUI::gconf("dPfacturation CRetrocession use_retrocessions")) {
            $this->registerFile('vw_retrocession_regles', TAB_ADMIN, self::TAB_SETTINGS);
        }

        $this->registerFile('vw_factures_rules', TAB_EDIT, self::TAB_SETTINGS);

        $this->registerFile('configure', TAB_ADMIN, self::TAB_CONFIGURE);
    }
}
