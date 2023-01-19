{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    var form = getForm('filterBilanCotations');
    console.log(form);
    console.log(form.begin_date);
    Calendar.regField(form.begin_date);
    Calendar.regField(form.end_date);
    Calendar.regField(form.begin_sejour);
    Calendar.regField(form.end_sejour);

    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('prof_sante', 1)
      .addParam('input_field', '_chir_view')
      .autoComplete(form.elements['_chir_view'], null, {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            if ($V(field) == '') {
              $V(field, selected.down('.view').innerHTML);
            }

            $V(field.form.elements['chir_id'], selected.getAttribute('id').split('-')[2]);
          }
        }
      );

    new Url('mediusers', 'ajax_functions_autocomplete')
      .addParam('type', 'cabinet')
      .addParam('input_field', '_function_view')
      .autoComplete(form.elements['_function_view'], null, {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            if ($V(field) == '') {
              $V(field, selected.down('.view').innerHTML);
            }

            $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
          }
        }
      );

    onChangeObjectClasses();
  });

  onSelectChir = function(element) {
    if ($V(element) != '') {
      $V(element.form.elements['function_id'], '', false);
      $V(element.form.elements['_function_view'], '', false);
      $V(element.form.elements['speciality'], '', false);
    }
  };

  onSelectFunction = function(element) {
    if ($V(element) != '') {
      $V(element.form.elements['chir_id'], '', false);
      $V(element.form.elements['_chir_view'], '', false);
      $V(element.form.elements['speciality'], '', false);
    }
  };

  onSelectSpeciality = function(element) {
    if ($V(element) != '') {
      $V(element.form.elements['chir_id'], '', false);
      $V(element.form.elements['_chir_view'], '', false);
      $V(element.form.elements['function_id'], '', false);
      $V(element.form.elements['_function_view'], '', false);
    }
  };

  onChangeObjectClasses = function() {
    var form = getForm('filterBilanCotations');
    var values = $V(form.elements['_object_classes']);
    $V(form.elements['object_classes'], values.join('|'));

    if ($V(getForm('filterBilanCotations').elements['_object_classes']) !== null) {
      $('display-bilan_cotations_button').enable();
      $('export-bilan_cotations_button').enable();
      $('print-bilan_cotations_button').enable();
    }
    else {
      $('display-bilan_cotations_button').disable();
      $('export-bilan_cotations_button').disable();
      $('print-bilan_cotations_button').disable();
    }
  };

  checkDates = function(form) {
    if ($V(form.elements['begin_date']) == '' && $V(form.elements['end_date']) == ''
      && $V(form.elements['begin_sejour']) == '' && $V(form.elements['end_sejour']) == ''
    ) {
      Modal.alert($T('CFilterCotation-error-cotation_ngap-no_dates'));
      return false;
    }
    else if (($V(form.elements['begin_date']) != '' && $V(form.elements['end_date']) == '')
      || ($V(form.elements['end_date']) != '' && $V(form.elements['begin_date']) == '')
    ) {
      Modal.alert($T('CFilterCotation-error-cotation_ngap-date_execution'));
      return false;
    }
    else if (($V(form.elements['begin_sejour']) != '' && $V(form.elements['end_sejour']) == '')
      || ($V(form.elements['end_sejour']) != '' && $V(form.elements['begin_sejour']) == '')
    ) {
      Modal.alert($T('CFilterCotation-error-cotation_ngap-date_sejour'));
      return false;
    }

    return true;
  };

  refreshBilanCotations = function(form) {
    if (checkDates(form)) {
      new Url('pmsi', 'ajax_bilan_cotations')
        .addFormData(form)
        .requestUpdate('BilanCotation-results');
    }
  };

  exportBilanCotations = function(form) {
    if (checkDates(form)) {
      $V(form.action, 'export');
      $V(form.number, '');
      $V(form.suppressHeaders, 1);

      form.submit();

      $V(form.action, '');
      $V(form.number, 30);
      $V(form.suppressHeaders, '');
    }
  };

  printBilanCotations = function(form) {
    if (checkDates(form)) {
      $V(form.action, 'print');
      $V(form.number, '');

      new Url('urgences', 'ajax_bilan_cotations')
        .addFormData(form)
        .popup(1200, 800);

      $V(form.action, '');
      $V(form.number, 30);
    }

    return false;
  };

  changePageBilanCotations = function(start) {
    var form = getForm('filterBilanCotations');
    if (form) {
      $V(form.elements['start'], start);
      refreshBilanCotations(form);
    }
  };

  sortBilanCotation = function(column, way) {
    var form = getForm('filterBilanCotations');
    if (form) {
      $V(form.elements['sort_column'], column);
      $V(form.elements['sort_way'], way);
      refreshBilanCotations(form);
    }
  };
</script>

