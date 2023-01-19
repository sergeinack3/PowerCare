<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\Locales\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// This file is used if an error occured too early in the FW initialization

$root_dir = dirname(__DIR__);
$src_logo = file_exists($root_dir . '/images/pictures/logo_custom.png')
    ? 'images/pictures/logo_custom.png'
    : 'images/pictures/logo.png';
$bg_custom = $root_dir . '/images/pictures/bg_custom.jpg';

$loader = new FilesystemLoader(dirname(__DIR__) . '/templates/');
$twig   = new Environment($loader, ['charset' => 'windows-1252']);
$twig->addGlobal('Translator', new Translator());
$twig->display(
    'error.html.twig',
    [
        'external_url' => rtrim(CApp::getBaseUrl(), '/') . '/',
        'src_logo'     => $src_logo,
        'bg_custom'    => $bg_custom,
        'bg'           => is_file($bg_custom),
    ]
);
