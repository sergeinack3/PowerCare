<?php
/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Webservices;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
class CSetupWebservices extends CSetup {
  function __construct() {
    parent::__construct();

    $this->mod_name = "webservices";
    $this->makeRevision("0.0");

    $this->makeRevision("0.10");

    $query = "CREATE TABLE `echange_soap` (
              `echange_soap_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
              `emetteur` VARCHAR (255),
              `destinataire` VARCHAR (255),
              `type` VARCHAR (255),
              `date_echange` DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
              `web_service_name` VARCHAR (255),
              `function_name` VARCHAR (255) NOT NULL,
              `input` TEXT NOT NULL,
              `output` TEXT
            ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);

    $this->makeRevision("0.11");
    $query = "ALTER TABLE `echange_soap`
               ADD `soapfault` ENUM ('0','1') DEFAULT '0',
               ADD INDEX (`date_echange`);";
     $this->addQuery($query);

     $this->makeRevision("0.12");
     $query = "ALTER TABLE `echange_soap`
              ADD `purge` ENUM ('0','1') DEFAULT '0';";
     $this->addQuery($query);

     $this->makeRevision("0.13");
     $query = "ALTER TABLE `echange_soap`
              CHANGE `input` `input` TEXT;";
     $this->addQuery($query);

     $this->makeRevision("0.14");
     $query = "ALTER TABLE `echange_soap`
               ADD `response_time` FLOAT,
               ADD INDEX (`type`);";
     $this->addQuery($query);

     $this->makeRevision("0.15");
     $query = "ALTER TABLE `echange_soap`
               ADD INDEX (`web_service_name`),
               ADD INDEX (`function_name`);";
     $this->addQuery($query);

     $this->makeRevision("0.16");
     $query = "ALTER TABLE `echange_soap`
              ADD `trace` ENUM ('0','1') DEFAULT '0',
              ADD `last_request_headers` TEXT,
              ADD `last_response_headers` TEXT,
              ADD `last_request` MEDIUMTEXT,
              ADD `last_response` MEDIUMTEXT;";
     $this->addQuery($query);

     $query = "CREATE TABLE IF NOT EXISTS `source_soap` (
                `source_soap_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `wsdl_mode` ENUM ('0','1') DEFAULT '1',
                `name` VARCHAR (255) NOT NULL,
                `host` TEXT NOT NULL,
                `user` VARCHAR (255),
                `password` VARCHAR (50)
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
   
    $this->makeRevision("0.17");

    $query = "ALTER TABLE `source_soap` 
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'qualif';";
    $this->addQuery($query, true);
    
    $query = "ALTER TABLE `source_soap` 
                ADD `evenement_name` VARCHAR (255),
                ADD `type_echange` VARCHAR (255);";
    $this->addQuery($query, true); 

    $query = "ALTER TABLE `source_soap` 
                ADD `single_parameter` VARCHAR (255);";
    $this->addQuery($query, true);
    
    $this->makeRevision("0.18");
    
    $query = "ALTER TABLE `source_soap` 
                ADD `active` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);
    
    $this->makeRevision("0.19");
    
