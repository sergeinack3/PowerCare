<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\System\Cron\CCronJob;

/**
 * @codeCoverageIgnore
 */
class CSetupRpps extends CSetup
{
    protected function disableUnusedMedecins(): bool
    {
        if (CModule::getActive('appFine')) {
            return true;
        }

        $count = (new UnusedMedecinDesactivator())->disableMedecins();
        CAppUI::stepAjax('UnusedMedecinDesactivator-Msg-Medecins disabled', UI_MSG_OK, $count);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "rpps";
        $this->makeRevision("0.0");
        $this->setModuleCategory("dossier_patient", "autre");

        $this->addDependency('dPpatients', '4.29');

        $this->makeRevision('0.01');

        $this->addMethod('addCrons');

        $this->makeRevision('0.02');

        $ds = CSQLDataSource::get('std');

        if (!$ds->hasField('medecin_exercice_place', 'adeli')) {
            $query = "ALTER TABLE `medecin_exercice_place`
                    ADD COLUMN `adeli` VARCHAR(9),
                    ADD INDEX (`adeli`)";
            $this->addQuery($query);
            $this->makeRevision('0.03');
        } else {
            $this->makeEmptyRevision('0.03');
        }

        // Create exercice places from existing medecin
        if (!$ds->hasTable('exercice_place') && $this->columnExists('medecin_exercice_place', 'adresse')) {
            $query = "INSERT INTO `medecin_exercice_place` (medecin_id, adresse, cp, commune, tel, tel2, fax, email, adeli)
                    SELECT medecin_id, adresse, cp, ville, tel, tel_autre, fax, email, adeli FROM medecin
                    WHERE adeli IS NOT NULL OR adresse IS NOT NULL;";
            $this->addQuery($query);
        }

        $this->makeEmptyRevision('0.04');

        $this->makeRevision('0.05');

        $this->addDependency('dPpatients', '4.43');

        $query = "ALTER TABLE `exercice_place` DROP INDEX `exercice_place_identifier`";
        $this->addQuery($query);

        $this->makeRevision('0.06');

        $query = "ALTER TABLE `exercice_place`
                    ADD INDEX `exercice_place_identifier` (exercice_place_identifier)";
        $this->addQuery($query);

        $this->makeRevision('0.07');

        $this->addMethod('addCronDisableExercicePlaces');

        $this->makeRevision('0.08');

        /* @todo: this triggers a lot of sql errors then fatal sql syntax error */
        $this->addMethod('disableUnusedMedecins');

        $this->makeRevision('0.09');

        $this->addMethod('addTypeIdentifiantIndexes');

        $this->makeRevision('0.10');

        $this->addDependency('dPpatients', '4.65');
        $this->addQuery('ALTER TABLE `medecin_exercice_place` CHANGE COLUMN `mssante_address` `mssante_address` TEXT');

        $this->mod_version = '0.11';
    }

    protected function addCrons(): bool
    {
        $this->addCronSync();
        $this->addCronDump();

        return true;
    }

    protected function addCronSync(): void
    {
        $cron       = new CCronJob();
        $cron->name = 'RPPS : Sync médecins';
        $cron->loadMatchingObjectEsc();

        if (!$cron->_id) {
            $token          = $this->createToken("m=rpps\na=cron_synchronize_medecin", 'RPPS : Sync médecins');
            $cron->active   = '1';
            $cron->token_id = $token->_id;
            $cron->_second  = '0';
            $cron->_minute  = '*';
            $cron->_hour    = '*';
            $cron->_day     = '*';
            $cron->_month   = '*';
            $cron->_week    = '*';

            if ($msg = $cron->store()) {
                throw new Exception($msg, E_USER_ERROR);
            }
        }
    }

    protected function addCronDisableExercicePlaces(): bool
    {
        $cron       = new CCronJob();
        $cron->name = 'RPPS : Désactiver lieux d\'exercice';
        $cron->loadMatchingObjectEsc();

        if (!$cron->_id) {
            $token          = $this->createToken(
                "m=rpps\na=cron_disable_exercice_places",
                'RPPS : Désactiver lieux d\'exercice'
            );

            $cron->active   = '1';
            $cron->token_id = $token->_id;
            $cron->_second  = '0';
            $cron->_minute  = '0';
            $cron->_hour    = '*';
            $cron->_day     = '*';
            $cron->_month   = '*';
            $cron->_week    = '*';

            if ($msg = $cron->store()) {
                throw new Exception($msg, E_USER_ERROR);
            }
        }

        return true;
    }

    protected function addCronDump(): void
    {
        $cron       = new CCronJob();
        $cron->name = 'RPPS : Maj base externe';
        $cron->loadMatchingObjectEsc();

        if (!$cron->_id) {
            $token          = $this->createToken("m=rpps\na=ajax_populate_database", 'RPPS : Maj base externe');
            $cron->active   = '1';
            $cron->token_id = $token->_id;
            $cron->_second  = '0';
            $cron->_minute  = '0';
            $cron->_hour    = '8';
            $cron->_day     = '*';
            $cron->_month   = '*';
            $cron->_week    = '1';

            if ($msg = $cron->store()) {
                throw new Exception($msg, E_USER_ERROR);
            }
        }
    }

    protected function createToken(string $params, string $name): CViewAccessToken
    {
        $token               = new CViewAccessToken();
        $token->label        = $name;
        $token->params       = $params;
        $token->user_id      = CUser::get()->_id;
        $token->restricted   = '1';
        $token->_hash_length = 10;

        if ($msg = $token->store()) {
            throw new Exception($msg, E_USER_ERROR);
        }

        return $token;
    }

    protected function addTypeIdentifiantIndexes(): bool
    {
        if ($ds = CSQLDataSource::get('rpps_import', true)) {
            $result = true;
            if (
                $ds->hasTable('personne_exercice')
                && !$this->isIndexPresent($ds, 'personne_exercice', 'type_identifiant')
            ) {
                $result &= (false !== $ds->exec('ALTER TABLE `personne_exercice` ADD INDEX (type_identifiant);'));
            }

            if (
                $ds->hasTable('diplome_autorisation_exercice')
                && !$this->isIndexPresent($ds, 'diplome_autorisation_exercice', 'type_identifiant')
            ) {
                $result &= (false !== $ds->exec('ALTER TABLE `diplome_autorisation_exercice` ADD INDEX (type_identifiant);'));
            }

            if (
                $ds->hasTable('savoir_faire')
                && !$this->isIndexPresent($ds, 'savoir_faire', 'type_identifiant')
            ) {
                $result &= (false !== $ds->exec('ALTER TABLE `savoir_faire` ADD INDEX (type_identifiant);'));
            }

            if (
                $ds->hasTable('mssante_info')
                && !$this->isIndexPresent($ds, 'mssante_info', 'type_identifiant')
            ) {
                $result &= (false !== $ds->exec('ALTER TABLE `mssante_info` ADD INDEX (type_identifiant);'));
            }

            return $result;
        }

        return true;
    }

    private function isIndexPresent(CSQLDataSource $ds, string $table_name, string $index_name): bool
    {
        return $ds->loadResult(
            $ds->prepare(
                "SELECT COUNT(*)
                    FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE table_schema=DATABASE() 
                    AND table_name=?1 
                    AND index_name=?2;",
                $table_name,
                $index_name
            )
        );
    }
}
