{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  <span class="rootname">{{$treecda.contain.name}}</span>
{{foreach from=$treecda.contain.child item=_node}}
  <li>
    {{mb_include template=inc_node_treecda node=$_node}}
  </li>
{{/foreach}}
</ul>