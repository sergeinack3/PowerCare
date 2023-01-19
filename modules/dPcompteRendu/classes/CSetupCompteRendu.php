<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;

/**
 * @codeCoverageIgnore
 */
class CSetupCompteRendu extends CSetup
{
    /**
     * Build an SQL query to replace a template string
     * Will check over content_html table to specify update query
     *
     * @param string $search              text to search
     * @param string $replace             text to replace
     * @param bool   $force_content_table Update content_html or compte_rendu table [optional]
     *
     * @return string The sql query
     */
    static function replaceTemplateQuery($search, $replace, $force_content_table = false)
    {
        static $_compte_rendu = null;
        static $_compte_rendu_content_id = null;

        $search  = CMbString::htmlEntities($search);
        $replace = CMbString::htmlEntities($replace);

        $ds = CSQLDataSource::get("std");

        if ($_compte_rendu === null || $_compte_rendu_content_id === null) {
            $_compte_rendu            = $ds->loadTable("compte_rendu") != null;
            $_compte_rendu_content_id = $_compte_rendu && $ds->loadField("compte_rendu", "content_id");
        }

        if (!$_compte_rendu) {
            return "SELECT 1;";
        }

        // Content specific table
        if ($force_content_table || ($_compte_rendu && $_compte_rendu_content_id)) {
            return "UPDATE compte_rendu AS cr, content_html AS ch
        SET ch.content = REPLACE(`content`, '$search', '$replace')
        WHERE cr.object_id IS NULL
        AND cr.content_id = ch.content_id";
        }

        // Single table
        return "UPDATE `compte_rendu` 
      SET `source` = REPLACE(`source`, '$search', '$replace') 
      WHERE `object_id` IS NULL";
    }

    /**
     * Build an SQL query to rename a template field
     * Will check over content_html table to specify update query
     *
     * @param string $oldname             text to search
     * @param string $newname             text to replace
     * @param bool   $force_content_table update content_html or compte_rendu table [optional]
     *
     * @return string The SQL Query
     */
    static function renameTemplateFieldQuery($oldname, $newname, $force_content_table = false)
    {
        return self::replaceTemplateQuery("[$oldname]", "[$newname]", $force_content_table);
    }

    /**
     * Insertion des modèles par référence pour les packs
     *
     * @return boolean
     */
    protected function setupAddmodeles()
    {
        $ds = $this->ds;

        $query = "SELECT * from pack;";
        $packs = $ds->loadList($query);

        foreach ($packs as $_pack) {
            if ($_pack['modeles'] == '') {
                continue;
            }
            $modeles = explode("|", $_pack['modeles']);
            if (count($modeles) == 0) {
                continue;
            }

            $compterendu = new CCompteRendu();
            foreach ($modeles as $_modele) {
                if (!$compterendu->load($_modele)) {
                    continue;
                }
                $query = "INSERT INTO modele_to_pack (modele_id, pack_id)
                  VALUES ($_modele, {$_pack['pack_id']})";
                $ds->exec($query);
            }
        }

        return true;
    }

    /**
     * @see parent::__construct()
     */
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPcompteRendu";

