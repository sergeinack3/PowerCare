<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\SalleOp;

use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;

/**
 * @package Ox\Mediboard\SalleOp
 * @codeCoverageIgnore
 */
class CSetupSalleOp extends CSetup
{
    /**
     * Add a new HAS check list
     *
     * @param array  $check_list   The check list description
     * @param string $object_class The associated object class
     *
     * @return void
     */
    private function addNewCheckList($check_list, $object_class = 'COperation')
    {
        foreach ($check_list as $title => $cat) {
            // Ajout de la catégorie
            $query = "INSERT INTO `daily_check_item_category` (`title`, `desc`, `target_class`, `type`) VALUES
                               (?1, ?2, '$object_class', ?3)";
            $query = $this->ds->prepare($query, $title, $cat[1], $cat[0]);
            $this->addQuery($query);

            // Ajout des élements
            foreach ($cat[2] as $i => $type) {
                $query = "INSERT INTO `daily_check_item_type` (`title`, `active`, `attribute`, `category_id`, `index`, `default_value`) VALUES
                    (?1, '1', ?2, (
                      SELECT `daily_check_item_category_id`
                      FROM `daily_check_item_category`
                      WHERE `title` = ?3 AND `target_class` = '$object_class' AND `type` = ?4
                    ), ?5, ?6)";
                $query = $this->ds->prepare($query, $type[0], $type[1], $title, $cat[0], $i + 1, $type[2]);
                $this->addQuery($query);
            }
        }
    }

    /**
     * Move a check list category
     *
     * @param string $title        Original title
     * @param string $type         Original type
     * @param string $new_title    New title
     * @param string $new_type     New type
     * @param string $object_class Target class
     *
     * @return void
     */
    private function moveCheckListCategory($title, $type, $new_title, $new_type = null, $object_class = "COperation")
    {
        $update_new_type = "";
        if ($new_type) {
            $update_new_type = ", `type` = '$new_type";
        }

        $query = "UPDATE `daily_check_item_category` SET
                `title` = '$new_title'
                $update_new_type
                WHERE `title` = '$title'
                  AND `target_class` = '$object_class'
                  AND `type` = '$type'";
        $this->addQuery($query);
    }

    /**
     * Changes check list categories
     *
     * @param array $category_changes Categories changes
     *
     * @return void
     */
    private function changeCheckListCategories($category_changes)
    {
        // reverse because of the title changes
        $category_changes = array_reverse($category_changes);

        // Category changes
        foreach ($category_changes as $_change) {
            $cat_class     = $_change[0];
            $cat_type      = $_change[1];
            $cat_title     = $_change[2];
            $cat_new_title = addslashes(CValue::read($_change, 3, ''));
            $cat_new_desc  = addslashes(CValue::read($_change, 4, ''));

            $query = "UPDATE `daily_check_item_category` SET
        `daily_check_item_category`.`title` = '$cat_new_title' ";

            if (isset($cat_new_desc)) {
                $query .= ", `daily_check_item_category`.`desc` = '$cat_new_desc' ";
            }

            $query .= "WHERE
        `daily_check_item_category`.`target_class` = '$cat_class' AND
        `daily_check_item_category`.`type` = '$cat_type' AND
        `daily_check_item_category`.`title` = '$cat_title'";
            $this->addQuery($query);
        }
    }

    /**
     * Creates check list categories
     *
     * @param array $category_additions A structure containing categories
     *
     * @return void
     */
    private function addCheckListCategories($category_additions)
    {
        foreach ($category_additions as $_change) {
            $query = "INSERT INTO `daily_check_item_category` (`target_class`, `type`, `title`, `desc`) VALUES (%1, %2, %3, %4)";
            $query = $this->ds->prepare($query, $_change[0], $_change[1], $_change[2], '');
            $this->addQuery($query);
        }
    }

    /**
     * Change check list types
     *
     * @param array $changes A structure containing changes
     *
     * @return void
     */
    private function changeCheckListTypes($changes)
    {
        foreach ($changes as $_change) {
            $cat_class = $_change[0];
            $cat_type  = $_change[1];
            $cat_title = $_change[2];

            $data  = $_change[3];
            $index = $data["index"];

            $query = "UPDATE `daily_check_item_type`
                  LEFT JOIN `daily_check_item_category` ON `daily_check_item_category`.`daily_check_item_category_id` = `daily_check_item_type`.`category_id`
                  SET ";

            if (isset($data["title"])) {
                $query .= " `daily_check_item_type`.`title` = '" . addslashes($data["title"]) . "' ";
            }

            if (isset($data["attribute"])) {
                if (isset($data["title"])) {
                    $query .= ",";
                }
                $query .= " `daily_check_item_type`.`attribute` = '" . $data["attribute"] . "' ";
            }

            if (isset($data["default"])) {
                if (isset($data["title"]) || isset($data["attribute"])) {
                    $query .= ",";
                }
                $query .= " `daily_check_item_type`.`default_value` = '" . $data["default"] . "' ";
            }

            $query .= "WHERE
        `daily_check_item_category`.`target_class` = '$cat_class' AND
        `daily_check_item_category`.`type` = '$cat_type' AND
        `daily_check_item_category`.`title` = '$cat_title' AND
        `daily_check_item_type`.`index` = '$index'";
            $this->addQuery($query);
        }
    }

    /**
     * Creation des index (pas au sens index SQL, mais pour ordonner les types dans chanque catégorie)
     *
     * @return bool
     */
    protected function addCheckItemsIndex()
    {
        $ds = $this->ds;

        $sub_query  = "SELECT `daily_check_item_category`.`daily_check_item_category_id` FROM `daily_check_item_category`";
        $categories = $ds->loadList($sub_query);

        foreach ($categories as $_category) {
            $id        = reset($_category);
            $sub_query = "SELECT `daily_check_item_type`.`daily_check_item_type_id`
          FROM `daily_check_item_type`
          WHERE `daily_check_item_type`.`category_id` = '$id'";

            $types = $ds->loadList($sub_query);

            foreach ($types as $_index => $_type) {
                $type_id = reset($_type);
                $_index++;

                $update_query = "UPDATE `daily_check_item_type`
            SET `daily_check_item_type`.`index` = '$_index'
            WHERE `daily_check_item_type`.`daily_check_item_type_id` = '$type_id'";
                $ds->exec($update_query);
            }
        }

        return true;
    }

    /**
     * Add a group_id to the check lists
     *
     * @return bool
     */
    protected function listToGroup()
    {
        $ds = $this->ds;

        $query         = "SELECT `daily_check_item_category`.`list_type_id`, `daily_check_item_type`.`group_id`
        FROM `daily_check_item_type`
        LEFT JOIN `daily_check_item_category`
               ON `daily_check_item_category`.`daily_check_item_category_id` = `daily_check_item_type`.`category_id`
        LEFT JOIN `daily_check_list_type`
               ON `daily_check_list_type`.`daily_check_list_type_id` = `daily_check_item_category`.`list_type_id`
        WHERE `daily_check_item_type`.`category_id` = `daily_check_item_category`.`daily_check_item_category_id`
        AND `daily_check_item_category`.`list_type_id` IS NOT NULL
        GROUP BY `daily_check_item_category`.`list_type_id`, `daily_check_item_type`.`group_id`";
        $list_to_group = $ds->loadHashList($query);

        foreach ($list_to_group as $list_type_id => $group_id) {
            $query = "UPDATE `daily_check_list_type` SET
           `group_id` = '$group_id'
           WHERE `daily_check_list_type`.daily_check_list_type_id = '$list_type_id'";
            $ds->exec($query);
        }

        return true;
    }

    /**
     * Modification de titre dans la checklist endo digestive 2013
     *
     * @return bool
     */
    protected function changeTitlesEndo2013()
    {
        $ds_std = CSQLDataSource::get("std");

        $sub_query                    = "SELECT `daily_check_item_category`.`daily_check_item_category_id`
                  FROM `daily_check_item_category`
                  WHERE `desc` = 'Vérification croisée par l\'équipe de points critiques et des mesures adéquates à prendre'
                  AND `target_class` = 'COperation'
                  AND `type` = 'preendoscopie'
                  AND `title` =  '04'";
        $daily_check_item_category_id = $ds_std->loadResult($sub_query);

        $query = "UPDATE `daily_check_item_category`
      SET `desc` = 'Vérification croisée par l\'équipe de points critiques et mise en ?uvre si besoin, des mesures adéquates. Le patient présente-t-il:'
      WHERE `daily_check_item_category_id` = '$daily_check_item_category_id'";
        $ds_std->exec($query);

        $query = "UPDATE `daily_check_item_type`
      SET `title` = 'un risque allergique'
      WHERE `category_id` = '$daily_check_item_category_id'
      AND title='allergie du patient'";
        $ds_std->exec($query);

        $query = "UPDATE `daily_check_item_type`
      SET `title` = 'un risque d\'inhalation, de difficulté d\'intubation ou de ventilation au masque'
      WHERE `category_id` = '$daily_check_item_category_id'
      AND title='risque d\'inhalation, de difficulté d\'intubation ou de ventilation au masque'";
        $ds_std->exec($query);

        $query = "UPDATE `daily_check_item_type`
      SET `title` = 'un risque de saignement important'
      WHERE `category_id` = '$daily_check_item_category_id'
      AND title='risque de saignement important'";
        $ds_std->exec($query);

        return true;
    }

