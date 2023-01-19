{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=pat_selector}}

<script type="text/javascript">
  Main.add(function() {
    var form = getForm('filterCotationsNGAP');
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

    new Url('system', 'ajax_seek_autocomplete')
      .addParam('object_class', 'CPatient')
      .addParam('field', 'patient_id')
      .addParam('view_field', '_patient_view')
      .addParam('input_field', '_patient_view')
      .autoComplete(form.elements['_patient_view'], null, {
        minChars: 3,
        method: 'get',
        select: 'view',
        dropdown: false,
        width: '300px',
        afterUpdateElement: function(field, selected) {
          $V(form.elements['patient_id'], selected.get('guid').split('-')[1]);
          $V(form.elements['_patient_view'], selected.down('.view').innerHTML.trim());
        }
      });

    onChangeObjectClasses();
  });

  onSelectChir = function(element) {
    if ($V(element) != '') {
      $V(element.form.elements['function_id'], '', false);
      $V(element.form.elements['_function_view'], '', false);
    }
  };

  onSelectFunction = function(element) {
    if ($V(element) != '') {
      $V(element.form.elements['chir_id'], '', false);
      $V(element.form.elements['_chir_view'], '', false);
    }
  };

  onSelectPatient = function(element) {
    if ($V(element) != '') {
      $V(element.form.elements['nda'], '', false);
    }
  };

  onSelectNDA = function(element) {
    if ($V(element) != '') {
      $V(element.form.elements['patient_id'], '', false);
      $V(element.form.elements['_patient_view'], '', false);
    }
  };

  onChangeObjectClasses = function() {
    var form = getForm('filterCotationsNGAP');
    var values = $V(form.elements['_object_classes']);
    $V(form.elements['object_classes'], values.join('|'));

    if ($V(getForm('filterCotationsNGAP').elements['_object_classes']) !== null) {
      $('display-cotations-NGAP_button').enable();
      $('export-cotations-NGAP_button').enable();
    }
    else {
      $('display-cotations-NGAP_button').disable();
      $('export-cotations-NGAP_button').disable();
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

  searchCotationsNGAP = function(form) {
    if (checkDates(form)) {
      new Url('pmsi', 'ajax_cotations_ngap')
        .addFormData(form)
        .requestUpdate('results');
    }
  };

  exportCotationsNGAP = function(form) {
    if (checkDates(form)) {
      $V(form.action, 'export');
      $V(form.number, 0);
      $V(form.suppressHeaders, 1);

      form.submit();

      $V(form.action, '');
      $V(form.number, 40);
      $V(form.suppressHeaders, '');
    }
  };

  printCotationsNGAP = function(form) {
    if (checkDates(form)) {
      $V(form.action, 'print');
      $V(form.number, 0);

      new Url('pmsi', 'ajax_cotations_ngap')
        .addFormData(form)
        .popup(1200, 800);

      $V(form.action, '');
      $V(form.number, 40);
    }
  };

  changePageCotationsNGAP = function(start) {
    var form = getForm('filterCotationsNGAP');
    if (form) {
      $V(form.elements['start'], start);
      searchCotationsNGAP(form);
    }
  };

  sortCotationNGAP = function(column, way) {
    var form = getForm('filterCotationsNGAP');
    if (form) {
      $V(form.elements['sort_column'], column);
      $V(form.elements['sort_way'], way);
      searchCotationsNGAP(form);
    }
  };

  PatSelector.init = function() {
    this.sForm = 'filterCotationsNGAP';
    this.sId = 'patient_id';
    this.sView = '_patient_view';
    this.pop();
  };
</script>

<form name="filterCotationsNGAP" action="?" method="get" target="_blank">
  <input type="hidden" name="m" value="pmsi"/>
  <input type="hidden" name="a" value="ajax_cotations_ngap"/>
  <input type="hidden" name="start" value="0"/>
  <input type="hidden" name="number" value="40"/>
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
        <label>{{tr}}date.From{{/tr}}<input type="hidden" name="begin_date" value="{{$begin_date}}" class="date"></label>
        <label>{{tr}}date.To{{/tr}}<input type="hidden" name="end_date" value="{{$end_date}}" class="date"></label>
      </td>
        {{me_form_field nb_cells=2 label="common-Practitioner"}}
          <input type="hidden" name="chir_id" value="{{$chir->_id}}" onchange="onSelectChir(this);">
          <input type="text" name="_chir_view" value="{{$chir}}">
          <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.elements['chir_id'], ''); $V(this.form.elements['_chir_view'], '');">{{tr}}Empty{{/tr}}</button>
        {{/me_form_field}}

        {{me_form_field nb_cells=2 label="CFunction"}}
          <input type="hidden" name="function_id" value="{{$function->_id}}" onchange="onSelectFunction(this);">
          <input type="text" name="_function_view" value="{{$function}}">
          <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.elements['function_id'], ''); $V(this.form.elements['_function_view'], '');">{{tr}}Empty{{/tr}}</button>
        {{/me_form_field}}
      <th>
        <label for="object_classes">{{tr}}CFilterCotation-object_classes{{/tr}}</label>
      </th>
      <td>
        <input type="hidden" name="object_classes" value="">
        <label>
          <input type="checkbox" name="_object_classes" value="CSejour"{{if in_array('CSejour', $object_classes)}} checked{{/if}} onchange="onChangeObjectClasses();">
          {{tr}}CSejour{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="_object_classes" value="COperation"{{if in_array('COperation', $object_classes)}} checked{{/if}} onchange="onChangeObjectClasses();">
          {{tr}}COperation{{/tr}}
        </label>
        <label>
          <input type="checkbox" name="_object_classes" value="CConsultation"{{if in_array('CConsultation', $object_classes)}} checked{{/if}} onchange="onChangeObjectClasses();">
          {{tr}}CConsultation{{/tr}}
        </label>
      </td>
    </tr>
    <tr>
      <th>
        <label title="{{tr}}CFilterCotation-dates_sejour-desc{{/tr}}">{{tr}}CFilterCotation-dates_sejour{{/tr}}</label>
      </th>
      <td>
        <label>{{tr}}date.From{{/tr}}<input type="hidden" name="begin_sejour" value="{{$begin_sejour}}" class="date"></label>
        <label>{{tr}}date.To{{/tr}}<input type="hidden" name="end_sejour" value="{{$end_sejour}}" class="date"></label>
      </td>
      {{me_form_field label='CPatient' nb_cells=2}}
        <input type="hidden" name="patient_id" value="" onchange="onSelectPatient(this);">
        <input type="text" name="_patient_view" value="">
        <button class="search notext compact me-tertiary" type="button" onclick="PatSelector.init()">{{tr}}Search{{/tr}}</button>
        <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.elements['patient_id'], ''); $V(this.form.elements['_patient_view'], '');">{{tr}}Empty{{/tr}}</button>
      {{/me_form_field}}
      {{me_form_field field_class="me-form-icon barcode" label="NDA" title_label="PMSI-action-Choose a file number directly" nb_cells=2}}
        <input type="text" name="nda" class="barcode me-margin-right-16" value="" onchange="onSelectNDA(this);">
      {{/me_form_field}}
      <th colspan="2"></th>
    </tr>
    <tr>
      <td class="button" colspan="8">
        <button type="button" id="display-cotations-NGAP_button" class="search me-primary" onclick="searchCotationsNGAP(this.form);">{{tr}}Search{{/tr}}</button>
        <button type="button" id="export-cotations-NGAP_button" class="download" onclick="exportCotationsNGAP(this.form);">{{tr}}common-action-Export{{/tr}}</button>
        <button type="button" id="print-cotations-NGAP_button" class="print" onclick="printCotationsNGAP(this.form);">{{tr}}common-action-Print{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="results" class="me-margin-0 me-padding-0">
  {{mb_include module=pmsi template=cotations/inc_cotations_ngap}}
</div>