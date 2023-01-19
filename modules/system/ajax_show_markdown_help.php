<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;

$styles = array(
  "Titles" => array("# Titre de niveau 1 #", "## Titre de niveau 2 ##", "### Titre de niveau 3 ###"),
  "Blockquotes" => "> Ceci est un bloc de citation avec deux paragraphes. Lorem ipsum dolor\n> sit amet, consectetuer adipiscing elit. Aliquam hendrerit mi posuere\n> lectus. Vestibulum enim wisi, viverra nec, fringilla in, laoreet vitae,\n> risus.\n>\n> Donec sit amet nisl. Aliquam semper ipsum sit amet velit. Suspendisse\n> id sem consectetuer libero luctus adipiscing.",
  "Lists" => array(
    "* Rouge\n* Vert\n* Bleu",
    "+ Rouge\n+ Vert\n+ Bleu",
    "- Rouge\n- Vert\n- Bleu",
    "1. Rouge\n2. Vert\n3. Bleu"
  ),
  "Code blocks" => "Ceci est un paragraphe normal :\n\tCeci est un bloc de code.",
  "Emphasis" => array(
    "*asterisques simples*",
    "_traits soulignes simples_",
    "**asterisques double**",
    "__traits soulignes doubles__"
  ),
  "Lines" => array(
    "* * *",
    "***",
    "- - -",
    "---------------------------------------"
  ),
  "Links" => array(
    "Lien automatique http://example.com",
    "Entre crochets [http://example.com]",
    "Entre accolades {http://example.com}",
    "Entre parentheses (http://example.com)"
  ),
  "Pictures" => "![Texte alternatif](images/icons/edit.png)",
  "Code"     => "Utilisez la fonction `printf()` pour afficher."
);

$smarty = new CSmartyDP();
$smarty->assign("styles", $styles);
$smarty->display("vw_markdown_help.tpl");