    function __construct()
    {
        parent::__construct();

        $this->mod_name = "dPsalleOp";

        $this->addDependency("dPpersonnel", "0.11");

        $this->makeRevision("0.0");

        $this->makeRevision("0.1");
        $query = "CREATE TABLE `acte_ccam` (
                `acte_id` INT NOT NULL ,
                `code_activite` VARCHAR( 2 ) NOT NULL ,
                `code_phase` VARCHAR( 1 ) NOT NULL ,
                `execution` DATETIME NOT NULL ,
                `modificateurs` VARCHAR( 4 ) ,
                `montant_depassement` FLOAT,
                `commentaire` TEXT,
                `operation_id` INT NOT NULL ,
                `executant_id` INT NOT NULL ,
              PRIMARY KEY ( `acte_id` )) /*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $this->makeRevision("0.11");
        $query = "ALTER TABLE `acte_ccam`
                ADD `code_acte` CHAR( 7 ) NOT NULL AFTER `acte_id`";
        $this->addQuery($query);
        $query = "ALTER TABLE `acte_ccam`
                ADD UNIQUE (`code_acte`, `code_activite`, `code_phase`, `operation_id`)";
        $this->addQuery($query);

        $this->makeRevision("0.12");
        $query = "ALTER TABLE `acte_ccam`
                CHANGE `acte_id` `acte_id` INT( 11 ) NOT NULL AUTO_INCREMENT";
        $this->addQuery($query);

        $this->makeRevision("0.13");
        $query = "ALTER TABLE `acte_ccam`
                DROP INDEX `code_acte`";
        $this->addQuery($query);

        $this->makeRevision("0.14");
        $query = "ALTER TABLE `acte_ccam`
                CHANGE `acte_id` `acte_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                CHANGE `operation_id` `operation_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `executant_id` `executant_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `code_activite` `code_activite` tinyint(2) unsigned zerofill NOT NULL,
                CHANGE `code_phase` `code_phase` tinyint(1) unsigned zerofill NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.15");
        $query = "ALTER TABLE `acte_ccam`
                CHANGE `code_acte` `code_acte` varchar(7) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.16");
        $query = "ALTER TABLE `acte_ccam`
                CHANGE `code_activite` `code_activite` TINYINT(4) NOT NULL,
                CHANGE `code_phase` `code_phase` TINYINT(4) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.17");
        $query = "ALTER TABLE `acte_ccam`
                CHANGE `operation_id` `subject_id` int(11) unsigned NOT NULL DEFAULT '0',
                ADD `subject_class` VARCHAR(25) NOT NULL;";
        $this->addQuery($query);
        $query = "UPDATE `acte_ccam` SET `subject_class` = 'COperation';";
        $this->addQuery($query);

        $this->makeRevision("0.18");
        $query = "ALTER TABLE `acte_ccam`
                CHANGE `subject_id` `object_id` int(11) unsigned NOT NULL DEFAULT '0',
                CHANGE `subject_class` `object_class` VARCHAR(25) NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.19");
        $query = "ALTER TABLE `acte_ccam`
                ADD `code_association` TINYINT(4)";
        $this->addQuery($query);

        $this->makeRevision("0.20");
        $query = "ALTER TABLE `acte_ccam`
                ADD `regle` ENUM('0','1');";
        $this->addQuery($query);

        $this->makeRevision("0.21");
        $query = "ALTER TABLE `acte_ccam`
                ADD INDEX ( `code_acte` ),
                ADD INDEX ( `code_activite` ),
                ADD INDEX ( `code_phase` ),
                ADD INDEX ( `object_id` ),
                ADD INDEX ( `executant_id` ),
                ADD INDEX ( `object_class` )";
        $this->addQuery($query);

        $this->makeRevision("0.22");
        $query = "ALTER TABLE `acte_ccam`
                ADD `montant_base` FLOAT;";
        $this->addQuery($query);

        $this->makeRevision("0.23");
        $query = "ALTER TABLE `acte_ccam`
                ADD `signe` ENUM('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.24");
        $query = "ALTER TABLE `acte_ccam`
                ADD `rembourse` ENUM('0','1'),
                CHANGE `object_class` `object_class` ENUM('COperation','CSejour','CConsultation') NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.25");
        $query = "CREATE TABLE `daily_check_item` (
                `daily_check_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `list_id` INT (11) UNSIGNED NOT NULL,
                `item_type_id` INT (11) UNSIGNED NOT NULL,
                `checked` ENUM ('0','1') NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_item` 
                ADD INDEX (`list_id`),
                ADD INDEX (`item_type_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `daily_check_item_type` (
                `daily_check_item_type_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `title` VARCHAR (255) NOT NULL,
                `desc` TEXT,
                `active` ENUM ('0','1') NOT NULL,
                `group_id` INT (11) UNSIGNED NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_item_type` ADD INDEX (`group_id`)";
        $this->addQuery($query);

        $query = "CREATE TABLE `daily_check_list` (
                `daily_check_list_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `date` DATE NOT NULL,
                `room_id` INT (11) UNSIGNED NOT NULL,
                `validator_id` INT (11) UNSIGNED
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_list` 
                ADD INDEX (`date`),
                ADD INDEX (`room_id`),
                ADD INDEX (`validator_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.26");
        $query = "ALTER TABLE `daily_check_item_type` 
                ADD `category_id` INT (11) UNSIGNED NOT NULL,
                ADD INDEX (`category_id`);";
        $this->addQuery($query);
        $query = "CREATE TABLE `daily_check_item_category` (
                `daily_check_item_category_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `title` VARCHAR (255) NOT NULL,
                `desc` TEXT
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $this->makeRevision("0.27");
        $query = "ALTER TABLE `acte_ccam` 
                ADD `charges_sup` ENUM ('0','1')";
        $this->addQuery($query);

        $this->makeRevision("0.28");
        $query = "ALTER TABLE `daily_check_list` 
                CHANGE `room_id` `object_id` INT (11) UNSIGNED NOT NULL,
                ADD `object_class` VARCHAR(80) NOT NULL DEFAULT 'CSalle'";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_item_category` 
                ADD `target_class` VARCHAR(80) NOT NULL DEFAULT 'CSalle'";
        $this->addQuery($query);

        $this->makeRevision("0.29");
        $query = "ALTER TABLE `daily_check_list` ADD `comments` TEXT";
        $this->addQuery($query);

        $this->makeRevision("0.30");
        $query = "ALTER TABLE `acte_ccam`
                ADD `regle_dh` ENUM('0','1') DEFAULT '0' AFTER `regle`;";
        $this->addQuery($query);

        $this->makeRevision("0.31");
        $query = "ALTER TABLE `daily_check_item_category` 
                ADD `type` ENUM ('preanesth','preop','postop');";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item` 
                CHANGE `checked` `checked` ENUM ('0','1');";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item_type` 
                ADD `attribute` ENUM ('normal','notrecommended','notapplicable'),
                CHANGE `group_id` `group_id` INT(11) UNSIGNED NULL";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list` 
                ADD `type` ENUM ('preanesth','preop','postop'),
                CHANGE `object_class` `object_class` ENUM ('CSalle','CBlocOperatoire','COperation') NOT NULL";
        $this->addQuery($query);

        // Liste des points de check liste spécifiés par la HAS
        $categories = [
            '01' => [
                'preanesth',
                'Identité du patient',
                [
                    [
                        'le patient a décliné son nom, sinon, par défaut, autre moyen de vérification de son identité',
                        'normal',
                    ],
                ],
            ],

            '02' => [
                'preanesth',
                'L\'intervention et site opératoire sont confirmés',
                [
                    [
                        'idéalement par le patient et dans tous les cas, par le dossier ou procédure spécifique',
                        'normal',
                    ],
                    ['la documentation clinique et para clinique nécessaire est disponible en salle', 'normal'],
                ],
            ],

            '03' => [
                'preanesth',
                null,
                [
                    [
                        'Le mode d\'installation est connu de l\'équipe en salle, cohérent avec le site/intervention et non dangereuse pour le patient',
                        'notapplicable',
                    ],
                ],
            ],

            '04' => [
                'preanesth',
                'Le matériel nécessaire pour l\'intervention est vérifié',
                [
                    ['pour la partie chirurgicale', 'normal'],
                    ['pour la partie anesthésique', 'normal'],
                ],
            ],

            '05' => [
                'preanesth',
                'Vérification croisée par l\'équipe de points critiques et des mesures adéquates à prendre',
                [
                    ['allergie du patient', 'normal'],
                    ['risque d\'inhalation, de difficulté d\'intubation ou de ventilation au masque', 'normal'],
                    ['risque de saignement important', 'normal'],
                ],
            ],

            '06' => [
                'preop',
                'Vérification « ultime » croisée au sein de l\'équipe',
                [
                    ['identité patient correcte', 'normal'],
                    ['intervention prévue confirmée', 'normal'],
                    ['site opératoire correct', 'normal'],
                    ['installation correcte', 'normal'],
                    ['documents nécessaires disponibles', 'notapplicable'],
                ],
            ],

            '07' => [
                'preop',
                'Partage des informations essentielles dans l\'équipe sur des éléments à risque / points critiques de l\'intervention',
                [
                    [
                        'sur le plan chirurgical (temps opératoire difficile, points spécifiques de l\'intervention, etc.)',
                        'normal',
                    ],
                    [
                        'sur le plan anesthésique (risques potentiels liés au terrain ou à des traitements éventuellement maintenus)',
                        'normal',
                    ],
                ],
            ],

            '08' => [
                'preop',
                null,
                [
                    ['Antibioprophylaxie effectuée', 'notrecommended'],
                ],
            ],

            '09' => [
                'postop',
                'Confirmation orale par le personnel auprès de l\'équipe',
                [
                    ['de l\'intervention enregistrée', 'normal'],
                    ['du compte final correct des compresses, aiguilles, instruments, etc.', 'notapplicable'],
                    ['de l\'étiquetage des prélèvements, pièces opératoires, etc.', 'notapplicable'],
                    ['du signalement de dysfonctionnements matériels et des événements indésirables', 'normal'],
                ],
            ],

            '10' => [
                'postop',
                null,
                [
                    [
                        'Les prescriptions pour les suites opératoires immédiates sont faites de manière conjointe',
                        'notrecommended',
                    ],
                ],
            ],
        ];

        foreach ($categories as $title => $cat) {
            $query = "INSERT INTO `daily_check_item_category` (`title`, `desc`, `target_class`, `type`) VALUES
                               (%1, %2, 'COperation', %3)";
            $query = $this->ds->prepare($query, $title, $cat[1], $cat[0]);
            $this->addQuery($query);

            foreach ($cat[2] as $type) {
                $query = "INSERT INTO `daily_check_item_type` (`title`, `active`, `attribute`, `category_id`) VALUES (
                    %1, '1', %2, (
                      SELECT `daily_check_item_category_id`
                      FROM `daily_check_item_category`
                      WHERE `title` = %3
                      AND `target_class` = 'COperation'
                    )
                  )";
                $query = $this->ds->prepare($query, $type[0], $type[1], $title);
                $this->addQuery($query);
            }
        }

        $this->makeRevision("0.32");
        $query = "ALTER TABLE `acte_ccam` 
                ADD INDEX (`execution`);";
        $this->addQuery($query);

        $this->makeRevision("0.33");
        $query = "CREATE TABLE `anesth_perop` (
                `anesth_perop_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `operation_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                `datetime` DATETIME NOT NULL
              ) /*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `anesth_perop` 
                ADD INDEX (`operation_id`),
                ADD INDEX (`datetime`);";
        $this->addQuery($query);

        $this->makeRevision("0.34");
        $query = "ALTER TABLE `daily_check_item` CHANGE 
                `checked` `checked` ENUM ('0','1','yes','no','na','nr')";
        $this->addQuery($query);

        // yes
        $query = "UPDATE `daily_check_item` SET `checked` = 'yes' WHERE `checked` = '1'";
        $this->addQuery($query);

        // no
        $query = "UPDATE `daily_check_item` 
                LEFT JOIN `daily_check_item_type` ON `daily_check_item_type`.`daily_check_item_type_id` = `daily_check_item`.`item_type_id`
                SET `daily_check_item`.`checked` = 'no' 
                WHERE `daily_check_item`.`checked` = '0' AND (
                  `daily_check_item_type`.`attribute` = 'normal' OR 
                  `daily_check_item_type`.`attribute` = 'notrecommended'
                )";
        $this->addQuery($query);

        // nr
        $query = "UPDATE `daily_check_item`
                LEFT JOIN `daily_check_item_type` ON `daily_check_item_type`.`daily_check_item_type_id` = `daily_check_item`.`item_type_id`
                SET `checked` = 'nr' 
                WHERE `daily_check_item`.`checked` IS NULL AND 
                  `daily_check_item_type`.`attribute` = 'notrecommended'";
        $this->addQuery($query);

        // na
        $query = "UPDATE `daily_check_item`
                LEFT JOIN `daily_check_item_type` ON `daily_check_item_type`.`daily_check_item_type_id` = `daily_check_item`.`item_type_id`
                SET `checked` = 'na' 
                WHERE `daily_check_item`.`checked` = '0' AND
                  `daily_check_item_type`.`attribute` = 'notapplicable'";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item` CHANGE 
                `checked` `checked` ENUM ('yes','no','na','nr') NOT NULL";
        $this->addQuery($query);

        $changes = [
            ['04', 1, "preanesth", "notapplicable"],
            ['05', 1, "preanesth", "notapplicable"],
            ['06', 4, "preop", "normal"],
            ['07', 1, "preop", "notapplicable"],
            ['10', 0, "postop", "normal"],
        ];

        foreach ($changes as $_change) {
            $libelle = addslashes($categories[$_change[0]][2][$_change[1]][0]);
            $query   = "UPDATE `daily_check_item_type` 
      LEFT JOIN `daily_check_item_category`
        ON `daily_check_item_category`.`daily_check_item_category_id` = `daily_check_item_type`.`category_id`
      SET `daily_check_item_type`.`attribute` = '{$_change[3]}'
      WHERE 
        `daily_check_item_category`.`target_class` = 'COperation' AND 
        `daily_check_item_category`.`type` = '{$_change[2]}' AND
        `daily_check_item_category`.`title` = '{$_change[0]}' AND 
        `daily_check_item_type`.`title` = '$libelle'";
            $this->addQuery($query);
        }

        $query = "ALTER TABLE `daily_check_list` 
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie')";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item_category` 
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie');";
        $this->addQuery($query);

        // Liste des points de check liste d'endoscopie digestive spécifiés par la HAS (au 24/08/2010)
        $category_changes = [
            '01' => [
                'preendoscopie',
                'Identité du patient',
                [
                    [
                        'le patient a décliné son nom, sinon, par défaut, autre moyen de vérification de son identité',
                        'normal',
                    ],
                ],
            ],

            '02' => [
                'preendoscopie',
                null,
                [
                    [
                        'Le type de l\'endoscopie est confirmé par le patient et dans tous les cas par le dossier',
                        'normal',
                    ],
                ],
            ],

            '03' => [
                'preendoscopie',
                'Le matériel nécessaire pour l\'intervention est opérationnel',
                [
                    ['pour la partie endoscopique', 'normal'],
                    ['pour la partie anesthésique', 'notapplicable'],
                ],
            ],

            '04' => [
                'preendoscopie',
                'Vérification croisée par l\'équipe de points critiques et des mesures adéquates à prendre',
                [
                    ['allergie du patient', 'normal'],
                    ['risque d\'inhalation, de difficulté d\'intubation ou de ventilation au masque', 'normal'],
                    ['risque de saignement important', 'normal'],
                ],
            ],

            '05' => [
                'preendoscopie',
                null,
                [
                    ['Patient à jeun', 'normal'],
                ],
            ],

            '06' => [
                'preendoscopie',
                null,
                [
                    ['La préparation adéquate (coloscopie, gastrostomie) a été mise en oeuvre', 'notapplicable'],
                ],
            ],

            '07' => [
                'preendoscopie',
                null,
                [
                    [
                        'Vérification croisée de situations spécifiques entre les membres de l\'équipe médico-soignante ' .
                        'concernant notamment la gestion des antiagrégants plaquettaires et/ou des anticoagulants',
                        'notapplicable',
                    ],
                ],
            ],

            '08' => [
                'preendoscopie',
                null,
                [
                    ['Antibioprophylaxie effectuée', 'notapplicable'],
                ],
            ],

            '09' => [
                'postendoscopie',
                null,
                [
                    [
                        'Confirmation orale par le personnel auprès de l\'équipe de l\'étiquetage des prélèvements, pièces opératoires, etc.',
                        'notapplicable',
                    ],
                ],
            ],

            '10' => [
                'postendoscopie',
                null,
                [
                    [
                        'Les prescriptions pour les suites immédiates de l\'endoscopie sont faites de manière conjointe',
                        'normal',
                    ],
                ],
            ],
        ];

        foreach ($category_changes as $title => $cat) {
            $query = "INSERT INTO `daily_check_item_category` (`title`, `desc`, `target_class`, `type`) VALUES (?1, ?2, 'COperation', ?3)";
            $query = $this->ds->prepare($query, $title, $cat[1], $cat[0]);
            $this->addQuery($query);

            foreach ($cat[2] as $type) {
                $query = "INSERT INTO `daily_check_item_type` (`title`, `active`, `attribute`, `category_id`) VALUES (
                    ?1, '1', ?2, (
                      SELECT `daily_check_item_category_id`
                      FROM `daily_check_item_category`
                      WHERE `title` = ?3
                      AND `target_class` = 'COperation'
                      AND `type` = ?4
                    )
                  )";
                $query = $this->ds->prepare($query, $type[0], $type[1], $title, $cat[0]);
                $this->addQuery($query);
            }
        }

        $this->makeRevision("0.35");
        $query = "ALTER TABLE `acte_ccam` 
                ADD `sent` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.36");
        $query = "ALTER TABLE `daily_check_item_type` 
                ADD `default_value` ENUM ('yes','no','nr','na') NOT NULL DEFAULT 'yes',
                ADD `index` TINYINT (2) UNSIGNED NOT NULL";
        $this->addQuery($query);

        $this->addMethod("addCheckItemsIndex");

        $this->makeRevision("0.37");

        $category_changes = [
            ['COperation', 'preanesth', '01', '01', ""],
            [
                'COperation',
                'preanesth',
                '04',
                '05',
                "L'équipement / matériel nécessaire pour l'intervention est vérifié et ne présente pas de dysfonctionnements",
            ],
            [
                'COperation',
                'preanesth',
                '05',
                '06',
                "Vérification croisée par l'équipe de points critiques et mise en oeuvre des mesures adéquates : Le patient présente-t-il un ?",
            ],

            [
                'COperation',
                'preop',
                '06',
                '07',
                "Vérification \"ultime\" croisée au sein de l'équipe en présence des chirurgiens(s), anesthésiste(s), /IADE-IBODE/IDE",
            ],
            [
                'COperation',
                'preop',
                '07',
                '08',
                "Partage des informations essentielles oralement au sein de l'équipe  sur les éléments à risque / étapes  critiques de l'intervention (Time out)",
            ],
            ['COperation', 'preop', '08', '09'],

            ['COperation', 'postop', '09', '10'],
            ['COperation', 'postop', '10', '11'],
        ];
        $this->changeCheckListCategories($category_changes);

        // Category additions
        $category_additions = [
            ['COperation', 'preanesth', '04'],
        ];
        $this->addCheckListCategories($category_additions);

        // Type changes
        $type_changes = [
            //     class         type      title/oldtitle
            [
                'COperation',
                'preanesth',
                '01',
                [
                    "index" => 1,
                    "title" => "L'identité du patient est correcte",
                ],
            ],
            [
                'COperation',
                'preanesth',
                '03',
                [
                    "index"     => 1,
                    "attribute" => "normal",
                ],
            ],
            [
                'COperation',
                'preanesth',
                '05',
                [
                    "index" => 2,
                    "title" => "pour la partie anesthésique. \n(N/A: Acte sans prise en charge anesthésique)",
                ],
            ],

            // 06
            [
                'COperation',
                'preanesth',
                '06',
                [
                    "index"   => 1,
                    "title"   => "risque d'allergie",
                    "default" => "no",
                ],
            ],
            [
                'COperation',
                'preanesth',
                '06',
                [
                    "index"   => 2,
                    "default" => "no",
                ],
            ],
            [
                'COperation',
                'preanesth',
                '06',
                [
                    "index"   => 3,
                    "default" => "no",
                ],
            ],

            [
                'COperation',
                'preop',
                '07',
                [
                    "index" => 3,
                    "title" => "site opératoire confirmé",
                ],
            ],
            [
                'COperation',
                'preop',
                '07',
                [
                    "index" => 4,
                    "title" => "installation correcte confirmée",
                ],
            ],
            [
                'COperation',
                'preop',
                '07',
                [
                    "index"     => 5,
                    "title"     => "documents nécessaires disponibles (notamment imagerie)",
                    "attribute" => "notapplicable",
                ],
            ],
            [
                'COperation',
                'preop',
                '08',
                [
                    "index" => 1,
                    "title" => "sur le plan chirurgical (temps opératoire difficile, points spécifiques de l'intervention, identification des matériels nécessaires, confirmation de leur opérationnalité, etc.)",
                ],
            ],
            [
                'COperation',
                'preop',
                '08',
                [
                    "index" => 2,
                    "title" => "sur le plan anesthésique (N/A: Acte sans prise en charge anesthésique) (risques potentiels liés au terrain ou à des traitements éventuellement maintenus, etc.)",
                ],
            ],
            [
                'COperation',
                'preop',
                '09',
                [
                    "index" => 1,
                    "title" => "L'antibioprophylaxie a été effectuée selon les recommandations et protocoles en vigueur dans l'établissement",
                ],
            ],
            [
                'COperation',
                'postop',
                '10',
                [
                    "index"     => 4,
                    "title"     => "si des évènements indésirables ou porteurs de risques médicaux sont survenus : ont-ils fait l'objet d'un signalement / déclaration ? (N/A: aucun évènement indésirable n'est survenu pendant l'intervention)",
                    "attribute" => "notapplicable",
                ],
            ],
            [
                'COperation',
                'postop',
                '11',
                [
                    "index" => 1,
                    "title" => "Les prescriptions pour les suites opératoires immédiates sont faites de manière conjointe entre les équipes chirurgicale et anesthésiste",
                ],
            ],
        ];
        $this->changeCheckListTypes($type_changes);

        // type additions
        $type_additions = [
            //     class         type      title/oldtitle
            [
                'COperation',
                'preanesth',
                '04',
                [
                    "index"     => 1,
                    "title"     => "La préparation cutanée de l'opéré est documentée dans la fiche de liaison service / bloc opératoire (ou autre procédure en oeuvre dans l'établissement)",
                    "attribute" => "notapplicable",
                    "default"   => "yes",
                ],
            ],
            [
                'COperation',
                'preop',
                '09',
                [
                    "index"     => 2,
                    "title"     => "La préparation du champ opératoire est réalisée selon le protocole en vigueur dans l'établissement",
                    "attribute" => "notapplicable",
                    "default"   => "yes",
                ],
            ],
        ];

        foreach ($type_additions as $_type) {
            $cat_class = $_type[0];
            $cat_type  = $_type[1];
            $cat_title = $_type[2];
            $data      = $_type[3];

            $query = $this->ds->prepare(
                "INSERT INTO `daily_check_item_type` (`title`, `attribute`, `default_value`, `index`, `active`, `category_id`)
      VALUES (?1, ?2, ?3, ?4, '1', (
        SELECT daily_check_item_category.daily_check_item_category_id
        FROM daily_check_item_category
        WHERE 
          `daily_check_item_category`.`target_class` = '$cat_class' AND 
          `daily_check_item_category`.`type` = '$cat_type' AND
          `daily_check_item_category`.`title` = '$cat_title'
        LIMIT 1
      ))",
                $data["title"],
                $data["attribute"],
                $data["default"],
                $data["index"]
            );
            $this->addQuery($query);
        }

        // Liste des points de check liste d'endoscopie bronchique spécifiés par la HAS (au 08/02/2011)
        $query = "ALTER TABLE `daily_check_list` 
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique')";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item_category` 
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique');";
        $this->addQuery($query);

        $check_list = [
            '01' => [
                'preendoscopie_bronchique',
                'Identité du patient',
                [
                    [
                        'le patient a décliné son nom, sinon, par défaut, autre moyen de vérification de son identité',
                        'normal',
                        'yes',
                    ],
                ],
            ],

            '02' => [
                'preendoscopie_bronchique',
                'Le matériel nécessaire pour l\'intervention est opérationnel',
                [
                    ['pour la partie endoscopique', 'normal', 'yes'],
                    ['pour la partie anesthésique', 'notapplicable', 'yes'],
                ],
            ],

            '03' => [
                'preendoscopie_bronchique',
                null,
                [
                    ['Patient à jeun', 'normal', 'yes'],
                ],
            ],

            '04' => [
                'preendoscopie_bronchique',
                'Vérification croisée par l\'équipe de points critiques et des mesures adéquates à prendre',
                [
                    ['allergie du patient', 'normal', 'yes'],
                    ['risque de saignement important', 'normal', 'yes'],
                ],
            ],

            '05' => [
                'preendoscopie_bronchique',
                null,
                [
                    [
                        'Vérification croisée de situations spécifiques entre les membres de l\'équipe médico-soignante ' .
                        'concernant notamment la gestion des antiagrégants plaquettaires et/ou des anticoagulants',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],

            '06' => [
                'postendoscopie_bronchique',
                null,
                [
                    [
                        'Confirmation orale par le personnel auprès de l\'équipe de l\'étiquetage des prélèvements, pièces opératoires, etc.',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],

            '07' => [
                'postendoscopie_bronchique',
                null,
                [
                    [
                        'Les prescriptions pour les suites immédiates de l\'endoscopie sont faites de manière conjointe',
                        'normal',
                        'yes',
                    ],
                ],
            ],
        ];

        $this->addNewCheckList($check_list);

        $this->makeRevision("0.38");
        $query = "ALTER TABLE `anesth_perop` 
                CHANGE `libelle` `libelle` TEXT NOT NULL;";
        $this->addQuery($query);
        $query = "ALTER TABLE `anesth_perop` 
                ADD `incident` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.39");
        $query = "ALTER TABLE `acte_ccam` 
                ADD `motif_depassement` ENUM ('d','e','f','n') AFTER `montant_depassement`;";
        $this->addQuery($query);

        $this->makeRevision("0.40");
        // Liste des points de check liste sécurité du patient en radiologie
        // interventionnelle spécifiés par la HAS, version 2011-01 (au 16/12/2011)
        $query = "ALTER TABLE `daily_check_list` 
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio')";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item_category` 
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio');";
        $this->addQuery($query);

        $check_list = [
            '00' => [
                'preanesth_radio',
                null,
                [
                    ['Informations au patient', 'normal', 'yes'],
                    ['Traçabilité du consentement éclairé', 'normal', 'yes'],
                    ['Dossier correspondant au patient', 'normal', 'yes'],
                ],
            ],

            '01' => [
                'preanesth_radio',
                null,
                [
                    ['L\'identité du patient est correcte', 'normal', 'yes'],
                ],
            ],

            '02' => [
                'preanesth_radio',
                'L\'intervention et site opératoire sont confirmés',
                [
                    [
                        'idéalement par le patient et dans tous les cas, par le dossier ou procédure spécifique',
                        'normal',
                        'yes',
                    ],
                    ['la documentation clinique et para clinique nécessaire est disponible en salle', 'normal', 'yes'],
                ],
            ],

            '03' => [
                'preanesth_radio',
                null,
                [
                    [
                        'Le mode d\'installation est connu de l\'équipe en salle, cohérent avec le site/intervention et non dangereux pour le patient',
                        'normal',
                        'yes',
                    ],
                ],
            ],

            '04' => [
                'preanesth_radio',
                null,
                [
                    [
                        'La préparation cutanée de l\'opéré est documentée dans la fiche de liaison service',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],

            '05' => [
                'preanesth_radio',
                'L\'équipement / matériel nécessaire pour l\'intervention est vérifié et ne présente pas de dysfonctionnement',
                [
                    ['pour la partie chirurgicale', 'normal', 'yes'],
                    ['pour la partie anesthésique', 'normal', 'yes'],
                    ['pour la partie imagerie', 'normal', 'yes'],
                ],
            ],

            '06' => [
                'preanesth_radio',
                'Vérification croisée par l\'équipe de points critiques et mise en oeuvre des mesures adéquates à prendre. Le patient présente-t-il :',
                [
                    ['un risque allergique', 'normal', 'no'],
                    ['un risque lié au produit de contraste', 'normal', 'no'],
                    ['une insuffisance rénale', 'normal', 'no'],
                    ['risque d\'inhalation, de difficulté d\'intubation ou de ventilation au masque', 'normal', 'no'],
                    ['un risque de saignement important', 'normal', 'no'],
                    ['un risque lié à l\'irradiation (grossesse)', 'normal', 'no'],
                ],
            ],

            '07' => [
                'preop_radio',
                'Vérification « ultime » croisée au sein de l\'équipe en présence des anesthésistes, radiologues et manipulateurs électroradio.',
                [
                    ['identité patient confirmée', 'normal', 'yes'],
                    ['intervention prévue confirmée', 'normal', 'yes'],
                    ['site interventionnel confirmé', 'normal', 'yes'],
                    ['installation correcte confirmée', 'normal', 'yes'],
                    ['documents nécessaires disponibles', 'normal', 'yes'],
                    ['monitorage du patient vérifié', 'normal', 'yes'],
                ],
            ],

            '08' => [
                'preop_radio',
                'Partage des informations essentielles oralement au sein de l\'équipe sur les éléments à risque / étapes critiques de l\'intervention.',
                [
                    [
                        'sur le plan interventionnel (voie d\'abord définie, technique précisée, DMI disponibles, etc.)',
                        'normal',
                        'yes',
                    ],
                    [
                        'sur le plan anesthésique (risques potentiels liés au terrain ou à des traitements éventuellement maintenus, etc.)',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],

            '09' => [
                'preop_radio',
                'Prise en compte de situations spécifiques concernant',
                [
                    ['la gestion des antiagrégants', 'notapplicable', 'yes'],
                    ['la gestion des anticoagulants', 'notapplicable', 'yes'],
                    ['l\'antibioprophylaxie effectuée', 'notapplicable', 'yes'],
                    ['la préparation du champ opératoire réalisé selon protocole en vigueur', 'notapplicable', 'yes'],
                ],
            ],

            '10' => [
                'postop_radio',
                'Confirmation orale par le personnel auprès de l\'équipe :',
                [
                    ['de l\'intervention enregistrée', 'normal', 'yes'],
                    ['de l\'étiquetage des prélèvements, pièces opératoires, etc.', 'notapplicable', 'yes'],
                    ['des médications utilisées', 'normal', 'yes'],
                    ['de la quantité de produit contraste', 'normal', 'yes'],
                    ['du recueil de l\'irradiation délivrée', 'normal', 'yes'],
                    ['de la traçabilité du matériel et DMI', 'normal', 'yes'],
                    ['de l\'enregistrement des images', 'normal', 'yes'],
                    ['de la feuille de liaison remplie', 'normal', 'yes'],
                    [
                        'si des événements indésirables ou porteurs de risques médicaux sont survenus : ont-ils fait l\'objet d\'un 
                 signalement / déclaration ? (Si aucun événement indésirable n\'est survenu pendant l\'intervention, cochez N/A)',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],

            '11' => [
                'postop_radio',
                null,
                [
                    [
                        'Les prescriptions pour les suites opératoires immédiates sont faites de manière conjointe entre les équipes de radiologie et d\'anesthésie',
                        'normal',
                        'yes',
                    ],
                ],
            ],
        ];

        $this->addNewCheckList($check_list);

        $this->makeRevision("0.41");

        // Check list pose CVC

        $query = "ALTER TABLE `daily_check_list` 
                CHANGE `object_class` `object_class` ENUM ('CSalle','CBlocOperatoire','COperation','CPoseDispositifVasculaire') NOT NULL DEFAULT 'CSalle',
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio','disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres')";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item_category` 
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio','disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres');";
        $this->addQuery($query);

        $check_list = [
            // AVANT
            '01' => [
                'disp_vasc_avant',
                null,
                [
                    ['Identité du patient vérifiée', 'normal', 'yes'],
                ],
            ],
            '02' => [
                'disp_vasc_avant',
                null,
                [
                    ['Patient / famille informé', 'normal', 'yes'],
                ],
            ],
            '03' => [
                'disp_vasc_avant',
                "ÉVALUATION DES RISQUES",
                [
                    ['Risque hémorragique, allergie, contre-indications anatomique ou pathologique', 'normal', 'yes'],
                ],
            ],
            '04' => [
                'disp_vasc_avant',
                null,
                [
                    ['Choix argumenté du site d\'insertion', 'normal', 'yes'],
                ],
            ],
            '05' => [
                'disp_vasc_avant',
                null,
                [
                    ['Choix concerté du matériel', 'normal', 'yes'],
                ],
            ],
            '06' => [
                'disp_vasc_avant',
                null,
                [
                    ['Préparation cutanée appropriée', 'normal', 'yes'],
                ],
            ],
            '07' => [
                'disp_vasc_avant',
                null,
                [
                    ['Monitorage approprié', 'normal', 'yes'],
                ],
            ],
            '08' => [
                'disp_vasc_avant',
                "Vérification du matériel",
                [
                    ['Date de péremption, intégrité de l\'emballage', 'normal', 'yes'],
                ],
            ],
            '09' => [
                'disp_vasc_avant',
                null,
                [
                    ['Échographie', 'normal', 'yes'],
                ],
            ],

            // PENDANT
            '10' => [
                'disp_vasc_pendant',
                "PROCÉDURES D'HYGIÈNE",
                [
                    ['Détersion/désinfection avec antiseptique alcoolique', 'normal', 'yes'],
                    ['Conditions d\'asepsie chirurgicale', 'normal', 'yes'],
                ],
            ],
            '11' => [
                'disp_vasc_pendant',
                "Vérifications per opératoires des matériels",
                [
                    ['Mécanique: Solidité des connexions', 'normal', 'yes'],
                    ['Positionnelle: Extrémité du cathéter', 'normal', 'yes'],
                    ['FONCTIONNELLE: Reflux sanguin', 'normal', 'yes'],
                    ['FONCTIONNELLE: Système perméable', 'normal', 'yes'],
                ],
            ],
            '12' => [
                'disp_vasc_pendant',
                null,
                [
                    ['Vérification de la fixation du dispositif', 'normal', 'yes'],
                ],
            ],
            '13' => [
                'disp_vasc_pendant',
                null,
                [
                    ['Pose d\'un pansement occlusif', 'normal', 'yes'],
                ],
            ],
            '14' => [
                'disp_vasc_pendant',
                "Si utilisation différée, fermeture du dispositif",
                [
                    ['En accord avec la procédure locale', 'normal', 'yes'],
                ],
            ],

            // APRES
            '15' => [
                'disp_vasc_apres',
                "CONTRÔLE CVC / DV",
                [
                    ['Position du CVC vérifiée', 'normal', 'yes'],
                    ['Recherche de complication', 'normal', 'yes'],
                ],
            ],
            '16' => [
                'disp_vasc_apres',
                "TRAÇABILITÉ / COMPTE RENDU",
                [
                    ['Matériel, technique, nombre de ponctions, incident', 'normal', 'yes'],
                ],
            ],
            '17' => [
                'disp_vasc_apres',
                null,
                [
                    ['Prescriptions pour le suivi après pose', 'normal', 'yes'],
                ],
            ],
            '18' => [
                'disp_vasc_apres',
                null,
                [
                    ['Documents remis au patient', 'normal', 'yes'],
                ],
            ],
        ];

        $this->addNewCheckList($check_list, "CPoseDispositifVasculaire");

        $this->makeRevision("0.42");

        $query = "ALTER TABLE `acte_ccam`
                ADD `facturable` ENUM ('0','1') NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.43");
        $query = "ALTER TABLE `daily_check_item_category`
                CHANGE `target_class` `target_class` ENUM ('CSalle','CBlocOperatoire','COperation','CPoseDispositifVasculaire') NOT NULL DEFAULT 'CSalle',
                ADD `target_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_item_category`
                ADD INDEX (`target_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.44");
        // Check list sécurité du patient en endoscopie digestive, version 2013
        $this->moveCheckListCategory("10", "postendoscopie", "11");
        $this->moveCheckListCategory("09", "postendoscopie", "10");

        // Nouveau point 09
        $check_list = [
            '09' => [
                'preendoscopie',
                'Patient suspect ou atteint d\'EST',
                [
                    [
                        '(en cas de réponse positive, l\'endoscopie doit être considérée comme un acte à risque de transmission d\'ATNC et ' .
                        'il convient de se référer aux procédures en cours dans l\'établissement en lien avec l\'Instruction n°DGS/R13/2011' .
                        '/449)',
                        'normal',
                        'yes',
                    ],
                ],
            ],
        ];
        $this->addNewCheckList($check_list);

        $this->makeRevision("0.45");

        $query = "ALTER TABLE `acte_ccam`
                ADD `ald` ENUM ('0','1') NOT NULL DEFAULT '0',
                ADD `lieu` ENUM('C', 'D') DEFAULT 'C' NOT NULL,
                ADD `exoneration` ENUM('N', '13', '15', '17', '19') DEFAULT 'N' NOT NULL;";
        $this->addQuery($query);

        $this->makeRevision("0.46");
        $query = "CREATE TABLE `daily_check_list_type` (
                `daily_check_list_type_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_class` ENUM ('CSalle','CBlocOperatoire') NOT NULL DEFAULT 'CSalle',
                `object_id` INT (11) UNSIGNED,
                `title` VARCHAR (255) NOT NULL,
                `description` TEXT
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_list_type`
                ADD INDEX (`object_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_item_category`
                ADD `list_type_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "INSERT INTO `daily_check_list_type` (`object_class`, `object_id`, `title`)
                SELECT `target_class`, `target_id`, 'Check list standard'
                FROM `daily_check_item_category`
                WHERE `target_class` NOT IN ('COperation', 'CPoseDispositifVasculaire')
                GROUP BY `target_class`, `target_id`;";
        $this->addQuery($query);
        $query = "UPDATE `daily_check_item_category` SET
                `list_type_id` = (
                  SELECT `daily_check_list_type_id`
                  FROM `daily_check_list_type`
                  WHERE `daily_check_list_type`.`object_class` = `daily_check_item_category`.`target_class`
                  AND   (
                       `daily_check_list_type`.`object_id`    = `daily_check_item_category`.`target_id`
                    OR `daily_check_list_type`.`object_id` IS NULL AND `daily_check_item_category`.`target_id` IS NULL
                  )
                  LIMIT 1
                )
                WHERE `target_class` NOT IN ('COperation', 'CPoseDispositifVasculaire');";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list`
                ADD `list_type_id` INT (11) UNSIGNED;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_list`
                ADD INDEX (`list_type_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.47");
        $query = "CREATE TABLE `daily_check_list_type_link` (
                `daily_check_list_type_link_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `object_class` ENUM ('CSalle','CBlocOperatoire') NOT NULL DEFAULT 'CSalle',
                `object_id` INT (11) UNSIGNED,
                `list_type_id` INT (11) UNSIGNED NOT NULL DEFAULT '0'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_list_type_link`
                ADD INDEX (`object_id`),
                ADD INDEX (`object_class`),
                ADD INDEX (`list_type_id`);";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_list_type`
                ADD `group_id` INT ( 11 ) UNSIGNED NOT NULL DEFAULT 0;";
        $this->addQuery($query);

        $this->makeRevision("0.48");

        $this->addMethod("listToGroup");

        $query = "INSERT INTO `daily_check_list_type_link` (`object_class`, `object_id`, `list_type_id`)
                SELECT `object_class`, `object_id`, `daily_check_list_type_id` FROM `daily_check_list_type`";
        $this->addQuery($query);

        $this->makeRevision("0.49");
        $query = "ALTER TABLE `acte_ccam`
                ADD `extension_documentaire` ENUM ('1','2','3','4','5','6') AFTER `code_association`;";
        $this->addQuery($query);

        $this->makeRevision("0.50");

        // Check list sécurité du patient en endoscopie bronchique, version 2013
        $this->moveCheckListCategory("07", "postendoscopie_bronchique", "08");
        $this->moveCheckListCategory("06", "postendoscopie_bronchique", "07");

        // Nouveau point 06
        $check_list = [
            '06' => [
                'preendoscopie_bronchique',
                'Patient suspect ou atteint d\'EST',
                [
                    [
                        '(en cas de réponse positive, l\'endoscopie doit être considérée comme un acte à risque de transmission d\'ATNC et ' .
                        'il convient de se référer aux procédures en cours dans l\'établissement en lien avec l\'Instruction n°DGS/R13/2011' .
                        '/449)',
                        'normal',
                        'yes',
                    ],
                ],
            ],
        ];
        $this->addNewCheckList($check_list);

        $this->moveCheckListCategory("04", "preendoscopie_bronchique", "05b");
        $this->moveCheckListCategory("02", "preendoscopie_bronchique", "04");
        $this->moveCheckListCategory("03", "preendoscopie_bronchique", "02");
        $this->moveCheckListCategory("05", "preendoscopie_bronchique", "03");
        $this->moveCheckListCategory("05b", "preendoscopie_bronchique", "05");

        $this->makeRevision("0.51");
        $this->addDependency("dPplanningOp", "1.50");
        $this->addDependency("dPbloc", "0.23");
        $query = "ALTER TABLE `daily_check_list`
                ADD `group_id` INT (11) UNSIGNED NOT NULL,
                ADD INDEX (`group_id`);";
        $this->addQuery($query);

        // Update CSalle
        $query = "UPDATE `daily_check_list` SET
                `daily_check_list`.`group_id` = (
                  SELECT `group_id`
                  FROM `bloc_operatoire`
                  LEFT JOIN `sallesbloc` ON `sallesbloc`.`bloc_id` = `bloc_operatoire`.`bloc_operatoire_id`
                  WHERE `sallesbloc`.`salle_id` = `daily_check_list`.`object_id`
                )
                WHERE `daily_check_list`.`object_class` = 'CSalle'";
        $this->addQuery($query);

        // Update CBlocOperatoire
        $query = "UPDATE `daily_check_list` SET
                `daily_check_list`.`group_id` = (
                  SELECT `group_id`
                  FROM `bloc_operatoire`
                  WHERE `bloc_operatoire_id` = `daily_check_list`.`object_id`
                )
                WHERE `daily_check_list`.`object_class` = 'CBlocOperatoire'";
        $this->addQuery($query);

        // Update COperation
        $query = "UPDATE `daily_check_list` SET
                `daily_check_list`.`group_id` = (
                  SELECT `group_id`
                  FROM `sejour`
                  LEFT JOIN `operations` ON `operations`.`sejour_id` = `sejour`.`sejour_id`
                  WHERE `operations`.`operation_id` = `daily_check_list`.`object_id`
                )
                WHERE `daily_check_list`.`object_class` = 'COperation'";
        $this->addQuery($query);

        // Update CPoseDispositifVasculaire
        $query = "UPDATE `daily_check_list` SET
                `daily_check_list`.`group_id` = (
                  SELECT `group_id`
                  FROM `sejour`
                  LEFT JOIN `pose_dispositif_vasculaire` ON `pose_dispositif_vasculaire`.`sejour_id` = `sejour`.`sejour_id`
                  WHERE `pose_dispositif_vasculaire`.`pose_dispositif_vasculaire_id` = `daily_check_list`.`object_id`
                )
                WHERE `daily_check_list`.`object_class` = 'CPoseDispositifVasculaire'";
        $this->addQuery($query);

        $this->makeRevision("0.52");
        $query = "ALTER TABLE `acte_ccam`
                ADD `num_facture` INT (11) UNSIGNED NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $this->makeRevision("0.53");
        self::addDefaultConfig("dPsalleOp COperation use_sortie_reveil_reel");

        $this->makeRevision("0.54");
        $query = "ALTER TABLE `daily_check_list_type`
                ADD `type_validateur` TEXT;";
        $this->addQuery($query);

        $this->makeRevision("0.55");
        $query = "UPDATE `daily_check_list_type` SET
                `type_validateur` = 'chir|anesth|op|op_panseuse|iade|sagefemme|manipulateur'
                WHERE `object_class` = 'CSalle'";
        $this->addQuery($query);
        $query = "UPDATE `daily_check_list_type` SET
                `type_validateur` = 'reveil'
                WHERE `object_class` = 'CBlocOperatoire'";
        $this->addQuery($query);
        $this->makeRevision("0.56");

        $query = "ALTER TABLE `daily_check_item_category`
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio','disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres', 'avant_indu_cesar', 'cesarienne_avant', 'cesarienne_apres');";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list`
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio','disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres', 'avant_indu_cesar', 'cesarienne_avant', 'cesarienne_apres');";
        $this->addQuery($query);

        $check_list = [
            // AVANT
            '01' => [
                'avant_indu_cesar',
                'Identité patient',
                [
                    ['Identité de la patiente est correct', 'normal', 'yes'],
                ],
            ],
            '02' => [
                'avant_indu_cesar',
                'Les éléments essentiels à la prise en charge sont connus par l\'équipe',
                [
                    ['La localisation du placenta', 'normal', 'yes'],
                    ['La présentation de l\'enfant', 'normal', 'yes'],
                    ['Les bruits du coeur sont vérifiés', 'normal', 'yes'],
                    [
                        'La documentation clinique et para clinique nécessaire est disponible en salle:' .
                        'Bilan sanguin, carte de groupe, ACI',
                        'normal',
                        'yes',
                    ],
                ],
            ],
            '03' => [
                'avant_indu_cesar',
                null,
                [
                    ['Le pédiatre est prévenu', 'notapplicable', 'yes'],
                ],
            ],
            '04' => [
                'avant_indu_cesar',
                null,
                [
                    [
                        'La préparation cutanée de la patiente est documentée dans la fiche de liaison service / bloc opératoire (ou autre ' .
                        'procédure en oeuvre dans l\'établissement)',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '05' => [
                'avant_indu_cesar',
                'L\'équipe / matériel nécessaire pour l\'intervention est vérifié et fonctionnel:',
                [
                    ['Pour la partie obstétricale', 'normal', 'yes'],
                    ['Pour la partie anesthésique (mère)', 'normal', 'yes'],
                    ['Pour la partie réanimation (nouveau né)', 'normal', 'yes'],
                ],
            ],
            '06' => [
                'avant_indu_cesar',
                'Vérification croisée par l\'équipe de points critiques et mise en oeuvre des mesures adéquats'
                . '. La patiente présente-t-elle un?',
                [
                    ['risque alergique', 'normal', 'no'],
                    ['de la difficulté d\'intubation ou de ventilation au masque', 'normal', 'no'],
                    ['risque de saignement supérieur à 1000ml', 'normal', 'no'],
                    ['L\'administration d\'antiacide  a été effectuée', 'normal', 'yes'],
                ],
            ],
            '07' => [
                'cesarienne_avant',
                'Vérification "ultime" croisée au sein de l\'équipe',
                [
                    ['identité patiente confirmée', 'normal', 'yes'],
                    ['installation correcte confirmée', 'normal', 'yes'],
                    ['sondage urinaire efficace', 'normal', 'yes'],
                    ['compte initial de textiles et d\'instruments confirmé', 'normal', 'yes'],
                    ['electrode de scalp otée', 'notapplicable', 'yes'],
                ],
            ],
            '08' => [
                'cesarienne_avant',
                null,
                [
                    ['Présence du pédiatre', 'notapplicable', 'yes'],
                ],
            ],
            '09' => [
                'cesarienne_avant',
                'Partage des informations essentielles oralement au sein de l\'équipe sur les éléments à' .
                ' risque / étapes critiques de l\'intervention',
                [
                    [
                        'sur le plan obtétrical (temps opératoire difficile, localisation du placenta, points spécifiques de l\'intervention' .
                        ', prélèvements cordon placenta, identification des matériels nécessaires, confirmation de leur opérationnalité, etc.)',
                        'normal',
                        'yes',
                    ],
                    [
                        'sur le plan anesthésique (risque potentiels liés au terrain ou à des traitements éventuellement maintenus, etc.)',
                        'normal',
                        'yes',
                    ],
                ],
            ],
            '10' => [
                'cesarienne_avant',
                null,
                [
                    [
                        'La préparation du champ opératoire est réalisée selon le protocole en vigeur dans l\'établissement',
                        'normal',
                        'yes',
                    ],
                ],
            ],
            '11' => [
                'cesarienne_apres',
                'Confirmation orale par le personnel auprès de l\'équipe',
                [
                    ['du compte final concordant des textiles, aiguilles, instruments, etc.', 'normal', 'yes'],
                    ['de l\'enregistrement des pertes sanguines totales', 'normal', 'yes'],
                    [
                        'si des évènements indésirables ou porteurs de risques médicaux sont survenus: ont-ils fait l\'objet d\'un ' .
                        'signalement / déclaration',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '12' => [
                'cesarienne_apres',
                null,
                [
                    [
                        'L\'antibioprophylaxie a été effectuée selon les recommandations et protocoles en vigueur dans l\'établissement',
                        'normal',
                        'yes',
                    ],
                    [
                        'Les prescriptions pour les suites opératoires immédiates sont faites de manière conjointe entre les équipes ' .
                        'obstétricale et anesthésiste',
                        'normal',
                        'yes',
                    ],
                ],
            ],
            '13' => [
                'cesarienne_apres',
                null,
                [
                    [
                        'Le ou les nouveaux nés sont identifiés selon les protocoles en vigueur dans l\'établissement',
                        'normal',
                        'yes',
                    ],
                ],
            ],
        ];
        $this->addNewCheckList($check_list);
        $this->makeRevision("0.57");

        $query = "UPDATE `daily_check_item_type` SET `title` = 'Identité de la patiente est correcte'
            WHERE `title` = 'Identité de la patiente est correct'";
        $this->addQuery($query);

        $query = "UPDATE `daily_check_item_type` SET `title` = 'un risque alergique'
            WHERE `title` = 'risque alergique'";
        $this->addQuery($query);

        $query = "UPDATE `daily_check_item_type` SET `title` = 'un risque de saignement supérieur à 1000ml'
            WHERE `title` = 'risque de saignement supérieur à 1000ml'";
        $this->addQuery($query);

        $query = "UPDATE `daily_check_item_type` SET `title` = 'électrode de scalp ôtée'
            WHERE `title` = 'electrode de scalp otée'";
        $this->addQuery($query);

        $query = "UPDATE `daily_check_item_category`
      SET `desc` = 'Vérification croisée par l''équipe de points critiques et mise en oeuvre des mesures adéquates. La patiente présente-t-elle  ? '
      WHERE `target_class` = 'COperation'
      AND `type` = 'avant_indu_cesar'
      AND `title` =  '06'";
        $this->addQuery($query);

        $this->makeRevision("0.58");

        $query = "ALTER TABLE `daily_check_item_category`
                ADD `index` INT (11) UNSIGNED NOT NULL DEFAULT '1';";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item_category`
                ADD INDEX (`list_type_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.59");

        $query = "UPDATE `daily_check_item_type` SET `title` = 'un risque allergique'
            WHERE `title` = 'un risque alergique'";
        $this->addQuery($query);

        $query = "UPDATE `daily_check_item_type`
    SET `title` = 'sur le plan anesthésique (risques potentiels liés au terrain ou à des traitements éventuellement maintenus, etc.)'
    WHERE `title` = 'sur le plan anesthésique (risque potentiels liés au terrain ou à des traitements éventuellement maintenus, etc.)'";
        $this->addQuery($query);

        $query = "UPDATE `daily_check_item_type`
    SET `title` = 'La documentation clinique et para clinique nécessaire est disponible en salle: Bilan sanguin, carte de groupe, ACI'
    WHERE `title` = 'La documentation clinique et para clinique nécessaire est disponible en salle:Bilan sanguin, carte de groupe, ACI'";
        $this->addQuery($query);

        $this->makeRevision("0.60");
        $this->addPrefQuery("autosigne_sortie", "1");

        $this->makeRevision('0.61');

        $query = "ALTER TABLE `acte_ccam`
                ADD `gratuit` ENUM('0', '1') NOT NULL DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.62');

        $category_type = "'preanesth','preop','postop'
                ,'preendoscopie','postendoscopie'
                ,'preendoscopie_bronchique','postendoscopie_bronchique'
                ,'preanesth_radio','preop_radio','postop_radio'
                ,'disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres'
                ,'avant_indu_cesar', 'cesarienne_avant', 'cesarienne_apres'
                ,'preanesth_ch', 'preop_ch', 'postop_ch'";
        $query         = "ALTER TABLE `daily_check_item_category`
                CHANGE `type` `type` ENUM ($category_type);";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list`
                CHANGE `type` `type` ENUM ($category_type);";
        $this->addQuery($query);

        $check_list = [
            // AVANT
            '01' => [
                'preanesth_ch',
                null,
                [
                    ['Dossiers cliniques et personnel du patient disponibles en salle', 'normal', 'yes'],
                ],
            ],
            '02' => [
                'preanesth_ch',
                'Identité',
                [
                    ['Patient confirme: nom, prénom, date de naissance', 'normal', 'yes'],
                    ['Concordance avec bracelet d\'identité', 'normal', 'yes'],
                    ['Concordance avec dossier', 'normal', 'yes'],
                    ['Patient confirme le site', 'normal', 'yes'],
                ],
            ],
            '03' => [
                'preanesth_ch',
                null,
                [
                    ['Site marqué', 'notapplicable', 'yes'],
                ],
            ],
            '04' => [
                'preanesth_ch',
                'Risques évalués',
                [
                    ['Allergie', 'normal', 'yes'],
                    ['Broncho-aspiration (estomac plein, jeûne, patho gastro_oeso)', 'normal', 'yes'],
                    ['Voies aériennes', 'normal', 'yes'],
                    ['Saignement anticipé (>500 ml, 10 ml/kg en pédiatrie)', 'normal', 'yes'],
                    ['Contamination (MRSA, TBC, hépatite, HIV, ...)', 'normal', 'yes'],
                ],
            ],
            '05' => [
                'preanesth_ch',
                'Vérifications',
                [
                    ['Mode d\'installation', 'normal', 'yes'],
                    ['Matériel particulier pour l\'anesthésie', 'normal', 'yes'],
                ],
            ],
            '06' => [
                'preanesth_ch',
                null,
                [
                    ['Confirmation matériel chirurgical avant induction', 'normal', 'yes'],
                ],
            ],
            '07' => [
                'preop_ch',
                null,
                [
                    ['Vérification identité intervenants et visiteurs', 'normal', 'yes'],
                ],
            ],
            '08' => [
                'preop_ch',
                'Confirmation par le trinôme anesthésiste/chirurgien/instrumentiste sous la conduitre de l\'infirmière circulante',
                [
                    ['Identité patient', 'normal', 'yes'],
                    ['Site opératoire', 'normal', 'yes'],
                    ['Intervention', 'normal', 'yes'],
                    ['Installation opératoire', 'normal', 'yes'],
                    ['Matériel', 'normal', 'yes'],
                    ['Etapes critiques', 'normal', 'yes'],
                    ['Prophylaxie antibiotique si indiquée', 'normal', 'yes'],
                ],
            ],
            '09' => [
                'postop_ch',
                'Infirmière circulante confirme verbalement avec l\'équipe:',
                [
                    ['Nom de l\'acte chirurgical réalisé', 'normal', 'yes'],
                    ['Compte de compresses / guersounis', 'normal', 'yes'],
                ],
            ],
            '10' => [
                'postop_ch',
                'Prélèvements',
                [
                    ['Etiquetage: concordance identité patient', 'normal', 'yes'],
                    ['Milieu de conservation', 'normal', 'yes'],
                    ['Laboratoire de destination', 'normal', 'yes'],
                    ['Envoi de destination', 'notapplicable', 'yes'],
                ],
            ],
            '11' => [
                'postop_ch',
                'Debriefing chirurgien - anesthésiste',
                [
                    ['Revue des évènements critiques', 'normal', 'yes'],
                ],
            ],
            '12' => [
                'postop_ch',
                'Documents complétés',
                [
                    ['Feuille d\'ordre par anesthésiste', 'normal', 'yes'],
                    ['Feuille d\'ordre par chirurgien', 'normal', 'yes'],
                ],
            ],
        ];
        $this->addNewCheckList($check_list);

        $this->makeRevision('0.63');
        $this->addPrefQuery("default_salles_id", "{}");
        $this->makeRevision('0.64');

        $query = "ALTER TABLE `daily_check_list_type`
                ADD `type` ENUM ('salle','op','preop') NOT NULL DEFAULT 'salle';";
        $this->addQuery($query);
        $query = "UPDATE `daily_check_list_type`
                SET `type` = 'op'
                WHERE `object_class` = 'CBlocOperatoire'";
        $this->addQuery($query);
        $this->makeRevision('0.65');

        $query = "ALTER TABLE `daily_check_list_type`
                CHANGE `type` `type` ENUM ('ouverture_salle','ouverture_sspi','ouverture_preop') NOT NULL DEFAULT 'ouverture_salle';";
        $this->addQuery($query);
        $query = "UPDATE `daily_check_list_type`
                SET `type` = 'ouverture_sspi'
                WHERE `object_class` = 'CBlocOperatoire'";
        $this->addQuery($query);
        $query = "UPDATE `daily_check_list_type`
                SET `type` = 'ouverture_salle'
                WHERE `object_class` = 'CSalle'";
        $this->addQuery($query);
        $this->makeRevision('0.66');

        $query = "ALTER TABLE `daily_check_list_type`
                CHANGE `type` `type` ENUM ('ouverture_salle','ouverture_sspi','ouverture_preop', 'fermeture_salle') NOT NULL DEFAULT 'ouverture_salle';";
        $this->addQuery($query);
        $this->makeRevision('0.67');

        $query = "ALTER TABLE `acte_ccam`
                CHANGE `object_class` `object_class` ENUM('COperation','CSejour','CConsultation', 'CDevisCodage') NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision('0.68');

        $query = "ALTER TABLE `daily_check_list_type`
                ADD `check_list_group_id` INT (11) UNSIGNED,
                CHANGE `type` `type` ENUM ('ouverture_salle','ouverture_sspi','ouverture_preop','fermeture_salle','intervention') NOT NULL DEFAULT 'ouverture_salle';";
        $this->addQuery($query);

        $query = "CREATE TABLE `daily_check_list_group` (
                `check_list_group_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `title` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `actif` ENUM ('0','1') DEFAULT '1'
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_list_group`
                ADD INDEX (`group_id`);";
        $this->addQuery($query);
        $this->makeRevision('0.69');

        $query = "ALTER TABLE `daily_check_list_type`
                CHANGE `type` `type` ENUM ('ouverture_salle','ouverture_sspi','ouverture_preop','fermeture_salle','intervention','fermeture_sspi','fermeture_preop') NOT NULL DEFAULT 'ouverture_salle';";
        $this->addQuery($query);
        $this->makeRevision('0.70');

        $this->addDefaultConfig(
            "dPsalleOp CDailyCheckList active_salle_reveil",
            "dPsalleOp CDailyCheckList active_salle_reveil"
        );
        $this->addDefaultConfig("dPsalleOp CDailyCheckList active", "dPsalleOp CDailyCheckList active");
        $this->addDefaultConfig(
            "dPsalleOp Default_good_answer default_good_answer_COperation",
            "dPsalleOp CDailyCheckList default_good_answer_COperation"
        );
        $this->addDefaultConfig(
            "dPsalleOp Default_good_answer default_good_answer_CBlocOperatoire",
            "dPsalleOp CDailyCheckList default_good_answer_CBlocOperatoire"
        );
        $this->addDefaultConfig(
            "dPsalleOp Default_good_answer default_good_answer_CSalle",
            "dPsalleOp CDailyCheckList default_good_answer_CSalle"
        );
        $this->addDefaultConfig(
            "dPsalleOp Default_good_answer default_good_answer_CPoseDispositifVasculaire",
            "dPsalleOp CDailyCheckList default_good_answer_CPoseDispositifVasculaire"
        );
        $this->makeRevision('0.71');

        $query = "ALTER TABLE `daily_check_list`
                ADD `date_validate` DATETIME;";
        $this->addQuery($query);
        $query = "ALTER TABLE `daily_check_list`
                ADD INDEX (`date_validate`);";
        $this->addQuery($query);
        $this->makeRevision('0.72');

        $query = "ALTER TABLE `daily_check_list_type`
                ADD `lock_view` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision('0.73');

        $query = "ALTER TABLE `acte_ccam`
                CHANGE `object_class` `object_class` ENUM('COperation','CSejour','CConsultation', 'CDevisCodage', 'CModelCodage') NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision('0.74');

        $query = "ALTER TABLE `acte_ccam`
                CHANGE `object_class` `object_class` VARCHAR (80) NOT NULL;";
        $this->addQuery($query);
        $this->makeRevision('0.75');

        $query = "ALTER TABLE `daily_check_item_category`
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio',
                'disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres', 'avant_indu_cesar', 'cesarienne_avant', 'cesarienne_apres','preanesth_ch', 'preop_ch', 'postop_ch', 'preanesth_2016', 'preop_2016', 'postop_2016');";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list`
                CHANGE `type` `type` ENUM ('preanesth','preop','postop','preendoscopie','postendoscopie','preendoscopie_bronchique','postendoscopie_bronchique','preanesth_radio','preop_radio','postop_radio',
                'disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres', 'avant_indu_cesar', 'cesarienne_avant', 'cesarienne_apres','preanesth_ch', 'preop_ch', 'postop_ch', 'preanesth_2016', 'preop_2016', 'postop_2016');";
        $this->addQuery($query);

        $check_list = [
            '01' => [
                'preanesth_2016',
                null,
                [
                    ['L\'identité du patient est correcte', 'normal', 'yes'],
                    [
                        'L\'autorisation d\'opérer est signée par les parents ou le représentant légal',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '02' => [
                'preanesth_2016',
                'L\'intervention et le site opératoire sont confirmés :',
                [
                    [
                        'idéalement par le patient et, dans tous les cas, par le dossier ou procédure spécifique',
                        'normal',
                        'yes',
                    ],
                    ['la documentation clinique et para clinique nécessaire est disponible en salle', 'normal', 'yes'],
                ],
            ],
            '03' => [
                'preanesth_2016',
                null,
                [
                    [
                        'Le mode d\'installation est connu de l\'équipe en salle, cohérent avec le site / l\'intervention et non dangereux pour le patient',
                        'normal',
                        'yes',
                    ],
                ],
            ],
            '04' => [
                'preanesth_2016',
                null,
                [
                    [
                        'La préparation cutanée de l\'opéré est documentée dans la fiche de liaison service / bloc opératoire (ou autre procédure en œuvre dans l\'établissement)',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '05' => [
                'preanesth_2016',
                'L\'équipement / le matériel nécessaires pour l\'intervention sont vérifiés et adaptés au poids et à la taille du patient',
                [
                    ['pour la partie chirurgicale', 'normal', 'yes'],
                    [
                        'pour la partie anesthésique (N/A: Acte sans prise en charge anesthésique)',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '06' => [
                'preanesth_2016',
                'Le patient présente-t-il un :',
                [
                    ['risque allergique', 'normal', 'no'],
                    [
                        'risque d\'inhalation, de difficulté d\'intubation ou de ventilation au masque ',
                        'notapplicable',
                        'no',
                    ],
                    ['risque de saignement important', 'normal', 'no'],
                ],
            ],
            '07' => [
                'preop_2016',
                'Vérification "ultime" croisée au sein de l\'équipe en présence des chirurgiens(s), anesthésiste(s), IADE-IBODE / IDE',
                [
                    ['identité patient confirmée', 'normal', 'yes'],
                    ['intervention prévue confirmée', 'normal', 'yes'],
                    ['site opératoire confirmé', 'normal', 'yes'],
                    ['installation correcte confirmée', 'normal', 'yes'],
                    ['documents nécessaires disponibles (notamment imagerie)', 'notapplicable', 'yes'],
                ],
            ],
            '08' => [
                'preop_2016',
                'Partage des informations essentielles oralement au sein de l\'équipe sur les éléments à risque / étapes critiques de l\'intervention (time-out)',
                [
                    [
                        'sur le plan chirurgical (temps opératoire difficile, points spécifiques de l\'intervention, identification des matériels nécessaires, confirmation de leur opérationnalité, etc.)',
                        'normal',
                        'yes',
                    ],
                    [
                        '  sur le plan anesthésique Acte sans prise en charge anesthésique [risques potentiels liés au terrain (hypothermie, etc.) ou à des traitements éventuellement maintenus, etc.]',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '09' => [
                'preop_2016',
                null,
                [
                    [
                        'L\'antibioprophylaxie a été effectuée selon les recommandations et protocoles en vigueur dans l\'établissement',
                        'notrecommended',
                        'yes',
                    ],
                    [
                        'La préparation du champ opératoire est réalisée selon le protocole en vigueur dans l\'établissement',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '10' => [
                'postop_2016',
                'Confirmation orale par le personnel auprès de l\'équipe :',
                [
                    ['de l\'intervention enregistrée', 'normal', 'yes'],
                    ['du compte final correct des compresses, aiguilles, instruments, etc.', 'notapplicable', 'yes'],
                    ['de l\'étiquetage des prélèvements, pièces opératoires, etc', 'notapplicable', 'yes'],
                    [
                        'si des événements indésirables ou porteurs de risques médicaux sont survenus??: ont-ils fait l\'objet d\'un signalement / déclaration ? (Si aucun évènement indésirable n’est survenu pendant l’intervention cochez N/A)',
                        'notapplicable',
                        'yes',
                    ],
                ],
            ],
            '11' => [
                'postop_2016',
                null,
                [
                    [
                        'Les prescriptions et la surveillance postopératoires (y compris les seuils d’alerte spécifiques) sont faites conjointement par l\'équipe chirurgicale et anesthésique et adaptées à l\'âge, au poids et à la taille du patient',
                        'normal',
                        'yes',
                    ],
                ],
            ],
        ];
        $this->addNewCheckList($check_list);
        $this->makeRevision('0.76');

        //Pour réparer l'erreur de suppression de type sur les checklist
        $category_type = "'preanesth','preop','postop'
                ,'preendoscopie','postendoscopie'
                ,'preendoscopie_bronchique','postendoscopie_bronchique'
                ,'preanesth_radio','preop_radio','postop_radio'
                ,'disp_vasc_avant','disp_vasc_pendant','disp_vasc_apres'
                ,'avant_indu_cesar', 'cesarienne_avant', 'cesarienne_apres'
                ,'preanesth_ch', 'preop_ch', 'postop_ch'
                , 'preanesth_2016', 'preop_2016', 'postop_2016'";
        $query         = "ALTER TABLE `daily_check_item_category`
                CHANGE `type` `type` ENUM ($category_type);";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list`
                CHANGE `type` `type` ENUM ($category_type);";
        $this->addQuery($query);
        $this->makeRevision("0.77");

        $this->addFunctionalPermQuery("chir_modif_timing", "1");
        $this->makeRevision('0.78');

        $this->addPrefQuery("check_all_interventions", "0");

        $this->makeRevision("0.79");
        $this->addDependency('dPcabinet', '0.29');

        $query = "ALTER TABLE `consultation_anesth` 
                ADD `hepatite_b` ENUM ('?','NEG','POS') DEFAULT '?',
                ADD `hepatite_c` ENUM ('?','NEG','POS') DEFAULT '?',
                ADD `inr` FLOAT UNSIGNED,
                ADD `protides` FLOAT,
                ADD `chlore` FLOAT UNSIGNED;";
        $this->addQuery($query);
        $this->makeRevision("0.80");

        $this->addDefaultConfig(
            "dPsalleOp timings use_debut_installation",
            "dPsalleOp COperation use_installation_timings"
        );
        $this->addDefaultConfig(
            "dPsalleOp timings use_fin_installation",
            "dPsalleOp COperation use_installation_timings"
        );

        $conf_timing = [
            "use_tto",
            "timings_induction",
            "use_sortie_sejour_ext",
            "use_incision",
            "use_validation_timings",
            "use_alr_ag",
            "see_pec_anesth",
            "see_entree_reveil_timing",
        ];
        foreach ($conf_timing as $name_timing) {
            $query = "UPDATE `configuration`
            SET `configuration`.`feature` = 'dPsalleOp timings $name_timing'
            WHERE `configuration`.`feature` = 'dPsalleOp COperation $name_timing'";
            $this->addQuery($query);
        }
        $this->makeRevision("0.81");

        $query = "UPDATE `daily_check_item_type`
    SET `title` = 'La préparation adéquate (coloscopie, gastrostomie) a été effectuée dans les conditions prévues'
    WHERE `title` = 'La préparation adéquate (coloscopie, gastrostomie) a été mise en oeuvre'";
        $this->addQuery($query);

        $this->addMethod("changeTitlesEndo2013");
        $this->makeRevision("0.82");

        $query = "UPDATE `daily_check_item_type`, `daily_check_item_category`
    SET `daily_check_item_type`.`title` = 'Vérification croisée de situations spécifiques entre les membres de l\'équipe médico-soignante concernant notamment la gestion des antiagrégants plaquettaires et/ou des anticoagulants ou toute autre co-morbidité identifiée'
                  WHERE `daily_check_item_category`.`target_class` = 'COperation'
                  AND `daily_check_item_category`.`type` = 'preendoscopie'
                  AND `daily_check_item_category`.`title` =  '07'
                  AND `daily_check_item_type`.`category_id` = `daily_check_item_category`.`daily_check_item_category_id`";
        $this->addQuery($query);

        $this->makeRevision("0.83");
        $query = "CREATE TABLE `anesth_perop_categorie` (
                `anesth_perop_categorie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `actif` ENUM ('0','1') DEFAULT '1',
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */";
        $this->addQuery($query);

