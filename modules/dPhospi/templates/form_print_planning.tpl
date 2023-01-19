{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function checkFormPrint(csv) {
    var form = getForm("paramFrm");

    if (!(checkForm(form))) {
      return false;
    }

    popPlanning(csv);
  }

  function popPlanning(csv) {
    var form = getForm("paramFrm");

    var url = new Url("hospi", "print_planning", csv ? "raw" : null);
    url.addElement(form._date_min);
    url.addElement(form._date_max);
    url.addElement(form._horodatage);
    url.addElement(form._admission);
    url.addElement(form.ordre);
    url.addParam("_service", [$V(form._service)].flatten().join(","));
    url.addParam("_filter_type", [$V(form._filter_type)].flatten().join(","));
    url.addParam("praticien_id", [$V(form.praticien_id)].flatten().join(","));
    url.addElement(form._specialite);
    url.addElement(form.convalescence);
    {{if $conf.dPplanningOp.CSejour.consult_accomp}}
    url.addElement(form.consult_accomp);
    {{/if}}
    url.addParam("_ccam_libelle", $V(form._ccam_libelle));
    url.addParam("_coordonnees", $V(form._coordonnees));
    url.addParam('_notes', $V(form._notes));
    url.addParam('_by_date', $V(form._by_date));
    url.addParam('_bmr_filter', $V(form._bmr_filter));
    url.addParam('_bhre_filter', $V(form._bhre_filter));
    url.addParam('_bhre_contact_filter', $V(form._bhre_contact_filter));
    url.addParam("_export_csv", csv);
    url.popup(850, 600, "Planning");
    return;
  }

  function changeDate(sDebut, sFin) {
    var oForm = getForm("paramFrm");
    $V(oForm._date_min, sDebut, false);
    $V(oForm._date_max, sFin, false);
    $V(oForm._date_min_da, Date.fromDATETIME(sDebut).toLocaleDateTime(), false);
    $V(oForm._date_max_da, Date.fromDATETIME(sFin).toLocaleDateTime(), false);
  }

  function changeDateCal(minChanged) {
    var oForm = getForm("paramFrm");
    oForm.select_days[0].checked = false;
    oForm.select_days[1].checked = false;
    oForm.select_days[2].checked = false;

    var minElement = oForm._date_min,
      maxElement = oForm._date_max,
      minView = oForm._date_min_da,
      maxView = oForm._date_max_da;

    if ((minElement.value > maxElement.value) && minChanged) {
      var maxDate = Date.fromDATETIME(minElement.value).toDATE() + ' 21:00:00';
      var maxDateView = Date.fromDATETIME(maxDate).toLocaleDateTime();
      $V(maxElement, maxDate);
      $V(maxView, maxDateView);
    }
  }

  function toggleMultiple(select, multiple) {
    select.size = multiple ? 10 : 1;
    select.multiple = multiple;
  }

</script>

<form name="paramFrm" method="post" onsubmit="return checkFormPrint()">

  <table class="main me-align-auto">
    <tr>
      <td>

        <table class="form">
          <tr>
            <th class="category" colspan="4">{{tr}}common-period-choice{{/tr}}</th>
          </tr>

          <tr>
            <th>{{mb_label object=$filter field=_horodatage}}</th>
            <td>{{mb_field object=$filter field=_horodatage style="width: 15em;"}}</td>

            <td rowspan="2">
              <input type="radio" name="select_days" onclick="changeDate('{{$yesterday_deb}}','{{$yesterday_fin}}');"
                     value="yesterday" />
              <label for="select_days_yesterday">{{tr}}Yesterday{{/tr}}</label>
              <br />
              <input type="radio" name="select_days" onclick="changeDate('{{$today_deb}}','{{$today_fin}}');" value="today" checked />
              <label for="select_days_today">{{tr}}Today{{/tr}}</label>
              <br />
              <input type="radio" name="select_days" onclick="changeDate('{{$tomorrow_deb}}','{{$tomorrow_fin}}');" value="tomorrow" />
              <label for="select_days_tomorrow">{{tr}}Tomorrow{{/tr}}</label>
              <br />
            </td>
            <td rowspan="2">
              <input type="radio" name="select_days" onclick="changeDate('{{$j2_deb}}','{{$j2_fin}}');" value="j2" />
              <label for="select_days_j2">J+2</label>
              <br />
              <input type="radio" name="select_days" onclick="changeDate('{{$j3_deb}}','{{$j3_fin}}');" value="j3" />
              <label for="select_days_j3">J+3</label>
              <br />
              <input type="radio" name="select_days" onclick="changeDate('{{$next_week_deb}}','{{$next_week_fin}}');"
                     value="nextweek" />
              <label for="select_days_nextweek">{{tr}}Last-week{{/tr}}</label>
            </td>
          </tr>

          <tr>
            <th>{{mb_label object=$filter field="_date_min"}}</th>
            <td>{{mb_field object=$filter field="_date_min" form="paramFrm" register=true canNull="false" onchange="changeDateCal(true)"}} </td>
          </tr>

          <tr>
            <th>{{mb_label object=$filter field="_date_max"}}</th>
            <td colspan="3">
                {{mb_field object=$filter field="_date_max" form="paramFrm" register=true canNull="false" onchange="changeDateCal(false)"}}
            </td>
          </tr>
        </table>
      </td>

      <td>
        <table class="form">
          <tr>
            <th class="category" colspan="3">Paramètres de filtre</th>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="_service"}}</th>
            <td colspan="3">
              <select name="_service" style="width: 15em;">
                <option value="0">&mdash; {{tr}}All{{/tr}}</option>
                  {{foreach from=$listServ item=curr_serv}}
                    <option value="{{$curr_serv->service_id}}">{{$curr_serv->nom}}</option>
                  {{/foreach}}
              </select>
              <label style="vertical-align: top;">
                <input type="checkbox" name="_multiple_services" onclick="toggleMultiple(this.form._service, this.checked)"> Multiple
              </label>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="_filter_type"}}</th>
            <td colspan="2">
              <select name="_filter_type" style="width: 15em;">
                <option value="0">&mdash; {{tr}}All{{/tr}}</option>
                {{foreach from=$filter->_specs.type->_locales key=key_hospi item=curr_hospi}}
                  <option value="{{$key_hospi}}">{{$curr_hospi}}</option>
                {{/foreach}}
              </select>
              <label style="vertical-align: top;">
                <input type="checkbox" onclick="toggleMultiple(this.form._filter_type, this.checked)"> Multiple
              </label>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="praticien_id"}}</th>
            <td colspan="2">
              <select name="praticien_id" style="width: 15em;">
                <option value="0">&mdash; {{tr}}All{{/tr}}</option>
                {{mb_include module=mediusers template=inc_options_mediuser list=$listPrat}}
              </select>
              <label style="vertical-align: top;">
                <input type="checkbox" onclick="toggleMultiple(this.form.praticien_id, this.checked)"> Multiple
              </label>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="_specialite"}}</th>
            <td colspan="2">
              <select name="_specialite" style="width: 15em;">
                <option value="0">&mdash; {{tr}}All{{/tr}}</option>
                {{foreach from=$listSpec item=curr_spec}}
                  <option class="mediuser" style="border-color: #{{$curr_spec->color}};"
                          value="{{$curr_spec->function_id}}">{{$curr_spec->text}}</option>
                {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="convalescence"}}</th>
            <td colspan="2">
              <select name="convalescence" style="width: 15em;">
                <option value="0">&mdash; Indifférent</option>
                <option value="o">avec</option>
                <option value="n">sans</option>
              </select>
            </td>
          </tr>

          {{if $conf.dPplanningOp.CSejour.consult_accomp}}
            <tr>
              <th>{{mb_label object=$filter field="consult_accomp"}}</th>
              <td colspan="2">
                <select name="consult_accomp">
                  <option value="0">&mdash; Indifférent</option>
                  <option value="oui">oui</option>
                  <option value="non">non</option>
                </select>
              </td>
            </tr>
          {{/if}}
          <tr>
            <th>{{mb_label object=$filter field=_bmr_filter}}</th>
            <td colspan="2">{{mb_field object=$filter field=_bmr_filter}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field=_bhre_filter}}</th>
            <td colspan="2">{{mb_field object=$filter field=_bhre_filter}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field=_bhre_contact_filter}}</th>
            <td colspan="2">{{mb_field object=$filter field=_bhre_contact_filter}}</td>
          </tr>
        </table>
      </td>

      <td>
        <table class="form">
          <tr>
            <th class="category" colspan="2">{{tr}}common-display-choice{{/tr}}</th>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="_admission"}}</th>
            <td>
              <select name="_admission" style="width: 15em;">
                <option value="heure_admi">Par heure d'admission</option>
                <option value="heure_inter">Par heure d'intervention</option>
                <option value="nom">Par nom du patient</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="_ccam_libelle"}}</th>
            <td colspan="2">{{mb_field object=$filter field="_ccam_libelle"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field="_coordonnees"}}</th>
            <td colspan="2">{{mb_field object=$filter field="_coordonnees"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field=_notes}}</th>
            <td colspan="2">{{mb_field object=$filter field=_notes}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$filter field=_by_date}}</th>
            <td colspan="2">{{mb_field object=$filter field=_by_date}}</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="3">
        <table class="form me-no-align me-no-bg me-no-box-shadow">
          <tr>
            <td class="button">
              <button type="button" onclick="checkFormPrint();" class="print me-primary">{{tr}}Display{{/tr}}</button>

              <button type="button" onclick="checkFormPrint(1);" class="hslip">{{tr}}CBMRBHRe-extract_patient_csv{{/tr}}</button>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

</form>