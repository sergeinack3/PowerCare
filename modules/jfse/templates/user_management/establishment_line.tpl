{{*
 * @package Mediboard\se
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
    <td class="narrow">
        <button type="button" class="edit notext" onclick="UserManagement.editEstablishment('{{$establishment->id}}');">{{tr}}Edit{{/tr}}</button>
    </td>
    <td>{{mb_value object=$establishment field=name}}</td>
    <td>{{mb_value object=$establishment field=category}}</td>
    <td>{{mb_value object=$establishment field=health_center_number}}</td>
    <td{{if !$establishment->_object || !$establishment->_object->_id}} class="empty"{{/if}}>
        {{if $establishment->_object && $establishment->_object->_id && $establishment->_object->_class === 'CFunctions'}}
          {{mb_include module=mediusers template=inc_vw_function function=$establishment->_object}}
        {{elseif $establishment->_object && $establishment->_object->_id && $establishment->_object->_class === 'CGroups'}}
            {{mb_include module=etablissement template=inc_vw_group group=$establishment->_object}}
        {{else}}
            {{tr}}CJfseEstablishmentView-msg-not_linked{{/tr}}
        {{/if}}
    </td>
</tr>
