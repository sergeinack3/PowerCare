{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=timed_data value=false}}

{{if $timed_data}}
  <table class="tbl">
    {{foreach from=$data key=_time item=_data}}
      {{if is_array($_data|smarty:nodefaults)}}
        <tr>
          <th>{{$times.$_time|date_format:$conf.datetime}}</th>
          <td {{if $_data && array_key_exists("file_id", $_data) && $_data.file_id}} style="text-align: center;" {{/if}}>
            {{if $_data && array_key_exists("file_id", $_data) && $_data.file_id}}
              <img src="{{$_data.datauri}}" height="45" /><br/>{{$_data.file->_no_extension}}
            {{else}}
              {{$_data.value}}
            {{/if}}
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{else}}
  <table class="tbl">
    <tr>
      <th></th>
      {{foreach from=$series key=_serie_id item=_serie}}
        <th>{{$_serie}}</th>
      {{/foreach}}
    </tr>

    {{foreach from=$data key=_time item=_data}}
      {{if $_data}}
        <tr>
          <th>{{$times.$_time|date_format:$conf.datetime}}</th>
          {{foreach from=$series key=_serie_id item=_serie}}
            {{if array_key_exists($_serie_id,$_data)}}
              {{assign var=_datum value=$_data.$_serie_id}}
              <td>
                {{if $_datum.label}}
                  {{$_datum.label}}
                {{else}}
                  {{$_datum.value}}
                {{/if}}

                {{$_datum.unit}}
              </td>
            {{else}}
              <td></td>
            {{/if}}
          {{/foreach}}
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}