        $query = "ALTER TABLE `anesth_perop` 
                ADD `categorie_id` INT (11) UNSIGNED, 
                ADD INDEX (`categorie_id`);";
        $this->addQuery($query);

        $this->makeRevision("0.84");
        $this->addDependency('dPcompteRendu', '0.46');

        $query = "CREATE TABLE `geste_perop` (
                `geste_perop_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT NOT NULL,
                `group_id` INT (11) UNSIGNED,
                `function_id` INT (11) UNSIGNED,
                `user_id` INT (11) UNSIGNED,
                `categorie_id` INT (11) UNSIGNED,
                INDEX (`group_id`),
                INDEX (`function_id`),
                INDEX (`user_id`),
                INDEX (`categorie_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "INSERT INTO geste_perop 
                SELECT null, name, text, group_id, function_id, user_id, null 
                FROM aide_saisie
                WHERE class = 'CAnesthPerop'";
        $this->addQuery($query);

        $query = "ALTER TABLE `anesth_perop` 
                ADD `geste_perop_id` INT (11) UNSIGNED,
                ADD INDEX (`geste_perop_id`);";
        $this->addQuery($query);
        $this->makeRevision("0.85");

        $query = "ALTER TABLE `daily_check_list` 
                ADD `validator2_id` INT (11) UNSIGNED,
                ADD `date_validate2` DATETIME,
                ADD `com_validate2` TEXT,
                ADD INDEX (`validator2_id`),
                ADD INDEX (`date_validate2`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list_type` 
                ADD `use_validate_2` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("0.86");
        $query = "ALTER TABLE `anesth_perop` 
                ADD `commentaire` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("0.87");

        $query = "ALTER TABLE `daily_check_list` 
                ADD `decision_go` ENUM ('go','nogo'),
                ADD `result_nogo` ENUM ('retard','annulation');";
        $this->addQuery($query);
        $this->makeRevision("0.88");

        $query = "UPDATE `daily_check_item_category`
      SET `desc` = 'Vérification croisée par l\'équipe de points critiques et mise en oeuvre si besoin, des mesures adéquates. Le patient présente-t-il:'
      WHERE `desc` = 'Vérification croisée par l\'équipe de points critiques et mise en ?uvre si besoin, des mesures adéquates. Le patient présente-t-il:'";
        $this->addQuery($query);
        $this->makeRevision("0.89");

        $this->addFunctionalPermQuery("show_dh_salle_op", "1");
        $this->makeRevision('0.90');

        $this->addPrefQuery("pec_sspi_current_user", "0");
        $this->makeRevision("0.91");

        $query = "CREATE TABLE `protocole_geste_perop` (
                `protocole_geste_perop_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `actif` ENUM ('0','1') DEFAULT '1',
                `group_id` INT (11) UNSIGNED,
                `function_id` INT (11) UNSIGNED,
                `user_id` INT (11) UNSIGNED,
                INDEX (`group_id`),
                INDEX (`function_id`),
                INDEX (`user_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `protocole_geste_perop_item` (
                `protocole_geste_perop_item_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `protocole_geste_perop_id` INT (11) UNSIGNED NOT NULL,
                `object_id` INT (11) UNSIGNED,
                `object_class` ENUM ('CGestePerop','CAnesthPeropCategorie') NOT NULL,
                INDEX (`protocole_geste_perop_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("0.92");

        $query = "ALTER TABLE `acte_ccam`
                ADD `prescription_id` INT (11) UNSIGNED,
                ADD `other_executant_id` INT (11) UNSIGNED,
                ADD `motif` TEXT,
                ADD `motif_unique_cim` VARCHAR (6);";
        $this->addQuery($query);
        $this->makeRevision("0.93");

        $query = "ALTER TABLE `daily_check_list_type` 
                ADD `decision_go` ENUM('0','1') DEFAULT '0',
                ADD `alert_child` ENUM('0','1') DEFAULT '0';";
        $this->addQuery($query);
        $this->makeRevision("0.94");

        $query = "CREATE TABLE `anesth_perop_chapitre` (
                `anesth_perop_chapitre_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `actif` ENUM('0','1') DEFAULT '1',
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `anesth_perop_categorie` 
                ADD `chapitre_id` INT (11) UNSIGNED AFTER `group_id`,
                ADD INDEX (`chapitre_id`);";
        $this->addQuery($query);

        $query = "ALTER TABLE `geste_perop` 
                ADD `precision_1_id` INT (11) UNSIGNED,
                ADD `precision_2_id` INT (11) UNSIGNED, 
                ADD INDEX (`precision_1_id`),
                ADD INDEX (`precision_2_id`);";
        $this->addQuery($query);

        $query = "CREATE TABLE `geste_perop_precision` (
                `geste_perop_precision_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `valeur` VARCHAR (255),
                INDEX (`group_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);
        $this->makeRevision("0.95");

        $query = "ALTER TABLE `protocole_geste_perop_item` 
                ADD `rank` INT (11) UNSIGNED;";
        $this->addQuery($query);

        $query = "ALTER TABLE `geste_perop` 
                ADD `actif` ENUM('0','1') DEFAULT '1';";
        $this->addQuery($query);
        $this->makeRevision("0.96");

        $query = "DROP TABLE `geste_perop_precision`";
        $this->addQuery($query);

        $query = "ALTER TABLE `geste_perop` 
                DROP `precision_1_id`,
                DROP `precision_2_id`, 
                DROP INDEX `precision_1_id`,
                DROP INDEX `precision_2_id`;";
        $this->addQuery($query);

        $query = "CREATE TABLE `geste_perop_precision` (
                `geste_perop_precision_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `geste_perop_id` INT (11) UNSIGNED,
                `libelle` VARCHAR (255) NOT NULL,
                `description` TEXT,
                `actif` ENUM ('0', '1') DEFAULT '1',
                INDEX (`group_id`),
                INDEX (`geste_perop_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "CREATE TABLE `precision_valeur` (
                `precision_valeur_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `group_id` INT (11) UNSIGNED NOT NULL,
                `geste_perop_precision_id` INT (11) UNSIGNED,
                `valeur` VARCHAR (255) NOT NULL,
                `actif` ENUM ('0', '1') DEFAULT '1',
                INDEX (`group_id`),
                INDEX (`geste_perop_precision_id`)
              )/*! ENGINE=MyISAM */;";
        $this->addQuery($query);

        $query = "ALTER TABLE `anesth_perop` 
                ADD `geste_perop_precision_id` INT (11) UNSIGNED AFTER `geste_perop_id`,
                ADD `precision_valeur_id` INT (11) UNSIGNED AFTER `geste_perop_precision_id`, 
                ADD INDEX (`geste_perop_precision_id`),
                ADD INDEX (`precision_valeur_id`);";
        $this->addQuery($query);
        $this->makeRevision('0.97');

        $this->addPrefQuery("show_all_gestes_perop", "0");
        $this->makeRevision('0.98');

        $query = "ALTER TABLE `daily_check_list_type_link`
                CHANGE `object_class` `object_class` ENUM ('CSalle','CBlocOperatoire', 'CSSPI') NOT NULL DEFAULT 'CSalle';";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_list` 
                CHANGE `object_class` `object_class` ENUM ('CSalle','CBlocOperatoire','COperation','CPoseDispositifVasculaire', 'CSSPI') NOT NULL DEFAULT 'CSalle'";
        $this->addQuery($query);
        $this->makeRevision('0.99');

        $this->addPrefQuery("show_all_datas_surveillance_timeline", "0");
        $this->makeRevision('1.00');

        $query = "UPDATE `user_preferences`
                SET `restricted` = '1',
                    `value` = '0'
                WHERE `key` = 'show_all_gestes_perop';";
        $this->addQuery($query);
        $this->makeRevision("1.01");

        $query = "ALTER TABLE `geste_perop` 
                CHANGE `description` `description` TEXT;";
        $this->addQuery($query);
        $this->makeRevision("1.02");

        $query = "ALTER TABLE `anesth_perop` 
                ADD `user_id` INT (11) UNSIGNED,
                ADD INDEX (`user_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.03");
        $this->setModuleCategory("parametrage", "metier");

        $this->makeRevision("1.04");
        $query = "ALTER TABLE `protocole_geste_perop_item` 
                ADD `checked` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $this->makeRevision("1.05");
        $query = "ALTER TABLE `geste_perop` 
                ADD `incident` ENUM ('0','1') DEFAULT '0' AFTER `categorie_id`,
                ADD `antecedent_code_cim` VARCHAR (255) AFTER `incident`;";
        $this->addQuery($query);
        $this->makeRevision("1.06");

        $query = "ALTER TABLE `daily_check_item_type` 
                CHANGE `attribute` `attribute` ENUM ('normal','notrecommended','notapplicable','texte') DEFAULT 'normal';";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item` 
                ADD `commentaire` VARCHAR (255);";
        $this->addQuery($query);
        $this->makeRevision("1.07");

        $query = "ALTER TABLE `daily_check_list` 
                ADD `code_red` ENUM ('0','1') DEFAULT '0';";
        $this->addQuery($query);

        $query = "ALTER TABLE `daily_check_item` 
                CHANGE `checked` `checked` ENUM ('yes','no','nr','na');";
        $this->addQuery($query);

        $query = "UPDATE `daily_check_item_type`
    SET `daily_check_item_type`.`title` = 'sur le plan obstétrical (temps opératoire difficile, localisation du placenta, points spécifiques de l\'intervention, prélèvements cordon placenta, identification des matériels nécessaires, confirmation de leur opérationnalité, etc.)'
                  WHERE `daily_check_item_type`.`title` LIKE '%obtétrical %'";
        $this->addQuery($query);

        $this->makeRevision("1.08");
        $query = "ALTER TABLE `protocole_geste_perop_item` 
                ADD `geste_perop_precision_id` INT (11) UNSIGNED AFTER `protocole_geste_perop_id`,
                ADD `precision_valeur_id` INT (11) UNSIGNED AFTER `geste_perop_precision_id`,
                ADD INDEX (`geste_perop_precision_id`),
                ADD INDEX (`precision_valeur_id`);";
        $this->addQuery($query);

        $this->makeRevision("1.09");
        $this->addDefaultConfig('dPsalleOp General anesth_mode', 'dPsalleOp mode_anesth', '0');
        $this->addDefaultConfig(
            'dPsalleOp Timing_list max_add_minutes',
            'dPsalleOp max_add_minutes',
            '10'
        );
        $this->addDefaultConfig('dPsalleOp Timing_list max_sub_minutes', 'dPsalleOp max_sub_minutes', '30');
        $this->addDefaultConfig('dPsalleOp COperation mode', 'dPsalleOp COperation mode', '0');
        $this->addDefaultConfig('dPsalleOp COperation allow_change_room', 'dPsalleOp COperation modif_salle', '0');
        $this->addDefaultConfig(
            'dPsalleOp timings use_entry_exit_room',
            'dPsalleOp COperation use_entree_sortie_salle',
            '1'
        );
        $this->addDefaultConfig(
            'dPsalleOp timings use_exit_without_sspi',
            'dPsalleOp COperation use_sortie_sans_sspi',
            '0'
        );
        $this->addDefaultConfig('dPsalleOp timings use_garrot', 'dPsalleOp COperation use_garrot', '1');
        $this->addDefaultConfig('dPsalleOp timings use_end_op', 'dPsalleOp COperation use_debut_fin_op', '1');
        $this->addDefaultConfig('dPsalleOp timings use_entry_room', 'dPsalleOp COperation use_entree_bloc', '0');
        $this->addDefaultConfig('dPsalleOp timings use_delivery_surgeon', 'dPsalleOp COperation use_remise_chir', '0');
        $this->addDefaultConfig('dPsalleOp timings use_suture', 'dPsalleOp COperation use_suture', '0');
        $this->addDefaultConfig('dPsalleOp timings use_check_timing', 'dPsalleOp COperation use_check_timing', '0');
        $this->addDefaultConfig(
            'dPsalleOp timings use_cleaning_timings',
            'dPsalleOp COperation use_cleaning_timings',
            '0'
        );
        $this->addDefaultConfig('dPsalleOp timings use_prep_cutanee', 'dPsalleOp COperation use_prep_cutanee', '0');
        $this->addDefaultConfig(
            'dPsalleOp CActeCCAM check_incompatibility',
            'dPsalleOp CActeCCAM check_incompatibility',
            'allow'
        );
        $this->addDefaultConfig(
            'dPsalleOp CActeCCAM allow_send_acts_room',
            'dPsalleOp CActeCCAM envoi_actes_salle',
            '0'
        );
        $this->addDefaultConfig(
            'dPsalleOp CActeCCAM allow_send_reason_exceeding',
            'dPsalleOp CActeCCAM envoi_motif_depassement',
            '1'
        );
        $this->addDefaultConfig(
            'dPsalleOp CActeCCAM del_acts_not_rated',
            'dPsalleOp CActeCCAM del_actes_non_cotes',
            '0'
        );

        $this->mod_version = "1.10";
    }
}
