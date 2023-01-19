{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=line value=''}}

<script>
Main.add(function() {
  $('list-classes-integrity').fixedTableHeaders();
  if (ReferencesCheck.auto_refresh) {
    ReferencesCheck.refresh_timeout = setTimeout('ReferencesCheck.reloadTable();', 60000);
  }
});
</script>

<form name="order-way-integrity" method="get" onsubmit="return false;">
  <fieldset style="width: 33%;">
      <legend>{{tr}}dPdeveloppement-ref_check-Table sort by{{/tr}}</legend>
      <label>
        <input id="order-name" type="radio" name="order" value="name" {{if $order == 'name'}}checked{{/if}} onchange="ReferencesCheck.reloadTable(null, this.value)"/>
        <strong>{{tr}}CRefCheckTable-class-court{{/tr}}</strong>
      </label>
      <br/>

      <label>
        <input id="order-state" type="radio" name="order" value="state" {{if $order == 'state'}}checked{{/if}} onchange="ReferencesCheck.reloadTable(null, this.value)"/>
        <strong>{{tr}}CRefCheckTable-State{{/tr}}</strong>
      </label>
      <br/>

      <label>
        <input id="order-errors" type="radio" name="order" value="errors" {{if $order == 'errors'}}checked{{/if}} onchange="ReferencesCheck.reloadTable(null, this.value)"/>
        <strong>{{tr}}CRefCheckTable-_error_count-desc{{/tr}}</strong>
      </label>
  </fieldset>
</form>

<div id="list-classes-integrity">
  <table class="main tbl">
    <tbody>
    {{foreach from=$classes item=_ref_check_table}}
      {{if !$line && $_ref_check_table->_state < 100}}
        {{assign var=line value=$_ref_check_table->class}}
      {{/if}}

      <tr class="line-filter" id="line-{{$_ref_check_table->class}}"
          {{if $_ref_check_table->_error_count > 0}}
            onclick="ReferencesCheck.displayClass('{{$_ref_check_table->class}}')" style="cursor: pointer;"
          {{/if}}
      >

        <td class="display-class-name" colspan="2">
          <strong>{{mb_value object=$_ref_check_table field=class}}</strong> - {{tr}}{{$_ref_check_table->class|getShortName}}{{/tr}}
        </td>

        <td>
          <div class="progressBar" style="width: 99%;"
               title="{{$_ref_check_table->_total_lines|number_format:0:',':' '}} / {{$_ref_check_table->_max_lines|number_format:0:',':' '}}">

            {{assign var=background_color value=$_ref_check_table->_state|threshold:0:full:49:booked:99:empty:100:normal}}

            <div class="bar {{$background_color}}" style="width: {{$_ref_check_table->_state}}%"></div>
            <div class="text">{{$_ref_check_table->_state|number_format:2:',':' '}} %</div>
          </div>
        </td>

        <td>{{mb_value object=$_ref_check_table field=start_date}}</td>
        <td>{{$_ref_check_table->_duration}}</td>
        <td style="text-align: right;">{{$_ref_check_table->max_id|number_format:0:',':' '}}</td>
        <td style="text-align: right;">{{$_ref_check_table->count_rows|number_format:0:',':' '}}</td>
        <td style="text-align: right;">
          {{if $_ref_check_table->max_id == 0}}
            0
          {{else}}
            {{math assign=density equation="x/y" x=$_ref_check_table->count_rows y=$_ref_check_table->max_id}}
            {{$density|number_format:2:',':''}}
          {{/if}}
        </td>
        <td style="text-align: right;" {{if $_ref_check_table->_error_count > 0}}class="warning"{{/if}}>
          {{$_ref_check_table->_error_count|number_format:0:',':' '}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="8" class="empty">{{tr}}CRefCheckTable.none{{/tr}}</td>
      </tr>
    {{/foreach}}
    </tbody>

    <thead>
      <tr>
        <th class="narrow" rowspan="2">
          <button type="button" class="pause notext" onclick="ReferencesCheck.changeAutoRefresh(this);">
            {{tr}}CRefCheckTable-action-Stop auto refresh{{/tr}}
          </button>
          <button type="button" class="change notext" onclick="ReferencesCheck.reloadTable();">
            {{tr}}Refresh{{/tr}}
          </button>
        </th>
        <th class="narrow" rowspan="2">
          {{tr}}CRefCheckTable-class{{/tr}}
          <input type="text" class="search" onkeyup="ReferencesCheck.filterTable(this, 'line-filter');"/>
        </th>
        <th>{{tr}}CRefCheckTable-State{{/tr}}</th>
        <th class="narrow">{{mb_title class=CRefCheckTable field=start_date}}</th>
        <th class="narrow">{{tr}}CRefCheckTable-_duration{{/tr}}</th>
        <th class="narrow text" rowspan="2">{{mb_title class=CRefCheckTable field=max_id}}</th>
        <th class="narrow text" rowspan="2">{{mb_title class=CRefCheckTable field=count_rows}}</th>
        <th class="narrow text" rowspan="2">{{tr}}CRefCheckTable-Density{{/tr}}</th>
        <th class="narrow text" rowspan="2">{{tr}}CRefCheckTable-_error_count{{/tr}}</th>
      </tr>

      <tr>
        <th>
          <div class="progressBar" style="width: 99%;" title="{{$parsed_lines|number_format:0:',':' '}} / {{$max_lines|number_format:0:',':' '}}">

            {{assign var=background_header value=$progression|threshold:0:full:50:booked:99:empty:100:normal}}

            <div class="bar {{$background_header}}" style="width: {{$progression}}%"></div>
            <div class="text">{{tr}}Total{{/tr}} : {{$progression|number_format:4:',':' '}} %</div>
          </div>
        </th>
        <th>{{if $start}}{{$start|date_format:$conf.datetime}}{{/if}}</th>
        <th>{{if $end}}{{$end}}{{/if}}</th>
      </tr>
    </thead>
  </table>
</div>

{{if $line}}
  <script>
    Main.add(function() {
      $('line-{{$line}}').scrollIntoView();
      $('list-classes-integrity').scrollTop -= 70;
    });
  </script>
{{/if}}
