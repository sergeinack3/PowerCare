<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropSender;

/**
 * @codeCoverageIgnore
 */
class CSetupHl7 extends CSetup
{
    function __construct()
    {
        parent::__construct();

        $this->mod_name = "hl7";
        $this->makeRevision("0.0");

        $this->makeRevision("0.01");

        $this->addMethod("checkHL7v2Tables");

        $this->makeRevision("0.02");

        $query = "ALTER TABLE `table_description` 
                ADD `user` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query, true, "hl7v2");

        // Gestion du mode de placement en psychiatrie
        $query = "INSERT INTO `table_description` (
              `table_description_id`, `number`, `description`, `user`
              ) VALUES (
                NULL , '9000', 'Admit Reason (Psychiatrie)', '1'
              );";
        $this->addQuery($query, false, "hl7v2");

        $this->makeRevision("0.03");

        $query = "ALTER TABLE `table_entry`
                DROP INDEX `number_code_hl7` ,
                ADD INDEX `number_code_hl7` ( `number` , `code_hl7_from` );";
        $this->addQuery($query, false, "hl7v2");

        // Table - 0001
        // F - Female
        $set = [
            "code_hl7_to"  => "F",
            "code_mb_from" => "f",
            "code_mb_to"   => "f",
        ];
        $and = [
            "code_hl7_from" => "F",
        ];
        $this->updateTableEntry("1", $set, $and);
        // M - Male
        $set = [
            "code_hl7_to"  => "M",
            "code_mb_from" => "m",
            "code_mb_to"   => "m",
        ];
        $and = [
            "code_hl7_from" => "M",
        ];
        $this->updateTableEntry("1", $set, $and);
        // O - Other
        $set = [
            "code_mb_to" => "m",
        ];
        $and = [
            "code_hl7_from" => "O",
        ];
        $this->updateTableEntry("1", $set, $and);
        // U - Unknown
        $set = [
            "code_mb_to" => "m",
        ];
        $and = [
            "code_hl7_from" => "U",
        ];
        $this->updateTableEntry("1", $set, $and);
        // A - Ambiguous
        $set = [
            "code_mb_to" => "m",
        ];
        $and = [
            "code_hl7_from" => "A",
        ];
        $this->updateTableEntry("1", $set, $and);
        // N - Not applicable
        $set = [
            "code_mb_to" => "m",
        ];
        $and = [
            "code_hl7_from" => "N",
        ];
        $this->updateTableEntry("1", $set, $and);

        // Table 0004 - Patient Class
        // E - Emergency - Passage aux Urgences - Arrivée aux urgences
        $set = [
            "code_hl7_to"  => "E",
            "code_mb_from" => "urg",
            "code_mb_to"   => "urg",
        ];
        $and = [
            "code_hl7_from" => "E",
        ];
        $this->updateTableEntry("4", $set, $and);
        // I - Inpatient - Hospitalisation
        $set = [
            "code_hl7_to"  => "I",
            "code_mb_from" => "comp",
            "code_mb_to"   => "comp",
        ];
        $and = [
            "code_hl7_from" => "I",
        ];
        $this->updateTableEntry("4", $set, $and);
        $this->insertTableEntry("4", null, "I", "ssr", null, "Inpatient");
        $this->insertTableEntry("4", null, "I", "psy", null, "Inpatient");
        // O - Outpatient - Actes et consultation externe
        $set = [
            "code_hl7_to"  => "O",
            "code_mb_from" => "ambu",
            "code_mb_to"   => "ambu",
        ];
        $and = [
            "code_hl7_from" => "O",
        ];
        $this->updateTableEntry("4", $set, $and);
        $this->insertTableEntry("4", null, "O", "exte", null, "Outpatient");
        $this->insertTableEntry("4", null, "O", "consult", null, "Outpatient");
        // R - Recurring patient - Séances
        $set = [
            "code_hl7_to"  => "R",
            "code_mb_from" => "seances",
            "code_mb_to"   => "seances",
        ];
        $and = [
            "code_hl7_from" => "R",
        ];
        $this->updateTableEntry("4", $set, $and);

        // Table 0032 - Charge price indicator
        // 03 - Hospi. complète
        $set = [
            "code_hl7_to"  => "03",
            "code_mb_from" => "comp",
            "code_mb_to"   => "comp",
        ];
        $and = [
            "code_hl7_from" => "03",
        ];
        $this->updateTableEntry("32", $set, $and);
        // 07 - Consultations, soins externes
        $set = [
            "code_hl7_to"  => "07",
            "code_mb_from" => "consult",
            "code_mb_to"   => "consult",
        ];
        $and = [
            "code_hl7_from" => "07",
        ];
        $this->updateTableEntry("32", $set, $and);
        // 10 - Accueil des urgences
        $set = [
            "code_hl7_to"  => "10",
            "code_mb_from" => "urg",
            "code_mb_to"   => "urg",
        ];
        $and = [
            "code_hl7_from" => "10",
        ];
        $this->updateTableEntry("32", $set, $and);

        // Table - 9000
        // HL  - Hospitalisation libre
        $this->insertTableEntry("9000", "HL", "HL", "libre", "libre", "Hospitalisation libre");
        // HO  - Placement d'office
        $this->insertTableEntry("9000", "HO", "HO", "office", "office", "Placement d'office");
        // HDT - Hospitalisation à la demande d'un tiers
        $this->insertTableEntry("9000", "HDT", "HDT", "tiers", "tiers", "Hospitalisation à la demande d'un tiers");

        // Table - 0430
        // 0 - Police
        $this->insertTableEntry("430", "0", "0", "fo", "fo", "Police");
        // 1 - SAMU, SMUR terrestre
        $this->insertTableEntry("430", "1", "1", "smur", "smur", "SAMU, SMUR terrestre");
        // 2 - Ambulance publique
        $this->insertTableEntry("430", "2", "2", "ambu", "ambu", "Ambulance publique");
        // 3 - Ambulance privée
        $this->insertTableEntry("430", null, "3", "ambu", null, "Ambulance privée");
        // 4 - Taxi
        $this->insertTableEntry("430", "4", "4", "perso_taxi", "perso_taxi", "Taxi");
        // 5 - Moyens personnels
        $this->insertTableEntry("430", "5", "5", "perso", "perso", "Moyens personnels");
        // 6 - SAMU, SMUR hélicoptère
        $this->insertTableEntry("430", "6", "6", "heli", "heli", "SAMU, SMUR hélicoptère");
        // 7 - Pompier
        $this->insertTableEntry("430", "7", "7", "vsab", "vsab", "Pompier");
        // 8 - VSL
        $this->insertTableEntry("430", null, "8", "ambu_vsl", null, "VSL");
        // 9 - Autre
        $this->insertTableEntry("430", null, "9", "perso", null, "Autre");

        $this->makeRevision("0.04");

        $query = "CREATE TABLE `hl7_config` (
                `hl7_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `assigning_authority_namespace_id` VARCHAR (255),
                `assigning_authority_universal_id` VARCHAR (255),
                `assigning_authority_universal_type_id` VARCHAR (255),
                `sender_id` INT (11) UNSIGNED,
                `sender_class` ENUM ('CSenderFTP','CSenderSOAP')
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `hl7_config` 
              ADD INDEX (`sender_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.05");

        // Table - 0063
        // Collègue
        $set = [
            "code_hl7_to"  => "ASC",
            "code_mb_from" => "collegue",
            "code_mb_to"   => "collegue",
        ];
        $and = [
            "code_hl7_from" => "ASC",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Frère
        $set = [
            "code_hl7_to"  => "BRO",
            "code_mb_from" => "frere",
            "code_mb_to"   => "frere",
        ];
        $and = [
            "code_hl7_from" => "BRO",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Enfant
        $set = [
            "code_hl7_to"  => "CHD",
            "code_mb_from" => "enfant",
            "code_mb_to"   => "enfant",
        ];
        $and = [
            "code_hl7_from" => "CHD",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Frère
        $set = [
            "code_hl7_to"  => "DOM",
            "code_mb_from" => "compagnon",
            "code_mb_to"   => "compagnon",
        ];
        $and = [
            "code_hl7_from" => "DOM",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Employé
        $set = [
            "code_hl7_to"  => "EME",
            "code_mb_from" => "employe",
            "code_mb_to"   => "employe",
        ];
        $and = [
            "code_hl7_from" => "EME",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Employeur
        $set = [
            "code_hl7_to"  => "EMR",
            "code_mb_from" => "employeur",
            "code_mb_to"   => "employeur",
        ];
        $and = [
            "code_hl7_from" => "EMR",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Proche
        $set = [
            "code_hl7_to"  => "EXF",
            "code_mb_from" => "proche",
            "code_mb_to"   => "proche",
        ];
        $and = [
            "code_hl7_from" => "EXF",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Enfant adoptif
        $set = [
            "code_hl7_to"  => "FCH",
            "code_mb_from" => "enfant_adoptif",
            "code_mb_to"   => "enfant_adoptif",
        ];
        $and = [
            "code_hl7_from" => "FCH",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Ami
        $set = [
            "code_hl7_to"  => "FND",
            "code_mb_from" => "ami",
            "code_mb_to"   => "ami",
        ];
        $and = [
            "code_hl7_from" => "FND",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Père
        $set = [
            "code_hl7_to"  => "FTH",
            "code_mb_from" => "pere",
            "code_mb_to"   => "pere",
        ];
        $and = [
            "code_hl7_from" => "FTH",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Petits-enfants
        $set = [
            "code_hl7_to"  => "GCH",
            "code_mb_from" => "petits_enfants",
            "code_mb_to"   => "petits_enfants",
        ];
        $and = [
            "code_hl7_from" => "GCH",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Tuteur
        $set = [
            "code_hl7_to"  => "GRD",
            "code_mb_from" => "tuteur",
            "code_mb_to"   => "tuteur",
        ];
        $and = [
            "code_hl7_from" => "GRD",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Mère
        $set = [
            "code_hl7_to"  => "MTH",
            "code_mb_from" => "mere",
            "code_mb_to"   => "mere",
        ];
        $and = [
            "code_hl7_from" => "MTH",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Autre
        $set = [
            "code_hl7_to"  => "OTH",
            "code_mb_from" => "autre",
            "code_mb_to"   => "autre",
        ];
        $and = [
            "code_hl7_from" => "OTH",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Propriétaire
        $set = [
            "code_hl7_to"  => "OWN",
            "code_mb_from" => "proprietaire",
            "code_mb_to"   => "proprietaire",
        ];
        $and = [
            "code_hl7_from" => "OWN",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Beau-fils
        $set = [
            "code_hl7_to"  => "SCH",
            "code_mb_from" => "beau_fils",
            "code_mb_to"   => "beau_fils",
        ];
        $and = [
            "code_hl7_from" => "SCH",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Soeur
        $set = [
            "code_hl7_to"  => "SIS",
            "code_mb_from" => "soeur",
            "code_mb_to"   => "soeur",
        ];
        $and = [
            "code_hl7_from" => "SIS",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Époux
        $set = [
            "code_hl7_to"  => "SPO",
            "code_mb_from" => "epoux",
            "code_mb_to"   => "epoux",
        ];
        $and = [
            "code_hl7_from" => "SPO",
        ];
        $this->updateTableEntry("63", $set, $and);
        // Entraineur
        $set = [
            "code_hl7_to"  => "TRA",
            "code_mb_from" => "entraineur",
            "code_mb_to"   => "entraineur",
        ];
        $and = [
            "code_hl7_from" => "TRA",
        ];
        $this->updateTableEntry("63", $set, $and);

        // Table - 0131
        // Personne à prévenir
        $set = [
            "code_hl7_to"  => "C",
            "code_mb_from" => "prevenir",
            "code_mb_to"   => "prevenir",
        ];
        $and = [
            "code_hl7_from" => "C",
        ];
        $this->updateTableEntry("131", $set, $and);
        // Employeur
        $set = [
            "code_hl7_to"  => "E",
            "code_mb_from" => "employeur",
            "code_mb_to"   => "employeur",
        ];
        $and = [
            "code_hl7_from" => "E",
        ];
        $this->updateTableEntry("131", $set, $and);
        // Assurance
        $set = [
            "code_hl7_to"  => "I",
            "code_mb_from" => "assurance",
            "code_mb_to"   => "assurance",
        ];
        $and = [
            "code_hl7_from" => "I",
        ];
        $this->updateTableEntry("131", $set, $and);
        // Autre
        $set = [
            "code_hl7_to"  => "O",
            "code_mb_from" => "autre",
            "code_mb_to"   => "autre",
        ];
        $and = [
            "code_hl7_from" => "O",
        ];
        $this->updateTableEntry("131", $set, $and);
        // Inconnu
        $set = [
            "code_hl7_to"  => "U",
            "code_mb_from" => "inconnu",
            "code_mb_to"   => "inconnu",
        ];
        $and = [
            "code_hl7_from" => "U",
        ];
        $this->updateTableEntry("131", $set, $and);
        // Personne de confiance
        $this->insertTableEntry("131", "K", "K", "confiance", "confiance", "Personne de confiance");

        $this->makeRevision("0.06");
        $query = "CREATE TABLE `source_mllp` (
              `source_mllp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `port` INT (11) DEFAULT '7001',
              `name` VARCHAR (255) NOT NULL,
              `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'qualif',
              `host` TEXT NOT NULL,
              `user` VARCHAR (255),
              `password` VARCHAR (50),
              `type_echange` VARCHAR (255),
              `active` ENUM ('0','1') NOT NULL DEFAULT '1',
              `loggable` ENUM ('0','1') NOT NULL DEFAULT '1'
    ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.07");

        $this->insertTableEntry("399", "FRA", "FRA", "FRA", "FRA", "Française");

        $this->makeRevision("0.08");

        // Circonstance de sortie
        $this->insertTableEntry("112", "2", "2", "2", "2", "Mesures disciplinaires");
        $this->insertTableEntry("112", "3", "3", "3", "3", "Décision médicale (valeur par défaut");
        $this->insertTableEntry("112", "4", "4", "4", "4", "Contre avis médicale");
        $this->insertTableEntry("112", "5", "5", "5", "5", "En attente d'examen");
        $this->insertTableEntry("112", "6", "6", "6", "6", "Convenances personnelles");
        $this->insertTableEntry("112", "R", "R", "R", "R", "Essai (contexte psychiatrique)");
        $this->insertTableEntry("112", "E", "E", "E", "E", "Evasion");
        $this->insertTableEntry("112", "F", "F", "F", "F", "Fugue");

        $this->makeRevision("0.09");

        // Type d'activité, mode de traitement
        // Hospi. complète
        $this->insertTableEntry("32", "CM", "CM", "comp_m", "comp_m", "Hospi. complète en médecine");
        $this->insertTableEntry("32", "CM", "CM", "comp_c", "comp_c", "Hospi. complète en chirurgie");
        $this->insertTableEntry("32", "MATER", "MATER", "comp_o", "comp_o", "Hospi. complète en obstétrique");
        // Ambu
        $this->insertTableEntry("32", "AMBU", "AMBU", "ambu_m", "ambu_m", "Ambulatoire en médecine");
        $this->insertTableEntry("32", "AMBU", "AMBU", "ambu_c", "ambu_c", "Ambulatoire en chirurgie");
        $this->insertTableEntry("32", "MATERAMBU", "MATERAMBU", "ambu_o", "ambu_o", "Ambulatoire en obstétrique");
        // Externe
        $this->insertTableEntry("32", "EXT", "EXT", "exte_m", "exte_m", "Soins externes");
        $this->insertTableEntry("32", "EXT", "EXT", "exte_c", "exte_c", "Soins externes chirugicaux");
        $this->insertTableEntry("32", "MATEREXT", "MATEREXT", "exte_o", "exte_o", "Soins externes ");
        /// Seances
        $this->insertTableEntry("32", "CHIMIO", "CHIMIO", "seances_m", "seances_m", "Séance de chimiothérapie");
        $this->insertTableEntry("32", "CHIMIO", "CHIMIO", "seances_c", "seances_c", "Séance de chimiothérapie");
        $this->insertTableEntry("32", "CHIMIO", "CHIMIO", "seances_o", "seances_o", "Séance de chimiothérapie");
        // Urgence
        $this->insertTableEntry(
            "32",
            "URGENCE",
            "URGENCE",
            "urg_m",
            "urg_m",
            "Passage aux ugences médicales sans hosp."
        );
        $this->insertTableEntry(
            "32",
            "URGENCE",
            "URGENCE",
            "urg_c",
            "urg_c",
            "Passage aux ugences chirurgicales sans hosp."
        );
        $this->insertTableEntry("32", "URGENCE", "URGENCE", "urg_o", "urg_o", "Passage aux ugences");

        $this->makeRevision("0.10");

        // Ambu
        $set = [
            "code_hl7_to"   => "I",
            "code_hl7_from" => "I",
            "code_mb_to"    => "ambu",
        ];
        $and = [
            "code_mb_from" => "ambu",
        ];
        $this->updateTableEntry("4", $set, $and);

        $this->makeRevision("0.11");

        // Type d'activité, mode de traitement
        // Hospi. complète
        $this->insertTableEntry("32", "CM", "CM", "comp", "comp", "Hospi. complète en médecine");
        // Ambu
        $this->insertTableEntry("32", "MATERAMBU", "MATERAMBU", "ambu", "ambu", "Ambulatoire en obstétrique");
        // Externe
        $this->insertTableEntry("32", "EXT", "EXT", "exte", "exte", "Soins externes chirugicaux");
        /// Seances
        $this->insertTableEntry("32", "CHIMIO", "CHIMIO", "seances", "seances", "Séance de chimiothérapie");
        // Urgence
        $this->insertTableEntry(
            "32",
            "URGENCE",
            "URGENCE",
            "urg",
            "urg",
            "Passage aux ugences chirurgicales sans hosp."
        );

        $this->makeRevision("0.12");

        $set = [
            "code_hl7_to"   => "AMBU",
            "code_hl7_from" => "AMBU",
        ];
        $and = [
            "code_mb_from" => "ambu",
        ];
        $this->updateTableEntry("32", $set, $and);

        $this->makeRevision("0.13");

        $query = "CREATE TABLE `sender_mllp` (
                `sender_mllp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED,
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `actif` ENUM ('0','1') NOT NULL DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `sender_mllp` 
                ADD INDEX (`user_id`),
                ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.14");

        $query = "ALTER TABLE `hl7_config` 
                CHANGE `sender_class` `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderMLLP');";
        $this->addQuery($query);

        $this->makeRevision("0.15");

        // Character sets
        // UTF-8
        $set = [
            "code_hl7_to"  => "UNICODE UTF-8",
            "code_mb_from" => "UTF-8",
            "code_mb_to"   => "UTF-8",
        ];
        $and = [
            "code_hl7_from" => "UNICODE UTF-8",
        ];
        $this->updateTableEntry("211", $set, $and);
        // ISO-8859-1
        $set = [
            "code_hl7_to"  => "8859/1 ",
            "code_mb_from" => "ISO-8859-1",
            "code_mb_to"   => "ISO-8859-1",
        ];
        $and = [
            "code_hl7_from" => "8859/1 ",
        ];
        $this->updateTableEntry("211", $set, $and);

        $this->makeRevision("0.16");

        $this->makeRevision("0.17");

        // Externe
        $and = [
            "code_hl7_to"  => "O",
            "code_mb_from" => "exte",
        ];
        $this->deleteTableEntry("4", $and);

        $set = [
            "code_hl7_to"   => "O",
            "code_hl7_from" => "O",
            "code_mb_from"  => "exte",
            "code_mb_to"    => "exte",
        ];
        $and = [
            "code_hl7_from" => "I",
            "code_mb_from"  => "ambu",
        ];
        $this->updateTableEntry("4", $set, $and);

        // Ambu
        $this->insertTableEntry("4", null, "I", "ambu", null, "Inpatient");

        $this->makeRevision("0.18");

        // Gestion du mode de placement en psychiatrie
        $query = "INSERT INTO `table_description` (
              `table_description_id`, `number`, `description`, `user`
              ) VALUES (
                NULL , '9001', 'Mode de sortie PMSI', '1'
              );";
        $this->addQuery($query, false, "hl7v2");

        // Transfert
        $this->insertTableEntry("9001", "7", "7", "transfert", "transfert", "Transfert");
        // Mutation
        $this->insertTableEntry("9001", "6", "6", "mutation", "mutation", "Mutation (même hopital)");
        // Deces
        $this->insertTableEntry("9001", "9", "9", "deces", "deces", "Décès");
        // Normal
        $this->insertTableEntry("9001", "5", "5", "normal", "normal", "Sorti à l'essai");

        $this->makeRevision("0.19");

        $query = "ALTER TABLE `hl7_config` 
                ADD `handle_mode` ENUM ('normal','simple') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("0.20");

        $query = "ALTER TABLE `hl7_config` 
                CHANGE `sender_class` `sender_class` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision("0.21");

        $query = "ALTER TABLE `hl7_config` 
                ADD `get_NDA` ENUM ('PID_18','PV1_19') DEFAULT 'PID_18';";
        $this->addQuery($query);

        $this->makeRevision("0.22");

        $query = "ALTER TABLE `hl7_config` 
              ADD `handle_PV1_10` ENUM ('discipline','service') DEFAULT 'discipline',
              CHANGE `get_NDA` `handle_NDA` ENUM ('PID_18','PV1_19') DEFAULT 'PID_18';";
        $this->addQuery($query);

        $this->makeRevision("0.23");

        $query = "ALTER TABLE `sender_mllp` 
                ADD `save_unsupported_message` ENUM ('0','1') DEFAULT '1',
                ADD `create_ack_file` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.24");

        $this->makeRevision("0.25");

        $query = "ALTER TABLE `hl7_config` 
                ADD `encoding` ENUM ('UTF-8','ISO-8859-1') DEFAULT 'UTF-8';";
        $this->addQuery($query);

        $this->makeRevision("0.26");

        // Table - 0063
        // Ascendant
        $this->insertTableEntry("63", "DAN", "DAN", "ascendant", "ascendant", "Ascendant");

        // Collatéral
        $this->insertTableEntry("63", "COL", "COL", "colateral", "colateral", "Collatéral");

        // Conjoint
        $this->insertTableEntry("63", "CON", "CON", "conjoint", "conjoint", "Conjoint");

        // Directeur
        $this->insertTableEntry("63", "DIR", "DIR", "directeur", "directeur", "Directeur");

        // Divers
        $this->insertTableEntry("63", "DIV", "DIV", "divers", "divers", "Divers");

        // Grand-parent
        $this->insertTableEntry("63", "GRP", "GRP", "grand_parent", "grand_parent", "Grand-parent");

        $this->makeRevision("0.27");

        $query = "ALTER TABLE `hl7_config` 
                ADD `handle_NSS` ENUM ('PID_3','PID_19') DEFAULT 'PID_3';";
        $this->addQuery($query);

        $this->makeRevision("0.28");

        $query = "ALTER TABLE `hl7_config` 
                ADD `iti30_option_merge` ENUM ('0','1') DEFAULT '1',
                ADD `iti30_option_link_unlink` ENUM ('0','1') DEFAULT '0',
                ADD `iti31_in_outpatient_emanagement` ENUM ('0','1') DEFAULT '1',
                ADD `iti31_pending_event_management` ENUM ('0','1') DEFAULT '0',
                ADD `iti31_advanced_encounter_management` ENUM ('0','1') DEFAULT '1',
                ADD `iti31_temporary_patient_transfer_tracking` ENUM ('0','1') DEFAULT '0',
                ADD `iti31_historic_movement` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "ALTER TABLE `source_mllp` 
              ADD `ssl_enabled` ENUM ('0','1') NOT NULL DEFAULT '0',
              ADD `ssl_certificate` VARCHAR (255),
              ADD `ssl_passphrase` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "ALTER TABLE `hl7_config` 
              ADD `strict_segment_terminator` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `sender_mllp` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `hl7_config` 
                ADD `handle_PV1_14` ENUM ('admit_source','ZFM') DEFAULT 'admit_source',
                ADD `handle_PV1_36` ENUM ('discharge_disposition','ZFM') DEFAULT 'discharge_disposition';";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "ALTER TABLE `hl7_config` 
                ADD `purge_idex_movements` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.34");

        /* Remise à niveau des types d'hospitalisation */

        // Suppression
        $and = [
            "code_mb_from" => "exte",
        ];
        $this->deleteTableEntry("4", $and);
        $and = [
            "code_mb_from" => "seances",
        ];
        $this->deleteTableEntry("4", $and);
        $and = [
            "code_mb_from" => "comp",
        ];
        $this->deleteTableEntry("4", $and);
        $and = [
            "code_mb_from" => "ambu",
        ];
        $this->deleteTableEntry("4", $and);
        $and = [
            "code_mb_from" => "urg",
        ];
        $this->deleteTableEntry("4", $and);
        $and = [
            "code_mb_from" => "consult",
        ];
        $this->deleteTableEntry("4", $and);
        $and = [
            "code_mb_from" => "psy",
        ];
        $this->deleteTableEntry("4", $and);
        $and = [
            "code_mb_from" => "ssr",
        ];
        $this->deleteTableEntry("4", $and);

        // Table 0004 - Patient Class
        // E - Emergency - Passage aux Urgences - Arrivée aux urgences
        $this->insertTableEntry("4", "E", "E", "urg", "urg", "Emergency", 0);

        // I - Inpatient - Hospitalisation
        $this->insertTableEntry("4", "I", "I", "comp", "comp", "Inpatient", 0);
        $this->insertTableEntry("4", null, "I", "ssr", null, "Inpatient");
        $this->insertTableEntry("4", null, "I", "psy", null, "Inpatient");
        $this->insertTableEntry("4", null, "I", "ambu", null, "Inpatient");

        // O - Outpatient - Actes et consultation externe
        $this->insertTableEntry("4", "O", "O", "exte", "exte", "Outpatient", 0);
        $this->insertTableEntry("4", null, "O", "consult", null, "Outpatient");

        // R - Recurring patient - Séances
        $this->insertTableEntry("4", "R", "R", "seances", "seances", "Recurring patient", 0);

        $this->makeRevision("0.35");
        $query = "ALTER TABLE `hl7_config` 
                ADD `repair_patient` ENUM ('0','1') DEFAULT '1',
                ADD `control_date` ENUM ('permissif','strict') DEFAULT 'strict';";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $query = "ALTER TABLE `hl7_config` 
              ADD `handle_PV1_3` ENUM ('name','config_value','idex') DEFAULT 'name';";
        $this->addQuery($query);

        $this->addDependency("ihe", "0.26");

        $this->makeRevision("0.37");
        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `RAD48_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5';";
        $this->addQuery($query);

        $this->makeRevision("0.38");

        $query = "ALTER TABLE `exchange_ihe`
                CHANGE `object_class` `object_class` VARCHAR (80);";
        $this->addQuery($query);

        $this->makeRevision("0.39");

        $query = "ALTER TABLE `exchange_ihe` 
                ADD `reprocess` TINYINT (4) UNSIGNED DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.40");
        $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_telephone_number` ENUM ('XTN_1','XTN_12') DEFAULT 'XTN_12';";
        $this->addQuery($query);

        $this->makeRevision("0.41");
        $query = "ALTER TABLE `hl7_config` 
                ADD `handle_telephone_number` ENUM ('XTN_1','XTN_12') DEFAULT 'XTN_12';";
        $this->addQuery($query);

        $this->makeRevision("0.42");
        $query = "ALTER TABLE `hl7_config` 
                ADD `handle_PID_31` ENUM ('avs','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.43");

        $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_PID_31` ENUM ('avs','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.44");
        $query = "ALTER TABLE `hl7_config`
                ADD `segment_terminator` ENUM ('CR','LF','CRLF')";
        $this->addQuery($query);

        $this->makeRevision("0.45");
        $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_PID_34` ENUM ('finess','actor') DEFAULT 'finess';";
        $this->addQuery($query);

        $this->makeRevision("0.46");
        $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_PV2_45` ENUM ('operation','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.47");
        $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `build_cellular_phone` ENUM ('PRN','ORN') DEFAULT 'PRN';";
        $this->addQuery($query);

        $this->makeRevision("0.48");
        $query = "ALTER TABLE `receiver_ihe_config` 
                ADD `send_first_affectation` ENUM ('A02','Z99') DEFAULT 'Z99';";
        $this->addQuery($query);

        $this->makeRevision("0.49");

        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `ITI21_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5',
                ADD `ITI22_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5';";
        $this->addQuery($query);

        $this->makeRevision("0.50");
        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `build_PV1_26` ENUM ('movement_id','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.51");
        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `send_assigning_authority` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.52");
        $query = "ALTER TABLE `hl7_config`
                ADD `receiving_application` VARCHAR (255),
                ADD `receiving_facility` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.53");
        $query = "ALTER TABLE `hl7_config`
                ADD `handle_PV2_12` ENUM ('libelle','none') DEFAULT 'libelle';";
        $this->addQuery($query);

        $this->makeRevision("0.54");
        $query = "ALTER TABLE `hl7_config`
                ADD `send_assigning_authority` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.55");
        $query = "ALTER TABLE `hl7_config`
                ADD `send_self_identifier` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `send_self_identifier` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.56");
        $query = "ALTER TABLE `hl7_config`
                ADD `send_area_local_number` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.57");
        $query = "ALTER TABLE `source_mllp`
                CHANGE `password` `password` VARCHAR (255),
                ADD `iv` VARCHAR (16) AFTER `password`,
                ADD `iv_passphrase` VARCHAR (16) AFTER `ssl_passphrase`;";
        $this->addQuery($query);

        $this->makeRevision("0.58");
        $query = "ALTER TABLE `hl7_config`
                ADD `handle_PV1_7` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.59");
        $query = "ALTER TABLE `hl7_config`
                ADD `check_receiving_application_facility` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.60");
        $query = "ALTER TABLE `hl7_config`
                ADD `handle_PV1_20` ENUM ('old_presta','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.61");
        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `send_update_patient_information` ENUM ('A08','A31') DEFAULT 'A31';";
        $this->addQuery($query);

        $this->makeRevision("0.62");
        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `modification_admit_code` ENUM ('A08','Z99') DEFAULT 'Z99';";
        $this->addQuery($query);

        $this->makeRevision("0.63");
        $query = "ALTER TABLE `receiver_ihe_config`
                CHANGE `build_PID_34` `build_PID_34` ENUM ('finess','actor','domain') DEFAULT 'finess';";
        $this->addQuery($query);

        $this->makeRevision("0.64");

        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `country_code` ENUM ('FRA');";
        $this->addQuery($query);

        $query = "ALTER TABLE `hl7_config`
                ADD `country_code` ENUM ('FRA');";
        $this->addQuery($query);

        $this->makeRevision("0.65");

        $query = "ALTER TABLE `receiver_ihe_config`
                CHANGE `country_code` `country_code` ENUM ('FRA','INT');";
        $this->addQuery($query);

        $query = "ALTER TABLE `hl7_config`
                CHANGE `country_code` `country_code` ENUM ('FRA','INT');";
        $this->addQuery($query);

        $this->makeRevision("0.66");

        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `build_other_residence_number` ENUM ('ORN','WPN') DEFAULT 'ORN';";
        $this->addQuery($query);

        $this->makeRevision("0.67");

        $query = "CREATE TABLE `receiver_hl7_v3` (
                `receiver_hl7_v3_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `actif` ENUM ('0','1') NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7_v3`
                ADD INDEX (`group_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.68");

        $query = "CREATE TABLE `exchange_hl7v3` (
                `exchange_hl7v3_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `identifiant_emetteur` VARCHAR (255),
                `initiateur_id` INT (11) UNSIGNED,
                `group_id` INT (11) UNSIGNED NOT NULL DEFAULT '0',
                `date_production` DATETIME NOT NULL,
                `sender_id` INT (11) UNSIGNED,
                `sender_class` ENUM ('CSenderFTP','CSenderSOAP','CSenderFileSystem'),
                `receiver_id` INT (11) UNSIGNED,
                `type` VARCHAR (255),
                `sous_type` VARCHAR (255),
                `date_echange` DATETIME,
                `message_content_id` INT (11) UNSIGNED,
                `acquittement_content_id` INT (11) UNSIGNED,
                `statut_acquittement` VARCHAR (255),
                `message_valide` ENUM ('0','1') DEFAULT '0',
                `acquittement_valide` ENUM ('0','1') DEFAULT '0',
                `id_permanent` VARCHAR (255),
                `object_id` INT (11) UNSIGNED,
                `object_class` ENUM ('CPatient','CSejour','COperation','CAffectation','CConsultation'),
                `reprocess` TINYINT (4) UNSIGNED DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v3`
                ADD INDEX (`initiateur_id`),
                ADD INDEX (`group_id`),
                ADD INDEX (`date_production`),
                ADD INDEX (`sender_id`),
                ADD INDEX (`receiver_id`),
                ADD INDEX (`date_echange`),
                ADD INDEX (`message_content_id`),
                ADD INDEX (`acquittement_content_id`),
                ADD INDEX (`object_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.69");

        $query = "ALTER TABLE `receiver_ihe_config`
                ADD `send_change_after_admit` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.70");
        $this->addDependency("ihe", "0.28");

        $query = "UPDATE user_log
                SET user_log.object_class = 'CReceiverHL7v2'
                WHERE user_log.object_class = 'CReceiverIHE';";
        $this->addQuery($query);

        $query = "UPDATE user_log
                SET user_log.object_class = 'CReceiverHL7v2Config'
                WHERE user_log.object_class = 'CReceiverIHEConfig';";
        $this->addQuery($query);

        $this->makeRevision("0.71");
        $this->addDependency("eai", "0.02");

        $query = "UPDATE message_supported
              SET message_supported.object_class = 'CReceiverHL7v2'
              WHERE message_supported.object_class = 'CReceiverIHE';";
        $this->addQuery($query);

        self::updateTableSource("source_file_system");
        self::updateTableSource("source_mllp");

        $this->makeRevision("0.72");

        $query = "ALTER TABLE `receiver_hl7v2`
                ADD `OID` VARCHAR (255);";
        $this->addQuery($query);

        $query = "RENAME TABLE `receiver_hl7_v3`
                TO `receiver_hl7v3`;";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v3`
                CHANGE `receiver_hl7_v3_id` `receiver_hl7v3_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT;";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v3`
                ADD `OID` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.73");

        $query = "ALTER TABLE `hl7_config`
                ADD `ignore_fields` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("0.74");

        $query = "ALTER TABLE `exchange_hl7v3`
                CHANGE `object_class` `object_class` ENUM ('CPatient','CSejour','COperation','CAffectation','CConsultation','CFile','CCompteRendu');";
        $this->addQuery($query);

        $this->makeRevision("0.75");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_PID_19` ENUM ('matricule','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.76");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_actor_identifier` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.77");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_PV1_11` ENUM ('uf_medicale','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.78");
        $query = "ALTER TABLE `receiver_hl7v2`
                ADD `synchronous` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.79");
        $query = "ALTER TABLE `receiver_hl7v3`
                ADD `synchronous` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.80");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `iti31_in_outpatient_emanagement` `iti31_in_outpatient_management` ENUM ('0','1') DEFAULT '1',
                ADD `create_grossesse` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.81");
        $query = "ALTER TABLE `hl7_config`
                CHANGE `iti31_in_outpatient_emanagement` `iti31_in_outpatient_management` ENUM ('0','1') DEFAULT '1',
                ADD `create_grossesse` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.82");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_PID_6` ENUM ('nom_naissance','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.83");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `RAD3_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5'";
        $this->addQuery($query);

        $this->makeRevision("0.84");
        $query = "ALTER TABLE `exchange_hl7v2`
                CHANGE `object_class` `object_class` ENUM ('CPatient','CSejour','COperation','CAffectation','COperation','CConsultation','CPrescriptionLineElement');";
        $this->addQuery($query);

        $this->makeRevision("0.85");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_PID_11` ENUM ('simple','multiple') DEFAULT 'multiple',
                ADD `build_PID_13` ENUM ('simple','multiple') DEFAULT 'multiple',
                ADD `build_PV1_5` ENUM ('NPA','None') DEFAULT 'NPA',
                ADD `build_PV1_17` ENUM ('praticien','None') DEFAULT 'praticien',
                ADD `build_PV1_19` ENUM ('normal','simple') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("0.86");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `modification_before_admit` ENUM ('0','1') DEFAULT '1'";
        $this->addQuery($query);

        $this->makeRevision("0.87");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_PID_18` ENUM ('normal','simple') DEFAULT 'normal',
                ADD `send_patient_with_visit` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.88");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_identifier_authority` ENUM ('normal','PI_AN') DEFAULT 'normal',
                CHANGE `build_PV1_5` `build_PV1_5` ENUM ('NPA','none') DEFAULT 'NPA',
                CHANGE `build_PV1_17` `build_PV1_17` ENUM ('praticien','none') DEFAULT 'praticien';";
        $this->addQuery($query);

        $this->makeRevision("0.89");

        $query = "ALTER TABLE `hl7_config`
                ADD `search_master_IPP` ENUM ('0','1') DEFAULT '0',
                ADD `search_master_NDA` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.90");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_PV1_50` ENUM ('sejour_id','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("0.91");

        $query = "ALTER TABLE `receiver_hl7v2_config`
               ADD `send_patient_with_current_admit` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.92");

        $query = "ALTER TABLE `hl7_config`
                ADD `bypass_validating` ENUM ('0','1') DEFAULT '0'";
        $this->addQuery($query);

        $this->makeRevision("0.93");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `mode_identito_vigilance` ENUM ('light','medium','strict') DEFAULT 'light'";
        $this->addQuery($query);

        $this->makeRevision("0.94");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_no_facturable` ENUM ('0','1') DEFAULT '1'";
        $this->addQuery($query);

        $this->makeRevision("0.95");

        $query = "CREATE TABLE `receiver_hl7v3_config` (
                `receiver_hl7v3_config_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_id` INT (11) UNSIGNED,
                `use_receiver_oid` ENUM ('0','1') DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v3_config`
                ADD INDEX (`object_id`);";

        $this->addQuery($query);

        $this->makeRevision("0.96");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_a42_onmerge` ENUM ('0','1') DEFAULT '0';";

        $this->addQuery($query);

        $this->makeRevision("0.97");

        $query = "ALTER TABLE `source_mllp`
                ADD `libelle` VARCHAR (255);";

        $this->addQuery($query);

        $this->makeRevision("0.98");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_ZBE_7` ENUM ('medicale','soins') DEFAULT 'medicale',
                ADD `build_ZBE_8` ENUM ('medicale','soins') DEFAULT 'soins';";
        $this->addQuery($query);

        $this->makeRevision("0.99");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_ZBE_7` ENUM ('medicale','soins') DEFAULT 'medicale',
                ADD `handle_ZBE_8` ENUM ('medicale','soins') DEFAULT 'soins';";

        $this->addQuery($query);

        $this->makeRevision("1.00");

        $query = "ALTER TABLE `hl7_config`
                ADD `ins_integrated` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.01");

        $query = "ALTER TABLE `receiver_hl7v2`
                ADD `monitor_sources` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v3`
                ADD `monitor_sources` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.02");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `build_PID_34` `build_PID_3_4` ENUM ('finess','actor','domain') DEFAULT 'finess';";
        $this->addQuery($query);

        $this->makeRevision("1.03");
        $query = "ALTER TABLE `hl7_config`
                ADD `manage_npa` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_PV1_19_identifier_authority` ENUM ('AN','RI','VN') DEFAULT 'RI';";
        $this->addQuery($query);

        $this->makeRevision("1.04");
        $query = "ALTER TABLE `hl7_config`
                ADD `handle_PV1_3_null` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_PV1_3_1_default` VARCHAR (255),
                ADD `build_PV1_3_1` ENUM ('UF','service') DEFAULT 'UF';";
        $this->addQuery($query);

        $this->makeRevision("1.05");

        $query = "ALTER TABLE `hl7_config`
                ADD `change_filler_placer` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.06");
        $query = "CREATE TABLE `hl7_transformation` (
                `hl7_transformation_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `actor_id` INT (11) UNSIGNED,
                `actor_class` VARCHAR (80),
                `profil` VARCHAR (255),
                `message` VARCHAR (255),
                `version` VARCHAR (255),
                `extension` VARCHAR (255),
                `component` VARCHAR (255),
                `action` ENUM ('add','modify','move','delete')
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `hl7_transformation`
                ADD INDEX (`actor_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.07");

        $query = "DROP TABLE `hl7_transformation`";
        $this->addQuery($query);

        $this->makeRevision("1.08");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_expected_discharge_with_affectation` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.09");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_OBR_identity_identifier` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.10");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `build_PV1_10` `build_PV1_10` ENUM ('discipline','service','finess') DEFAULT 'discipline';";
        $this->addQuery($query);

        $query = "ALTER TABLE `hl7_config`
                CHANGE `handle_PV1_10` `handle_PV1_10` ENUM ('discipline','service','finess') DEFAULT 'discipline';";
        $this->addQuery($query);

        $this->makeRevision("1.11");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_child_admit` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.12");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `ITI9_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5';";
        $this->addQuery($query);

        $this->makeRevision("1.13");

        $query = "ALTER TABLE `hl7_config`
                ADD `control_identifier_type_code` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.14");
        $query = "ALTER TABLE `exchange_hl7v2`
                ADD `master_idex_missing` ENUM ('0','1') DEFAULT '0',
                ADD INDEX (`master_idex_missing`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v3`
                ADD `master_idex_missing` ENUM ('0','1') DEFAULT '0',
                ADD INDEX (`master_idex_missing`);";
        $this->addQuery($query);

        $this->makeRevision("1.15");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_not_master_IPP` ENUM ('0','1') DEFAULT '1',
                ADD `send_not_master_NDA` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.16");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `build_PID_18` `build_PID_18` ENUM ('normal','simple', 'none') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("1.17");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `send_no_facturable` `send_no_facturable` ENUM ('0','1','2') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.18");

        $query = "ALTER TABLE `hl7_config`
                ADD `associate_category_to_a_file` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.19");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `build_PID_18` `build_PID_18` ENUM ('normal','simple', 'sejour_id', 'none') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("1.20");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_insurance` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.21");

        $this->insertTableDescription("1295", "Entité");

        $this->insertTableEntry("1295", "ID_GLBL", "ID_GLBL", null, null, "Identifiant unique global");
        $this->insertTableEntry(
            "1295",
            "LBL_MTF_OVRTR",
            "LBL_MTF_OVRTR",
            null,
            null,
            "Libellé du motif de l'ouverture"
        );
        $this->insertTableEntry("1295", "LBL_MTF_FRMTR", "LBL_MTF_FRMTR", null, null, "Libellé du motif de fermeture");
        $this->insertTableEntry("1295", "LBL", "LBL", null, null, "Libellé");
        $this->insertTableEntry("1295", "LBL_CRT", "LBL_CRT", null, null, "Libellé court");
        $this->insertTableEntry("1295", "CD", "CD", "code", "code", "Code");
        $this->insertTableEntry("1295", "DSCRPTN", "DSCRPTN", "description", "description", "Description");
        $this->insertTableEntry(
            "1295",
            "ID_GLBL_RSPNSBL",
            "ID_GLBL_RSPNSBL",
            "user_id",
            "user_id",
            "Identifiant unique global du responsable"
        );
        $this->insertTableEntry(
            "1295",
            "NM_USL_RSPNSBL",
            "NM_USL_RSPNSBL",
            "_user_last_name",
            "_user_last_name",
            "Nom usuel du responsable"
        );
        $this->insertTableEntry(
            "1295",
            "NM_NSNC_RSPNSBL",
            "NM_NSNC_RSPNSBL",
            null,
            null,
            "Nom de naissance du responsable"
        );
        $this->insertTableEntry(
            "1295",
            "PRNM_RSPNSBL",
            "PRNM_RSPNSBL",
            "_user_first_name",
            "_user_first_name",
            "Prénom du responsable"
        );
        $this->insertTableEntry("1295", "RPPS_RSPNSBL", "RPPS_RSPNSBL", "rpps", "rpps", "Code RPPS du responsable");
        $this->insertTableEntry("1295", "ADL_RSPNSBL", "ADL_RSPNSBL", "adeli", "adeli", "Code ADELI du responsable");
        $this->insertTableEntry("1295", "CD_SPCLT", "CD_SPCLT", null, null, "Code spécialité B2 du responsable");
        $this->insertTableEntry(
            "1295",
            "TLPHN_RSPNSBL",
            "TLPHN_RSPNSBL",
            "_user_phone",
            "_user_phone",
            "Téléphone du responsable"
        );
        $this->insertTableEntry("1295", "DT_OVRTR", "DT_OVRTR", "opening_date", "opening_date", "Date d'ouverture");
        $this->insertTableEntry("1295", "DT_FRMTR", "DT_FRMTR", "closing_date", "closing_date", "Date de fermeture");
        $this->insertTableEntry(
            "1295",
            "DT_ACTVTN",
            "DT_ACTVTN",
            "activation_date",
            "activation_date",
            "Date d'activation"
        );
        $this->insertTableEntry(
            "1295",
            "DT_FN_ACTVTN",
            "DT_FN_ACTVTN",
            "inactivation_date",
            "inactivation_date",
            "Date de fin d'activation"
        );

        $this->makeRevision("1.22");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `ITI30_HL7_version` `ITI30_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','FR_2.3','FR_2.4','FR_2.5','FR_2.6') DEFAULT '2.5',
                CHANGE `ITI31_HL7_version` `ITI31_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','FR_2.3','FR_2.4','FR_2.5','FR_2.6') DEFAULT '2.5';";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI30_HL7_version` = 'FR_2.5'
                WHERE `ITI30_HL7_version` = 'FR_2.1' OR `ITI30_HL7_version` = 'FR_2.2' OR `ITI30_HL7_version` = 'FR_2.3';";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI31_HL7_version` = 'FR_2.5'
                WHERE `ITI31_HL7_version` = 'FR_2.1' OR `ITI31_HL7_version` = 'FR_2.2' OR `ITI31_HL7_version` = 'FR_2.3';";
        $this->addQuery($query);

        $this->makeRevision("1.23");

        $query = "ALTER TABLE `exchange_hl7v2`
                CHANGE `object_class` `object_class` VARCHAR (80) NOT NULL;";

        $this->addQuery($query);

        $this->makeRevision("1.24");

        $query = "ALTER TABLE `exchange_hl7v2`
                CHANGE `object_class` `object_class` VARCHAR (80);";

        $this->addQuery($query);

        $this->makeRevision("1.25");
        $query = "ALTER TABLE `hl7_config`
                ADD `create_user_to_patient` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.26");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `build_PV1_5` `build_PV1_5` ENUM ('NPA', 'sejour_id', 'none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("1.27");

        $query = "ALTER TABLE `table_description`
                ADD `valueset_id` VARCHAR (80);";
        $this->addQuery($query, true, "hl7v2");

        $this->updateTableValueSet("1", "");
        $this->updateTableValueSet("2", "1.3.6.1.4.1.12559.11.4.2.18");
        $this->updateTableValueSet("3", "");
        $this->updateTableValueSet("4", "1.3.6.1.4.1.21367.101.104");
        $this->updateTableValueSet("5", "1.3.6.1.4.1.21367.101.102");
        $this->updateTableValueSet("6", "1.3.6.1.4.1.21367.101.122");
        $this->updateTableValueSet("7", "");
        $this->updateTableValueSet("8", "");
        $this->updateTableValueSet("9", "1.3.6.1.4.1.21367.101.112");
        $this->updateTableValueSet("10", "1.3.6.1.4.1.21367.101.110");
        $this->updateTableValueSet("17", "");
        $this->updateTableValueSet("18", "");
        $this->updateTableValueSet("19", "");
        $this->updateTableValueSet("21", "");
        $this->updateTableValueSet("22", "");
        $this->updateTableValueSet("23", "");
        $this->updateTableValueSet("24", "");
        $this->updateTableValueSet("27", "");
        $this->updateTableValueSet("32", "");
        $this->updateTableValueSet("38", "1.3.6.1.4.1.12559.11.4.2.24");
        $this->updateTableValueSet("42", "");
        $this->updateTableValueSet("43", "");
        $this->updateTableValueSet("44", "");
        $this->updateTableValueSet("45", "");
        $this->updateTableValueSet("46", "");
        $this->updateTableValueSet("48", "");
        $this->updateTableValueSet("49", "");
        $this->updateTableValueSet("50", "");
        $this->updateTableValueSet("51", "");
        $this->updateTableValueSet("52", "");
        $this->updateTableValueSet("53", "");
        $this->updateTableValueSet("55", "");
        $this->updateTableValueSet("56", "");
        $this->updateTableValueSet("59", "");
        $this->updateTableValueSet("61", "");
        $this->updateTableValueSet("62", "");
        $this->updateTableValueSet("63", "");
        $this->updateTableValueSet("64", "");
        $this->updateTableValueSet("65", "");
        $this->updateTableValueSet("66", "");
        $this->updateTableValueSet("68", "");
        $this->updateTableValueSet("69", "1.3.6.1.4.1.21367.101.111");
        $this->updateTableValueSet("70", "");
        $this->updateTableValueSet("72", "");
        $this->updateTableValueSet("73", "");
        $this->updateTableValueSet("74", "");
        $this->updateTableValueSet("76", "");
        $this->updateTableValueSet("78", "2.16.840.1.113883.12.78");
        $this->updateTableValueSet("80", "");
        $this->updateTableValueSet("83", "");
        $this->updateTableValueSet("84", "");
        $this->updateTableValueSet("85", "");
        $this->updateTableValueSet("86", "");
        $this->updateTableValueSet("87", "");
        $this->updateTableValueSet("88", "");
        $this->updateTableValueSet("89", "");
        $this->updateTableValueSet("91", "");
        $this->updateTableValueSet("92", "");
        $this->updateTableValueSet("93", "");
        $this->updateTableValueSet("98", "");
        $this->updateTableValueSet("99", "");
        $this->updateTableValueSet("100", "");
        $this->updateTableValueSet("103", "");
        $this->updateTableValueSet("104", "");
        $this->updateTableValueSet("105", "");
        $this->updateTableValueSet("106", "");
        $this->updateTableValueSet("107", "");
        $this->updateTableValueSet("108", "");
        $this->updateTableValueSet("109", "");
        $this->updateTableValueSet("101", "");
        $this->updateTableValueSet("111", "");
        $this->updateTableValueSet("112", "");
        $this->updateTableValueSet("113", "");
        $this->updateTableValueSet("114", "");
        $this->updateTableValueSet("115", "1.3.6.1.4.1.21367.101.113");
        $this->updateTableValueSet("116", "");
        $this->updateTableValueSet("117", "");
        $this->updateTableValueSet("118", "");
        $this->updateTableValueSet("119", "");
        $this->updateTableValueSet("121", "");
        $this->updateTableValueSet("122", "");
        $this->updateTableValueSet("123", "1.3.6.1.4.1.12559.11.4.2.23");
        $this->updateTableValueSet("124", "1.3.6.1.4.1.21367.101.120");
        $this->updateTableValueSet("125", "2.16.840.1.113883.12.125");
        $this->updateTableValueSet("126", "");
        $this->updateTableValueSet("127", "");
        $this->updateTableValueSet("128", "");
        $this->updateTableValueSet("129", "");
        $this->updateTableValueSet("130", "");
        $this->updateTableValueSet("131", "");
        $this->updateTableValueSet("132", "");
        $this->updateTableValueSet("133", "");
        $this->updateTableValueSet("135", "");
        $this->updateTableValueSet("136", "");
        $this->updateTableValueSet("137", "");
        $this->updateTableValueSet("138", "");
        $this->updateTableValueSet("139", "");
        $this->updateTableValueSet("140", "");
        $this->updateTableValueSet("141", "");
        $this->updateTableValueSet("142", "");
        $this->updateTableValueSet("143", "");
        $this->updateTableValueSet("144", "");
        $this->updateTableValueSet("145", "");
        $this->updateTableValueSet("146", "");
        $this->updateTableValueSet("147", "");
        $this->updateTableValueSet("148", "");
        $this->updateTableValueSet("149", "");
        $this->updateTableValueSet("150", "");
        $this->updateTableValueSet("151", "");
        $this->updateTableValueSet("152", "");
        $this->updateTableValueSet("153", "");
        $this->updateTableValueSet("155", "");
        $this->updateTableValueSet("156", "");
        $this->updateTableValueSet("157", "");
        $this->updateTableValueSet("158", "");
        $this->updateTableValueSet("159", "");
        $this->updateTableValueSet("160", "");
        $this->updateTableValueSet("161", "");
        $this->updateTableValueSet("162", "");
        $this->updateTableValueSet("163", "");
        $this->updateTableValueSet("164", "");
        $this->updateTableValueSet("165", "");
        $this->updateTableValueSet("166", "");
        $this->updateTableValueSet("167", "");
        $this->updateTableValueSet("168", "");
        $this->updateTableValueSet("169", "");
        $this->updateTableValueSet("170", "");
        $this->updateTableValueSet("171", "");
        $this->updateTableValueSet("172", "");
        $this->updateTableValueSet("173", "");
        $this->updateTableValueSet("174", "");
        $this->updateTableValueSet("175", "");
        $this->updateTableValueSet("177", "");
        $this->updateTableValueSet("178", "");
        $this->updateTableValueSet("179", "");
        $this->updateTableValueSet("180", "");
        $this->updateTableValueSet("181", "");
        $this->updateTableValueSet("182", "");
        $this->updateTableValueSet("183", "");
        $this->updateTableValueSet("184", "");
        $this->updateTableValueSet("185", "");
        $this->updateTableValueSet("186", "");
        $this->updateTableValueSet("187", "");
        $this->updateTableValueSet("188", "");
        $this->updateTableValueSet("189", "");
        $this->updateTableValueSet("190", "");
        $this->updateTableValueSet("191", "");
        $this->updateTableValueSet("193", "");
        $this->updateTableValueSet("200", "1.3.6.1.4.1.12559.11.4.2.17");
        $this->updateTableValueSet("201", "");
        $this->updateTableValueSet("202", "");
        $this->updateTableValueSet("203", "");
        $this->updateTableValueSet("204", "");
        $this->updateTableValueSet("205", "");
        $this->updateTableValueSet("206", "");
        $this->updateTableValueSet("207", "");
        $this->updateTableValueSet("208", "");
        $this->updateTableValueSet("209", "");
        $this->updateTableValueSet("210", "");
        $this->updateTableValueSet("211", "");
        $this->updateTableValueSet("212", "");
        $this->updateTableValueSet("213", "");
        $this->updateTableValueSet("214", "");
        $this->updateTableValueSet("215", "");
        $this->updateTableValueSet("216", "");
        $this->updateTableValueSet("217", "");
        $this->updateTableValueSet("218", "");
        $this->updateTableValueSet("219", "");
        $this->updateTableValueSet("220", "");
        $this->updateTableValueSet("222", "");
        $this->updateTableValueSet("223", "");
        $this->updateTableValueSet("224", "1.3.6.1.4.1.21367.101.121");
        $this->updateTableValueSet("225", "");
        $this->updateTableValueSet("227", "");
        $this->updateTableValueSet("228", "");
        $this->updateTableValueSet("229", "");
        $this->updateTableValueSet("230", "");
        $this->updateTableValueSet("231", "");
        $this->updateTableValueSet("232", "");
        $this->updateTableValueSet("233", "");
        $this->updateTableValueSet("234", "");
        $this->updateTableValueSet("235", "");
        $this->updateTableValueSet("236", "");
        $this->updateTableValueSet("237", "");
        $this->updateTableValueSet("238", "");
        $this->updateTableValueSet("239", "");
        $this->updateTableValueSet("240", "");
        $this->updateTableValueSet("241", "");
        $this->updateTableValueSet("242", "");
        $this->updateTableValueSet("243", "");
        $this->updateTableValueSet("244", "");
        $this->updateTableValueSet("245", "");
        $this->updateTableValueSet("246", "");
        $this->updateTableValueSet("247", "");
        $this->updateTableValueSet("248", "");
        $this->updateTableValueSet("249", "");
        $this->updateTableValueSet("250", "");
        $this->updateTableValueSet("251", "");
        $this->updateTableValueSet("252", "");
        $this->updateTableValueSet("253", "");
        $this->updateTableValueSet("254", "");
        $this->updateTableValueSet("255", "");
        $this->updateTableValueSet("256", "");
        $this->updateTableValueSet("257", "");
        $this->updateTableValueSet("258", "");
        $this->updateTableValueSet("259", "");
        $this->updateTableValueSet("260", "");
        $this->updateTableValueSet("261", "");
        $this->updateTableValueSet("262", "");
        $this->updateTableValueSet("263", "");
        $this->updateTableValueSet("264", "");
        $this->updateTableValueSet("265", "");
        $this->updateTableValueSet("267", "");
        $this->updateTableValueSet("268", "");
        $this->updateTableValueSet("269", "");
        $this->updateTableValueSet("270", "");
        $this->updateTableValueSet("271", "");
        $this->updateTableValueSet("272", "");
        $this->updateTableValueSet("273", "");
        $this->updateTableValueSet("275", "");
        $this->updateTableValueSet("276", "");
        $this->updateTableValueSet("277", "");
        $this->updateTableValueSet("278", "");
        $this->updateTableValueSet("279", "");
        $this->updateTableValueSet("280", "");
        $this->updateTableValueSet("281", "");
        $this->updateTableValueSet("282", "");
        $this->updateTableValueSet("283", "");
        $this->updateTableValueSet("284", "");
        $this->updateTableValueSet("285", "");
        $this->updateTableValueSet("286", "");
        $this->updateTableValueSet("287", "1.3.6.1.4.1.21367.101.123");
        $this->updateTableValueSet("288", "");
        $this->updateTableValueSet("289", "");
        $this->updateTableValueSet("291", "");
        $this->updateTableValueSet("292", "");
        $this->updateTableValueSet("293", "");
        $this->updateTableValueSet("294", "");
        $this->updateTableValueSet("295", "");
        $this->updateTableValueSet("296", "");
        $this->updateTableValueSet("297", "");
        $this->updateTableValueSet("298", "");
        $this->updateTableValueSet("299", "");
        $this->updateTableValueSet("300", "");
        $this->updateTableValueSet("300", "");
        $this->updateTableValueSet("301", "");
        $this->updateTableValueSet("302", "");
        $this->updateTableValueSet("303", "");
        $this->updateTableValueSet("304", "");
        $this->updateTableValueSet("305", "");
        $this->updateTableValueSet("306", "");
        $this->updateTableValueSet("307", "");
        $this->updateTableValueSet("308", "");
        $this->updateTableValueSet("309", "");
        $this->updateTableValueSet("311", "");
        $this->updateTableValueSet("312", "");
        $this->updateTableValueSet("313", "");
        $this->updateTableValueSet("315", "");
        $this->updateTableValueSet("316", "");
        $this->updateTableValueSet("317", "");
        $this->updateTableValueSet("319", "");
        $this->updateTableValueSet("320", "");
        $this->updateTableValueSet("321", "");
        $this->updateTableValueSet("322", "");
        $this->updateTableValueSet("323", "");
        $this->updateTableValueSet("324", "");
        $this->updateTableValueSet("325", "");
        $this->updateTableValueSet("326", "");
        $this->updateTableValueSet("327", "");
        $this->updateTableValueSet("328", "");
        $this->updateTableValueSet("329", "");
        $this->updateTableValueSet("330", "");
        $this->updateTableValueSet("331", "");
        $this->updateTableValueSet("332", "");
        $this->updateTableValueSet("333", "");
        $this->updateTableValueSet("334", "");
        $this->updateTableValueSet("335", "");
        $this->updateTableValueSet("336", "");
        $this->updateTableValueSet("337", "");
        $this->updateTableValueSet("338", "");
        $this->updateTableValueSet("339", "");
        $this->updateTableValueSet("340", "");
        $this->updateTableValueSet("341", "");
        $this->updateTableValueSet("342", "");
        $this->updateTableValueSet("343", "");
        $this->updateTableValueSet("344", "");
        $this->updateTableValueSet("345", "");
        $this->updateTableValueSet("346", "");
        $this->updateTableValueSet("347", "");
        $this->updateTableValueSet("348", "");
        $this->updateTableValueSet("349", "");
        $this->updateTableValueSet("350", "");
        $this->updateTableValueSet("351", "");
        $this->updateTableValueSet("353", "");
        $this->updateTableValueSet("354", "");
        $this->updateTableValueSet("355", "");
        $this->updateTableValueSet("356", "");
        $this->updateTableValueSet("357", "");
        $this->updateTableValueSet("358", "");
        $this->updateTableValueSet("359", "");
        $this->updateTableValueSet("360", "");
        $this->updateTableValueSet("361", "");
        $this->updateTableValueSet("362", "");
        $this->updateTableValueSet("363", "");
        $this->updateTableValueSet("364", "");
        $this->updateTableValueSet("365", "");
        $this->updateTableValueSet("366", "");
        $this->updateTableValueSet("367", "");
        $this->updateTableValueSet("368", "");
        $this->updateTableValueSet("369", "2.16.840.1.113883.12.369");
        $this->updateTableValueSet("370", "");
        $this->updateTableValueSet("371", "1.3.6.1.4.1.12559.11.4.2.25");
        $this->updateTableValueSet("372", "");
        $this->updateTableValueSet("373", "");
        $this->updateTableValueSet("374", "");
        $this->updateTableValueSet("375", "");
        $this->updateTableValueSet("376", "");
        $this->updateTableValueSet("377", "");
        $this->updateTableValueSet("378", "");
        $this->updateTableValueSet("379", "");
        $this->updateTableValueSet("380", "");
        $this->updateTableValueSet("381", "");
        $this->updateTableValueSet("382", "");
        $this->updateTableValueSet("383", "");
        $this->updateTableValueSet("384", "");
        $this->updateTableValueSet("385", "");
        $this->updateTableValueSet("386", "");
        $this->updateTableValueSet("387", "");
        $this->updateTableValueSet("388", "");
        $this->updateTableValueSet("389", "");
        $this->updateTableValueSet("391", "");
        $this->updateTableValueSet("392", "");
        $this->updateTableValueSet("393", "");
        $this->updateTableValueSet("394", "");
        $this->updateTableValueSet("395", "");
        $this->updateTableValueSet("396", "");
        $this->updateTableValueSet("397", "");
        $this->updateTableValueSet("398", "");
        $this->updateTableValueSet("399", "");
        $this->updateTableValueSet("401", "");
        $this->updateTableValueSet("402", "");
        $this->updateTableValueSet("403", "");
        $this->updateTableValueSet("404", "");
        $this->updateTableValueSet("405", "");
        $this->updateTableValueSet("406", "");
        $this->updateTableValueSet("409", "");
        $this->updateTableValueSet("411", "");
        $this->updateTableValueSet("412", "");
        $this->updateTableValueSet("413", "");
        $this->updateTableValueSet("414", "");
        $this->updateTableValueSet("415", "");
        $this->updateTableValueSet("416", "");
        $this->updateTableValueSet("417", "");
        $this->updateTableValueSet("418", "");
        $this->updateTableValueSet("421", "");
        $this->updateTableValueSet("422", "");
        $this->updateTableValueSet("423", "");
        $this->updateTableValueSet("424", "");
        $this->updateTableValueSet("425", "");
        $this->updateTableValueSet("426", "");
        $this->updateTableValueSet("427", "");
        $this->updateTableValueSet("428", "");
        $this->updateTableValueSet("429", "");
        $this->updateTableValueSet("430", "");
        $this->updateTableValueSet("431", "");
        $this->updateTableValueSet("432", "");
        $this->updateTableValueSet("433", "");
        $this->updateTableValueSet("434", "");
        $this->updateTableValueSet("435", "");
        $this->updateTableValueSet("436", "");
        $this->updateTableValueSet("437", "");
        $this->updateTableValueSet("438", "");
        $this->updateTableValueSet("440", "");
        $this->updateTableValueSet("441", "");
        $this->updateTableValueSet("442", "");
        $this->updateTableValueSet("443", "1.3.6.1.4.1.21367.101.124");
        $this->updateTableValueSet("444", "");
        $this->updateTableValueSet("445", "");
        $this->updateTableValueSet("446", "");
        $this->updateTableValueSet("447", "");
        $this->updateTableValueSet("448", "");
        $this->updateTableValueSet("450", "");
        $this->updateTableValueSet("451", "");
        $this->updateTableValueSet("452", "");
        $this->updateTableValueSet("453", "");
        $this->updateTableValueSet("454", "");
        $this->updateTableValueSet("455", "");
        $this->updateTableValueSet("456", "");
        $this->updateTableValueSet("457", "");
        $this->updateTableValueSet("458", "");
        $this->updateTableValueSet("459", "");
        $this->updateTableValueSet("460", "");
        $this->updateTableValueSet("461", "");
        $this->updateTableValueSet("462", "");
        $this->updateTableValueSet("463", "");
        $this->updateTableValueSet("464", "");
        $this->updateTableValueSet("465", "");
        $this->updateTableValueSet("466", "");
        $this->updateTableValueSet("467", "");
        $this->updateTableValueSet("468", "");
        $this->updateTableValueSet("469", "");
        $this->updateTableValueSet("470", "");
        $this->updateTableValueSet("471", "");
        $this->updateTableValueSet("472", "");
        $this->updateTableValueSet("473", "");
        $this->updateTableValueSet("474", "");
        $this->updateTableValueSet("475", "");
        $this->updateTableValueSet("476", "");
        $this->updateTableValueSet("477", "");
        $this->updateTableValueSet("478", "");
        $this->updateTableValueSet("479", "");
        $this->updateTableValueSet("480", "");
        $this->updateTableValueSet("482", "");
        $this->updateTableValueSet("483", "");
        $this->updateTableValueSet("484", "");
        $this->updateTableValueSet("485", "");
        $this->updateTableValueSet("487", "");
        $this->updateTableValueSet("488", "2.16.840.1.113883.12.488");
        $this->updateTableValueSet("489", "2.16.840.1.113883.12.489");
        $this->updateTableValueSet("490", "");
        $this->updateTableValueSet("491", "");
        $this->updateTableValueSet("492", "");
        $this->updateTableValueSet("493", "");
        $this->updateTableValueSet("494", "");
        $this->updateTableValueSet("495", "");
        $this->updateTableValueSet("496", "");
        $this->updateTableValueSet("497", "");
        $this->updateTableValueSet("498", "");
        $this->updateTableValueSet("499", "");
        $this->updateTableValueSet("500", "");
        $this->updateTableValueSet("501", "");
        $this->updateTableValueSet("502", "");
        $this->updateTableValueSet("503", "");
        $this->updateTableValueSet("504", "");
        $this->updateTableValueSet("505", "");
        $this->updateTableValueSet("506", "");
        $this->updateTableValueSet("507", "");
        $this->updateTableValueSet("508", "");
        $this->updateTableValueSet("509", "");
        $this->updateTableValueSet("510", "");
        $this->updateTableValueSet("511", "");
        $this->updateTableValueSet("512", "");
        $this->updateTableValueSet("513", "");
        $this->updateTableValueSet("514", "");
        $this->updateTableValueSet("515", "");
        $this->updateTableValueSet("516", "");
        $this->updateTableValueSet("517", "");
        $this->updateTableValueSet("518", "");
        $this->updateTableValueSet("519", "");
        $this->updateTableValueSet("521", "");
        $this->updateTableValueSet("523", "");
        $this->updateTableValueSet("524", "");
        $this->updateTableValueSet("525", "");
        $this->updateTableValueSet("526", "");
        $this->updateTableValueSet("527", "");
        $this->updateTableValueSet("528", "");
        $this->updateTableValueSet("529", "");
        $this->updateTableValueSet("530", "");
        $this->updateTableValueSet("531", "");
        $this->updateTableValueSet("532", "");
        $this->updateTableValueSet("533", "");
        $this->updateTableValueSet("534", "");
        $this->updateTableValueSet("535", "");
        $this->updateTableValueSet("536", "");
        $this->updateTableValueSet("537", "");
        $this->updateTableValueSet("538", "");
        $this->updateTableValueSet("539", "");
        $this->updateTableValueSet("540", "");
        $this->updateTableValueSet("541", "");
        $this->updateTableValueSet("542", "");
        $this->updateTableValueSet("543", "");
        $this->updateTableValueSet("544", "");
        $this->updateTableValueSet("545", "");
        $this->updateTableValueSet("547", "");
        $this->updateTableValueSet("548", "");
        $this->updateTableValueSet("549", "");
        $this->updateTableValueSet("550", "");
        $this->updateTableValueSet("552", "");

        $query = "ALTER TABLE `table_entry`
                ADD `codesystem_id` VARCHAR (80);";
        $this->addQuery($query, true, "hl7v2");

        $this->updateTableCodeSytem("7", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("8", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("9", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("10", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("11", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("12", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("13", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("14", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("15", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("16", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("17", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("18", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("19", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("20", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("21", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("22", "2.16.840.1.113883.12.2");
        $this->updateTableCodeSytem("331", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("332", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("335", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("337", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("339", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5145", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5142", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5147", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5146", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5144", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5148", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5143", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("5141", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("340", "2.16.840.1.113883.12.5");
        $this->updateTableCodeSytem("341", "2.16.840.1.113883.12.5");
        $this->updateTableCodeSytem("342", "2.16.840.1.113883.12.5");
        $this->updateTableCodeSytem("343", "2.16.840.1.113883.12.5");
        $this->updateTableCodeSytem("344", "2.16.840.1.113883.12.5");
        $this->updateTableCodeSytem("345", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("346", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("347", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("348", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("349", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("350", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("351", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("352", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("353", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("354", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("355", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("356", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("357", "");
        $this->updateTableCodeSytem("358", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("359", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("360", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("361", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("362", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("363", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("364", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("365", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("366", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("367", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("368", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("369", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("370", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("371", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("372", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("373", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("374", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("375", "");
        $this->updateTableCodeSytem("376", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("377", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("378", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("379", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("380", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("381", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("382", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("383", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("384", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("385", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("386", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("387", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("388", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("389", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("390", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("391", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("392", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("393", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("394", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("395", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("396", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("397", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("398", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("399", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("400", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("401", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("402", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("403", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("404", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("405", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("406", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("407", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("408", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("409", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("410", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("411", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("412", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("413", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("414", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("415", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("416", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("417", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("418", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("419", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("420", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("421", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("422", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("423", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("424", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("425", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("426", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("427", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("428", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("429", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("430", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("444", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("445", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("446", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("447", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("448", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("449", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("450", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("451", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("452", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("453", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("454", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("455", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("456", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("457", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("458", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("459", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("479", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("480", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("481", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("485", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("487", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("887", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("888", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("889", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("890", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("891", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("892", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("893", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("894", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("895", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("896", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("897", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("898", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("899", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("900", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("901", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("902", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("903", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1078", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1079", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1080", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1081", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1082", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1083", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1084", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1085", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1088", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1089", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1090", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1091", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1092", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1093", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1094", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1095", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1096", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1097", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1098", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1099", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1100", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1101", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1102", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1103", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1104", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1105", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1106", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1107", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1108", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1109", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1110", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1111", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1112", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1113", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1114", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1115", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1116", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1492", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1493", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1494", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1495", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1496", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1498", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1499", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1500", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1501", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1502", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1503", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1504", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1505", "2.16.840.1.113883.12.200");
        $this->updateTableCodeSytem("1707", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1708", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("1709", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2248", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2249", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2250", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2251", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2252", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2253", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2254", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2882", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2883", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2884", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2885", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2886", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2887", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2888", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2889", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2890", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2891", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2892", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("2901", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2902", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2903", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2904", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2905", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2906", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2907", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2908", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2909", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2910", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2911", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2912", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2913", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2914", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2915", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2916", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2917", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2918", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2919", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2920", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2921", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2922", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2923", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2924", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2925", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2926", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2927", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2928", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2929", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2930", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2931", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2932", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2933", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2934", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2935", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2936", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2937", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2938", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2939", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2940", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2941", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2942", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2943", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2944", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2945", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2946", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2947", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2948", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2949", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2950", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2951", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2952", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2953", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2954", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2955", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2956", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("2957", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3434", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3436", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3437", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3438", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3440", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3441", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3442", "2.16.840.1.113883.12.1");
        $this->updateTableCodeSytem("3866", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3867", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3868", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3869", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3870", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3871", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3872", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3873", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3874", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3875", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3876", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3877", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3878", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3879", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3880", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3881", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3882", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3883", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3884", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3885", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3886", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3887", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3888", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3889", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3890", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3891", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3892", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3893", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3894", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3895", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3896", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3897", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3898", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3899", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3900", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3901", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3902", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3903", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3904", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3905", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3906", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3907", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3908", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3909", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3910", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3911", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3912", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3913", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3914", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3915", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3916", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3917", "1.3.6.1.4.1.21367.100.1");
        $this->updateTableCodeSytem("3918", "1.3.6.1.4.1.21367.100.1");

        $this->makeRevision("1.28");
        $query = "ALTER TABLE `hl7_config`
                ADD `handle_portail_patient` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.29");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_sejour_to_mbdmp` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.30");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `send_sejour_to_mbdmp` `send_evenement_to_mbdmp` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.31");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_not_price_indicator_for_birth` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.32");
        $this->addDefaultConfig("hl7 CHL7 sending_application", "hl7 sending_application");
        $this->addDefaultConfig("hl7 CHL7 sending_facility", "hl7 sending_facility");
        $this->addDefaultConfig("hl7 CHL7 assigning_authority_namespace_id", "hl7 assigning_authority_namespace_id");
        $this->addDefaultConfig("hl7 CHL7 assigning_authority_universal_id", "hl7 assigning_authority_universal_id");
        $this->addDefaultConfig(
            "hl7 CHL7 assigning_authority_universal_type_id",
            "hl7 assigning_authority_universal_type_id"
        );

        $this->makeRevision("1.33");
        $query = "ALTER TABLE `receiver_hl7v2`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v3`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
        $this->addQuery($query);

        $this->makeRevision("1.34");
        $query = "ALTER TABLE `sender_mllp`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
        $this->addQuery($query);

        $this->makeRevision("1.35");

        $query = "ALTER TABLE `receiver_hl7v2`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
        $this->addQuery($query);
        $query = "ALTER TABLE `receiver_hl7v3`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
        $this->addQuery($query);
        $query = "ALTER TABLE `sender_mllp`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
        $this->addQuery($query);

        $this->makeRevision("1.36");
        $query = "ALTER TABLE `exchange_hl7v2`
                ADD INDEX( `receiver_id`, `date_echange`),
                ADD INDEX( `sender_id`, `sender_class`, `date_echange`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v3`
                ADD INDEX( `receiver_id`, `date_echange`),
                ADD INDEX( `sender_id`, `sender_class`, `date_echange`);";
        $this->addQuery($query);

        $this->makeRevision("1.37");
        $query = "ALTER TABLE `hl7_config`
                ADD `ack_severity_mode` ENUM ('IWE','W','E','I') NOT NULL DEFAULT 'IWE';";
        $this->addQuery($query);

        $this->makeRevision("1.38");
        $query = "ALTER TABLE `hl7_config`
                ADD `handle_patient_ITI_31` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.39");
        $query = "ALTER TABLE `hl7_config`
                ADD `check_similar_patient` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.40");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `ITI30_HL7_version` `ITI30_HL7_version`
                ENUM( '2.1', '2.2', '2.3', '2.3.1', '2.4', '2.5', 'FR_2.3', 'FR_2.4', 'FR_2.5', 'FR_2.6', 'FRA_2.3', 'FRA_2.4', 'FRA_2.5', 'FRA_2.6' )";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI30_HL7_version` = 'FRA_2.3'
                WHERE `ITI30_HL7_version` = 'FR_2.3'";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI30_HL7_version` = 'FRA_2.4'
                WHERE `ITI30_HL7_version` = 'FR_2.4'";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI30_HL7_version` = 'FRA_2.5'
                WHERE `ITI30_HL7_version` = 'FR_2.5'";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI30_HL7_version` = 'FRA_2.6'
                WHERE `ITI30_HL7_version` = 'FR_2.6'";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `ITI30_HL7_version` `ITI30_HL7_version`
                ENUM( '2.1', '2.2', '2.3', '2.3.1', '2.4', '2.5', 'FRA_2.3', 'FRA_2.4', 'FRA_2.5', 'FRA_2.6' )";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `ITI31_HL7_version` `ITI31_HL7_version`
                ENUM( '2.1', '2.2', '2.3', '2.3.1', '2.4', '2.5', 'FR_2.3', 'FR_2.4', 'FR_2.5', 'FR_2.6', 'FRA_2.3', 'FRA_2.4', 'FRA_2.5', 'FRA_2.6' )";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI31_HL7_version` = 'FRA_2.3'
                WHERE `ITI31_HL7_version` = 'FR_2.3'";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI31_HL7_version` = 'FRA_2.4'
                WHERE `ITI31_HL7_version` = 'FR_2.4'";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI31_HL7_version` = 'FRA_2.5'
                WHERE `ITI31_HL7_version` = 'FR_2.5'";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v2_config`
                SET `ITI31_HL7_version` = 'FRA_2.6'
                WHERE `ITI31_HL7_version` = 'FR_2.6'";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v2_config`
                CHANGE `ITI31_HL7_version` `ITI31_HL7_version`
                ENUM( '2.1', '2.2', '2.3', '2.3.1', '2.4', '2.5', 'FRA_2.3', 'FRA_2.4', 'FRA_2.5', 'FRA_2.6' )";
        $this->addQuery($query);

        $this->makeRevision("1.41");

        $query = "UPDATE `message_supported`
                SET `message` = CONCAT( `message` , 'A' )
                WHERE `message` LIKE '%\\_FR'";
        $this->addQuery($query);

        $this->makeRevision("1.42");

        // Civilité
        $query = "INSERT INTO `table_description` (
              `table_description_id`, `number`, `description`, `user`
              ) VALUES (
                NULL , '9002', 'Civilité', '1'
              );";
        $this->addQuery($query, false, "hl7v2");

        // Table - 9002
        // M. - Monsieur
        $this->insertTableEntry("9002", "M.", "M.", "m", "m", "Monsieur");
        // Mme - Madame
        $this->insertTableEntry("9002", "Mme", "Mme", "mme", "mme", "Madame");

        $this->makeRevision("1.43");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `change_idex_for_admit` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.44");

        $query = "ALTER TABLE `hl7_config`
                ADD `create_duplicate_patient` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.45");

        $query = "ALTER TABLE `receiver_hl7v2_config`
               ADD `build_OBX_2` ENUM ('AD','CE','CF','CK','CN','CP','CX','DT','ED','FT','MO','NM','PN','RP','SN',
              'ST','TM','TN','TS','TX','XAD','XCN','XON','XPN','XTN') DEFAULT 'ED';";
        $this->addQuery($query);

        $this->makeRevision("1.46");

        $query = "ALTER TABLE `exchange_hl7v2`
                ADD `emptied` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `exchange_hl7v2`
                SET `emptied` = '1'
                WHERE `message_content_id` IS NULL
                AND  `acquittement_content_id` IS NULL";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v2`
                ADD INDEX `emptied_production` (`emptied`, `date_production`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v3`
                ADD `emptied` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $query = "UPDATE `exchange_hl7v3`
                SET `emptied` = '1'
                WHERE `message_content_id` IS NULL
                AND  `acquittement_content_id` IS NULL";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v3`
                ADD INDEX `emptied_production` (`emptied`, `date_production`);";
        $this->addQuery($query);

        $this->makeRevision("1.47");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_context_url` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.48");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_PV1_8` ENUM ('adressant','traitant') DEFAULT 'adressant',
                ADD `handle_PV1_9` ENUM ('null','famille') DEFAULT 'null';";
        $this->addQuery($query);

        $this->makeRevision("1.49");

        $query = "ALTER TABLE `exchange_hl7v2`
                ADD `response_datetime` DATETIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v3`
                ADD `response_datetime` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.50");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                CHANGE `ITI30_HL7_version` `ITI30_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6') DEFAULT '2.5',
                CHANGE `ITI31_HL7_version` `ITI31_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6') DEFAULT '2.5',
                CHANGE `RAD3_HL7_version` `RAD3_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1') DEFAULT '2.5',
                CHANGE `RAD48_HL7_version` `RAD48_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1') DEFAULT '2.5',
                CHANGE `ITI21_HL7_version` `ITI21_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1') DEFAULT '2.5',
                CHANGE `ITI22_HL7_version` `ITI22_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1') DEFAULT '2.5',
                CHANGE `ITI9_HL7_version` `ITI9_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1') DEFAULT '2.5',
                CHANGE `modification_admit_code` `modification_admit_code` ENUM ('A08','Z99') DEFAULT 'Z99',
                CHANGE `build_PID_6` `build_PID_6` ENUM ('nom_naissance','none') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("1.51");

        $query = "ALTER TABLE `exchange_hl7v3`
                CHANGE `date_echange` `send_datetime` DATETIME;";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v2`
                CHANGE `date_echange` `send_datetime` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("1.52");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `build_PV1_19_idex_tag` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.53");
        $this->addDependency('eai', '0.21');

        $query = "UPDATE `message_supported`
                SET `profil` = CONCAT( `profil` , 'A' )
                WHERE `profil` LIKE '%\\FR'";
        $this->addQuery($query);

        $this->makeRevision("1.54");

        $query = "ALTER TABLE `hl7_config`
                ADD `define_date_category_name` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.55");

        $query = "ALTER TABLE `hl7_config`
                ADD `control_nda_target_document` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.56");

        $query = "CREATE TABLE `log_modification_exchange` (
                `log_modification_exchange_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `content_id` INT (11) UNSIGNED NOT NULL,
                `content_class` ENUM ('CContentTabular') NOT NULL,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `datetime_update` DATETIME NOT NULL,
                `data_update` TEXT NOT NULL
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `log_modification_exchange` 
                ADD INDEX (`content_id`),
                ADD INDEX (`user_id`),
                ADD INDEX (`datetime_update`);";
        $this->addQuery($query);

        $this->makeRevision("1.57");

        $query = "ALTER TABLE `exchange_hl7v2`
                ADD INDEX `ots` (`object_class`, `type`, `sous_type`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v3`
                ADD INDEX `ots` (`object_class`, `type`, `sous_type`);";
        $this->addQuery($query);

        $this->makeRevision("1.58");

        $query = "ALTER TABLE `source_mllp` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.59");
        $query = "ALTER TABLE `source_mllp` 
                DROP `delete_file`;";
        $this->addQuery($query);

        $this->makeRevision("1.60");
        $query = "ALTER TABLE `hl7_config`
                ADD `handle_OBX_photo_patient` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.61");
        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `build_fields_format` ENUM ('normal','uppercase') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("1.62");
        $query = "ALTER TABLE `hl7_config`
                ADD `exclude_not_collide_exte` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.63");
        $query = "ALTER TABLE `hl7_config`
                ADD `force_attach_doc_admit` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.64");
        $query = "ALTER TABLE `hl7_config`
                ADD `force_attach_doc_patient` ENUM ('0','1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.65");
        $query = "ALTER TABLE `hl7_config`
                ADD `id_category_patient` INT (11);";
        $this->addQuery($query);

        $this->makeRevision("1.66");
        $query = "ALTER TABLE `hl7_config` 
                ADD `object_attach_OBX` ENUM ('CPatient','CSejour','CMbObject') DEFAULT 'CMbObject';";
        $this->addQuery($query);

        $this->makeRevision("1.67");

        $this->addMethod("changeAttachObjectOBX");

        $query = "ALTER TABLE `hl7_config`
                DROP `force_attach_doc_admit`,
                DROP `force_attach_doc_patient`;";
        $this->addQuery($query);

        $this->makeRevision("1.69");

        $query = "ALTER TABLE `hl7_config` 
                CHANGE `object_attach_OBX` `object_attach_OBX` ENUM ('CPatient','CSejour','COperation','CMbObject') DEFAULT 'CMbObject';";
        $this->addQuery($query);

        $this->makeRevision("1.70");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                CHANGE `ITI30_HL7_version` `ITI30_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6','FRA_2.7','FRA_2.8','FRA_2.9') DEFAULT '2.5',
                CHANGE `ITI31_HL7_version` `ITI31_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6','FRA_2.7','FRA_2.8','FRA_2.9') DEFAULT '2.5',
                CHANGE `RAD3_HL7_version` `RAD3_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7') DEFAULT '2.5',
                CHANGE `RAD48_HL7_version` `RAD48_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7') DEFAULT '2.5',
                CHANGE `ITI21_HL7_version` `ITI21_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7') DEFAULT '2.5',
                CHANGE `ITI22_HL7_version` `ITI22_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7') DEFAULT '2.5',
                CHANGE `ITI9_HL7_version` `ITI9_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7') DEFAULT '2.5';";
        $this->addQuery($query);

        $this->makeRevision("1.71");

        $this->addMethod("insertUserField");

        // Types de prise en charge
        $query = "INSERT INTO `table_description` (
              `table_description_id`, `number`, `description`, `user`
              ) VALUES (
                NULL , '3306', 'Types de prise en charge durant le transport', '1'
              );";
        $this->addQuery($query, false, "hl7v2");

        $this->insertTableEntry("3306", "MED", "MED", "med", "med", "Médicalisé");
        $this->insertTableEntry("3306", "PARAMED", "PARAMED", "paramed", "paramed", "Para médicalisé");
        $this->insertTableEntry(
            "3306",
            "AUCUN",
            "AUCUN",
            "aucun",
            "aucun",
            "Sans prise en charge médicalisée ou para médicalisée"
        );

        // Ajouts des nouvelles valeurs à la table 0112
        $this->insertTableEntry("112", "A", "A", "A", "A", "Absence (<12h)");
        $this->insertTableEntry("112", "P", "P", "P", "P", "Permission (< 72h)");
        $this->insertTableEntry("112", "S", "S", "S", "S", "Sortie avec programme de soins");
        $this->insertTableEntry("112", "B", "B", "B", "B", "Départ vers MCO");
        $this->insertTableEntry("112", "REO", "REO", "REO", "REO", "Réorientation");
        $this->insertTableEntry("112", "PSA", "PSA", "PSA", "PSA", "Patient parti sans attendre les soins");

        // Ajouts des nouvelles valeurs à la table 0063
        $this->insertTableEntry("63", "CUR", "CUR", "curateur", "curateur", "Curateur");

        $this->makeRevision("1.72");

        // Ajouts des nouvelles valeurs à la table 0131
        // I - Inpatient - Hospitalisation
        $set = [
            "code_hl7_to"  => "N",
            "code_mb_from" => "parent_proche",
            "code_mb_to"   => "parent_proche",
            "description"  => "Parent proche",
        ];
        $and = [
            "code_hl7_from" => "N",
        ];
        $this->updateTableEntry("131", $set, $and);
        $this->insertTableEntry("131", "P", "P", "ne_pas_prevenir", "ne_pas_prevenir", "Personne à ne pas prévenir");

        $this->makeRevision("1.73");

        // Ajouts des nouvelles valeurs à la table 0099
        $this->insertTableEntry("99", "Y", "Y", "1", "1", "Yes");
        $this->insertTableEntry("99", "N", "N", "0", "0", "No");

        $this->makeRevision("1.74");

        // Ajouts des nouvelles valeurs à la table 0323
        $this->insertTableEntry("323", "A", "A", "create", "create", "Add/Insert");
        $this->insertTableEntry("323", "D", "D", "delete", "delete", "Delete");
        $this->insertTableEntry("323", "U", "U", "store", "store", "Update");
        $this->insertTableEntry("323", "X", "X", "", "", "No change");

        $this->makeRevision("1.75");

        $set = [
            "description" => "No",
        ];
        $and = [
            "code_hl7_from" => "N",
        ];
        $this->updateTableEntry("99", $set, $and);


        $this->makeRevision("1.76");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7') DEFAULT '2.5'";
        $this->addQuery($query);

        $this->makeRevision("1.77");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `exclude_admit_type` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.78");

        $query = "ALTER TABLE `hl7_config`
                ADD `check_identifiers` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.79");

        $query = "ALTER TABLE `hl7_config`
                ADD `oru_use_sas` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.80");

        $query = "ALTER TABLE `hl7_config` 
                CHANGE `object_attach_OBX` `object_attach_OBX` ENUM ('CPatient','CSejour','COperation','CMbObject', 'CFilesCategory') DEFAULT 'CMbObject';";
        $this->addQuery($query);

        $this->makeRevision("1.81");

        $query = "ALTER TABLE `hl7_config`
                ADD `retrieve_all_PI_identifiers` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.82");

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `check_field_length` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("1.83");

        $query = "ALTER TABLE `receiver_hl7v3` 
                ADD `type` ENUM ('none','DMP','Zepra','ASIP') DEFAULT 'none';";
        $this->addQuery($query);

        $this->makeRevision("1.84");

        $query = "ALTER TABLE `hl7_config` 
                ADD `type_sas` ENUM ('lifen', 'zepra');";
        $this->addQuery($query);

        $this->addMethod("changeTypeSASORULifen");

        $this->makeRevision("1.85");

        $query = "ALTER TABLE `hl7_config` 
                DROP `oru_use_sas`;";
        $this->addQuery($query);

        $this->makeRevision("1.86");

        $query = "ALTER TABLE `hl7_config` 
                CHANGE `type_sas` `type_sas` ENUM ('lifen', 'sisra');";
        $this->addQuery($query);

        $this->makeRevision("1.87");

        $query = "ALTER TABLE `hl7_config`
                ADD `ignore_admit_with_field` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.88");

        $query = "ALTER TABLE `receiver_hl7v3`
                CHANGE `type` `type` ENUM ('DMP','ZEPRA','ASIP');";
        $this->addQuery($query);

        $query = "UPDATE `receiver_hl7v3` SET `type` = NULL WHERE `type`= 'none';
              UPDATE `receiver_hl7v3` SET `type` = 'ZEPRA' WHERE `type`= 'Zepra';";
        $this->addQuery($query);

        $this->makeRevision("1.89");

        $query = "ALTER TABLE `hl7_config`
                ADD `unqualified_identity` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.90");

        $query = "ALTER TABLE `hl7_config`
                ADD `change_OBX_5` VARCHAR (50);";
        $this->addQuery($query);

        $this->makeRevision("1.91");

        $query = "ALTER TABLE `receiver_hl7v2`
                ADD `type` VARCHAR (255);";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v3`
                CHANGE `type` `type` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.92");

        $query = "ALTER TABLE `receiver_hl7v2`
                ADD `use_specific_handler` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `receiver_hl7v3`
                ADD `use_specific_handler` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.93");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_doctolib` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.94");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_patient_SIU` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.95");
        $this->setModuleCategory("interoperabilite", "echange");

        $this->makeRevision("1.96");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `build_empty_fields` ENUM ('yes','no','restricted') DEFAULT 'no',
                ADD `fields_allowed_to_empty` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.97");

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_tamm_sih` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision("1.98");

        // Ajout d'une nouvelle valeur à la table 0111
        $this->insertTableEntry('111', 'D', 'D', 'd', 'd', "Admit cancel");

        $this->makeRevision("1.99");

        $query = "ALTER TABLE `hl7_config` 
                CHANGE `define_date_category_name` `define_name` VARCHAR (50) default 'name' ;";
        $this->addQuery($query);

        $this->addMethod("updateDefaultDefineName");

        $this->makeRevision("2.00");

        // Table - 0007 - Admission Type
        // A - Accident
        $set = [
            "code_hl7_to"  => "A",
            "code_mb_from" => "A",
            "code_mb_to"   => "A",
        ];
        $and = [
            "code_hl7_from" => "A",
        ];
        $this->updateTableEntry("7", $set, $and);

        // C - Elective
        $set = [
            "code_hl7_to"  => "C",
            "code_mb_from" => "C",
            "code_mb_to"   => "C",
        ];
        $and = [
            "code_hl7_from" => "C",
        ];
        $this->updateTableEntry("7", $set, $and);

        // E - Emergency
        $set = [
            "code_hl7_to"  => "E",
            "code_mb_from" => "E",
            "code_mb_to"   => "E",
        ];
        $and = [
            "code_hl7_from" => "E",
        ];
        $this->updateTableEntry("7", $set, $and);

        // L - Labor and Delivery
        $set = [
            "code_hl7_to"  => "L",
            "code_mb_from" => "L",
            "code_mb_to"   => "L",
        ];
        $and = [
            "code_hl7_from" => "L",
        ];
        $this->updateTableEntry("7", $set, $and);

        // N - Newborn (Birth in healthcare facility)
        $set = [
            "code_hl7_to"  => "N",
            "code_mb_from" => "N",
            "code_mb_to"   => "N",
        ];
        $and = [
            "code_hl7_from" => "N",
        ];
        $this->updateTableEntry("7", $set, $and);

        // R - Routine
        $set = [
            "code_hl7_to"  => "R",
            "code_mb_from" => "R",
            "code_mb_to"   => "R",
        ];
        $and = [
            "code_hl7_from" => "R",
        ];
        $this->updateTableEntry("7", $set, $and);

        // U - Urgent
        $set = [
            "code_hl7_to"  => "U",
            "code_mb_from" => "U",
            "code_mb_to"   => "U",
        ];
        $and = [
            "code_hl7_from" => "U",
        ];
        $this->updateTableEntry("7", $set, $and);

        $this->makeRevision("2.01");

        // RM - Rétrocession du médicament
        $this->insertTableEntry('7', 'RM', 'RM', 'RM', 'RM', "Rétrocession du médicament");
        // IE - Prestation inter-établissements
        $this->insertTableEntry('7', 'IE', 'IE', 'IE', 'IE', "Prestation inter-établissements");

        $this->makeRevision("2.02");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `build_PV1_4` ENUM ('normal','charge_price_indicator') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("2.03");

        $query = "ALTER TABLE `hl7_config` 
                ADD `handle_PV1_4` ENUM ('normal','charge_price_indicator') DEFAULT 'normal';";
        $this->addQuery($query);

        $this->makeRevision("2.04");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `sih_cabinet_id` INT (11);";
        $this->addQuery($query);

        $this->makeRevision("2.05");

        $query = "ALTER TABLE `sender_mllp` 
                ADD `response` ENUM ('none','auto_generate_before','postprocessor') DEFAULT 'none';";
        $this->addQuery($query);

        $query = "UPDATE `sender_mllp`
                SET `response` = 'postprocessor'
                WHERE `create_ack_file` = '1'";
        $this->addQuery($query);

        $query = "ALTER TABLE `sender_mllp` 
                DROP `create_ack_file`,
                DROP `delete_file`;";
        $this->addQuery($query);

        $this->makeRevision("2.06");

        $query = "ALTER TABLE `hl7_config` 
                ADD `generate_IPP_unqualified_identity` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("2.07");

        $query = "ALTER TABLE `hl7_config` 
                ADD `handle_IIM_6` ENUM ('group','service','sejour');";
        $this->addQuery($query);

        $this->makeRevision("2.08");

        $query = "ALTER TABLE `hl7_config` 
                ADD `search_patient` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("2.09");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `RAD28_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5') DEFAULT '2.5';";
        $this->addQuery($query);

        $this->makeRevision("2.10");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `files_mode_sas` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("2.11");

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                ADD `cabinet_sih_id` INT (11);";
        $this->addQuery($query);

        $this->makeRevision("2.12");

        $query = "ALTER TABLE `hl7_config`
                ADD `creation_date_file_like_treatment` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.13");

        $this->insertTableDescription('9003', 'Mode d\'obtention de l\'identité');
        $this->insertTableEntry('9003', 'SM', 'SM', 'manuel', 'manuel', 'Saisie manuelle');
        $this->insertTableEntry('9003', 'CV', 'CV', 'carte_vitale', 'carte_vitale', 'Carte vitale');
        $this->insertTableEntry('9003', 'CV', 'CV', 'insi', 'insi', 'Téléservice INSi');
        $this->insertTableEntry('9003', 'CV', 'CV', 'code_barre', 'code_barre', 'Code à barre');
        $this->insertTableEntry('9003', 'RFID', 'RFID', 'rfid', 'rfid', 'Puce RFID');
        $this->insertTableEntry('9003', 'IN', 'IN', 'interop', 'interop', 'Interopérabilité');
        $this->insertTableEntry('9003', 'IM', 'IM', 'import', 'import', 'Import');

        $this->insertTableDescription('9004', 'Type de justificatif d\'identité');
        $this->insertTableEntry('9004', 'AN', 'AN', 'acte_naissance', 'acte_naissance', 'Extrait d\'acte de naissance');
        $this->insertTableEntry('9004', 'CC', 'CC', '', '', 'Carnet de circulation');
        $this->insertTableEntry('9004', 'CE', 'CE', '', '', 'Carte européenne');
        $this->insertTableEntry('9004', 'CM', 'CM', '', '', 'Carte militaire');
        $this->insertTableEntry('9004', 'CN', 'CN', 'carte_identite', 'carte_identite', 'Carte nationale d\'identité');
        $this->insertTableEntry('9004', 'CS', 'CS', 'carte_sejour', 'carte_sejour', 'Carte de séjour');
        $this->insertTableEntry('9004', 'LE', 'LE', 'livret_famille', 'livret_famille', 'Livret de famille');
        $this->insertTableEntry('9004', 'PA', 'PA', 'passeport', 'passeport', 'Passeport');
        $this->insertTableEntry('9004', 'PC', 'PC', '', '', 'Permis de conduire');

        $this->makeRevision('2.14');

        $query = 'ALTER TABLE `exchange_hl7v2` ADD `altered_content_id` INT (11) UNSIGNED;';
        $this->addQuery($query);

        $query = "ALTER TABLE `exchange_hl7v2` 
                ADD INDEX (`altered_content_id`);";
        $this->addQuery($query);

        $this->makeRevision('2.15');

        $query = "ALTER TABLE `receiver_hl7v2_config`
               ADD `build_OBX_3` ENUM ('DMP','INTERNE') DEFAULT 'INTERNE';";
        $this->addQuery($query);

        $this->makeRevision('2.16');

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_SIU_object` ENUM ('consultation','intervention') DEFAULT 'consultation';";
        $this->addQuery($query);

        $this->makeRevision('2.17');

        // INSI
        $set   = [
            "code_hl7_to"   => "INSI",
            "code_hl7_from" => "INSI",
        ];
        $where = [
            "code_mb_from" => "insi",
        ];
        $this->updateTableEntry("9003", $set, $where);

        // Code à barre
        $set   = [
            "code_hl7_to"   => "CB",
            "code_hl7_from" => "CB",
        ];
        $where = [
            "code_mb_from" => "code_barre",
        ];
        $this->updateTableEntry("9003", $set, $where);

        $this->makeRevision('2.18');

        $query = "ALTER TABLE `source_mllp`
                  ADD `timeout_socket` INT (3) DEFAULT '5',
                  ADD `timeout_period_stream` INT (3);";
        $this->addQuery($query);

        $this->makeRevision('2.19');

        $query = "ALTER TABLE `source_mllp`
                  ADD `set_blocking` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.20');

        $query = "CREATE TABLE `exchange_mllp` (
                `exchange_mllp_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `emetteur` VARCHAR (255),
                `destinataire` VARCHAR (255),
                `date_echange` DATETIME NOT NULL,
                `response_datetime` DATETIME,
                `function_name` VARCHAR (255) NOT NULL,
                `input` LONGTEXT,
                `output` LONGTEXT,
                `purge` ENUM ('0','1') DEFAULT '0',
                `response_time` FLOAT,
                `source_id` INT (11) UNSIGNED,
                `source_class` ENUM ('CSourceMLLP')
              )/*! ENGINE=MyISAM */;
              ALTER TABLE `exchange_mllp` 
                ADD INDEX (`date_echange`),
                ADD INDEX (`response_datetime`),
                ADD INDEX source (source_class, source_id);";
        $this->addQuery($query);

        $this->makeRevision('2.21');

        $query = "ALTER TABLE `hl7_config` 
                CHANGE `type_sas` `type_sas` ENUM ('lifen', 'sisra', 'sih_cabinet');";
        $this->addQuery($query);
        $this->makeRevision('2.22');

        $query = "ALTER TABLE `sender_mllp` 
                ADD `type` VARCHAR (255);";
        $this->addQuery($query);

        $this->makeRevision('2.23');

        $this->insertTableEntry("364", 'WITHDRAWAL', null, null, null, "Appointment to move forward if withdrawal");

        $this->makeRevision('2.24');

        $this->insertTableEntry("364", 'CE', null, null, null, "Clinical examination");
        $this->insertTableEntry("364", 'HD', null, null, null, "History of the disease");

        $this->makeRevision('2.25');

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                CHANGE `ITI30_HL7_version` `ITI30_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6','FRA_2.7','FRA_2.8','FRA_2.9','FRA_2.10') DEFAULT '2.5',
                CHANGE `ITI31_HL7_version` `ITI31_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6','FRA_2.7','FRA_2.8','FRA_2.9','FRA_2.10') DEFAULT '2.5';";
        $this->addQuery($query);

        $this->makeRevision('2.26');

        $query = "ALTER TABLE `exchange_hl7v2`
                    DROP INDEX group_id,
                    ADD INDEX group_date (group_id, date_production),
                    ADD INDEX sender (sender_class, sender_id),
                    ADD INDEX object (object_class, object_id),
                    ADD INDEX sender_date (sender_id, date_production),
                    ADD INDEX receiver_date (receiver_id, date_production);";
        $this->addQuery($query);

        $this->makeRevision('2.27');

        $query = "ALTER TABLE `hl7_config`
                    ADD `mode_sas` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->addMethod("updateModeSAS");

        $this->makeRevision('2.28');

        $query = "ALTER TABLE `receiver_hl7v2_config`
                    ADD `build_PID_5_2` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.29');

        $query = "ALTER TABLE `receiver_hl7v2_config` 
                    CHANGE `HL7_version` `HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2') DEFAULT '2.5',
                    CHANGE `ITI30_HL7_version` `ITI30_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6','FRA_2.7','FRA_2.8','FRA_2.9','FRA_2.10') DEFAULT '2.5',
                    CHANGE `ITI31_HL7_version` `ITI31_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2','FRA_2.3','FRA_2.4','FRA_2.5','FRA_2.6','FRA_2.7','FRA_2.8','FRA_2.9','FRA_2.10') DEFAULT '2.5',
                    CHANGE `RAD3_HL7_version` `RAD3_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2') DEFAULT '2.5',
                    CHANGE `RAD28_HL7_version` `RAD28_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2') DEFAULT '2.5',
                    CHANGE `RAD48_HL7_version` `RAD48_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2') DEFAULT '2.5',
                    CHANGE `ITI21_HL7_version` `ITI21_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2') DEFAULT '2.5',
                    CHANGE `ITI22_HL7_version` `ITI22_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2') DEFAULT '2.5',
                    CHANGE `ITI9_HL7_version` `ITI9_HL7_version` ENUM ('2.1','2.2','2.3','2.3.1','2.4','2.5','2.5.1','2.6','2.7','2.7.1','2.8','2.8.1','2.8.2') DEFAULT '2.5';";
        $this->addQuery($query);

        $this->makeRevision('2.30');

        $query = "ALTER TABLE `hl7_config`
                ADD `handle_patient_ORU` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('2.31');

        $query = "ALTER TABLE `receiver_hl7v2_config`
                ADD `send_NTE` ENUM ('0','1') DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision('2.32');

        // Acte de naissance
        $set = [
            "code_mb_from" => "BIRTH_ACT",
            "code_mb_to"   => "BIRTH_ACT",
        ];
        $and = [
            "code_hl7_from" => "AN",
        ];
        $this->updateTableEntry("9004", $set, $and);

        // Carte nationale d'identité
        $set = [
            "code_mb_from" => "ID_CARD",
            "code_mb_to"   => "ID_CARD",
        ];
        $and = [
            "code_hl7_from" => "CN",
        ];
        $this->updateTableEntry("9004", $set, $and);

        // Carte de séjour
        $set = [
            "code_mb_from" => "RESIDENT_PERMIT",
            "code_mb_to"   => "RESIDENT_PERMIT",
        ];
        $and = [
            "code_hl7_from" => "CS",
        ];
        $this->updateTableEntry("9004", $set, $and);

        // Livret de famille
        $set = [
            "code_mb_from" => "FAMILY_RECORD_BOOK",
            "code_mb_to"   => "FAMILY_RECORD_BOOK",
        ];
        $and = [
            "code_hl7_from" => "LE",
        ];
        $this->updateTableEntry("9004", $set, $and);

        // Passeport
        $set = [
            "code_mb_from" => "PASSEPORT",
            "code_mb_to"   => "PASSEPORT",
        ];
        $and = [
            "code_hl7_from" => "PA",
        ];
        $this->updateTableEntry("9004", $set, $and);

        $this->makeRevision('2.33');
        $query = "ALTER TABLE `source_mllp` ADD `client_name`  VARCHAR(255);";
        $this->addQuery($query);

        $this->makeRevision('2.34');
        $query = "ALTER TABLE `hl7_config` 
                ADD `search_patient_strategy` VARCHAR(255) DEFAULT 'best';";
        $this->addQuery($query);

        $this->makeRevision('2.35');
        $query = "ALTER TABLE `source_mllp` 
        ADD `retry_strategy` VARCHAR(255), 
        ADD `first_call_date` DATETIME;";
        $this->addQuery($query);

        $this->makeRevision("2.36");

        $query = "ALTER TABLE `hl7_config`
                ADD `ignore_the_patient_with_an_unauthorized_IPP` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("2.37");

        $query = "ALTER TABLE `hl7_config`
                CHANGE `handle_SIU_object` `handle_SIU_object` ENUM ('consultation','intervention', 'element_prescription') DEFAULT 'consultation';";
        $this->addQuery($query);

        $this->mod_version = "2.38";

        $query = "SHOW TABLES LIKE 'table_description'";
        $this->addDatasource("hl7v2", $query);
    }

    function updateTableEntry($number, $update, $where)
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if (!$dshl7 || !$dshl7->loadTable("table_entry")) {
            return true;
        }

        foreach ($update as $field => $value) {
            $set[] = "`$field` = '$value'";
        }

        $and = "";
        foreach ($where as $field => $value) {
            $and .= "AND `$field` = '$value' ";
        }
        $query = "UPDATE `table_entry`
              SET " . implode(", ", $set) . "
              WHERE `number` = '$number'
              $and;";

        $this->addQuery($query, false, "hl7v2");
    }

    function insertTableEntry(
        $number,
        $code_hl7_from,
        $code_hl7_to,
        $code_mb_from,
        $code_mb_to,
        $description,
        $user = 1
    ) {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if (!$dshl7 || !$dshl7->loadTable("table_entry")) {
            return true;
        }

        $description = $this->ds->escape($description);

        $code_hl7_from = ($code_hl7_from === null) ? "NULL" : "'$code_hl7_from'";
        $code_hl7_to   = ($code_hl7_to === null) ? "NULL" : "'$code_hl7_to'";
        $code_mb_from  = ($code_mb_from === null) ? "NULL" : "'$code_mb_from'";
        $code_mb_to    = ($code_mb_to === null) ? "NULL" : "'$code_mb_to'";


        $query = "INSERT INTO `table_entry` (
              `table_entry_id`, `number`, `code_hl7_from`, `code_hl7_to`, `code_mb_from`, `code_mb_to`, `description`, `user`
              ) VALUES (
                NULL , '$number', $code_hl7_from, $code_hl7_to, $code_mb_from, $code_mb_to, '$description', '$user'
              );";

        $this->addQuery($query, false, "hl7v2");
    }

    function deleteTableEntry($number, $where)
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if (!$dshl7 || !$dshl7->loadTable("table_entry")) {
            return true;
        }

        $and = "";
        foreach ($where as $field => $value) {
            $and .= "AND `$field` = '$value' ";
        }

        $query = "DELETE FROM `table_entry`
              WHERE `number` = '$number'
              $and;";

        $this->addQuery($query, false, "hl7v2");
    }

    function updateTableSource($nameTable)
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if (!$dshl7 || !$dshl7->loadTable("table_entry")) {
            return true;
        }

        $query = "UPDATE $nameTable
                SET $nameTable.name = Replace(name, 'CReceiverIHE', 'CReceiverHL7v2')
                WHERE $nameTable.name LIKE 'CReceiverIHE-%';";
        $this->addQuery($query);
    }

    function insertTableDescription($number, $description, $user = 1)
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if (!$dshl7 || !$dshl7->loadTable("table_description")) {
            return true;
        }

        $description = $this->ds->escape($description);

        $query = "INSERT INTO `table_description` (
              `table_description_id`, `number`, `description`, `user`
              ) VALUES (
                NULL , '$number', '$description', '$user'
              );";

        $this->addQuery($query, false, "hl7v2");
    }

    protected function insertUserField(): bool
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if ($dshl7 && !$dshl7->hasField("table_description", "user")) {
            $query = "ALTER TABLE `table_description`
                ADD `user` ENUM ('0','1') NOT NULL DEFAULT '0';";
            $this->addQuery($query, true, "hl7v2");
        }

        return true;
    }

    function updateTableValueSet($numberTable, $id)
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if (!$dshl7 || !$dshl7->loadTable("table_entry")) {
            return true;
        }

        $query = "UPDATE `table_description`
                SET `valueset_id` = '$id'
                WHERE `table_description_id` = '$numberTable' ;";
        $this->addQuery($query, false, "hl7v2");
    }

    function updateTableCodeSytem($table_id, $id)
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);
        if (!$dshl7 || !$dshl7->loadTable("table_entry")) {
            return true;
        }

        $query = "UPDATE `table_entry`
                SET `codesystem_id` = '$id'
                WHERE `table_entry_id` = '$table_id' ;";
        $this->addQuery($query, false, "hl7v2");
    }

    /**
     * Check HL7v2 tables presence
     *
     * @return bool
     */
    protected function checkHL7v2Tables()
    {
        $dshl7 = CSQLDataSource::get("hl7v2", true);

        if (!$dshl7 || !$dshl7->loadTable("table_entry")) {
            CAppUI::setMsg("CHL7v2Tables-missing", UI_MSG_ERROR);

            return false;
        }

        return true;
    }

    /**
     * Add constantes ranks
     *
     * @return bool
     */
    protected function changeAttachObjectOBX()
    {
        $ds = $this->ds;

        if ($ds->hasTable("hl7_config")) {
            $results = $ds->exec("SELECT * FROM `hl7_config`");

            if ($results) {
                while ($row = $ds->fetchAssoc($results)) {
                    $hl7_config_id = CMbArray::get($row, "hl7_config_id");

                    $object_attach_OBX = null;
                    if (CMbArray::get($row, "force_attach_doc_admit")) {
                        $object_attach_OBX = "CSejour";
                    }
                    if (CMbArray::get($row, "force_attach_doc_patient")) {
                        $object_attach_OBX = "CPatient";
                    }

                    if ($object_attach_OBX) {
                        $query = "UPDATE `hl7_config`
                        SET `object_attach_OBX` = '$object_attach_OBX'
                        WHERE `hl7_config_id` = '$hl7_config_id';";

                        $ds->exec($query);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Set default config define_name
     *
     * @return bool
     */
    protected function updateDefaultDefineName()
    {
        $ds = $this->ds;

        if ($ds->hasTable("hl7_config")) {
            $results = $ds->exec("SELECT * FROM `hl7_config`");

            if ($results) {
                while ($row = $ds->fetchAssoc($results)) {
                    $hl7_config_id = CMbArray::get($row, "hl7_config_id");

                    $define_name = 'name';
                    if (CMbArray::get($row, "id_category_patient") || CMbArray::get($row, "define_name")) {
                        $define_name = "datefile_category";
                    }

                    $query = "UPDATE `hl7_config`
                      SET `define_name` = '$define_name'
                      WHERE `hl7_config_id` = '$hl7_config_id';";

                    $ds->exec($query);
                }
            }
        }

        return true;
    }

    /**
     * Change type SAS
     *
     * @return bool
     */
    protected function changeTypeSASORULifen()
    {
        $ds = $this->ds;

        if ($ds->hasTable("hl7_config")) {
            $results = $ds->exec("SELECT * FROM `hl7_config`");

            if ($results) {
                while ($row = $ds->fetchAssoc($results)) {
                    $query = "UPDATE `hl7_config`
                      SET `type_sas` = 'lifen'
                      WHERE `oru_use_sas` = '1'";

                    $ds->exec($query);
                }
            }
        }

        return true;
    }

    /**
     * Update mode SAS
     *
     * @return bool
     */
    protected function updateModeSAS()
    {
        $ds = $this->ds;

        if ($ds->hasTable("hl7_config")) {
            $results = $ds->exec(
                "SELECT * FROM `hl7_config` WHERE `type_sas` = 'lifen' OR `type_sas` = 'sisra' OR `type_sas` = 'sih_cabinet'"
            );

            if ($results) {
                while ($row = $ds->fetchAssoc($results)) {
                    $hl7_config_id = CMbArray::get($row, "hl7_config_id");

                    // On active le mode SAS
                    $query = "UPDATE `hl7_config` SET `mode_sas` = '1' WHERE `hl7_config_id` = '$hl7_config_id';";
                    $ds->exec($query);

                    $sender_class = CMbArray::get($row, "sender_class");
                    $sender_id    = CMbArray::get($row, "sender_id");
                    /** @var CInteropSender $sender */
                    $sender = CMbObject::loadFromGuid("$sender_class-$sender_id");
                    if ($sender->_id) {
                        switch (CMbArray::get($row, "type_sas")) {
                            case 'sisra':
                                $sender->type = CInteropActor::ACTOR_ZEPRA;
                                $sender->store();
                                break;
                            case 'sih_cabinet':
                                $sender->type = CInteropActor::ACTOR_TAMM;
                                $sender->store();
                                break;

                            default:
                        }
                    }
                }
            }
        }

        return true;
    }
}
