<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig["dPfiles"] = array(
  "CFile" => array(
    "upload_directory"         => "files",
    "upload_directory_private" => "",
    "signature_filename"       => "",
    "ooo_active"               => "0",
    "python_path"              => "",
    "migration_limit"          => "100",
    "migration_ratio"          => "10",
    "migration_finished"       => "0",
    "migration_started"        => "0",
    "prefix_format"            => "",
    "prefix_format_qualif"     => "",
    "hierarchy"                => "2,2,2",
  ),

  "CThumbnail" => array(
    "gs_alias" => "gs",
  ),

  "import_dir"         => "",
  "import_mediuser_id" => "",

  "tika" => array(
    "host"           => "",
    "port"           => "",
    "active_ocr_pdf" => "0",
    "timeout"        => "60",
  )
);
