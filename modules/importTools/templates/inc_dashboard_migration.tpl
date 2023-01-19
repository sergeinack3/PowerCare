{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  showStatusDetails = function(type, path) {
    var url = new Url('importTools', 'vw_status_file');
    url.addParam('type', type);
    url.addParam('path', path);
    url.requestModal('50%', '50%');
  }
</script>

<tr>
  <th class="section">[{{$tag}}] : {{$root_dir}}/{{$tag}}</th>
  <th class="section">{{$infos.total.date_min|date_format:$conf.date}}</th>
  <th class="section">{{$infos.total.date_max|date_format:$conf.date}}</th>
  <th class="section">{{$infos.total.total_patients|number_format:0:',':' '}}</th>
  <th class="section" title="{{$infos.total.export_last_update}}">
    {{$infos.total.export_last_update|date_format:$conf.datetime}}
  </th>
  <th class="section">{{$infos.total.export_patients|number_format:0:',':' '}}</th>
  {{if $infos.total.export_patients > 0}}
    {{math assign=pct equation="(x/y)*100" x=$infos.total.export_patients y=$infos.total.total_patients}}
  {{else}}
    {{assign var=pct value=0}}
  {{/if}}
  <th class="section">
    {{$pct|number_format:2:',':''}}%
  </th>
  <th class="section">{{'Ox\Core\CMbDT::getHumanReadableDuration'|static_call:$infos.total.export_duration}}</th>
  <th class="section">{{$infos.total.total_size|decasi}}</th>
  <th class="section" title="{{$infos.total.import_last_update}}">
    {{$infos.total.import_last_update|date_format:$conf.datetime}}
  </th>
  <th class="section">{{$infos.total.import_patients|number_format:0:',':' '}}</th>
  {{if $infos.total.total_patients > 0}}
    {{math assign=pct equation="(x/y)*100" x=$infos.total.import_patients y=$infos.total.total_patients}}
  {{else}}
    {{assign var=pct value=0}}
  {{/if}}
  <th class="section">
    {{$pct|number_format:2:',':''}}%
  </th>
  <th class="section">{{'Ox\Core\CMbDT::getHumanReadableDuration'|static_call:$infos.total.import_duration}}</th>
</tr>

{{foreach from=$infos.infos key=_dates item=_infos}}
  <tr>
    <td>
      {{$root_dir}}/{{$tag}}/{{$_dates}}
    </td>
    <td align="center">
      {{$_infos.date_min|date_format:$conf.date}}
    </td>
    <td align="center">
      {{$_infos.date_max|date_format:$conf.date}}
    </td>
    <td align="right">
      {{if "patient_total"|array_key_exists:$_infos.export}}
        {{$_infos.export.patient_total|number_format:0:',':' '}}
      {{/if}}
    </td>

    {{if "patient_current"|array_key_exists:$_infos.export}}
      {{assign var=current_patient value=$_infos.export.patient_current}}
    {{else}}
      {{assign var=current_patient value=0}}
    {{/if}}

    {{if "patient_total"|array_key_exists:$_infos.export && $_infos.export.patient_total < $current_patient}}
      {{assign var=current_patient value=$_infos.export.patient_total}}
    {{/if}}
    {{if "patient_total"|array_key_exists:$_infos.export && $_infos.export.patient_total > 0}}
      {{math assign=pct equation="(x/y)*100" x=$current_patient y=$_infos.export.patient_total}}
    {{else}}
      {{assign var=pct value=0}}
    {{/if}}


    <td title="{{$_infos.export.last_update}}" align="center" {{if $pct == 100}}class="ok"{{/if}}>
      {{if "patient_total"|array_key_exists:$_infos.export}}
      {{$_infos.export.last_update|date_format:$conf.datetime}}
        <button class="notext compact search" style="float: right"
                onclick="showStatusDetails('export', '{{$root_dir}}/{{$tag}}/{{$_dates}}')">
          {{tr}}importTools-migration-show status{{/tr}}
        </button>
      {{/if}}
    </td>
    <td align="right" {{if $pct == 100}}class="ok"{{/if}}>
      {{$current_patient|number_format:0:',':' '}}
    </td>

    <td align="right" {{if $pct == 100}}class="ok"{{/if}}>
      {{$pct|number_format:2:',':''}}%
    </td>
    <td align="right" {{if $pct == 100}}class="ok"{{/if}}>
      {{if "duration"|array_key_exists:$_infos.export}}
        {{math assign=real_duration equation="x/60" x=$_infos.export.duration}}
        {{assign var=real_duration value=$real_duration|number_format:2:',':''}}
        {{'Ox\Core\CMbDT::getHumanReadableDuration'|static_call:$real_duration}}
      {{/if}}
    </td>

    <td align="right">
      {{if "size"|array_key_exists:$_infos.export}}
        {{$_infos.export.size|decasi}}
      {{/if}}
    </td>

    {{if $_infos.import}}
      {{assign var=current_patient value=$_infos.import.patient_current}}
      {{if $_infos.import.patient_total < $current_patient}}
        {{assign var=current_patient value=$_infos.import.patient_total}}
      {{/if}}

      {{if "patient_total"|array_key_exists:$_infos.import && $_infos.import.patient_total > 0}}
        {{math assign=pct equation="(x/y)*100" x=$current_patient y=$_infos.import.patient_total}}
      {{else}}
        {{assign var=pct value=0}}
      {{/if}}


      <td title="{{$_infos.import.last_update}}" align="center" {{if $pct == 100}}class="ok"{{/if}}>
        {{$_infos.import.last_update|date_format:$conf.datetime}}
        <button class="notext compact search" style="float: right"
                onclick="showStatusDetails('import', '{{$root_dir}}/{{$tag}}/{{$_dates}}')">
          {{tr}}importTools-migration-show status{{/tr}}
        </button>
      </td>
      <td align="right" {{if $pct == 100}}class="ok"{{/if}}>
        {{$current_patient|number_format:0:',':' '}}
      </td>

      <td align="right" {{if $pct == 100}}class="ok"{{/if}}>
        {{$pct|number_format:2:',':''}}%
      </td>
      <td align="right" {{if $pct == 100}}class="ok"{{/if}}>
        {{if "duration"|array_key_exists:$_infos.import}}
          {{math assign=real_duration equation="x/60" x=$_infos.import.duration}}
          {{assign var=real_duration value=$real_duration|number_format:2:',':''}}
          {{'Ox\Core\CMbDT::getHumanReadableDuration'|static_call:$real_duration}}
        {{/if}}

      </td>

      {{else}}
      <td class="empty" colspan="4">
        {{tr}}importTools-migration-import.none{{/tr}}
      </td>
    {{/if}}
  </tr>
{{/foreach}}