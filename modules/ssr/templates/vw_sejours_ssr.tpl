{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planification}}
{{if $conf.ssr.CPrescription.show_dossier_soins}}
  {{mb_script module=soins script=soins}}

  {{if "planSoins"|module_active}}
    {{mb_script module=planSoins script=plan_soins}}
  {{/if}}

  {{if "dPprescription"|module_active}}
    {{mb_script module=prescription script=prescription}}
    {{mb_script module=prescription script=element_selector}}
  {{/if}}

  {{if "dPmedicament"|module_active}}
    {{mb_script module=medicament script=medicament_selector}}
    {{mb_script module=medicament script=equivalent_selector}}
  {{/if}}

  {{mb_script module=compteRendu script=document}}
  {{mb_script module=compteRendu script=modele_selector}}
  {{mb_script module=files       script=file}}

  <script>
    showDossierSoins = function(sejour_id, date, default_tab){
      var url = new Url("soins", "viewDossierSejour");
      url.addParam("sejour_id", sejour_id);
      url.addParam("modal", 1);
      if(default_tab){
        url.addParam("default_tab", default_tab);
      }
      url.requestModal("100%", "100%", {onClose: function() {
        if (window.closeModal) {
          closeModal();
        }
      }});
      modalWindow = url.modalObject;
    }
  </script>
{{/if}}

<script>
  Main.add(function(){
    Planification.current_m = '{{$m}}';
  });
</script>

{{mb_script module=ssr script=sejours_ssr}}

{{if $dialog}}
  {{mb_include style=mediboard_ext template=open_printable}}
{{else}}
  {{if $can->edit && !$conf.ssr.recusation.sejour_readonly}}
  <a class="button new me-float-left me-margin-left-8" href="?m={{$m}}&tab=vw_aed_sejour_ssr&sejour_id=0">
    {{tr}}ssr-create_pec{{/tr}}
  </a>
  {{/if}}

  {{if "ssr print_week new_format_pdf"|gconf}}
    {{me_button icon=print onclick="Modal.open(\$('print_all_plannings_ssr'), { width: '500' });" label=Print_Plannings
                attr="style='float: right'"}}
    <div id="print_all_plannings_ssr" style="display: none;">
      <table class="tbl me-no-align me-no-box-shadow">
        <tr>
          <th>{{tr}}ssr-print_week{{/tr}}</th>
        </tr>
        <tr>
          <td class="button">
            <button type="button" class="print" onclick="Planification.printAllPlanningSejour('{{$date}}', 0);">
              {{tr}}Print{{/tr}}
            </button>
          </td>
        </tr>
        <tr>
          <th>{{tr}}ssr-print_day{{/tr}}</th>
        </tr>
        <tr>
          <td class="button">
            <script>
              Main.add(function () {
                Calendar.regField(getForm("DateSelectPrintPlanningTechnicienSSR").date);
              });
            </script>
            <form name="DateSelectPrintPlanningTechnicienSSR" action="?" method="get">
              <input type="hidden" name="date" class="date" value="{{$date}}"/>
              <button type="button" class="print"
                      onclick="Planification.printAllPlanningSejour($V(this.form.date), 1);">
                {{tr}}Print{{/tr}}
              </button>
            </form>
          </td>
        </tr>
        <tr>
          <td class="button">
            <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
          </td>
        </tr>
      </table>
    </div>
  {{/if}}
  {{me_button icon=print attr="style='float:right'" onclick="new Url().setModuleAction('$m', '$action').popup(800, 600);"
              label=Print}}
  {{me_dropdown_button button_label=Print button_icon=print container_class="me-dropdown-button-right me-float-right"}}
{{/if}}

<form class="not-printable" name="Filter" action="?" method="get" style="float: right;" onsubmit="this.submit();">
  <input name="m" value="{{$m}}" type="hidden" />
  <input name="{{$actionType}}" value="{{$action}}" type="hidden" />
  <input name="dialog" value="{{$dialog}}" type="hidden" />

  <input name="service_id"   value="{{$filter->service_id}}"   type="hidden" onchange="this.form.onsubmit()" />
  <input name="praticien_id" value="{{$filter->praticien_id}}" type="hidden" onchange="this.form.onsubmit()" />
  <input name="referent_id"  value="{{$filter->referent_id}}"  type="hidden" onchange="this.form.onsubmit()" />

  {{if $dialog}}
  <input type="checkbox" name="group_by" value="1" {{if $group_by}}checked{{/if}} onclick="this.form.onsubmit();">
  <label for="group_by">
    {{tr}}ssr-group_by_kine{{/tr}}
  </label>
  &mdash;
  {{/if}}

  {{mb_include module=ssr template=inc_show_cancelled_services}}
  &mdash;

  {{tr}}CPrescription{{/tr}}
  <select name="show" onchange="this.form.submit();">
    <option value="all"     {{if $show == "all"    }}selected{{/if}}>{{tr}}CSejour.all{{/tr}}</option>
    <option value="nopresc" {{if $show == "nopresc"}}selected{{/if}}>{{tr}}ssr-sejour_nopresc{{/tr}}</option>
  </select>
</form>

{{if $group_by}} 
  {{foreach from=$kines item=_kine name=kines}}
    {{assign var=kine_id value=$_kine->_id}}
    <h1 {{if $smarty.foreach.kines.first}}class="no-break"{{/if}}>
      {{$_kine}}
    </h1>
    {{mb_include module=ssr template=inc_sejours_ssr sejours=$sejours_by_kine.$kine_id}}
  {{/foreach}}   

  {{if !$filter->referent_id}}
    {{assign var=kine_id value=""}}
    <h1>
      <em>
        &mdash; {{tr}}None{{/tr}} {{tr}}CBilanSSR-kine_id{{/tr}}
      </em>
    </h1>
    {{mb_include module=ssr template=inc_sejours_ssr sejours=$sejours_by_kine.$kine_id}}
  {{/if}}
{{else}}
  {{mb_include module=ssr template=inc_sejours_ssr sejours=$sejours}}
{{/if}}

{{if $dialog}}
  {{mb_include style=mediboard_ext template=close_printable}}
{{/if}}
