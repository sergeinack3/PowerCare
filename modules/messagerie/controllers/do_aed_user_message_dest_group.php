<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CUserMessageDestGroupController;

CCanDo::checkAdmin();

(new CUserMessageDestGroupController())->doIt();
