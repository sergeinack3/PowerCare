{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2">{{mb_title class=CViewSenderSource field=name}}</th>
    <th>{{mb_title class=CViewSenderSource field=libelle}}</th>
    <th>{{mb_title class=CViewSenderSource field=group_id}}</th>
    <th class="narrow" colspan="3">{{mb_title class=CViewSenderSource field=actif}}</th>
    <th>{{tr}}CViewSenderSource-back-senders_link{{/tr}}</th>
  </tr>

  {{foreach from=$senders_source item=_sender_source}}
  <tr>
    <td class="narrow">
      <button class="edit notext" style="float: right;" onclick="ViewSenderSource.edit('{{$_sender_source->_id}}');">
        {{tr}}Edit{{/tr}}
      </button> 
    </td>
    <td>{{mb_value object=$_sender_source field=name}}</td>
    <td class="text compact">{{mb_value object=$_sender_source field=libelle}}</td>
    <td class="text compact">{{mb_value object=$_sender_source field=group_id}}</td>
    <td {{if !$_sender_source->actif}}class="error"{{/if}}>{{mb_value object=$_sender_source field=actif}}</td>
    <td>
      {{if $_sender_source->_ref_source->role != $conf.instance_role}}
        <i class="fas fa-exclamation-triangle" style="color: goldenrod;"
           title="{{tr var1=$_sender_source->_ref_source->role var2=$conf.instance_role}}CViewSenderSource-msg-View sender source incompatible %s with the instance role %s{{/tr}}"></i>
      {{/if}}

      {{if $_sender_source->_ref_source->role == "prod"}}
        <strong style="color: red" title="{{tr}}CViewSenderSource_role.prod{{/tr}}">{{tr}}CViewSenderSource_role.prod-court{{/tr}}</strong>
      {{else}}
        <span style="color: green" title="{{tr}}CViewSenderSource_role.qualif{{/tr}}">{{tr}}CViewSenderSource_role.qualif-court{{/tr}}</span>
      {{/if}}
    </td>
    <td>
      {{mb_include module=system template=inc_img_status_source exchange_source=$_sender_source->_ref_source}}
    </td>
    <td>
      {{foreach from=$_sender_source->_ref_senders item=_sender}}
        <div>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_sender->_guid}}');">{{$_sender}}</span>
        </div>
      {{foreachelse}}
      <div class="empty">{{tr}}CViewSenderSource-back-senders_link.empty{{/tr}}</div>
      {{/foreach}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="65">{{tr}}CViewSenderSource.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>