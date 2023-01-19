<?php
/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Global system and modules
 * WARNING: no config documentation in those files
 * Use instead locales for UI documentation
 */
// Needed for module config file inclusions
// Beginning of this file or installer will fail on config loading.

$dPconfig = [];

// No trailing slash, no backslashes for Win users (use slashes instead)
$dPconfig["root_dir"]            = "/var/www/mediboard";
$dPconfig["product_name"]        = "Produit";
$dPconfig["company_name"]        = "OpenXtrem";
$dPconfig["page_title"]          = "Mediboard SIH";
$dPconfig["base_url"]            = "http://localhost/mediboard/";
$dPconfig["external_url"]        = "http://localhost/mediboard/";
$dPconfig["master_key_filepath"] = "";

$dPconfig['intercept_database_engine_instruction'] = '0';

$dPconfig["offline"]                   = "0";
$dPconfig["offline_non_admin"]         = "0";
$dPconfig["instance_role"]             = "qualif";
$dPconfig["mb_id"]                     = "";
$dPconfig["mb_oid"]                    = "";
$dPconfig["servers_ip"]                = "";
$dPconfig["minify_javascript"]         = "1";
$dPconfig["minify_css"]                = "1";
$dPconfig["currency_symbol"]           = "&euro;";
$dPconfig["ref_pays"]                  = "1";
$dPconfig["hide_confidential"]         = "0";
$dPconfig["modal_windows_draggable"]   = "0";
$dPconfig["locale_warn"]               = "0";
$dPconfig["locale_alert"]              = "^";
$dPconfig["login_browser_check"]       = "0";
$dPconfig["debug"]                     = "1";
$dPconfig["readonly"]                  = "0";
$dPconfig["check_server_connectivity"] = "0";

// Logging
$dPconfig["dir_log_mediboard"]          = "";
$dPconfig["log_datasource_metrics"]     = "1";
$dPconfig["log_access"]                 = "1";
$dPconfig["access_log_buffer_lifetime"] = "100";
$dPconfig["aggregate_lifetime"]         = "100";
$dPconfig["human_long_request_level"]   = "10";
$dPconfig["bot_long_request_level"]     = "100";
$dPconfig['log_all_queries']            = '';
$dPconfig["long_request_whitelist"]     = "";
$dPconfig["logged_handler_calls_list"]  = "";

// Logging NoSQL
$dPconfig["application_log_using_nosql"]        = "0";
$dPconfig["error_log_using_nosql"]              = "0";

// user_action
$dPconfig['activer_user_action']              = '0';
$dPconfig['activer_migration_log_to_action']  = '1';
$dPconfig['activer_compression_diff']         = '1';
$dPconfig['migration_log_to_action_probably'] = '100';
$dPconfig['migration_log_to_action_nbr']      = '1000';


// Shared memory
$dPconfig["shared_memory"]             = "none";
$dPconfig["shared_memory_distributed"] = "";
$dPconfig["shared_memory_params"]      = "";

// Session
$dPconfig["session_handler"]            = "files";
$dPconfig["session_handler_mutex_type"] = "files";

// Purge
$dPconfig['CAlert_purge_lifetime'] = '100';
$dPconfig['CAlert_purge_delay']    = '90';

$dPconfig['CViewAccessToken_purge_delay'] = '7';

$dPconfig['CCronJobLog_purge_probability'] = '1000';
$dPconfig['CCronJobLog_purge_delay']       = '30';

// Mutex
$dPconfig["mutex_drivers"] = [
    "CMbRedisMutex" => "0",
    "CMbAPCMutex"   => "0",
    "CMbFileMutex"  => "1",
];

$dPconfig["mutex_drivers_params"] = [
    "CMbRedisMutex" => "127.0.0.1:6379", // List of Redis servers
    "CMbFileMutex"  => "", // The folder that will contain the lock files
];

$dPconfig["weinre_debug_host"] = "";

$dPconfig["offline_time_start"] = "";
$dPconfig["offline_time_end"]   = "";

$dPconfig["issue_tracker_url"] = "";
$dPconfig["help_page_url"]     = "";

// Security
$dPconfig['purify_text_input']        = '0';
$dPconfig['app_private_key_filepath'] = '';
$dPconfig['app_public_key_filepath']  = '';
$dPconfig['app_master_key_filepath']  = '';
$dPconfig['anti_csrf_enable']         = '1';

$dPconfig["config_db"] = "0";

// Dataminer limit
$dPconfig["dataminer_limit"] = "20";

// Object merge
$dPconfig["merge_prevent_base_without_idex"] = "1";

$dPconfig["aio_output_path"] = "";

// Object handlers
$dPconfig["object_handlers"] = [];

// Index handlers
$dPconfig["index_handlers"] = [];

// EAI handlers
$dPconfig["eai_handlers"] = [];

// Template placehodlers
$dPconfig["appbar_shortcuts"] = [];

// Slaving
$dPconfig["enslaving_active"] = "1";
$dPconfig["enslaving_ratio"]  = "100";

// Time format
$dPconfig["date"]     = "%d/%m/%Y";
$dPconfig["time"]     = "%Hh%M";
$dPconfig["datetime"] = "%d/%m/%Y %Hh%M";
$dPconfig["longdate"] = "%A %d %B %Y";
$dPconfig["longtime"] = "%H heures %M minutes";
$dPconfig["timezone"] = "Europe/Paris";

// PHP config
$dPconfig["php"] = [];

// Standard database config
$dPconfig["db"]["std"] = [
    "dbtype" => "mysql",     // Change to use another dbms
    "dbhost" => "localhost", // Change to connect to a distant Database
    "dbname" => "", // Change to match your Mediboard Database Name
    "dbuser" => "", // Change to match your Username
    "dbpass" => "", // Change to match your Password
];

// Slave database config for readonly time consuming views
$dPconfig["db"]["slave"] = [
    "dbtype" => "",
    "dbhost" => "",
    "dbname" => "",
    "dbuser" => "",
    "dbpass" => "",
];

// Compatibility mode
$dPconfig["interop"] = [
    "mode_compat" => "default",
];

// File parsers to return indexing information about uploaded files
$dPconfig["ft"] = [
    "default"            => "/usr/bin/strings",
    "application/msword" => "/usr/bin/strings",
    "text/html"          => "/usr/bin/strings",
    "application/pdf"    => "/usr/bin/pdftotext",
];

$dPconfig["other_databases"] = "";
