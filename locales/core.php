<?php

use Ox\Core\CAppUI;

/**
 * @package Mediboard
 * @author SAS OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html 
 */

CAppUI::loadLocales();

// Encoding definition
require __DIR__."/".CAppUI::$lang."/meta.php";