<form name="filterBilanCotations" action="?" method="get" target="_blank">
  <input type="hidden" name="m" value="urgences"/>
  <input type="hidden" name="a" value="ajax_bilan_cotations"/>
  <input type="hidden" name="start" value="0"/>
  <input type="hidden" name="number" value="30"/>
  <input type="hidden" name="sort_column" value="{{$sort_column}}">
  <input type="hidden" name="sort_way" value="{{$sort_way}}">
  <input type="hidden" name="action" value=""/>
  <input type="hidden" name="suppressHeaders" value="" />

  <table class="form">
    <tr>
      <th class="title" colspan="8">
        {{tr}}filter-criteria{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        <label title="{{tr}}CFilterCotation-date_execution-desc{{/tr}}">{{tr}}CFilterCotation-date_execution{{/tr}}</label>
      </th>
      <td>
        <label>{{tr}}date.From{{/tr}} <input type="hidden" name="begin_date" value="{{$begin_date}}" class="date"></label>
        <label>{{tr}}date.To{{/tr}} <input type="hidden" name="end_date" value="{{$end_date}}" class="date"></label>
      </td>
      {{me_form_field nb_cells=2 label="common-Practitioner"}}
        <input type="hidden" name="chir_id" value="{{$chir->_id}}" onchange="onSelectChir(this);"/>
        <input type="text" name="_chir_view" value="{{$chir}}"/>
        <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.elements['chir_id'], ''); $V(this.form.elements['_chir_view'], '');">{{tr}}Empty{{/tr}}</button>
      {{/me_form_field}}
      <th>
        <label for="speciality">{{tr}}common-Speciality{{/tr}}</label>
      </th>
      <td>
        <select name="speciality" id="filterBilanCotations_speciality" onchange="onSelectSpeciality(this);">
          <option value="">&mdash; {{tr}}Select{{/tr}}</option>
          {{foreach from=$specialities item=_speciality}}
            <option value="{{$_speciality->spec_cpam_id}}">
              {{$_speciality->text}}
            </option>
          {{/foreach}}
        </select>
        <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.elements['speciality'], '');">{{tr}}Empty{{/tr}}</button>
      </td>

      {{me_form_field nb_cells=2 label="CFunction"}}
        <input type="hidden" name="function_id" value="{{$function->_id}}" onchange="onSelectFunction(this);"/>
        <input type="text" name="_function_view" value="{{$function}}"/>
        <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.elements['function_id'], ''); $V(this.form.elements['_function_view'], '');">{{tr}}Empty{{/tr}}</button>
      {{/me_form_field}}
    </tr>
    <tr>
      <th>
        <label title="{{tr}}CFilterCotation-dates_sejour-desc{{/tr}}">{{tr}}CFilterCotation-dates_sejour{{/tr}}</label>
      </th>
      <td>
        <label>{{tr}}date.From{{/tr}} <input type="hidden" name="begin_sejour" value="{{$begin_sejour}}" class="date"></label>
        <label>{{tr}}date.To{{/tr}} <input type="hidden" name="end_sejour" value="{{$end_sejour}}" class="date"></label>
      </td>
      {{me_form_field field_class="me-form-icon barcode" label="NDA" title_label="PMSI-action-Choose a file number directly" nb_cells=2}}
        <input type="text" name="nda" class="barcode me-margin-right-16" value="">
      {{/me_form_field}}
      <th>
        <label for="object_classes" title="{{tr}}CFilterCotation-object_classes-bilan_cotation-desc{{/tr}}">{{tr}}CFilterCotation-object_classes{{/tr}}</label>
      </th>
      <td>
        <input type="hidden" name="object_classes" value=""/>
        <label>
          <input type="checkbox" name="_object_classes" id="filterBilanCotations__object_classes-CSejour" value="CSejour"{{if in_array('CSejour', $object_classes)}} checked{{/if}} onchange="onChangeObjectClasses();">
          {{tr}}CSejour{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="_object_classes" id="filterBilanCotations__object_classes-COperation" value="COperation"{{if in_array('COperation', $object_classes)}} checked{{/if}} onchange="onChangeObjectClasses();">
          {{tr}}COperation{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="_object_classes" id="filterBilanCotations__object_classes-CConsultation" value="CConsultation"{{if in_array('CConsultation', $object_classes)}} checked{{/if}} onchange="onChangeObjectClasses();">
          {{tr}}CConsultation{{/tr}}
        </label>
      </td>
      <th colspan="2"></th>
    </tr>
    <tr>
      <td class="button" colspan="8">
        <button type="button" id="display-bilan_cotations_button" class="search me-primary" onclick="refreshBilanCotations(this.form);">{{tr}}Search{{/tr}}</button>
        <button type="button" id="export-bilan_cotations_button" class="download" onclick="exportBilanCotations(this.form);">{{tr}}common-action-Export{{/tr}}</button>
        <button type="button" id="print-bilan_cotations_button" class="print" onclick="printBilanCotations(this.form);">{{tr}}common-action-Print{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="BilanCotation-results" class="me-margin-0 me-padding-0">
  {{mb_include module=urgences template=inc_bilan_cotations}}
</div>