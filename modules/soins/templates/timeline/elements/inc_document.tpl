{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  {{foreach from=$list item=item name="documents"}}
    <tr>
      <td>
        <span class="type_item circled">
          {{if $item->_class == "CCompteRendu"}}
            {{tr}}CTimelineCabinet-Document{{/tr}}
            {{mb_value object=$item field=nom}}
          {{elseif $item->_class == 'CFile'}}
            {{tr}}CFile{{/tr}}
            {{mb_value object=$item field=file_name}}
          {{else}}
            {{tr}}CExObject{{/tr}}
            {{mb_value object=$item->_ref_ex_object->_ref_ex_class field=name}}
          {{/if}}
        </span>
      </td>
    </tr>

    {{if $item->_class == "CCompteRendu"}}
      <tr>
        <td class="halfPane">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
            &mdash;
            {{mb_value object=$item field=creation_date}}
          </span>
          <br />
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_author}}
          <br />
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_ref_object->_guid}}');">
            {{tr}}common-Context{{/tr}} : {{$item->_ref_object}}
          </span>
        </td>
        <td>
          <a href="#1" onclick="new Url().ViewFilePopup('{{$item->object_class}}', '{{$item->object_id}}', 'CCompteRendu', '{{$item->_id}}')">
            {{thumbnail document=$item profile=small}}
          </a>
        </td>
      </tr>
    {{elseif $item->_class == 'CFile'}}
      <tr>
        <td class="halfPane">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
            &mdash;
            {{mb_value object=$item field=file_date}}
          </span>
          <br>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_author}}
          <br>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_ref_object->_guid}}');">
            {{tr}}common-Context{{/tr}} : {{$item->_ref_object}}
          </span>
        </td>
        <td>
          <a href="#1" onclick="new Url().ViewFilePopup('{{$item->object_class}}', '{{$item->object_id}}', 'CFile', '{{$item->_id}}')">
            {{thumbnail document=$item profile=small}}
          </a>
        </td>
      </tr>
    {{else}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
            <br>
            {{mb_value object=$item field=datetime_create}}
            <br>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_ex_object->_ref_owner}}
            <br>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_ref_object->_guid}}');">
            <strong>{{tr}}mod-soins-Contexte{{/tr}}: </strong> {{$item->_ref_object}}
        </td>
        <td>
          <a href="#1" onclick="ExObject.display('{{$item->_ref_ex_object->_id}}', '{{$item->_ref_ex_object->_ex_class_id}}', '{{$item->_ref_ex_object->_guid}}')">
            {{mb_value object=$item->_ref_ex_object->_ref_ex_class field=name}}
          </a>
        </td>
      </tr>
    {{/if}}
    {{if !$smarty.foreach.documents.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
