{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  
  <tr>
    <th class="title" colspan="2">Statistiques</th>
  </tr>
  <tr>
    <th>{{mb_title class=CPlageOp field=chir_id}}</th>
    <th>{{tr}}dPcabinet-stats-consultations_creation{{/tr}}</th>
  </tr>
  {{foreach from=$stats_creation item=_stat}}
    {{assign var=praticien_id value=$_stat.user_id}}
    <tr>
      <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$prats_creation.$praticien_id}}
      </td>
      <td>
        {{$_stat.total}}
      </td>
    </tr>
  {{foreachelse}}
    <td class="empty" colspan="2">{{tr}}CConsultation.none{{/tr}}</td>
  {{/foreach}}
</table>
