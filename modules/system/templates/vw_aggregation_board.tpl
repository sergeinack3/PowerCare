{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .aggregate_10 {
    background-color: rgba(70, 130, 180, 0.3) !important;
  }

  .aggregate_60 {
    background-color: rgba(34, 139, 34, 0.3) !important;
  }

  .aggregate_1440 {
    background-color: rgba(178, 34, 34, 0.3) !important;
  }
</style>

<script>
  aggregate = function (dry_run, object_class) {
    var url = new Url('system', 'aggregate_accesslogs');
    url.addParam("object_class", object_class);

    if (dry_run) {
      url.addParam('dry_run', 1);
      url.requestUpdate("aggregate_" + object_class);
    }
    else {
      url.requestUpdate("aggregate_" + object_class);
    }
  };
</script>

<div class="small-info">
  {{tr}}CAccessLog-msg-Logs that will be aggregated.{{/tr}}
</div>

<table class="main tbl">
  <tr>
    <th>{{tr}}Table{{/tr}}</th>
    <th>{{tr}}Aggregation{{/tr}}</th>
    <th>{{tr}}Entries{{/tr}}</th>
    <th>Date min.</th>
    <th>Date max.</th>

    <th>{{tr}}Data{{/tr}}</th>
    <th>{{tr}}Indexes{{/tr}}</th>
    <th>{{tr}}Free{{/tr}}</th>
    <th>{{tr}}Total{{/tr}}</th>

    <th>{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>

  {{foreach from=$stats key=_table item=_stats}}
    {{assign var=rowspan value=$_stats.data|@count}}

    {{foreach name=_loop from=$_stats.data item=_aggregate}}
      <tr>
        {{if $smarty.foreach._loop.first}}
          <th class="section" rowspan="{{$rowspan}}">{{$_table}}</th>
        {{/if}}

        <td class="aggregate_{{$_aggregate.aggregate}}" style="text-align: right;">{{$_aggregate.aggregate}}</td>
        <td class="aggregate_{{$_aggregate.aggregate}}" style="text-align: right;">{{$_aggregate.records|integer}}</td>
        <td class="aggregate_{{$_aggregate.aggregate}}" style="text-align: right;">{{$_aggregate.date_min|date_format:$conf.date}}</td>
        <td class="aggregate_{{$_aggregate.aggregate}}" style="text-align: right;">{{$_aggregate.date_max|date_format:$conf.date}}</td>

        {{if $smarty.foreach._loop.first}}
          <td rowspan="{{$rowspan}}" style="text-align: right;">{{$_stats.meta.data_length|decabinary}}</td>
          <td rowspan="{{$rowspan}}" style="text-align: right;">{{$_stats.meta.index_length|decabinary}}</td>
          <td rowspan="{{$rowspan}}" style="text-align: right;">{{$_stats.meta.data_free|decabinary}}</td>
          <td rowspan="{{$rowspan}}" style="text-align: right;">{{$_stats.meta.total|decabinary}}</td>

          <td rowspan="{{$rowspan}}" style="text-align: center;">
            <button class="search" type="button" onclick="aggregate(true, '{{$_stats.class}}');">{{tr}}DryRun{{/tr}}</button>
            <button class="search" type="button" onclick="aggregate(false, '{{$_stats.class}}')">{{tr}}Aggregate{{/tr}}</button>
          </td>

          <td rowspan="{{$rowspan}}" id="aggregate_{{$_stats.class}}"></td>
        {{/if}}
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>