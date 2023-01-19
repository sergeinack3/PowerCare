{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=plage}}

<script>
  updateList = function (form) {
    var url = new Url("astreintes", "vw_list_astreinte");
    url.addParam("mode", $V(form.mode));
    url.addParam("type_names", [$V(form.type_names)].flatten().join(","));
    url.addParam("date", $V(form.date));
    url.requestUpdate("astreintesList");
  };

  Main.add(function () {
    $("listAstreintesDiv").fixedTableHeaders();
    Calendar.regField(getForm('astreintesForm').date, null, {noView: true});
  });
</script>

<div id="astreintesList">
  <form name="astreintesForm" method="get" action="">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="tab" value="listAstreintes" />
    <table class="main form">
      <tr>
        {{me_form_field nb_cells=2 label="common-period"}}
          <select name="mode">
            <option value="day" {{if $mode == "day"  }}selected{{/if}}>{{tr}}Day{{/tr}}</option>
            <option value="week" {{if $mode == "week" }}selected{{/if}}>{{tr}}Week{{/tr}}</option>
            <option value="month" {{if $mode == "month"}}selected{{/if}}>{{tr}}Month{{/tr}}</option>
            <option value="year" {{if $mode == "year" }}selected{{/if}}>{{tr}}Year{{/tr}}</option>
          </select>
        {{/me_form_field}}
        {{me_form_field nb_cells=2 rowspan=2 mb_object=$astreinte mb_field=type}}
          <select name="type_names[]" size="5" multiple>
            <option value="all" {{if is_array($type_names) && $type_names.0 == 'all'}}selected{{/if}}>&ndash; {{tr}}CPlageAstreinte.type.all{{/tr}}</option>
            {{foreach from=$astreinte->_specs.type->_list item=curr_type}}
              <option value="{{$curr_type}}"
                      {{if (is_array($type_names) && in_array($curr_type, $type_names))}}selected{{/if}}>
                {{tr}}CPlageAstreinte.type.{{$curr_type}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field nb_cells=2 mb_class=CCategorieAstreinte mb_field=name}}
          <select name="category" id="category">
            <option value="0">&mdash; {{tr}}All{{/tr}}</option>
            {{foreach from=$categories item=_category}}
              <option value="{{$_category->_id}}"
                      {{if $current_category_id && $current_category_id == $_category->_id}}selected{{/if}}>
                {{$_category->name}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      </tr>
      <tr>
        <td class="button" colspan="8">
          <button type="button" class="search" onclick="this.form.submit();">{{tr}}Filter{{/tr}}</button>
        </td>
      </tr>
    </table>

  <div id="listAstreintesDiv" class="me-align-auto">
    <table class="tbl me-no-box-shadow">
      <tbody>
      {{foreach from=$astreintes item=_astreinte}}
        <tr>
          <td style="width:40px;">
            <button type="button" class="edit notext"
                    onclick="PlageAstreinte.modal('{{$_astreinte->_id}}')">{{tr}}Modify{{/tr}}</button>
          </td>
          <td style="background:#{{$_astreinte->_color}}; color:#{{$_astreinte->_font_color}}">{{$_astreinte->libelle}}</td>
          <td>{{mb_value object=$_astreinte->_ref_category field=name}}</td>
          <td>{{mb_value object=$_astreinte->_ref_user field=_user_last_name}}<br />
            <strong>{{mb_value object=$_astreinte field=phone_astreinte}}</strong></td>
          <td>{{mb_include module="system" template="inc_interval_datetime" from=$_astreinte->start to=$_astreinte->end}}</td>
          <td>{{mb_include module="system" template="inc_vw_duration" duration=$_astreinte->_duree}}</td>
          <td>{{mb_value object=$_astreinte field=type}}</td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="7" class="empty">{{tr}}CPlageAstreinte.none{{/tr}}</td>
        </tr>
      {{/foreach}}
      </tbody>

      <thead>
      <tr>
        <th colspan="7" class="title">
          <button type="button" onclick="PlageAstreinte.modal()" class="new" style="float: left;">{{tr}}Create{{/tr}}</button>
          <a class="button notext left" href="?m={{$m}}&mode={{$mode}}&date={{$date_prev}}">{{tr}}Previous{{/tr}}</a>
          {{$today|date_format:$conf.longdate}}
          <input type="hidden" name="date" class="date" value="{{$today}}" onchange="getForm('astreintesForm').submit();" />
          <a class="button notext right" href="?m={{$m}}&mode={{$mode}}&date={{$date_next}}">{{tr}}Next{{/tr}}</a>
          <button type="button"
                  class="print float-right"
                  style="float: right;"
                  onclick="PlageAstreinte.printShifts('astreintesForm');">{{tr}}Print{{/tr}}</button>
        </th>
      </tr>
      <tr>
        <th class="narrow"></th>
        <th class="narrow">{{tr}}common-Label{{/tr}}</th>
        <th class="narrow">{{mb_title class=CCategorieAstreinte field=name}}</th>
        <th>{{tr}}User{{/tr}}</th>
        <th>{{tr}}common-Date|pl{{/tr}}</th>
        <th>{{tr}}common-Duration{{/tr}}</th>
        <th>{{tr}}common-Type{{/tr}}</th>
      </tr>
      </thead>
    </table>
  </div>
  </form>
</div>
