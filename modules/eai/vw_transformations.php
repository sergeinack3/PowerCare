<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;
use Ox\Interop\Eai\Transformations\CTransformationRuleSet;

/**
 * View transformations EAI
 */

CCanDo::checkAdmin();

CView::checkin();

$transf_ruleset  = new CTransformationRuleSet();
/** @var CTransformationRuleSet[] $transf_rulesets */
$transf_rulesets = $transf_ruleset->loadList();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("transf_rulesets", $transf_rulesets);
$smarty->assign("transf_rule_sequence", new CTransformationRuleSequence());
$smarty->display("vw_transformations.tpl");
