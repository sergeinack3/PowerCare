{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ol>
{{foreach from=$problems item=_problem}}
  <li>
    <a href="?m=compteRendu&a=edit&compte_rendu_id={{$_problem->_id}}">
      {{$_problem}}
    </a>
  </li>
{{/foreach}}
</ol>
