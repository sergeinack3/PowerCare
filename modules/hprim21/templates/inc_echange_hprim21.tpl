{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td class="narrow">
    <form name="delEchange" action="" method="post">
      <input type="hidden" name="m" value="hprim21" />
      <input type="hidden" name="dosql" value="do_echangehprim21_aed" />
      <input type="hidden" name="del" value="1" />
      {{mb_key object=$object}}
      <button class="cancel notext" onclick="confirmDeletion(this.form, {
          typeName:'l\'échange',
          objName:'{{$object|smarty:nodefaults|JSAttribute}}'
        })">
      </button>
    </form>
  </td>
  <td class="narrow">
    <a href="?m=hprim21&tab=vw_idx_echange_hprim21&echange_hprim21_id={{$object->_id}}" class="button search">
     {{$object->echange_hprim21_id|str_pad:6:'0':$smarty.const.STR_PAD_LEFT}}
    </a>
  </td>
  <td>
    {{$object->object_class}}
  </td>
  <td>
    {{if $object->object_id}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$object->object_class}}-{{$object->object_id}}');">
        {{$object->object_id|str_pad:6:'0':$smarty.const.STR_PAD_LEFT}}
      </span>
    {{/if}}
  </td>
  <td>
    {{if $object->id_permanent}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$object->object_class}}-{{$object->object_id}}', 'identifiers');">
        {{$object->id_permanent|str_pad:6:'0':$smarty.const.STR_PAD_LEFT}}
      </span>
    {{/if}}
  </td>
  <td>
    <label title='{{mb_value object=$object field="date_production"}}'>
      {{mb_value object=$object field="date_production" format=relative}}
    </label>
  </td>
  <td>
    {{$object->version}}
  </td>
  <td>
    {{$object->type_message}}
  </td>
  {{assign var=emetteur value=$object->_ref_sender}}
  <td class="{{if $object->sender_id == '0'}}error{{/if}}">
     {{if $object->_self_sender}}
     <label title='[SELF]' style="font-weight:bold">
       [SELF]
     </label>
     {{else}}
       {{mb_value object=$emetteur field="nom"}}
     {{/if}}
  </td>
  {{assign var=destinataire value=$object->_ref_receiver}}
  <td>
    {{if $object->_self_receiver}}
     <label title='[SELF]' style="font-weight:bold">
       [SELF]
     </label>
     {{else}}
       {{mb_value object=$destinataire field="nom"}}
     {{/if}}
  </td>
  <td class="{{if $object->send_datetime}}ok{{else}}warning{{/if}}">
    <span>
      <label title='{{mb_value object=$object field="send_datetime"}}'>
        {{mb_value object=$object field="send_datetime" format=relative}}
      </label>
    </span>
  </td>
  <td class="narrow">
    <button class="change" onclick="reprocessing('{{$object->_id}}', '{{$object->_class}}')" type="button">{{tr}}Reprocess{{/tr}}</button>
  </td>
  <td class="{{if !$object->message_valide}}error{{/if}}">
   {{mb_value object=$object field="message_valide"}}
  </td>
  <td class="narrow">
    <a target="blank" href="?m=hprim21&raw=download_echange&echange_hprim21_id={{$object->_id}}" class="button modify notext"></a>
  </td>
</tr>