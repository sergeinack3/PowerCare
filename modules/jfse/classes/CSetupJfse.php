<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 */
final class CSetupJfse extends CSetup
{
    /**
     * @see parent::__construct()
     */
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = 'jfse';
        $this->makeRevision('0.0');
        $this->setModuleCategory('autre', 'metier');

        $this->makeRevision('0.01');

        $this->addQuery(
            "CREATE TABLE `jfse_users` (
                `jfse_user_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `jfse_id` VARCHAR (32) NOT NULL,
                `mediuser_id` INT (11) UNSIGNED,
                `jfse_establishment_id` INT (11) UNSIGNED,
                `creation` DATETIME NOT NULL,
                `securing_mode` ENUM ('3', '4') DEFAULT '3',
                INDEX (`jfse_id`),
                INDEX (`mediuser_id`),
                INDEX (`creation`),
                CONSTRAINT unique_jfse_id UNIQUE (jfse_id)
            )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.02');

        $this->addQuery(
            'CREATE TABLE `jfse_patients` (
                `jfse_patient_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `patient_id` INT (11) UNSIGNED NOT NULL,
                `nir` VARCHAR (32),
                `birth_date` VARCHAR(10),
                `birth_rank` INT (11) UNSIGNED NOT NULL,
                `quality` INT (11) UNSIGNED NOT NULL,
                INDEX (`patient_id`),
                INDEX (`nir`),
                INDEX (`birth_date`)
            )/*! ENGINE=MyISAM */;'
        );

        $this->makeRevision('0.03');

        $this->addQuery(
            "CREATE TABLE `jfse_establishments` (
                `jfse_establishment_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `jfse_id` VARCHAR (32) NOT NULL,
                `object_class` ENUM ('CGroups', 'CFunctions'),
                `object_id` INT (11) UNSIGNED,
                `creation` DATETIME NOT NULL,
                INDEX (`jfse_id`),
                INDEX (`object_class`),
                INDEX (`object_id`),
                INDEX (`creation`),
                CONSTRAINT unique_jfse_id UNIQUE (jfse_id)
            )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.04');

        $this->addQuery(
            "CREATE TABLE `jfse_invoices` (
                `jfse_invoice_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `jfse_id` VARCHAR (32),
                `consultation_id` INT (11) UNSIGNED NOT NULL,
                `jfse_user_id` INT (11) UNSIGNED NOT NULL,
                `jfse_patient_id` INT (11) UNSIGNED,
                `creation` DATETIME NOT NULL,
                `status` ENUM ('pending', 'validated') DEFAULT 'pending',
                INDEX (`jfse_id`),
                INDEX (`consultation_id`),
                INDEX (`jfse_user_id`),
                INDEX (`jfse_patient_id`),
                INDEX (`creation`),
                INDEX (`status`)
            )/*! ENGINE=MyISAM */;"
        );

        $this->addQuery(
            "CREATE TABLE `jfse_acts` (
                `jfse_act_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `jfse_id` VARCHAR (32) NOT NULL,
                `jfse_invoice_id` INT (11) UNSIGNED NOT NULL,
                `act_class` ENUM ('CActeCCAM', 'CActeNGAP', 'CActeLPP') NOT NULL,
                `act_id` INT (11) UNSIGNED NOT NULL,
                INDEX (`jfse_id`),
                INDEX (`jfse_invoice_id`),
                INDEX (`act_class`),
                INDEX (`act_id`)
            )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.05');

        $this->addQuery('ALTER TABLE `jfse_invoices` ADD `invoice_number` INT (11) UNSIGNED;');

        $this->makeRevision('0.06');

        $this->addQuery(
            'ALTER TABLE `jfse_patients`
                ADD `certified_nir` VARCHAR(32),
                ADD `last_name` VARCHAR (100),
                ADD `first_name` VARCHAR (100),
                ADD `amo_regime_code` VARCHAR (2),
                ADD `amo_managing_fund` VARCHAR (3),
                ADD `amo_managing_center` VARCHAR (4),
                ADD `amo_managing_code` VARCHAR(10);'
        );

        $this->makeRevision('0.07');

        $this->addQuery(
            'CREATE TABLE `jfse_payments`(
                `jfse_payment_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `jfse_id` VARCHAR (32) NOT NULL,
                `jfse_user_id` INT (11) UNSIGNED NOT NULL,
                `date` DATE,
                `label` VARCHAR (255),
                `organism` VARCHAR (255),
                `amount` FLOAT,
                INDEX (`jfse_id`),
                INDEX (`jfse_user_id`),
                INDEX (`date`)
            )/*! ENGINE=MyISAM */;'
        );

        $this->addQuery(
            'CREATE TABLE `jfse_invoice_payments`(
                `jfse_invoice_payment_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `invoice_id` INT (11) UNSIGNED NOT NULL,
                `payment_id` INT (11) UNSIGNED NOT NULL,
                `amount_amo` FLOAT,
                `amount_amc` FLOAT,
                `total` FLOAT,
                INDEX (`invoice_id`),
                INDEX (`payment_id`)
            )/*! ENGINE=MyISAM */;'
        );

        $this->addQuery(
            "CREATE TABLE `jfse_invoice_sets`(
                `jfse_invoice_set_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `jfse_id` VARCHAR (32) NOT NULL,
                `jfse_user_id` INT (11) UNSIGNED NOT NULL,
                `number` INT (9),
                `date` DATE,
                `status` ENUM ('accepted', 'rejected'),
                `return_label` VARCHAR (255),
                INDEX (`jfse_id`),
                INDEX (`jfse_user_id`),
                INDEX (`number`),
                INDEX (`date`),
                INDEX (`status`)
            )/*! ENGINE=MyISAM */;"
        );

        $this->addQuery(
            "ALTER TABLE `jfse_invoices`
                    MODIFY `status` ENUM (
                        'pending', 'validated', 'sent', 'accepted',
                        'rejected', 'paid', 'payment_rejected', 'no_ack_needed'
                    ) DEFAULT 'pending',
                    ADD `third_party_payment` ENUM ('0', '1') DEFAULT '0',
                    ADD `set_id` INT (11) UNSIGNED,
                    ADD `reject_reason` VARCHAR (255);"
        );

        $this->addQuery(
            "INSERT INTO `cronjob` (`name`, `description`, `active`, `params`, `execution`, `mode`) VALUES
                    (
                        'JFSE: RSPs',
                        'Récupération des RSP (retours Noemie) et traitement des virements',
                        '0',
                        'm=jfse&raw=importNoemiePayments',
                        '0 */5 20-05 * * *',
                        'lock'
                     ),
                    (
                        'JFSE: ARLs',
                        'Récupération des ARL et mises à jour de l\'état des FSEs',
                        '0',
                        'm=jfse&raw=importInvoiceAcknowledgements',
                        '0 */5 20-05 * * *',
                        'lock'
                    );"
        );

        $this->mod_version = '0.08';
    }
}
