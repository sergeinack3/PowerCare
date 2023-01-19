{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="astreintes" script="plage"}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl tooltip">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}
      {{$object}}
    </th>

  </tr>
  <tr>
    <th>{{tr}}User{{/tr}}</th>
    <td>{{mb_value object=$object field=user_id}}</td>
  </tr>

  <tr>
    <th>{{tr}}CPlageAstreinte-start{{/tr}}</th>
    <td>
      {{$object->start|date_format:$conf.longdate}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CPlageAstreinte-end{{/tr}}</th>
    <td>
      {{$object->end|date_format:$conf.longdate}}
    </td>
  </tr>

  <tr>
    <th>{{tr}}CPlageAstreinte-_duree{{/tr}}</th>
    <td>{{mb_include module="system" template="inc_vw_duration" duration=$object->_duree}}</td>
  </tr>

  {{if $object->_ref_user}}
    <tr>
      <th><img src="images/icons/phone.png" alt="{{tr}}CPlageAstreinte.PhoneNumber{{/tr}}"/></th>
      <td>
        {{mb_value object=$object field=phone_astreinte}}
      </td>
    </tr>
    <tr>
      <th>{{tr}}CUser-user_astreinte_autre-court{{/tr}}</th>
      <td>
        {{mb_value object=$object->_ref_user field=_user_astreinte_autre}}
      </td>
    </tr>
    {{if $object->_ref_user->_user_phone}}
      <tr>
        <th>{{tr}}CUser-user_phone{{/tr}}</th>
        <td>
          {{mb_value object=$object->_ref_user field=_user_phone}}
        </td>
      </tr>
    {{/if}}
  {{/if}}
  {{if $object->_can->edit}}
    <tr style="text-align: center;">
      <td class="button" colspan="2">
        <button class="edit" onclick="PlageAstreinte.modal({{$object->_id}}, {{$object->user_id}})">{{tr}}Edit{{/tr}}</button>
      </td>
    </tr>
  {{/if}}
</table>

