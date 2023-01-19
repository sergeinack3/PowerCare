{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=context_print_futurs_rdv value=$app->user_prefs.context_print_futurs_rdv}}

<script>
  Main.add(function() {
    var form = getForm("filterFutursRDV");
    form.nombre_consultations.addSpinner({min:0});

    var urlUsers = new Url("mediusers", "ajax_users_autocomplete");
    urlUsers.addParam("rdv", "1");
    urlUsers.addParam("input_field", "chir_id_view");
    urlUsers.autoComplete(form.chir_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(form.chir_id_view, selected.down('.view').innerHTML);
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.chir_id, id);
      }
    });

    var urlFunctions = new Url("mediusers", "ajax_functions_autocomplete");
    urlFunctions.addParam("edit", "1");
    urlFunctions.addParam("input_field", "function_id_view");
    urlFunctions.addParam("view_field", "text");
    urlFunctions.autoComplete(form.function_id_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        $V(form.function_id_view, selected.down('.view').innerHTML);
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.function_id, id);
      }
    });

    form.up('div.content').setStyle({overflow: 'visible'});
    form.up('div.modal').setStyle({overflow: 'visible'});
  });

  printFutursRDV = function() {
    var form = getForm("filterFutursRDV");
    var prat_id     = $V(form.chir_id);
    var function_id = $V(form.function_id);
    if (!prat_id && !function_id) {
      return false;
    }

    form.submit();
  };

  onChangePrat = function(form) {
    $V(form.function_id, '', false);
    $V(form.function_id_view, '');
    $V(form.select_prat_cab, "prat", false);
    form.print_button.removeAttribute("disabled");
  };

  onSelectPrat = function(form){
    $V(form.function_id, '', false);
    $V(form.function_id_view, '', false);
    if(!$V(form.chir_id)) {
      form.print_button.disabled = true;
    }
    else{
      form.print_button.removeAttribute("disabled");
    }
  };

  onChangeFunction = function(form){
    $V(form.chir_id, '', false);
    $V(form.chir_id_view, '');
    $V(form.select_prat_cab, "cab", false);
    form.print_button.removeAttribute("disabled");
  };

  onSelectFunction = function(form){
    $V(form.chir_id, '', false);
    $V(form.chir_id_view, '', false);
    if(!$V(form.function_id)) {
      form.print_button.disabled = true;
    }
    else{
      form.print_button.removeAttribute("disabled");
    }
  };
</script>

<iframe name="download_pdf" style="width: 50px; height: 50px; position: absolute; top: -1000px;"></iframe>

<form name="filterFutursRDV" method="get" target="download_pdf" onsubmit="return printFutursRDV();">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="raw" value="ajax_print_futurs_rdv" />
  <input type="hidden" name="patient_id" value="{{$patient_id}}" />
  <fieldset>
    <legend>{{tr}}CConsultations-print{{/tr}}</legend>
    <table class="form">
      <tr>
        <th>Sélection du praticien / cabinet</th>
        <td>
          {{assign var=selected value=""}}
          {{if $context_print_futurs_rdv == "prat"}}
            {{assign var=selected value=$curr_user->_id}}
          {{/if}}

          <input type="radio" name="select_prat_cab" value="prat" onchange="onSelectPrat(this.form);"/>
          <input type="hidden" name="chir_id" class="autocomplete" onchange="onChangePrat(this.form)"/>
          <input type="text" name="chir_id_view" class="autocomplete" style="text-align: left;"
                   onmousedown="$V(this, '');"
                   onblur="if (!$V(this)){$V(this, '');$V(this.form.chir, '');}"
                   placeholder="&mdash; {{tr}}CMediusers-select-praticien{{/tr}}"/>
        </td>
        <td>
          {{assign var=selected value=""}}
          {{if $context_print_futurs_rdv == "cabinet"}}
            {{assign var=selected value=$curr_user->_ref_function}}
          {{/if}}

          <input type="radio" name="select_prat_cab" value="cab" onchange="onSelectFunction(this.form);" checked />
          <input type="hidden" name="function_id" value="{{$curr_user->function_id}}" class="autocomplete"
                 onchange="onChangeFunction(this.form);" id="editFrm_function_id"/>
          <input type="text" name="function_id_view" value="{{$selected}}" placeholder="&mdash; {{tr}}CFunctions-select{{/tr}}"/>
        </td>
      </tr>
      <tr>
        <th>{{tr}}CConsultation-Number_to_print{{/tr}}</th>
        <td colspan="2"><input type="number" name="nombre_consultations" value="1" style="width:75px"/></td>
      </tr>
      <tr>
        <td class="button" colspan="3">
          <button name="print_button" class="print singleclick">{{tr}}Print{{/tr}}</button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>
