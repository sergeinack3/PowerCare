{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    var form = getForm('filterCotations');
    Calendar.regField(form.begin_date);
    Calendar.regField(form.end_date);
  });
</script>

<form name="filterCotations" action="?" method="get" target="_blank">
  <input type="hidden" name="m" value="pmsi"/>
  <input type="hidden" name="a" value="ajax_cotations_stats"/>
  <input type="hidden" name="start" value="0"/>
  <input type="hidden" name="limit" value="20"/>
  <input type="hidden" name="export" value="0"/>
  <input type="hidden" name="suppressHeaders" value="" />

  <table class="form">
    <tr>
      <th class="title" colspan="8">
        {{tr}}filter-criteria{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        <label>{{tr}}Period{{/tr}}</label>
      </th>
      <td>
        <label>{{tr}}date.From{{/tr}}<input type="hidden" name="begin_date" value="{{$begin_date}}" class="date notNull"/></label>
        <label>{{tr}}date.To{{/tr}}<input type="hidden" name="end_date" value="{{$end_date}}" class="date notNull"/></label>
      </td>
        {{me_form_field animated=false nb_cells=2 label="CSejour-type"}}
          <select name="sejour_type">
            <option value="all" {{if $sejour_type == 'all'}} selected="selected"{{/if}}>
              &mdash; {{tr}}All{{/tr}}
            </option>
            {{foreach from=$sejour_types item=_type}}
              <option value="{{$_type}}"{{if $sejour_type == $_type}} selected="selected"{{/if}}>
                {{tr}}CSejour.type.{{$_type}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}

        {{me_form_field animated=false nb_cells=2 label="common-Practitioner"}}
          <select name="chir_id" onchange="$V(this.form.start, 0);">
            <option value="0"{{if $chir_id == 0}} selected="selected"{{/if}}>
              &mdash; {{tr}}common-Practitioner.all{{/tr}}
            </option>
            {{mb_include module=mediusers template=inc_options_mediuser selected=$chir_id list=$chir_list}}
          </select>
        {{/me_form_field}}
      <th>
        <label for="only_show_missing_codes">{{tr}}CFilterCotation-only_show_missing_codes{{/tr}}</label>
      </th>
      <td>
        <input type="checkbox"{{if $only_show_missing_codes}} checked="checked"{{/if}} name="_cb_only_show_missing_codes" onchange="toggleValueCheckbox(this, this.form.only_show_missing_codes);"/>
        <input type="hidden" name="only_show_missing_codes" value="{{$only_show_missing_codes}}"/>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="8">
        <button type="button" class="search me-primary" onclick="return searchCotations(this.form);">{{tr}}Search{{/tr}}</button>
        <button type="button" class="download" onclick="exportStats(this.form);">{{tr}}common-action-Export{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>