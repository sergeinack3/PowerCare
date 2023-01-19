{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_id && !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>

  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="title text" colspan="2">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}

      {{$object}}
    </th>
  </tr>

  <tr>
    <th>{{mb_label object=$object field=user_first_name}}</th>
    <td>{{mb_value object=$object field=user_first_name}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$object field=user_last_name}}</th>
    <td>{{mb_value object=$object field=user_last_name}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$object field=user_sexe}}</th>
    <td>{{mb_value object=$object field=user_sexe}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$object field=user_email}}</th>
    <td>{{mb_value object=$object field=user_email}}</td>
  </tr>
</table>
