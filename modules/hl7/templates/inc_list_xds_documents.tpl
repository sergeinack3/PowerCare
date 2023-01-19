{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="xds" script="xds_document" ajax=true}}

<table class="main tbl">
  <tr>
    <th class="category narrow"></th>
    <th class="category"></th>
    <th class="category">{{mb_title class="CXDSFile" field="type_document"}}</th>
    <th class="category">{{mb_title class="CXDSFile" field="title"}}</th>
    <th class="category">{{mb_title class="CXDSFile" field="event_date_start"}}</th>
    <th class="category">{{mb_title class="CXDSFile" field="event_date_end"}}</th>
    <th class="category">{{mb_title class="CXDSFile" field="author"}}</th>
    <th class="category">{{mb_title class="CXDSFile" field="profession"}}</th>
  </tr>
  {{foreach from=$list_documents item=_document}}
    <tr>
      <td class="narrow">
        <button type="button" class="search notext"
                onclick="Cxds.displayDocument('{{$_document->repository_id}}', '{{$_document->oid}}', '{{$patient->_id}}');">
          {{tr}}Display{{/tr}}
        </button>
      </td>
      <td></td>
      <td>{{mb_value object=$_document field="type_document"}}</td>
      <td>{{mb_value object=$_document field="title"}}</td>
      <td>
        {{if $_document->event_date_end}}
        <label title="{{mb_value object=$_document field="event_date_start"}}">
          {{mb_value object=$_document field="event_date_start"}}
        </label>
        {{/if}}
      </td>
      <td {{if $_document->_old}}class="dmp-old"{{/if}}>
        {{if $_document->event_date_end}}
        <label title="{{mb_value object=$_document field="event_date_end"}}">
          {{mb_value object=$_document field="event_date_end"}}
        </label>
        {{/if}}
      </td>
      <td>{{mb_value object=$_document field="author"}}</td>
      <td>{{mb_value object=$_document field="profession"}}</td>
    </tr>
  {{foreachelse}}
    <tr><td class="empty" colspan="7">{{tr}}CDMPFile.none{{/tr}}</td></tr>
  {{/foreach}}
</table>

