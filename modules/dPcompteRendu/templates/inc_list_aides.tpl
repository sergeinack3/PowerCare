{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=filter_class value=""}}

{{if $can->admin}}
  {{assign var=owner_escape value=$owner|escape:"javascript"}}
  {{assign var=aides_ids_json value=$aides_ids|@json}}

  {{me_button label="Export-CSV" icon=hslip onclick="Aide.exportAidesCSV('`$owner_escape`', '$class', $aides_ids_json)"}}
  {{me_button label="Import-CSV" icon=hslip onclick="Aide.popupImport('`$owner->_guid`', '$class');"}}

  {{me_dropdown_button button_label=Actions button_icon="opt"
       button_class="notext me-tertiary" container_class="me-dropdown-button-right me-float-right"}}
{{/if}}

{{mb_include module=system template=inc_pagination change_page="changePage['$type']" total=$aidesCount.$type current=$start.$type step=30}}

<table class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th>{{mb_colonne class=CAideSaisie field=class order_col=$order_col_aide order_way=$order_way function=sortBy}}</th>
    <th>{{mb_colonne class=CAideSaisie field=field order_col=$order_col_aide order_way=$order_way function=sortBy}}</th>
    <th class="narrow">{{mb_colonne class=CAideSaisie field=depend_value_1 order_col=$order_col_aide order_way=$order_way function=sortBy}}</th>
    <th class="narrow">{{mb_colonne class=CAideSaisie field=depend_value_2 order_col=$order_col_aide order_way=$order_way function=sortBy}}</th>
    <th>{{mb_colonne class=CAideSaisie field=name order_col=$order_col_aide order_way=$order_way function=sortBy}}</th>
    <th>{{mb_title class=CAideSaisie field=text}}</th>
      {{if "loinc"|module_active || "snomed"|module_active}}
        {{if in_array($class, 'Ox\Mediboard\Loinc\CLoinc'|static:binding_classes) || !$filter_class}}
          <th class="narrow" title="{{tr}}CAntecedent-Nomenclature-desc{{/tr}}">{{tr}}CAntecedent-Nomenclature{{/tr}}</th>
        {{/if}}
      {{/if}}
    <th class="narrow"></th>
  </tr>

  {{foreach from=$aides item=_aide}}

  {{assign var=readonly value=0}}

  {{if $_aide->_is_for_instance && !$can->admin()}}
    {{assign var=readonly value=1}}
  {{/if}}

  <tr {{if $_aide->_id == $aide_id}}class="selected"{{/if}}>
    {{assign var="class" value=$_aide->class}}
    {{assign var="field" value=$_aide->field}}

    <td class="text">
      {{if !$readonly}}
        <a href="#1" onclick="Aide.edit('{{$_aide->_id}}')">
      {{/if}}
          {{tr}}{{$class}}{{/tr}}
      {{if !$readonly}}
        </a>
      {{/if}}
    </td>
    <td class="text">{{tr}}{{$class}}-{{$field}}{{/tr}}</td>
    <td>
      {{if $_aide->_depend_field_1 && !$_aide->_is_ref_dp_1}}
        <form name="edit-CAidesSaisie-depend1-{{$_aide->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          {{mb_class object=$_aide}}
          {{mb_key   object=$_aide}}

          {{me_form_field animated=false}}
            <select
              style="width: 10em;"
              onchange="this.form.onsubmit()"
              name="depend_value_1"
              onmouseover="Aide.getListDependValues(this, '{{$class}}', '{{$_aide->_depend_field_1}}')"
              {{if $readonly}}
                disabled
              {{/if}}>
              <option value="{{$_aide->depend_value_1}}">
                {{if $_aide->depend_value_1}}
                  {{tr}}{{$class}}.{{$_aide->_depend_field_1}}.{{$_aide->depend_value_1}}{{/tr}}
                {{else}}
                  &mdash; {{tr}}None{{/tr}}
                {{/if}}
              </option>
            </select>
          {{/me_form_field}}
        </form>
      {{elseif $_aide->_is_ref_dp_1}}
        {{$_aide->_vw_depend_field_1}}
      {{else}}
        &mdash;
      {{/if}}
    </td>
    <td>
      {{if $_aide->_depend_field_2 && !$_aide->_is_ref_dp_2}}
        <form name="edit-CAidesSaisie-depend2-{{$_aide->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          {{mb_class object=$_aide}}
          {{mb_key   object=$_aide}}

          <select
            style="width: 10em;"
            onchange="this.form.onsubmit()"
            name="depend_value_2"
            onmouseover="Aide.getListDependValues(this, '{{$class}}', '{{$_aide->_depend_field_2}}')"
            {{if $readonly}}
              disabled
            {{/if}}>
            <option value="{{$_aide->depend_value_2}}">
              {{if $_aide->depend_value_2}}
                {{tr}}{{$class}}.{{$_aide->_depend_field_2}}.{{$_aide->depend_value_2}}{{/tr}}
              {{else}}
                &mdash; {{tr}}None{{/tr}}
              {{/if}}
            </option>
          </select>
        </form>
      {{elseif $_aide->_is_ref_dp_2}}
        {{$_aide->_vw_depend_field_2}}
      {{else}}
        &mdash;
      {{/if}}
    </td>

    <td class="text">{{mb_value object=$_aide field=name}}</td>
    <td class="text compact" title="{{$_aide->text}}">
      <div style="float: right;">
        {{mb_include module=sante400 template=inc_hypertext_links object=$_aide}}
      </div>
      {{mb_value object=$_aide field=text truncate=60}}
    </td>
     {{if "loinc"|module_active || "snomed"|module_active}}
      {{if in_array($_aide->class, 'Ox\Mediboard\Loinc\CLoinc'|static:binding_classes)}}
          <td>
            {{if "loinc"|module_active}}
              {{mb_include module=loinc  template=inc_vw_tag_loinc  object=$_aide}}
            {{/if}}
            {{if "snomed"|module_active}}
              {{mb_include module=snomed template=inc_vw_tag_snomed object=$_aide}}
            {{/if}}
          </td>
      {{elseif !$filter_class}}
        <td></td>
      {{/if}}
     {{/if}}

    <td>
      {{if !$readonly}}
        <button class="trash notext" type="button" onclick="Aide.remove('{{$_aide->_id}}', '{{$_aide->_view|smarty:nodefaults|JSAttribute}}')">
          {{tr}}Delete{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">{{tr}}CAideSaisie.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>
