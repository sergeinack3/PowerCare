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

{{mb_script module=admin script=rgpd ajax=true}}

<table class="tbl">
  <tr>
    <th class="title text">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}

      {{$object}}
    </th>
  </tr>

  <tr>
    <td>
      {{foreach from=$object->_specs key=prop item=spec}}
        {{mb_include module=system template=inc_field_view}}
      {{/foreach}}

      {{assign var=file value=$object->loadProofFile()}}
      {{if $file && $file->_id}}
        <strong>{{tr}}CRGPDConsent-Proof file{{/tr}}</strong> :
        <span onmouseover="ObjectTooltip.createEx(this, '{{$file->_guid}}');">
          {{$file}}
        </span>
      {{/if}}
    </td>
  </tr>

  <tr>
    <td style="text-align: center;">
      <button type="button" class="fa fa-upload" onclick="RGPD.uploadProofFile('{{$object->_id}}');">
        {{tr}}CRGPDConsent-action-Manage{{/tr}}
      </button>
    </td>
  </tr>
</table>