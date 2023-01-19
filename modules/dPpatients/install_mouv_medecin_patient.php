<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;

CCAnDo::checkAdmin();

CAppUI::stepAjax("install-mmp-alert", UI_MSG_ALERT);

$ds = CSQLDataSource::get("std");

$queries = array(
  "table deletion" => "
    DROP TABLE IF EXISTS `medecin_trigger`;
  ",

  "table creation" => "
    CREATE TABLE `medecin_trigger` (
      `trigger_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `datetime` datetime NOT NULL,
      `type` enum('insert','update','delete') NOT NULL,
      `old_medecin_id` int(10) unsigned DEFAULT NULL,
      `new_medecin_id` int(10) unsigned DEFAULT NULL,
      `old_nom` varchar(50) DEFAULT NULL,
      `new_nom` varchar(50) DEFAULT NULL,
      `old_prenom` varchar(50) DEFAULT NULL,
      `new_prenom` varchar(50) DEFAULT NULL,
      PRIMARY KEY (`trigger_id`),
      KEY `datetime` (`datetime`),
      KEY `old_medecin_id` (`old_medecin_id`),
      KEY `new_medecin_id` (`new_medecin_id`)
    );
  ",

  "trigger update deletion" => "
    DROP TRIGGER IF EXISTS medecin_update;
  ",

  "trigger update creation" => "
    CREATE TRIGGER medecin_update AFTER UPDATE ON medecin
    FOR EACH ROW
    BEGIN
        INSERT INTO medecin_trigger
        SET 
          datetime = NOW(),
          type = 'update',
          old_medecin_id = OLD.medecin_id,
          new_medecin_id = NEW.medecin_id,
          old_nom        = OLD.nom,
          new_nom        = NEW.nom,
          old_prenom     = OLD.prenom, 
          new_prenom     = NEW.prenom;
    END
  ",

  "trigger insert deletion" => "
    DROP TRIGGER IF EXISTS medecin_insert;
  ",

  "trigger insert creation" => "
    CREATE TRIGGER medecin_insert AFTER INSERT ON medecin
    FOR EACH ROW
    BEGIN
        INSERT INTO medecin_trigger
        SET 
          datetime = NOW(),
          type = 'insert',
          old_medecin_id = NULL,
          new_medecin_id = NEW.medecin_id,
          old_nom        = NULL,
          new_nom        = NEW.nom,
          old_prenom     = NULL, 
          new_prenom     = NEW.prenom;
    END
  ",

  "trigger delete deletion" => "
    DROP TRIGGER IF EXISTS medecin_delete;
  ",

  "trigger delete creation" => "
    CREATE TRIGGER medecin_delete AFTER DELETE ON medecin
    FOR EACH ROW
    BEGIN
        INSERT INTO medecin_trigger
        SET 
          datetime = NOW(),
          type = 'insert',
          old_medecin_id = OLD.medecin_id,
          new_medecin_id = NULL,
          old_nom        = OLD.nom,
          new_nom        = NULL,
          old_prenom     = OLD.prenom, 
          new_prenom     = NULL;
    END
  ",

);

foreach ($queries as $_name => $_query) {
  if (false === $ds->exec($_query)) {
    CAppUI::stepAjax("install-mmp-error-query", UI_MSG_WARNING, $_name);
    CAppUI::stepAjax("install-mmp-failure", UI_MSG_ERROR);
  }

  CAppUI::stepAjax("install-mmp-success-query", UI_MSG_OK, $_name);
}

CAppUI::stepAjax("install-mmp-success-install", UI_MSG_OK, $_name);