    $query = "CREATE TABLE `sender_soap` (
                `sender_soap_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `nom` VARCHAR (255) NOT NULL,
                `libelle` VARCHAR (255),
                `group_id` INT (11) UNSIGNED NOT NULL,
                `actif` ENUM ('0','1') NOT NULL DEFAULT '0'
              ) /*! ENGINE=MyISAM */;";
    $this->addQuery($query);
    
    $query = "ALTER TABLE `sender_soap` 
                ADD INDEX (`group_id`);";
    $this->addQuery($query);
    
    $this->makeRevision("0.20");
    
    $query = "ALTER TABLE `sender_soap` 
                ADD `user_id` INT (11) UNSIGNED;";
    $this->addQuery($query);
    
    $query = "ALTER TABLE `sender_soap` 
                ADD INDEX (`user_id`);";
    $this->addQuery($query);
    
    $this->makeRevision("0.21");
    
    $query = "ALTER TABLE `source_soap` 
                ADD `loggable` ENUM ('0','1') NOT NULL DEFAULT '1';";
    $this->addQuery($query);
    
    $this->makeRevision("0.22");
    
    $query = "ALTER TABLE `echange_soap` 
                ADD INDEX (`purge`);";
    $this->addQuery($query);
    
    $this->makeRevision("0.23");
    
    $query = "ALTER TABLE `source_soap` 
                ADD `encoding` ENUM ('UTF-8','ISO-8859-1','ISO-8859-15') DEFAULT 'UTF-8';";
    $this->addQuery($query);
    
    $this->makeRevision("0.24");
    
    $query = "ALTER TABLE `source_soap` 
                ADD `stream_context` VARCHAR (255);";
    $this->addQuery($query);
    
    $this->makeRevision("0.25");
    
    $query = "ALTER TABLE `sender_soap` 
                ADD `save_unsupported_message` ENUM ('0','1') DEFAULT '1',
                ADD `create_ack_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);
    
    $this->makeRevision("0.26");
    
    $query = "ALTER TABLE `source_soap` 
                ADD `type_soap` ENUM ('CMbSOAPClient','CNuSOAPClient') NOT NULL DEFAULT 'CMbSOAPClient';";
    $this->addQuery($query);
    
    $this->makeRevision("0.27");
    
    $query = "ALTER TABLE `source_soap` 
                ADD `local_cert` VARCHAR (255),
                ADD `passphrase` VARCHAR (255);";
    $this->addQuery($query);
    
    $this->makeRevision("0.28");
    
    $query = "ALTER TABLE `sender_soap` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.29");
    $query = "ALTER TABLE `source_soap`
                CHANGE `password` `password` VARCHAR (255),
                ADD `iv` VARCHAR (16) AFTER `password`,
                ADD `iv_passphrase` VARCHAR (16) AFTER `passphrase`;";
    $this->addQuery($query);

    $this->makeRevision("0.30");
    $query = "UPDATE source_soap
                SET source_soap.name = Replace(name, 'CReceiverIHE', 'CReceiverHL7v2')
                WHERE source_soap.name LIKE 'CReceiverIHE-%';";
    $this->addQuery($query);

    $this->makeRevision("0.31");

    $query = "ALTER TABLE source_soap
                ADD `safe_mode` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.32");

    $query = "ALTER TABLE source_soap
                ADD `soap_version` ENUM ('SOAP_1_1','SOAP_1_2') NOT NULL DEFAULT 'SOAP_1_1',
                ADD `verify_peer` ENUM ('0','1') DEFAULT '0',
                ADD `cafile` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.33");

    $query = "ALTER TABLE `source_soap`
                ADD `return_raw` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.34");

    $query = "ALTER TABLE `source_soap`
                ADD `xop_mode` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.35");

    $query = "ALTER TABLE `echange_soap`
                CHANGE `input` `input` LONGTEXT,
                CHANGE `output` `output` LONGTEXT;";
    $this->addQuery($query);

    $this->makeRevision("0.36");

    $query = "ALTER TABLE `source_soap`
                ADD `wsdl_external` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.37");

    $query = "ALTER TABLE `source_soap`
                ADD `use_tunnel` ENUM ('0','1') DEFAULT '0';";
    $this->addQuery($query);

    $this->makeRevision("0.38");

    $query = "ALTER TABLE `source_soap`
                ADD `socket_timeout` INT (11) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.39");

    $query = "ALTER TABLE `source_soap`
                ADD `return_mode` ENUM ('normal','raw','file') NOT NULL DEFAULT 'normal' AFTER `return_raw`;";
    $this->addQuery($query);

    $query = "UPDATE `source_soap`
                SET `return_mode` = 'raw' WHERE `return_raw` = '1';";
    $this->addQuery($query);

    $query = "ALTER TABLE `source_soap`
                DROP `return_raw`;";
    $this->addQuery($query);

    $this->makeRevision("0.40");

    $query = "ALTER TABLE `source_soap`
                ADD `feature` ENUM ('SOAP_SINGLE_ELEMENT_ARRAYS','SOAP_USE_XSI_ARRAY_TYPE','SOAP_WAIT_ONE_WAY_CALLS');";
    $this->addQuery($query);

    $this->makeRevision("0.41");

    $query = "ALTER TABLE `source_soap`
                ADD `libelle` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.42");

    $query = "ALTER TABLE `source_soap`
                ADD `connection_timeout` INT (4) UNSIGNED;";
    $this->addQuery($query);

    $this->makeRevision("0.43");
    $query = "ALTER TABLE `sender_soap`
                CHANGE `actif` `actif` ENUM ('0','1') NOT NULL DEFAULT '1',
                ADD `role` ENUM ('prod','qualif') NOT NULL DEFAULT 'prod';";
    $this->addQuery($query);

    $this->makeRevision("0.44");
    $query = "ALTER TABLE `sender_soap`
                ADD `exchange_format_delayed` SMALLINT (4) UNSIGNED DEFAULT '60';";
    $this->addQuery($query);

    $this->makeRevision("0.45");

    $query = "ALTER TABLE `source_soap`
                ADD `port_name` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.46");

    $query = "ALTER TABLE `source_soap`
                DROP `wsdl_mode`;";
    $this->addQuery($query);

    $this->makeRevision("0.47");
    $query = "ALTER TABLE `sender_soap` 
                ADD `OID` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision("0.48");

    $query = "ALTER TABLE `echange_soap`
                ADD INDEX `purge_echange` (`purge`, `date_echange`);";
    $this->addQuery($query, true);

    $this->makeRevision("0.49");

    $query = "ALTER TABLE `echange_soap`
                ADD `response_datetime` DATETIME;";
    $this->addQuery($query);

    $this->makeRevision("0.50");

    $query = "ALTER TABLE `echange_soap`
                ADD `source_id` INT (11) UNSIGNED AFTER `destinataire`,
                ADD `source_class` VARCHAR (80) AFTER `source_id`;";
    $this->addQuery($query);

    $this->makeRevision("0.51");
    $query = "ALTER TABLE `source_soap` 
                ADD `delete_file` ENUM ('0','1') DEFAULT '1';";
    $this->addQuery($query);

    $this->makeRevision("0.52");
    $query = "ALTER TABLE `source_soap` 
                DROP `delete_file`";
    $this->addQuery($query);

    $this->makeRevision("0.53");
    $query = "ALTER TABLE `source_soap` 
                MODIFY `type_soap` ENUM ('CMbSOAPClient') NOT NULL DEFAULT 'CMbSOAPClient';";
    $this->addQuery($query);

    $this->makeRevision("0.54");
    $this->setModuleCategory("interoperabilite", "echange");

    $this->makeRevision("0.55");

    $query = "ALTER TABLE `sender_soap` 
                ADD `response` ENUM ('none','auto_generate_before','postprocessor') DEFAULT 'none';";
    $this->addQuery($query);

    $query = "UPDATE `sender_soap`
                SET `response` = 'postprocessor'
                WHERE `create_ack_file` = '1'";
    $this->addQuery($query);

    $query = "ALTER TABLE `sender_soap` 
                DROP `create_ack_file`,
                DROP `delete_file`;";
    $this->addQuery($query);

    $this->makeRevision('0.56');
    $query = "ALTER TABLE `sender_soap` 
                ADD `type` VARCHAR (255);";
    $this->addQuery($query);

    $this->makeRevision('0.57');
    $query = "ALTER TABLE `source_soap` ADD `client_name` VARCHAR (255);";
    $this->addQuery($query);

      $this->makeRevision('0.58');
      $query = "ALTER TABLE `source_soap` 
        ADD `retry_strategy` VARCHAR(255), 
        ADD `first_call_date` DATETIME;";
      $this->addQuery($query);

    $this->mod_version = "0.59";
  }
}
