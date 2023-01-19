{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs-migration-tags", true);
  });
</script>

<h2>
  {{tr}}importTools-migration-dashboard{{/tr}}
</h2>

<h3>
  {{tr}}importTools-migration-Root directory{{/tr}} : {{$paths.root}}
</h3>

{{if $paths.sub_dirs}}
  <table class="main tbl">
    <tr>
      <th rowspan="2">{{tr}}importTools-migration-Directory{{/tr}}</th>
      <th rowspan="2">{{tr}}importTools-migration-Date min{{/tr}}</th>
      <th rowspan="2">{{tr}}importTools-migration-Date max{{/tr}}</th>
      <th rowspan="2">{{tr}}importTools-migration-Total pat{{/tr}}</th>
      <th colspan="4">{{tr}}importTools-migration-export{{/tr}}</th>
      <th rowspan="2">{{tr}}Size{{/tr}}</th>
      <th colspan="4">{{tr}}importTools-migration-import{{/tr}}</th>
    </tr>

    <tr>
      <th>{{tr}}importTools-migration-Last execution{{/tr}}</th>
      <th>{{tr}}importTools-migration-Patient count export{{/tr}}</th>
      <th>%</th>
      <th>{{tr}}Duration{{/tr}}</th>
      <th>{{tr}}importTools-migration-Last execution{{/tr}}</th>
      <th>{{tr}}importTools-migration-Patient count import{{/tr}}</th>
      <th>%</th>
      <th>{{tr}}Duration{{/tr}}</th>
    </tr>

    {{foreach from=$paths.sub_dirs key=_tag item=_datas}}
      {{mb_include module=importTools template=inc_dashboard_migration infos=$_datas root_dir=$paths.root tag=$_tag}}
    {{/foreach}}

    <tr>
      <th class="section">{{tr}}Total{{/tr}}</th>
      <th class="section">{{$paths.total.date_min|date_format:$conf.date}}</th>
      <th class="section">{{$paths.total.date_max|date_format:$conf.date}}</th>
      <th class="section">{{$paths.total.total_patients|number_format:0:',':' '}}</th>
      <th class="section" title="{{$paths.total.export_last_update}}">
        {{$paths.total.export_last_update|date_format:$conf.datetime}}
      </th>
      <th class="section">{{$paths.total.export_patients|number_format:0:',':' '}}</th>

      {{if "total_patients"|array_key_exists:$paths.total && $paths.total.total_patients > 0}}
        {{math assign=pct equation="(x/y)*100" x=$paths.total.export_patients y=$paths.total.total_patients}}
      {{else}}
        {{assign var=pct value=0}}
      {{/if}}

      <th class="section">
        {{$pct|number_format:2:',':''}}%
      </th>
      <th class="section">{{'Ox\Core\CMbDT::getHumanReadableDuration'|static_call:$paths.total.export_duration}}</th>
      <th class="section">{{$paths.total.total_size|decasi}}</th>
      <th class="section" title="{{$paths.total.import_last_update}}">
        {{$paths.total.import_last_update|date_format:$conf.datetime}}
      </th>
      <th class="section">{{$paths.total.import_patients|number_format:0:',':' '}}</th>

      {{if "total_patients"|array_key_exists:$paths.total && $paths.total.total_patients > 0}}
        {{math assign=pct equation="(x/y)*100" x=$paths.total.import_patients y=$paths.total.total_patients}}
      {{else}}
        {{assign var=pct value=0}}
      {{/if}}

      <th class="section">
        {{$pct|number_format:2:',':''}}%
      </th>
      <th class="section">{{'Ox\Core\CMbDT::getHumanReadableDuration'|static_call:$paths.total.import_duration}}</th>
    </tr>
  </table>

{{else}}
  <div class="small-warning">
    {{tr}}importTools-migration.none{{/tr}}
  </div>
{{/if}}