{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="6" class="title">{{tr}}CLieuConsult-list{{/tr}}</th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th class="narrow">{{mb_label object=$lieu field=label}}</th>
    <th class="narrow">{{mb_label object=$lieu field=adresse}}</th>
    <th class="narrow">{{mb_label object=$lieu field=cp}}</th>
    <th class="narrow">{{mb_label object=$lieu field=ville}}</th>
  </tr>
  {{foreach from=$lieux item=_lieu}}
    <tr {{if !$_lieu->active}}class="hatching opacity-50"{{/if}}>
      <td class="text">
        {{if !$can->admin && count($_lieu->_ref_lieux_consult_prat) > 1}}
          {{tr}}mod-dPCabinet-several-mediuser-associated-to-a-place-please-contact-admin-to-edit{{/tr}}
        {{else}}
          <button type="button" class="edit notext" onclick="Lieu.editLieux({{$_lieu->_id}})">{{tr}}CLieuConsult-action-edit{{/tr}}</button>
          <button type="button" class="add compact" onclick="Lieu.agendaLieux({{$_lieu->_id}})">{{tr}}CAgendaPraticien-action-manage{{/tr}}</button>
        {{/if}}
      </td>
      <td>
        {{mb_value object=$_lieu field=label}}
      </td>
      <td>
        {{mb_value object=$_lieu field=adresse}}
      </td>
      <td>
        {{mb_value object=$_lieu field=cp}}
      </td>
      <td>
        {{mb_value object=$_lieu field=ville}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="6">
        {{tr}}CLieuConsult.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>