<?php
/**
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Lpp;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupLpp extends CSetup
{
    /**
     * @see parent::__construct()
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = 'lpp';
        $this->makeRevision('0.0');

        $query = "CREATE TABLE `actes_lpp` (
                      `acte_lpp_id` INT (11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL,
                      `object_id` INT (11) UNSIGNED NOT NULL,
                      `object_class` VARCHAR (80) NOT NULL,
                      `executant_id` INT (11) UNSIGNED NOT NULL,
                      `execution` DATETIME NOT NULL,
                      `montant_base` FLOAT,
                      `montant_depassement` FLOAT,
                      `facturable` ENUM ('0','1') NOT NULL DEFAULT '1',
                      `num_facture` INT (11) UNSIGNED NOT NULL DEFAULT '1',
                      `gratuit` ENUM ('0','1') NOT NULL DEFAULT '0',
                      `code_prestation` VARCHAR (3) NOT NULL,
                      `code` VARCHAR (7) NOT NULL,
                      `type_prestation` ENUM('A', 'E', 'L', 'P', 'S', 'R', 'V'),
                      `siret` VARCHAR (14),
                      `date` DATE NOT NULL,
                      `date_fin` DATE,
                      `quantite` INT (4) UNSIGNED DEFAULT 1,
                      `prix_unitaire` FLOAT NOT NULL,
                      `montant_total` FLOAT,
                      `montant_final` FLOAT
                    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision('0.01');

        $query = "ALTER TABLE `actes_lpp`
                    ADD `qualif_depense` ENUM('d', 'e', 'f', 'g', 'n', 'a', 'b', 'l'),
                    ADD `accord_prealable` ENUM('0', '1') DEFAULT '0',
                    ADD `date_demande_accord` DATE,
                    ADD `reponse_accord` ENUM('no_answer', 'accepted', 'emergency', 'refused'),
                    ADD `concerne_ald` ENUM('0', '1') DEFAULT '0',
                    DROP `prix_unitaire`;";
        $this->addQuery($query);

        $this->makeRevision("0.02");

        $this->addDefaultConfig("lpp General cotation_lpp", "lpp cotation_lpp");

        $this->makeRevision("0.03");
        $this->setModuleCategory("referentiel", "referentiel");

        $this->mod_version = '0.04';

        $this->addDatasource('lpp', "SELECT * FROM `fiche` WHERE `CODE_TIPS` = '2344873';");
    }
}
