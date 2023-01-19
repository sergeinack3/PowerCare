{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
  createFacturesConsult = function(form) {
    $V(form.consultations_id, Object.toJSON(jsonConsultations));
    onSubmitFormAjax(form, function() { document.location.reload();});
  };

  selectAllConsultations = function(valeur){
    $('consults_no_facture').select('input[type=checkbox]').each(function(e){
      if (e.name.indexOf('_check_') >= 0 && !e.disabled) {
        $V(e, valeur);
      }
    });
  };

  showCheckConsultations = function() {
    var checked   = 0;
    var count     = 0;
    var consult_no_facture = $('consults_no_facture');
    consult_no_facture.select('input,checkbox').each(function(e){
      if (e.name.indexOf('_check_') >= 0) {
        count++;
        if ($V(e)) { checked ++; }
      }
    });

    var check_all = consult_no_facture.down('input[name=check_all]');
    var valide_consults = $('btt_valide_consults');
    valide_consults.disabled  = "disabled";
    check_all.checked = '';
    check_all.style.opacity = '1';

    if (checked) {
      valide_consults.disabled = '';
      check_all.checked = '1';
      if (checked < count) {
        check_all.style.opacity = '0.5';
      }
    }
  };

  //Initialisation du tableau json de consultations à valider
  jsonConsultations = {};
</script>

<div>
  <strong>
    <button type="button" class="notext print not-printable" onclick="window.print();">{{tr}}Print{{/tr}}</button>
    {{tr}}Report{{/tr}}
    {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
    {{tr}}common-for{{/tr}}: {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$praticien}}
  </strong>
</div>

<form name="create_for_consult_no_valid" action="?" method="post" id="consults_no_facture">
  <input type="hidden" name="m" value="facturation">
  <input type="hidden" name="dosql" value="do_facture_valid_consult_aed">
  <input type="hidden" name="consultations_id" value="">
  <input type="hidden" name="chir_id" value="{{$praticien->_id}}">
  <table class="main tbl">
    <tr>
      <th class="narrow">{{mb_label class=CConsultation field=patient_id}}</th>
      <th class="narrow">{{mb_label class=CConsultation field=_date}}</th>
      <th>{{mb_label class=CConsultation field=tarif}}</th>
      <th style="width:40%;">{{tr}}CConsultation-cotation{{/tr}}</th>
      <th class="narrow">
        {{if $consults_patient|@count}}
          <input type="checkbox" name="check_all" onchange="selectAllConsultations($V(this));">
        {{/if}}
      </th>
    </tr>
    {{foreach from=$consults_patient item=_consultations}}
      {{foreach from=$_consultations item=consult name="consultations"}}
        <tr>
          {{if $smarty.foreach.consultations.first}}
            <td rowspan="{{$_consultations|@count}}">
              {{mb_include module=system template=inc_vw_mbobject object=$consult->_ref_patient}}
            </td>
          {{/if}}
          <td class="text">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$consult->_guid}}')">
              {{mb_value object=$consult->_ref_plageconsult field=date}}
            </span>
          </td>
          <td class="text">{{mb_value object=$consult field=tarif}}</td>
          <td>
            <table class="form">
              {{if "dPccam codage use_cotation_ccam"|gconf == "1"}}
                <tr>
                  {{if $consult->_ref_actes_ccam|@count}}
                    <td style="width: 15%;">{{tr}}CConsultation-codes_ccam{{/tr}}</td>
                  {{/if}}
                  <td>
                    {{foreach from=$consult->_ref_actes_ccam item=acte_ccam}}
                      <span onmouseover="ObjectTooltip.createEx(this, '{{$acte_ccam->_guid}}');">{{$acte_ccam->_shortview}}</span>
                    {{foreachelse}}
                      <span class="empty">{{tr}}CActeCCAM.none{{/tr}}</span>
                    {{/foreach}}
                  </td>
                </tr>
                <tr>
                  {{if $consult->_ref_actes_ngap|@count}}
                    <td style="width: 15%;">{{tr}}CConsultation-codes_ngap{{/tr}}</td>
                  {{/if}}
                  <td>
                    {{foreach from=$consult->_ref_actes_ngap item=acte_ngap}}
                      <span onmouseover="ObjectTooltip.createEx(this, '{{$acte_ngap->_guid}}');">{{$acte_ngap->_shortview}}</span>
                    {{foreachelse}}
                      <span class="empty">{{tr}}CActeNGAP.none{{/tr}}</span>
                    {{/foreach}}
                  </td>
                </tr>
                {{if 'Ox\Core\Module\CModule::getActive'|static_call:'lpp' && "lpp General cotation_lpp"|gconf}}
                  <tr>
                    {{if $consult->_ref_actes_lpp}}
                      <td style="width: 15%;">{{tr}}CConsultation-codes_lpp{{/tr}}</td>
                    {{/if}}
                    <td>
                      {{foreach from=$consult->_ref_actes_lpp item=_acte_lpp}}
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$_acte_lpp->_guid}}');">{{$_acte_lpp->code}}</span>
                      {{foreachelse}}
                        <span class="empty">{{tr}}CActeLPP.none{{/tr}}</span>
                      {{/foreach}}
                    </td>
                  </tr>
                {{/if}}

                {{if "dPccam frais_divers use_frais_divers_CConsultation"|gconf}}
                  <tr>
                    {{if $consult->_ref_frais_divers|@count}}
                      <td style="width: 15%;">{{tr}}CConsultation-frais-divers{{/tr}}</td>
                    {{/if}}
                    <td>
                      {{foreach from=$consult->_ref_frais_divers item=frais}}
                        <span onmouseover="ObjectTooltip.createEx(this, '{{$frais->_guid}}');">{{$frais->_shortview}}</span>
                        {{foreachelse}}
                        <span class="empty">{{tr}}CFraisDivers.none{{/tr}}</span>
                      {{/foreach}}
                    </td>
                  </tr>
                {{/if}}
              {{/if}}
            </table>
          </td>
          <td>
            <script>
              jsonConsultations["{{$consult->_guid}}"] = {consult_id : "{{$consult->_id}}",checked : 0};
            </script>
            <input type="checkbox" name="_check_{{$consult->_guid}}"
                   onchange="jsonConsultations['{{$consult->_guid}}'].checked = this.checked ? 1 : 0;showCheckConsultations();"/>
          </td>
        </tr>
      {{/foreach}}
    {{foreachelse}}
      <tr>
        <td colspan="5" class="empty">{{tr}}CConsultation.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
  {{if $consults_patient|@count}}
    <table class="main">
      <tr>
        <td class="button" colspan="5">
          <button type="button" class="add" onclick="createFacturesConsult(this.form);" id="btt_valide_consults" disabled>
            {{tr}}CFacture-create_for_consult_no_valid{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  {{/if}}
</form>