        $this->makeRevision("0.0");
        $query = "CREATE TABLE compte_rendu (
                compte_rendu_id BIGINT NOT NULL AUTO_INCREMENT ,
                chir_id BIGINT DEFAULT '0' NOT NULL ,
                nom VARCHAR(50) ,
                source TEXT,
                type ENUM('consultation', 'operation', 'hospitalisation', 'autre') DEFAULT 'autre' NOT NULL ,
                PRIMARY KEY (compte_rendu_id) ,
                INDEX (chir_id)
                ) /*! ENGINE=MyISAM */ COMMENT = 'Table des modeles de compte-rendu';";
        $this->addQuery($query);
        $query = "ALTER TABLE permissions
                CHANGE permission_grant_on permission_grant_on VARCHAR(25) NOT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.1");
        $query = "CREATE TABLE `aide_saisie` (
                `aide_id` INT NOT NULL AUTO_INCREMENT ,
                `user_id` INT NOT NULL ,
                `module` VARCHAR(20) NOT NULL ,
                `class` VARCHAR(20) NOT NULL ,
                `field` VARCHAR(20) NOT NULL ,
                `name` VARCHAR(40) NOT NULL ,
                `text` TEXT NOT NULL ,
                PRIMARY KEY (`aide_id`)) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.11");
        $query = "CREATE TABLE `liste_choix` (
                  `liste_choix_id` BIGINT NOT NULL AUTO_INCREMENT ,
                  `chir_id` BIGINT NOT NULL ,
                  `nom` VARCHAR(50) NOT NULL ,
                  `valeurs` TEXT,
                  PRIMARY KEY (`liste_choix_id`) ,
                  INDEX (`chir_id`)
                ) /*! ENGINE=MyISAM */ COMMENT = 'table des listes de choix personnalisées';";
        $this->addQuery($query);

        $this->makeRevision("0.12");
        $query = "CREATE TABLE `pack` (
                  `pack_id` BIGINT NOT NULL AUTO_INCREMENT ,
                  `chir_id` BIGINT NOT NULL ,
                  `nom` VARCHAR(50) NOT NULL ,
                  `modeles` TEXT,
                  PRIMARY KEY (`pack_id`) ,
                  INDEX (`chir_id`)
                ) /*! ENGINE=MyISAM */ COMMENT = 'table des packs post hospitalisation';";
        $this->addQuery($query);

        $this->makeRevision("0.13");
        $query = "ALTER TABLE `liste_choix` ADD `compte_rendu_id` BIGINT DEFAULT '0' NOT NULL AFTER `chir_id` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `liste_choix` ADD INDEX (`compte_rendu_id`) ;";
        $this->addQuery($query);

        $this->makeRevision("0.14");
        $query = "ALTER TABLE `compte_rendu` ADD `object_id` BIGINT DEFAULT NULL AFTER `chir_id` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `compte_rendu` ADD INDEX (`object_id`) ;";
        $this->addQuery($query);

        $this->makeRevision("0.15");
        $query = "ALTER TABLE `compte_rendu` ADD `valide` TINYINT DEFAULT 0;";
        $this->addQuery($query);

        $this->makeRevision("0.16");
        $query = "ALTER TABLE `compte_rendu` ADD `function_id` BIGINT DEFAULT NULL AFTER `chir_id` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `compte_rendu` ADD INDEX (`function_id`) ;";
        $this->addQuery($query);
        $query = " ALTER TABLE `compte_rendu` CHANGE `chir_id` `chir_id` BIGINT(20) DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.17");
        $query = "ALTER TABLE `liste_choix` ADD `function_id` BIGINT DEFAULT NULL AFTER `chir_id` ;";
        $this->addQuery($query);
        $query = "ALTER TABLE `liste_choix` ADD INDEX (`function_id`) ;";
        $this->addQuery($query);
        $query = " ALTER TABLE `liste_choix` CHANGE `chir_id` `chir_id` BIGINT(20) DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.18");
        $query = "ALTER TABLE `aide_saisie` DROP `module` ";
        $this->addQuery($query);

        $this->makeRevision("0.19");
        $query = "ALTER TABLE `compte_rendu`
      CHANGE `type` `type` ENUM('consultation', 'consultAnesth', 'operation', 'hospitalisation', 'autre') DEFAULT 'autre' NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.20");
        $query = "ALTER TABLE `compte_rendu`
      CHANGE `type` `type` ENUM('patient', 'consultation', 'consultAnesth', 'operation', 'hospitalisation', 'autre')
      DEFAULT 'autre' NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "UPDATE `aide_saisie` SET `class`=CONCAT(\"C\",`class`);";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `compte_rendu` CHANGE `type` `type`  VARCHAR(30) NOT NULL DEFAULT 'autre'";
        $this->addQuery($query);

        $this->makeRevision("0.23");
        $this->addDependency("dPfiles", "0.14");
        $query = "ALTER TABLE `compte_rendu` CHANGE `type` `object_class` VARCHAR(30) DEFAULT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `compte_rendu` ADD `file_category_id` INT(11) DEFAULT 0;";
        $this->addQuery($query);

        $aConversion = [
            "operation"       => ["class" => "COperation", "nom" => "Opération"],
            "hospitalisation" => ["class" => "COperation", "nom" => "Hospitalisation"],
            "consultation"    => ["class" => "CConsultation", "nom" => null],
            "consultAnesth"   => ["class" => "CConsultAnesth", "nom" => null],
            "patient"         => ["class" => "CPatient", "nom" => null],
        ];

        foreach ($aConversion as $sKey => $aValue) {
            $sClass = $aValue["class"];

            // Création de nouvelle catégories
            if ($sNom = $aValue["nom"]) {
                $query = "INSERT INTO files_category (`nom`,`class`) VALUES ('$sNom','$sClass')";
                $this->addQuery($query);
            }

            // Passage des types aux classes
            $query = "UPDATE `compte_rendu` SET `object_class`='$sClass' WHERE `object_class`='$sKey'";
            $this->addQuery($query);
        }


        $this->makeRevision("0.24");
        $query = "ALTER TABLE `aide_saisie` ADD `function_id` int(10) unsigned NULL AFTER `user_id` ;";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $query = "ALTER TABLE `aide_saisie` 
                CHANGE `aide_id` `aide_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `user_id` `user_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `function_id` `function_id` int(11) unsigned NULL,
                CHANGE `class` `class` varchar(255) NOT NULL,
                CHANGE `field` `field` varchar(255) NOT NULL,
                CHANGE `name` `name` varchar(255) NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `compte_rendu` 
                CHANGE `compte_rendu_id` `compte_rendu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `chir_id` `chir_id` int(11) unsigned NULL,
                CHANGE `function_id` `function_id` int(11) unsigned NULL,
                CHANGE `object_id` `object_id` int(11) unsigned NULL,
                CHANGE `nom` `nom` varchar(255) NOT NULL,
                CHANGE `source` `source` mediumtext NULL,
                CHANGE `object_class` `object_class` enum('CPatient','CConsultAnesth','COperation','CConsultation')
                  NOT NULL DEFAULT 'CPatient',
                CHANGE `valide` `valide` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
                CHANGE `file_category_id` `file_category_id` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `liste_choix` 
                CHANGE `liste_choix_id` `liste_choix_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `chir_id` `chir_id` int(11) unsigned NULL,
                CHANGE `function_id` `function_id` int(11) unsigned NULL,
                CHANGE `nom` `nom` varchar(255) NOT NULL,
                CHANGE `compte_rendu_id` `compte_rendu_id` int(11) unsigned NOT NULL DEFAULT '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `pack` 
                CHANGE `pack_id` `pack_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `chir_id` `chir_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `nom` `nom` varchar(255) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.26");
        $query = "ALTER TABLE `compte_rendu` ADD INDEX ( `object_class` );";
        $this->addQuery($query);
        $query = "ALTER TABLE `compte_rendu` ADD INDEX ( `file_category_id` );";
        $this->addQuery($query);

        $this->makeRevision("0.27");
        $query = "UPDATE `liste_choix` SET function_id = NULL WHERE function_id='0';";
        $this->addQuery($query);
        $query = "UPDATE `liste_choix` SET chir_id = NULL WHERE chir_id='0';";
        $this->addQuery($query);

        $this->makeRevision("0.28");
        $query = "DELETE FROM `pack` WHERE chir_id='0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `compte_rendu` CHANGE `file_category_id` `file_category_id` int(11) unsigned NULL DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `compte_rendu` SET `file_category_id` = NULL WHERE `file_category_id` = '0';";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "UPDATE `compte_rendu` SET `function_id` = NULL WHERE `function_id` = '0';";
        $this->addQuery($query);
        $query = "UPDATE `compte_rendu` SET `chir_id` = NULL WHERE `chir_id` = '0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `liste_choix` CHANGE `compte_rendu_id` `compte_rendu_id` int(11) unsigned NULL DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `liste_choix` SET compte_rendu_id = NULL WHERE compte_rendu_id='0';";
        $this->addQuery($query);
        $query = "ALTER TABLE `aide_saisie` CHANGE `user_id` `user_id` int(11) unsigned NULL DEFAULT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `aide_saisie` SET function_id = NULL WHERE function_id='0';";
        $this->addQuery($query);
        $query = "UPDATE `aide_saisie` SET user_id = NULL WHERE user_id='0';";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "ALTER TABLE `aide_saisie` ADD `depend_value` varchar(255) DEFAULT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `compte_rendu`
      CHANGE `object_class` `object_class` ENUM('CPatient','CConsultAnesth','COperation','CConsultation','CSejour') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `pack`
      ADD `object_class` ENUM('CPatient','CConsultAnesth','COperation','CConsultation','CSejour') NOT NULL DEFAULT 'COperation';";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "UPDATE aide_saisie
      SET `depend_value` = `class`,
          `class` = 'CCompteRendu',
          `field` = 'source'
      WHERE `field` = 'compte_rendu';";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `compte_rendu` 
      ADD `type` ENUM ('header','body','footer'),
      CHANGE `valide` `valide` ENUM ('0','1'),
      ADD `header_id` INT (11) UNSIGNED,
      ADD `footer_id` INT (11) UNSIGNED,
      ADD INDEX (`header_id`),
      ADD INDEX (`footer_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.35");
        $query = "UPDATE `compte_rendu` 
      SET `type` = 'body'
      WHERE `object_id` IS NULL";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $query = "UPDATE `compte_rendu` 
      SET `object_class` = 'CSejour'
      WHERE `file_category_id` = 3
      AND `object_class` = 'COperation'
      AND `object_id` IS NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.37");
        $query = "ALTER TABLE `compte_rendu` 
      ADD `height` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision("0.38");
        $query = "ALTER TABLE `compte_rendu` 
      ADD `group_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu` 
      ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.39");
        $this->addPrefQuery("saveOnPrint", 1);

        $this->makeRevision("0.40");
        $query = "UPDATE `compte_rendu` 
      SET `source` = REPLACE(`source`, '<br style=\"page-break-after: always;\" />', '<hr class=\"pagebreak\" />')";
        // attention: dans le code source de la classe Cpack, on a :
        // <br style='page-break-after:always' />
        // Mais FCKeditor le transforme en :
        // <br style="page-break-after: always;" />
        // Apres verification, c'est toujours comme ça quil a transformé, donc c'est OK.
        $this->addQuery($query);

        if (CModule::getInstalled('dPcabinet') && CModule::getInstalled('dPpatients')) {
            $this->addDependency("dPcabinet", "0.79");
            $this->addDependency("dPpatients", "0.73");
        }

        $this->makeRevision("0.41");
        $query = "ALTER TABLE `aide_saisie` 
            CHANGE `depend_value` `depend_value_1` VARCHAR (255),
            ADD `depend_value_2` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.42");
        $query = "ALTER TABLE `compte_rendu` 
            ADD `etat_envoi` ENUM ('oui','non','obsolete') NOT NULL default 'non';";
        $this->addQuery($query);

        $this->makeRevision("0.43");
        $query = "ALTER TABLE `compte_rendu` 
    CHANGE `object_class` `object_class` ENUM ('CPatient','CConsultation','CConsultAnesth','COperation','CSejour','CPrescription')
    NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.44");
        $query = "ALTER TABLE `compte_rendu` 
            CHANGE `object_class` `object_class` VARCHAR (80) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.45");
        $query = "ALTER TABLE `liste_choix` ADD `group_id` INT (11) UNSIGNED";
        $this->addQuery($query);
        $query = "ALTER TABLE `liste_choix` ADD INDEX (`group_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.46");
        $query = "ALTER TABLE `aide_saisie` ADD `group_id` INT (11) UNSIGNED AFTER `function_id`";
        $this->addQuery($query);

        $query = "ALTER TABLE `aide_saisie` 
              ADD INDEX (`user_id`),
              ADD INDEX (`function_id`),
              ADD INDEX (`group_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.47");
        $query = self::renameTemplateFieldQuery(
            "Opération - personnel prévu - Panseuse",
            "Opération - personnel prévu - Panseur"
        );
        $this->addQuery($query);
        $query = self::renameTemplateFieldQuery(
            "Opération - personnel réel - Panseuse",
            "Opération - personnel réel - Panseur"
        );
        $this->addQuery($query);

        $this->makeRevision("0.48");
        $query = "ALTER TABLE `pack` 
              ADD `function_id` INT (11) UNSIGNED,
              ADD `group_id` INT (11) UNSIGNED,
              ADD INDEX (`function_id`),
              ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `pack` 
              CHANGE `chir_id` `chir_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.49");
        $query = "ALTER TABLE `compte_rendu` 
              ADD `margin_top`    FLOAT UNSIGNED NOT NULL DEFAULT '2',
              ADD `margin_bottom` FLOAT UNSIGNED NOT NULL DEFAULT '2',
              ADD `margin_left`   FLOAT UNSIGNED NOT NULL DEFAULT '2',
              ADD `margin_right`  FLOAT UNSIGNED NOT NULL DEFAULT '2',
              ADD `page_height`   FLOAT UNSIGNED NOT NULL DEFAULT '29.7',
              ADD `page_width`    FLOAT UNSIGNED NOT NULL DEFAULT '21'";
        $this->addQuery($query);

        $this->makeRevision("0.50");
        $query = "ALTER TABLE `compte_rendu` 
              ADD `private` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.51");
        $this->addPrefQuery("choicepratcab", "prat");

        $this->makeRevision("0.52");

        $query = "INSERT INTO content_html (content, cr_id) SELECT source, compte_rendu_id FROM compte_rendu";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu` DROP `source`";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu` 
              ADD `content_id` INT (11) UNSIGNED";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu` 
              ADD INDEX (`content_id`);";
        $this->addQuery($query);

        $query = "UPDATE compte_rendu c JOIN content_html ch ON c.compte_rendu_id = ch.cr_id
            SET c.content_id = ch.content_id";
        $this->addQuery($query);

        $query = "ALTER TABLE `content_html` DROP `cr_id`";
        $this->addQuery($query);

        $this->makeRevision("0.53");

        // Déplacement du contenthtml dans system

        $this->makeRevision("0.54");

        $query = "ALTER TABLE `compte_rendu`
            ADD `fast_edit` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `aide_saisie` SET `field` = '_source' WHERE `class` = 'CCompteRendu' AND `field` = 'source'";
        $this->addQuery($query);

        $this->makeRevision("0.55");

        $query = "CREATE TABLE `modele_to_pack` (
              `modele_to_pack_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `modele_id` INT (11) UNSIGNED,
              `pack_id` INT (11) UNSIGNED
           ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `modele_to_pack` 
              ADD INDEX (`modele_id`),
              ADD INDEX (`pack_id`);
           ";
        $this->addQuery($query);


        $this->addMethod("setupAddmodeles");

        $this->makeRevision("0.56");

        $query = "ALTER TABLE `pack`
              DROP `modeles`";
        $this->addQuery($query);

        $this->makeRevision("0.57");

        // Modification des user logs, seulement pour ceux qui ne font
        // reference qu'au champ "source" des compte rendus. Dans le cas où d'autres
        // champs sont listés, il faudrait splitter le user_log en deux :
        // un pour le CR et un pour le ContentHTML
        // Actions effectuées :
        // - remplacement de source par content
        // - remplacement de CCompteRendu par CContentHTML
        // - mise à jour de l'object_id
        $query = "
    UPDATE user_log 
    LEFT JOIN compte_rendu ON compte_rendu.compte_rendu_id = user_log.object_id
    LEFT JOIN content_html ON content_html.content_id = compte_rendu.content_id
    
    SET 
      user_log.object_class = 'CContentHTML',
      user_log.object_id = compte_rendu.content_id,
      user_log.fields = 'content'
      
    WHERE user_log.object_class = 'CCompteRendu'
      AND user_log.object_id = compte_rendu.compte_rendu_id
      AND user_log.fields = 'source'";
        $this->addQuery($query);

        $this->makeRevision("0.58");
        $this->addPrefQuery("listDefault", "ulli");
        $this->addPrefQuery("listBrPrefix", "&bull;");
        $this->addPrefQuery("listInlineSeparator", ";");

        $this->makeRevision("0.59");
        $query = "ALTER TABLE `compte_rendu`
            ADD `fast_edit_pdf` ENUM ('0','1') NOT NULL DEFAULT '0'";
        $this->addQuery($query);

        $query = self::replaceTemplateQuery("-- tous]", "- tous]", true);
        $this->addQuery($query);

        $query = self::replaceTemplateQuery("-- tous par appareil]", "- tous par appareil]", true);
        $this->addQuery($query);

        $query = self::replaceTemplateQuery("[Constantes mode", "[Constantes - mode", true);
        $this->addQuery($query);

        $this->makeRevision("0.60");
        $query = self::replaceTemplateQuery(
            "[Patient - médecin correspondants]",
            "[Patient - médecins correspondants]",
            true
        );
        $this->addQuery($query);

        $this->makeRevision("0.61");
        $this->addPrefQuery("aideTimestamp", "1");
        $this->addPrefQuery("aideOwner", "0");
        $this->addPrefQuery("aideFastMode", "1");
        $this->addPrefQuery("aideAutoComplete", "1");
        $this->addPrefQuery("aideShowOver", "1");

        $this->makeRevision("0.62");
        $this->addPrefQuery("mode_play", "0");

        $this->makeRevision("0.63");
        $query = "ALTER TABLE `compte_rendu`
              CHANGE chir_id user_id INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `pack`
              CHANGE chir_id user_id INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `liste_choix`
              CHANGE chir_id user_id INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("0.64");
        $query = "ALTER TABLE `pack`
              ADD `fast_edit` ENUM ('0','1') NOT NULL DEFAULT '0',
              ADD `fast_edit_pdf` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.65");
        $query = self::replaceTemplateQuery("[RPU - Mode", "[Sejour - Mode", true);

        $this->addQuery($query);

        $this->makeRevision("0.66");
        // Table consultation_anesth
        $this->addDependency("dPcabinet", "0.31");
        // Table sejour
        $this->addDependency("dPplanningOp", "0.37");

        $query = "ALTER TABLE `compte_rendu`
      ADD `author_id` INT(11) UNSIGNED AFTER `function_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu`
      ADD INDEX (`author_id`);";
        $this->addQuery($query);

        // Table temporaire de mappage entre le author_id (user_id du first log) et le compte_rendu_id
        $query = "CREATE TEMPORARY TABLE `owner_doc` (
      `compte_rendu_id` INT(11), `author_id` INT(11)) AS
      SELECT `compte_rendu_id`, `user_log`.`user_id` as `author_id`
      FROM `compte_rendu`, `user_log`
      WHERE `user_log`.`object_class` = 'CCompteRendu'
      AND `user_log`.`object_id` = `compte_rendu`.`compte_rendu_id`
      AND `user_log`.`type` = 'create';";
        $this->addQuery($query);

        $query = "UPDATE `compte_rendu`
      JOIN `owner_doc` ON `compte_rendu`.`compte_rendu_id` = `owner_doc`.`compte_rendu_id`
      SET `compte_rendu`.`author_id` = `owner_doc`.`author_id`;";
        $this->addQuery($query);

        // Mise à jour les compte-rendus de consultation
        $query = "UPDATE `compte_rendu`
      SET `author_id` =
          (
           SELECT `chir_id`
           FROM `plageconsult`
           LEFT JOIN `consultation` ON `consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`
           WHERE `consultation`.`consultation_id` = `compte_rendu`.`object_id`
           LIMIT 1
          )
      WHERE `author_id` IS NULL
      AND `compte_rendu`.`object_class` = 'CConsultation'
      AND `compte_rendu`.`object_id` IS NOT NULL;";
        $this->addQuery($query);

        // Pour les consultations d'anesthésie rattachées à une opération
        $query = "UPDATE `compte_rendu`
      SET `author_id` =
        (
          SELECT `operations`.`chir_id`
          FROM `consultation_anesth`
          LEFT JOIN `operations` ON `operations`.`operation_id` = `consultation_anesth`.`operation_id`
          WHERE `consultation_anesth`.`consultation_anesth_id` = `compte_rendu`.`object_id`
          LIMIT 1
        )
      WHERE `author_id` IS NULL
      AND `compte_rendu`.`object_class` = 'CConsultAnesth'
      AND `compte_rendu`.`object_id` IS NOT NULL;";
        $this->addQuery($query);

        // Ou non
        $query = "UPDATE `compte_rendu`
      SET `author_id` =
        (
          SELECT `plageconsult`.`chir_id`
          FROM `consultation_anesth`
          LEFT JOIN `consultation` ON `consultation`.`consultation_id` = `consultation_anesth`.`consultation_id`
          LEFT JOIN `plageconsult` ON `plageconsult`.`plageconsult_id` = `consultation`.`plageconsult_id`
          WHERE `consultation_anesth`.`consultation_anesth_id` = `compte_rendu`.`object_id`
          LIMIT 1
        )
      WHERE `author_id` IS NULL
      AND `compte_rendu`.`object_class` = 'CConsultAnesth'
      AND `compte_rendu`.`object_id` IS NOT NULL;";
        $this->addQuery($query);

        // Pour les opérations
        $query = "UPDATE `compte_rendu`
      SET `author_id` =
        (
          SELECT `chir_id`
          FROM `operations`
          WHERE `operations`.`operation_id` = `compte_rendu`.`object_id`
          LIMIT 1
        )
      WHERE `author_id` IS NULL
      AND `compte_rendu`.`object_class` = 'COperation'
      AND `compte_rendu`.`object_id` IS NOT NULL;";
        $this->addQuery($query);

        // Pour les séjours
        $query = "UPDATE `compte_rendu`
      SET `author_id` =
        (
          SELECT `praticien_id`
          FROM `sejour`
          WHERE `sejour`.`sejour_id` = `compte_rendu`.`object_id`
          LIMIT 1
        )
      WHERE `author_id` IS NULL
      AND `compte_rendu`.`object_class` = 'CSejour'
      AND `compte_rendu`.`object_id` IS NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.67");
        $query = "ALTER TABLE `compte_rendu`
      ADD date_print DATETIME DEFAULT NULL";
        $this->addQuery($query);

        $this->makeRevision("0.68");
        $this->addPrefQuery("choice_factory", "CDomPDFConverter");

        $this->makeRevision("0.69");
        $query = "CREATE TABLE `correspondant_courrier` (
              `correspondant_courrier_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `compte_rendu_id` INT (11) UNSIGNED NOT NULL,
              `nom` VARCHAR (255),
              `adresse` TEXT,
              `cp_ville` VARCHAR (255),
              `email` VARCHAR (255),
              `active` ENUM ('0','1') DEFAULT '0',
              `tag` VARCHAR (255), 
              `object_class` VARCHAR (255)
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_courrier` 
              ADD INDEX (`compte_rendu_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.70");
        $query = "ALTER TABLE `correspondant_courrier`
      DROP `nom`,
      DROP `adresse`,
      DROP `cp_ville`,
      DROP `email`,
      DROP `active`,
      DROP `object_class`,
      ADD `object_guid` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.71");
        $query = "ALTER TABLE `correspondant_courrier`
      ADD `object_class` ENUM ('CMedecin','CPatient','CCorrespondantPatient') NOT NULL,
      ADD `object_id` INT (11) UNSIGNED NOT NULL;";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_courrier` 
      ADD INDEX (`object_id`);";
        $this->addQuery($query);

        $query = "UPDATE `correspondant_courrier`
      SET `object_class` = SUBSTRING_INDEX(`object_guid`, '-', 1),
          `object_id` = SUBSTR(`object_guid`, LOCATE('-', `object_guid`, 2)+1);";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_courrier`
      DROP `object_guid`";
        $this->addQuery($query);

        $this->makeRevision("0.72");
        $query = self::replaceTemplateQuery("[Courrier - copie à", "[Courrier - copie à - simple", true);
        $this->addQuery($query);

        $query = self::replaceTemplateQuery("[Courrier - copie à (complet)", "[Courrier - copie à - complet", true);
        $this->addQuery($query);

        $this->makeRevision("0.73");
        $query = "ALTER TABLE `correspondant_courrier` 
      ADD `quantite` INT (11) UNSIGNED NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.74");
        $this->addPrefQuery("multiple_docs", 0);

        $this->makeRevision("0.75");
        $query = "ALTER TABLE `compte_rendu`
      ADD `purge_field` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.76");
        $query = self::replaceTemplateQuery("[Patient - âge", "[Patient - années", true);
        $this->addQuery($query);

        $this->makeRevision("0.77");
        $query = "ALTER TABLE `pack` 
      ADD `merge_docs` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.78");

        $this->addPrefQuery("auto_capitalize", 0);

        $this->makeRevision("0.79");
        $query = "ALTER TABLE `compte_rendu` 
      ADD `modele_id` INT (11) UNSIGNED AFTER `object_id`,
      ADD `purgeable` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu`
      ADD INDEX (`modele_id`),
      ADD INDEX (`date_print`);";
        $this->addQuery($query);

        $this->makeRevision("0.80");
        $query = "ALTER TABLE `compte_rendu`
      CHANGE `type` `type` ENUM ('header','preface','body','ending','footer') DEFAULT 'body', 
      ADD `preface_id` INT (11) UNSIGNED AFTER `header_id`,
      ADD `ending_id` INT (11) UNSIGNED AFTER `preface_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu` 
      ADD INDEX (`preface_id`),
      ADD INDEX (`ending_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.81");
        $query = "ALTER TABLE `compte_rendu`
      ADD `fields_missing` INT (11) UNSIGNED DEFAULT 0";
        $this->addQuery($query);

        $this->makeRevision("0.82");
        $this->addPrefQuery("default_font", "");

        $this->addPrefQuery("default_size", "");

        $this->makeRevision("0.83");
        $this->delPrefQuery("default_font");
        $this->delPrefQuery("default_size");

        $query = "ALTER TABLE `compte_rendu` 
      ADD `font` ENUM ('arial','comic','courier','georgia','lucida','tahoma','times','trebuchet','verdana') AFTER `object_class`,
      ADD `size` ENUM ('xx-small','x-small','small','medium','large','x-large','xx-large',
                       '8pt','9pt','10pt','11pt','12pt','14pt','16pt','18pt','20pt','22pt','24pt','26pt','28pt','36pt','48pt','72pt')
      AFTER `font`";
        $this->addQuery($query);

        $this->makeRevision("0.84");
        $query = self::replaceTemplateQuery("[Patient - Il/Elle", "[Patient - Il/Elle (majuscule)", true);
        $this->addQuery($query);

        $query = self::replaceTemplateQuery("[Patient - Le/La", "[Patient - Le/La (majuscule)", true);
        $this->addQuery($query);

        $this->makeRevision("0.85");
        $query = "ALTER TABLE `compte_rendu`
      CHANGE `font` `font` ENUM ('arial','calibri','comic','courier','georgia','lucida','tahoma','times','trebuchet','verdana')";
        $this->addQuery($query);

        $this->makeRevision("0.86");
        $query = "ALTER TABLE `compte_rendu`
      CHANGE `font` `font` ENUM ('arial','calibri','comic','courier','georgia','lucida','symbol','tahoma',
                                 'times','trebuchet','verdana','zapfdingbats')";
        $this->addQuery($query);

        $this->makeRevision("0.87");
        // Table temporaire de mappage entre le author_id (user_id du first log) et le compte_rendu_id
        // Les compte-rendus affectés sont principalement ceux en édition rapide
        $query = "CREATE TEMPORARY TABLE IF NOT EXISTS `owner_doc` (
      `compte_rendu_id` INT(11), `author_id` INT(11)) AS
      SELECT `compte_rendu_id`, `user_log`.`user_id` as `author_id`
      FROM `compte_rendu`, `user_log`
      WHERE `user_log`.`object_class` = 'CCompteRendu'
      AND `compte_rendu`.`author_id` IS NULL
      AND `user_log`.`object_id` = `compte_rendu`.`compte_rendu_id`
      AND `user_log`.`type` = 'create';";
        $this->addQuery($query);

        $query = "UPDATE `compte_rendu`
      JOIN `owner_doc` ON `compte_rendu`.`compte_rendu_id` = `owner_doc`.`compte_rendu_id`
      SET `compte_rendu`.`author_id` = `owner_doc`.`author_id`;";
        $this->addQuery($query);

        $this->makeRevision("0.88");
        $query = "UPDATE `compte_rendu`
      SET `nom` = '[ENTETE ORDONNANCE]'
      WHERE `object_class` = 'CPrescription'
      AND `type` = 'header'";
        $this->addQuery($query);

        $query = "UPDATE `compte_rendu`
      SET `nom` = '[PIED DE PAGE ORDONNANCE]'
      WHERE `object_class` = 'CPrescription'
      AND `type` = 'footer'";
        $this->addQuery($query);

        $this->makeRevision("0.89");
        $query = self::replaceTemplateQuery(
            "[Patient - Antécédents - Autres",
            "[Patient - Antécédents - Autres (type)",
            true
        );
        $this->addQuery($query);

        $this->makeRevision("0.90");
        $query = "ALTER TABLE `compte_rendu`
                ADD `version` INT (11) DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.91");
        $this->addPrefQuery("auto_replacehelper", 0);

        $this->makeRevision("0.92");

        if (CModule::getActive("dPprescription")) {
            $query = "UPDATE aide_saisie
        JOIN category_prescription ON category_prescription.nom = aide_saisie.depend_value_2
        SET aide_saisie.depend_value_2 = category_prescription.category_prescription_id
        WHERE category_prescription_id IS NOT NULL";
            $this->addQuery($query);
        }

        $this->makeRevision("0.93");
        $query = "ALTER TABLE `compte_rendu`
      ADD `language` ENUM ('en-EN','es-ES','fr-CH','fr-FR') DEFAULT 'fr-FR' AFTER modele_id";
        $this->addQuery($query);

        $this->makeRevision("0.94");
        $query = "ALTER TABLE `compte_rendu`
      ADD `locker_id` INT(11) UNSIGNED AFTER `author_id`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu`
      ADD INDEX (`locker_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.95");
        $query = "ALTER TABLE `compte_rendu`
      ADD `type_doc` VARCHAR(128);";
        $this->addQuery($query);

        $this->makeRevision("0.96");
        $this->addPrefQuery("pass_lock", 0);

        $this->makeRevision('0.97');

        $this->addPrefQuery('hprim_med_header', 0);

        $this->makeRevision("0.98");
        if (CModule::getActive("dPprescription")) {
            $query = "UPDATE aide_saisie
        JOIN element_prescription ON element_prescription.libelle = aide_saisie.depend_value_1
        SET aide_saisie.depend_value_1 = element_prescription.element_prescription_id
        WHERE element_prescription_id IS NOT NULL
        AND aide_saisie.class = 'CPrescriptionLineElement'";
            $this->addQuery($query);
        }

        $this->makeRevision("0.99");
        $query = "ALTER TABLE `compte_rendu`
      ADD `factory` ENUM ('none', 'CDomPDFConverter', 'CWkHtmlToPDFConverter', 'CPrinceXMLConverter');";
        $this->addQuery($query);

        $this->makeRevision("1.00");
        $query = "ALTER TABLE `compte_rendu`
                ADD `type_doc_sisra` VARCHAR(10);";
        $this->addQuery($query);

        $this->makeRevision("1.01");
        $this->addPrefQuery("show_old_print", 1);

        $this->makeRevision("1.02");
        $query = "ALTER TABLE `compte_rendu`
                ADD `creation_date` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.03");
        $query = "ALTER TABLE `compte_rendu`
      ADD `annule` ENUM ('0','1') DEFAULT '0' AFTER `modele_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.04");
        $query = "ALTER TABLE `compte_rendu`
      ADD `doc_size` INT (11) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `compte_rendu`
      JOIN `content_html` ON `content_html`.`content_id` = `compte_rendu`.`content_id`
      SET `compte_rendu`.`doc_size` = LENGTH(`content_html`.`content`)";
        $this->addQuery($query);

        $this->makeRevision("1.05");

        $this->addPrefQuery("send_document_subject", CAppUI::tr("CCompteRendu.default_mail_subject"));
        $this->addPrefQuery("send_document_body", CAppUI::tr("CCompteRendu.default_mail_body"));

        $this->makeRevision("1.06");
        $multiple_doc_correspondants = @CAppUI::conf("dPcompteRendu CCompteRendu multiple_doc_correspondants");
        $this->addPrefQuery(
            "multiple_doc_correspondants",
            $multiple_doc_correspondants === null ? 0 : $multiple_doc_correspondants
        );

        $this->makeRevision("1.07");

        $query = self::replaceTemplateQuery(
            "[Patient - Grossesse - Fausse couche",
            "[Patient - Grossesse - Nombre de semaines",
            true
        );
        $this->addQuery($query);

        $this->makeRevision("1.08");

        $query = "ALTER TABLE `compte_rendu`
      ADD INDEX (purgeable);";
        $this->addQuery($query);
        $this->makeRevision("1.09");

        $this->addPrefQuery("show_creation_date", 0);
        $this->makeRevision("1.10");

        $this->addPrefQuery("secure_signature", 0);
        $this->makeRevision("1.11");

        $query = "ALTER TABLE `compte_rendu` 
      ADD `signataire_id` INT (11) UNSIGNED,
      ADD `signature_mandatory` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("1.12");

        $query = "ALTER TABLE `compte_rendu`
      ADD INDEX `signataire_id`(`signataire_id`);";
        $this->addQuery($query, true);
        $this->makeRevision("1.13");

        $query = "ALTER TABLE `compte_rendu`
      ADD INDEX `creation_date`(`creation_date`);";
        $this->addQuery($query, true);
        $this->makeRevision("1.14");

        $query = "ALTER TABLE `compte_rendu`
      ADD `alert_creation` ENUM ('0','1') DEFAULT '0',
      ADD INDEX (`alert_creation`);";
        $this->addQuery($query);
        $this->makeRevision("1.15");

        $query = self::replaceTemplateQuery("[Patient - nom jeune fille", "[Patient - nom de naissance", true);
        $this->addQuery($query);

        $query = self::replaceTemplateQuery(
            "[Prescription Pré-admission - Dérivés sanguins",
            "Prescription Pré-admission - PSL",
            true
        );
        $this->addQuery($query);

        $query = self::replaceTemplateQuery(
            "[Prescription Séjour - Dérivés sanguins",
            "[Prescription Séjour - PSL",
            true
        );
        $this->addQuery($query);

        $query = self::replaceTemplateQuery(
            "[Prescription Sortie - Dérivés sanguins",
            "[Prescription Sortie - PSL",
            true
        );
        $this->addQuery($query);
        $this->makeRevision("1.16");

        $check_to_empty_field = @CAppUI::conf("dPcompteRendu CCompteRendu check_to_empty_field");
        $this->addPrefQuery("check_to_empty_field", $check_to_empty_field !== null ? $check_to_empty_field : "1");
        $this->makeRevision("1.17");

        $query = "ALTER TABLE `compte_rendu`
      ADD `remis_patient` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("1.18");

        $query = "ALTER TABLE `compte_rendu` 
                ADD `signe` ENUM ('0','1');";
        $this->addQuery($query);
        $this->makeRevision("1.19");

        $query = "ALTER TABLE `compte_rendu` 
      ADD `actif` ENUM ('0','1') DEFAULT '1' AFTER `nom`;";
        $this->addQuery($query);

        $this->makeRevision("1.20");
        $query = "CREATE TABLE `whitelist` (
      `whitelist_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
      `email` VARCHAR (50) NOT NULL,
      `actif` ENUM ('0','1') DEFAULT '1'
    )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("1.21");

        $this->addPrefQuery("time_autosave", "0");

        $this->makeRevision("1.22");

        $query = "ALTER TABLE `whitelist` 
      ADD `group_id` INT (11) UNSIGNED AFTER `email`;";
        $this->addQuery($query);

        $this->makeRevision("1.23");

        $this->addPrefQuery("show_favorites", 0);

        $this->makeRevision("1.24");

        $this->addDefaultConfig(
            "dPcompteRendu CCompteRenduPrint pdf_thumbnails",
            "dPcompteRendu CCompteRendu pdf_thumbnails"
        );
        $this->addDefaultConfig("dPcompteRendu CCompteRenduPrint same_print", "dPcompteRendu CCompteRendu same_print");
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRenduPrint time_before_thumbs",
            "dPcompteRendu CCompteRendu time_before_thumbs"
        );
        $this->addDefaultConfig("dPcompteRendu CCompteRendu default_size", "dPcompteRendu CCompteRendu default_size");
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRendu header_footer_fly",
            "dPcompteRendu CCompteRendu header_footer_fly"
        );
        $this->addDefaultConfig("dPcompteRendu CCompteRendu clean_word", "dPcompteRendu CCompteRendu clean_word");
        $this->addDefaultConfig("dPcompteRendu CCompteRendu dompdf_host", "dPcompteRendu CCompteRendu dompdf_host");
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRendu days_to_lock",
            "dPcompteRendu CCompteRendu days_to_lock base"
        );
        $this->addDefaultConfig("dPcompteRendu CCompteRendu pass_lock", "dPcompteRendu CCompteRendu pass_lock");
        $this->addDefaultConfig("dPcompteRendu CCompteRendu unlock_doc", "dPcompteRendu CCompteRendu unlock_doc");
        $this->addDefaultConfig("dPcompteRendu CCompteRendu shrink_pdf", "dPcompteRendu CCompteRendu shrink_pdf");
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRendu purge_lifetime",
            "dPcompteRendu CCompteRendu purge_lifetime"
        );
        $this->addDefaultConfig("dPcompteRendu CCompteRendu purge_limit", "dPcompteRendu CCompteRendu purge_limit");
        $this->addDefaultConfig("dPcompteRendu CCompteRendu align_table", "dPcompteRendu CCompteRendu align_table");
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRendu private_owner_func",
            "dPcompteRendu CCompteRendu private_owner_func"
        );
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRendu probability_regenerate",
            "dPcompteRendu CCompteRendu probability_regenerate"
        );
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRenduAcces access_group",
            "dPcompteRendu CCompteRendu access_group"
        );
        $this->addDefaultConfig(
            "dPcompteRendu CCompteRenduAcces access_function",
            "dPcompteRendu CCompteRendu access_function"
        );
        $this->addDefaultConfig("dPcompteRendu CAideSaisie access_group", "dPcompteRendu CAideSaisie access_group");
        $this->addDefaultConfig(
            "dPcompteRendu CAideSaisie access_function",
            "dPcompteRendu CAideSaisie access_function"
        );
        $this->addDefaultConfig("dPcompteRendu CListeChoix access_group", "dPcompteRendu CListeChoix access_group");
        $this->addDefaultConfig(
            "dPcompteRendu CListeChoix access_function",
            "dPcompteRendu CListeChoix access_function"
        );

        $this->makeRevision("1.25");

        $query = "ALTER TABLE `compte_rendu`
                CHANGE `type_doc` `type_doc_dmp` VARCHAR(128);";
        $this->addQuery($query);

        $this->makeEmptyRevision("1.26");

        /*$query = "ALTER TABLE `compte_rendu`
                    ADD INDEX object (object_class, object_id)";
        $this->addQuery($query);

        $query = "ALTER TABLE `correspondant_courrier`
                    ADD INDEX object (object_class, object_id);";
        $this->addQuery($query);

        $query = "ALTER TABLE `whitelist`
                    ADD INDEX (`group_id`);";
        $this->addQuery($query);*/

        $this->makeRevision("1.27");

        $query = "ALTER TABLE `compte_rendu`
                ADD `nb_print` INT (11) UNSIGNED DEFAULT '1' AFTER `size`;";
        $this->addQuery($query);

        $this->makeRevision("1.28");

        $query = self::replaceTemplateQuery("[Opération - pose garrot", "[Opération - gonflage garrot", true);
        $this->addQuery($query);

        $this->makeRevision("1.29");

        $query = "ALTER TABLE `compte_rendu`
                ADD `send` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.30");

        $query = self::replaceTemplateQuery("[Consultation - conclusion", "[Consultation - au total", true);
        $this->addQuery($query);

        $this->makeRevision("1.31");
        $this->setModuleCategory("parametrage", "metier");

        $this->makeRevision("1.32");

        $query = "ALTER TABLE `compte_rendu`
                ADD `printer_id` INT (11) UNSIGNED AFTER `signataire_id`;";
        $this->addQuery($query);

        $this->makeRevision("1.33");

        $query = "ALTER TABLE `pack` 
                ADD `category_id` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $this->makeRevision("1.34");

        $query = "ALTER TABLE `compte_rendu`
      CHANGE `font` `font` ENUM ('arial','calibri','carlito','comic','courier','georgia','lucida','tahoma','times','trebuchet','verdana');";
        $this->addQuery($query);

        $query = "UPDATE `compte_rendu`
      SET font = 'carlito'
      WHERE font = 'calibri';";
        $this->addQuery($query);

        $query = "ALTER TABLE `compte_rendu`
      CHANGE `font` `font` ENUM ('arial','carlito','comic','courier','georgia','lucida','tahoma','times','trebuchet','verdana');";
        $this->addQuery($query);

        $query = self::replaceTemplateQuery("calibri", "carlito", true);
        $this->addQuery($query);

        $query = "UPDATE `config_db`
      SET `value` = REPLACE(`value`, 'Calibri/Calibri', 'Carlito/Carlito')
      WHERE `key` = 'dPcompteRendu CCompteRendu default_fonts'";
        $this->addQuery($query);

        $this->makeRevision("1.35");

        $query = "ALTER TABLE `pack`
        ADD `is_eligible_selection_document` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `modele_to_pack` 
        ADD `is_selected` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.36");

        $query = "UPDATE `user_preferences`
                SET `value` = '1'
                WHERE `key` = 'show_old_print' 
                AND `user_id` IS NULL;";
        $this->addQuery($query);

        $this->makeRevision("1.37");

        $query = "UPDATE `user_preferences`
                SET `value` = '0'
                WHERE `key` = 'show_old_print' 
                AND `user_id` IS NULL;";
        $this->addQuery($query);

        $this->makeRevision('1.38');

        $query = "ALTER TABLE `compte_rendu`
                ADD `duree_lecture` INT (11) UNSIGNED DEFAULT '0',
                ADD `duree_ecriture` INT (11) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('1.39');

        $query = "ALTER TABLE `pack` 
                    MODIFY `object_class`
                        ENUM ('CPatient','CConsultAnesth','COperation','CConsultation','CSejour','CEvenementPatient')
                        NOT NULL DEFAULT 'COperation';";
        $this->addQuery($query);

        $this->makeRevision('1.40');

        $query = "ALTER TABLE `correspondant_courrier`
                    CHANGE `object_class` `object_class`
                        ENUM ('CMedecin','CPatient','CCorrespondantPatient','CMediusers') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision('1.41');

        $this->addQuery(
            'ALTER TABLE `correspondant_courrier` 
                ADD `medecin_exercice_place_id` INT (11) UNSIGNED AFTER `object_id`,
                ADD INDEX (`medecin_exercice_place_id`);'
        );

        $this->makeRevision('1.42');

        $this->addQuery(
            "UPDATE `aide_saisie` SET `class` = 'Cerfa' WHERE `class` = 'CCerfa';"
        );

        $this->makeRevision('1.43');

        $this->addQuery(
            'ALTER TABLE compte_rendu
             ADD INDEX `signature_mandatory` (`signature_mandatory`),
             ADD INDEX `valide` (`valide`)',
            true
        );
        $this->makeRevision('1.44');
        $query = "UPDATE aide_saisie SET class = 'Cerfa' WHERE class = 'CCerfa';";
        $this->addQuery($query);

        $this->makeRevision('1.45');

        $this->addQuery(
            self::renameTemplateFieldQuery(
                CompteRenduFieldReplacer::PATIENT_NOM,
                CompteRenduFieldReplacer::PATIENT_NOM_UTILISE,
                true
            )
        );

        $this->addQuery(
            self::renameTemplateFieldQuery(
                CompteRenduFieldReplacer::PATIENT_NOM_NAISSANCE,
                CompteRenduFieldReplacer::PATIENT_NOM_NAISSANCE_MAJ,
                true
            )
        );

        $this->addQuery(
            self::renameTemplateFieldQuery(
                CompteRenduFieldReplacer::PATIENT_PRENOM,
                CompteRenduFieldReplacer::PATIENT_PRENOM_PRENOM_NAISSANCE,
                true
            )
        );

        $this->makeRevision('1.46');

        $this->addQuery(
            "ALTER TABLE `compte_rendu`
             ADD `masquage` ENUM ('aucun','praticien','patient','representants_legaux') DEFAULT 'aucun'
             AFTER `object_class`;"
        );

        if (CModule::getActive('dmp')) {
            $this->addQuery(
                'UPDATE `compte_rendu`
                 LEFT JOIN `cdmp_document` ON `cdmp_document`.`object_id` = `compte_rendu`.`compte_rendu_id`
                      AND `cdmp_document`.`object_class` = "CCompteRendu"
                 SET `masquage` =
                     CASE `cdmp_document`.`visibilite`
                         WHEN "0" THEN "praticien"
                         WHEN "1" THEN "patient"
                         WHEN "2" THEN "representants_legaux"
                         ELSE "aucun"
                     END
                 WHERE `cdmp_document`.`cdmp_document_id` IS NOT NULL'
            );
        }

        $this->makeRevision('1.47');

        $this->addQuery("CREATE TABLE `statut_compte_rendu` (
                `statut_compte_rendu_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `compte_rendu_id` INT (11) UNSIGNED NOT NULL,
                `statut` ENUM ('brouillon','attente_validation_praticien','attente_correction_secretariat','a_envoyer','envoye') DEFAULT 'brouillon',
                `commentaire` VARCHAR (255),
                `datetime` DATETIME NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL
              )/*! ENGINE=MyISAM */;");

        $this->addQuery("ALTER TABLE `statut_compte_rendu` 
                ADD INDEX (`compte_rendu_id`),
                ADD INDEX (`datetime`),
                ADD INDEX (`user_id`);");

        $this->makeRevision('1.48');

        $this->addQuery(
            "ALTER TABLE `compte_rendu`
             ADD `masquage_patient` ENUM ('0','1') DEFAULT '0' AFTER `masquage`,
             ADD `masquage_praticien` ENUM ('0','1') DEFAULT '0' AFTER `masquage_patient`,
             ADD `masquage_representants_legaux` ENUM ('0','1') DEFAULT '0' AFTER `masquage_praticien`;"
        );

        $this->addQuery(
            "UPDATE `compte_rendu`
             SET `masquage_patient` = IF(`masquage` = 'patient', '1', '0'),
                 `masquage_praticien` = IF(`masquage` = 'praticien', '1', '0'),
                 `masquage_representants_legaux` = IF(`masquage` = 'representants_legaux', '1', '0');"
        );

        $this->addQuery('ALTER TABLE `compte_rendu` DROP `masquage`;');

        $this->makeRevision('1.49');

        $this->addQuery(
            "ALTER TABLE `compte_rendu`
            ADD `modification_date` DATETIME AFTER `creation_date`,
            ADD INDEX (`modification_date`);"
        );

        $this->makeRevision('1.50');

        $this->addQuery(
            'ALTER TABLE `compte_rendu`
            ADD `validation_date` DATETIME AFTER `modification_date`,
            ADD INDEX (`validation_date`);'
        );

        $this->makeRevision('1.51');

        $this->addQuery(
            "UPDATE `compte_rendu`
             SET `duree_lecture` = NULL, `duree_ecriture` = NULL
             WHERE `duree_lecture` = '0'
             AND `duree_ecriture` = '0'"
        );

        $this->mod_version = '1.52';
    }
}
