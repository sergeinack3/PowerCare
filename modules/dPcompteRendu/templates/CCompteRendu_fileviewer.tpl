{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{mb_default var=display value="icon"}}
{{mb_default var=copy_mode value=0}}

{{assign var="src" value="?m=files&raw=thumbnail&document_guid=`$doc->_class`-`$doc->_id`&profile=medium"}}

{{if $display == "icon"}}
  <table class="layout table_icon_fileview" onmouseover="ObjectTooltip.createEx(this, '{{$doc->_guid}}')">
    <tr>
      <td style="text-align: center; height: 120px; vertical-align: middle; position: relative;">
        {{mb_include module=files template="inc_file_synchro" docItem=$doc}}

        <div class="icon_fileview"
             onclick="DocumentItem.toggleSelectFile(this);"
             style="position: relative;"
             data-docitem-guid="{{$doc->_guid}}"
             ondblclick="
               {{if $copy_mode}}
               copyDoc('{{$doc->_id}}');
               {{else}}
               popFile('{{$doc->object_class}}', '{{$doc->object_id}}', '{{$doc->_class}}', '{{$doc->_id}}', '0');
               {{/if}}
               "
             ontouchend="
               {{if $copy_mode}}
               copyDoc('{{$doc->_id}}');
               {{else}}
               popFile('{{$doc->object_class}}', '{{$doc->object_id}}', '{{$doc->_class}}', '{{$doc->_id}}', '0');
               {{/if}}
               ">
          <img src="{{$src}}" style="background: white; max-width: 64px; max-height: 92px;" />
        </div>
      </td>
    </tr>
    <tr>
      <td class="text item_name" style="text-align: center; vertical-align: top;">
        {{if $doc->file_category_id}}<span class="compact circled">{{$doc->_ref_category}}</span>{{/if}} {{$doc->_icon_name}}
      </td>
    </tr>
  </table>

  {{mb_return}}
{{/if}}

<tr {{if $doc->annule}}class="doc_canceled hatching"{{/if}}>
  <td class="narrow">
    <span style="font-family: FontAwesome; font-size: 11pt;">&#xf0f6;</span>
  </td>
  <td class="item_name">
    <span onclick="popFile('{{$doc->object_class}}', '{{$doc->object_id}}', '{{$doc->_class}}', '{{$doc->_id}}', '0')"
          onmouseover="ObjectTooltip.createEx(this, '{{$doc->_guid}}')"
          style="cursor: pointer;">
      {{$doc}}
    </span>
  </td>
  <td>
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$doc->_ref_author}}
  </td>
  <td style="width: 25%">
    {{if $doc->file_category_id}}<span class="compact circled">{{$doc->_ref_category}}</span>{{/if}}
  </td>
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$doc->_ref_object->_guid}}')">
      {{$doc->_ref_object}}
    </span>
  </td>
  <td class="narrow">
    {{mb_value object=$doc->_ref_content field=last_modified}}
  </td>
</tr>
