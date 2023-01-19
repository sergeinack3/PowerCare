{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$consult_anesth->_ref_techniques item=curr_tech}}
  <li>
    <form name="delTechFrm-{{$curr_tech->_id}}" action="?m=dPcabinet" method="post" onsubmit="return submitTech(this)">
	    <input type="hidden" name="del" value="1" />
	    {{mb_class object=$curr_tech}}
	    {{mb_key object=$curr_tech}}
      <button class="trash notext not-printable" type="submit">{{tr}}Delete{{/tr}}</button>
	    {{$curr_tech->technique}}
    </form>
  </li>
  {{foreachelse}}
  <li class="empty">Pas de technique complémentaire</li>
  {{/foreach}}
</ul>