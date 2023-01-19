{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPfiles script=files ajax=true}}
{{mb_script module=dPfiles script=file ajax=true}}

{{if $files|@count == 0}}
  <div class="small-error">
    {{tr}}No-file{{/tr}}
  </div>
  {{mb_return}}
{{else}}
  {{foreach from=$files item=_file}}
    <div id="list_{{$_file->_class}}{{$_file->_id}}">
      <table class="form">
        <tr id="tr_{{$_file->_guid}}">
          <td id="td_{{$_file->_guid}}">

            {{assign var=object_class  value=$_file->_ref_object->_class}}
            {{assign var=object_id     value=$_file->_ref_object->_id}}
            {{assign var=object        value=$_file->_ref_object}}
            {{assign var=name_readonly value=0}}

            {{mb_include module=dPfiles template="inc_widget_line_file"}}
        </tr>
      </table>
    </div>
  {{/foreach}}
{{/if}}
