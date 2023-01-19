{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hprim21 script=pat_hprim_selector}}
{{mb_script module=hprim21 script=sejour_hprim_selector}}
{{mb_script module=admissions script=admissions}}
{{mb_script module=planningOp  script=prestations}}

<script>
  function showLegend() {
    new Url('admissions', "vw_legende").requestModal();
  }

  function printPlanning() {
    var oForm = getForm("selType");
    var url = new Url("admissions", "print_entrees");
    url.addParam("date"      , "{{$date}}");
    url.addParam("service_id", $V(oForm.service_id));
    url.popup(700, 550, "Entrees");
  }

  function printDHE(type, object_id) {
    var url = new Url("planningOp", "view_planning");
    url.addParam(type, object_id);
    url.popup(700, 550, "DHE");
  }

  function reloadFullAdmissions(filterFunction) {
    var oForm = getForm("selType");
    var url = new Url("admissions", "ajax_vw_all_sejours");
    url.addParam("current_m" , "{{$current_m}}");
    url.addParam("date"      , "{{$date}}");
    url.addParam("service_id", $V(oForm.service_id));
    url.addParam("prat_id"   , $V(oForm.prat_id));
    url.requestUpdate("allAdmissions");
    reloadAdmission(filterFunction);
  }

  function reloadAdmission(filterFunction) {
    var oForm = getForm("selType");
    var url = new Url("admissions", "ajax_vw_sejours");
    url.addParam("current_m" , "{{$current_m}}");
    url.addParam("recuse"    , "{{$recuse}}");
    {{if "reservation"|module_active}}
      url.addParam("envoi_mail", "{{$envoi_mail}}");
    {{/if}}
    url.addParam("date"      , "{{$date}}");
    url.addParam("service_id", $V(oForm.service_id));
    url.addParam("prat_id", $V(oForm.prat_id));
    if(!Object.isUndefined(filterFunction)){
      url.addParam("filterFunction", filterFunction);
    }
    url.requestUpdate("listAdmissions");
  }

  function confirmation(oForm) {
    if(confirm('La date enregistrée d\'admission est différente de la date prévue, souhaitez vous confimer l\'admission du patient ?')){
      submitAdmission(oForm);
    }
  }

  function submitAdmission(oForm, bPassCheck) {
    {{if "dPsante400"|module_active && "dPsante400 CIdSante400 admit_ipp_nda_obligatory"|gconf}}
      var oIPPForm = getForm("editIPP" + $V(oForm.patient_id));
      var oNumDosForm = getForm("editNumdos" + $V(oForm.sejour_id));
      if (!bPassCheck && oIPPForm && oNumDosForm && (!$V(oIPPForm.id400) || !$V(oNumDosForm.id400))) {
        Idex.edit_manually($V(oNumDosForm.object_class)+"-"+$V(oNumDosForm.object_id),
                           $V(oIPPForm.object_class)+"-"+$V(oIPPForm.object_id),
                           reloadAdmission.curry());
      } else {
        return onSubmitFormAjax(oForm, reloadAdmission);
      }
    {{else}}
      return onSubmitFormAjax(oForm, reloadAdmission);
    {{/if}}
  }

  Main.add(function() {
    var totalUpdater = new Url("admissions", "ajax_vw_all_sejours");
    totalUpdater.addParam("current_m", "{{$current_m}}");
    totalUpdater.addParam("date", "{{$date}}");
    totalUpdater.periodicalUpdate('allAdmissions', { frequency: 120 });

    var listUpdater = new Url("admissions", "ajax_vw_sejours");
    listUpdater.addParam("recuse", "{{$recuse}}");
    listUpdater.addParam("current_m", "{{$current_m}}");
    listUpdater.addParam("date", "{{$date}}");
    listUpdater.periodicalUpdate('listAdmissions', { frequency: 120 });
  });
</script>

<table class="main">
  <tr>
    <td>
      <a href="#legend" onclick="showLegend()" class="button search">Légende</a>
    </td>
    <td>
      {{if $can->edit && "ssr"|module_active}} 
      <a class="button new" style="float: left;" href="?m={{$m}}&tab=vw_aed_sejour_ssr&sejour_id=0">
        {{tr}}ssr-create_pec{{/tr}}
      </a>
      {{/if}}
      <a href="#" onclick="printPlanning()" class="button print" style="float: right">Imprimer</a>
      <form action="?" name="selType" method="get" style="float: right">
        <select name="service_id" onchange="reloadFullAdmissions();">
          <option value="">&mdash; Tous les services</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if $_service->_id == $sejour->service_id}}selected{{/if}}>{{$_service}}</option>
          {{/foreach}}
        </select>
        <select name="prat_id" onchange="reloadFullAdmissions();">
          <option value="">&mdash; Tous les praticiens</option>
          {{foreach from=$prats item=_prat}}
            <option value="{{$_prat->_id}}"{{if $_prat->_id == $sejour->praticien_id}}selected{{/if}}>{{$_prat}}</option>
          {{/foreach}}
        </select>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {{if $nb_sejours_attente}}
      <div class="small-warning">
        Il y a {{$nb_sejours_attente}} patients à venir en attente de validation
      </div>
      {{else}}
      <div class="small-info">
        Il n'y a  pas de patients à venir en attente de validation
      </div>
      {{/if}}
    </td>
  </tr>
  <tr>
    <td id="allAdmissions" style="width: 250px">
    </td>
    <td id="listAdmissions" style="width: 100%">
    </td>
  </tr>
</table>