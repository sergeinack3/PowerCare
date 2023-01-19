{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hprim21     script=pat_hprim_selector}}
{{mb_script module=hprim21     script=sejour_hprim_selector}}
{{mb_script module=admissions  script=admissions}}
{{mb_script module=patients    script=antecedent}}
{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=files     script=file}}
{{mb_script module=planningOp  script=sejour}}
{{if "web100T"|module_active}}
  {{mb_script module=web100T script=web100T}}
{{/if}}
{{mb_script module=admissions script=patients_presents ajax=$ajax}}

<script>
  {{assign var=auto_refresh_frequency value="dPadmissions automatic_reload auto_refresh_frequency_presents"|gconf}}

  Main.add(function() {
    Admissions.table_id = "listPresents";

    var totalUpdater = new Url("admissions", "httpreq_vw_all_presents");
    var listUpdater = new Url("admissions", "httpreq_vw_presents");

    listUpdater.addParam("date", "{{$date}}");
    totalUpdater.addParam("date", "{{$date}}");

    {{if $auto_refresh_frequency != 'never'}}
      Admissions.totalUpdater = totalUpdater.periodicalUpdate('allPresents', {frequency: {{$auto_refresh_frequency}}});
      Admissions.listUpdater = listUpdater.periodicalUpdate('listPresents', {
        frequency: {{$auto_refresh_frequency}},
        onCreate:  function () {
          WaitingMessage.cover($('listPresents'));
          Admissions.rememberSelection();
        }
      });
    {{else}}
      totalUpdater.requestUpdate('allPresents');
      listUpdater.requestUpdate('listPresents', {
        onCreate: function() {
          WaitingMessage.cover($('listPresents'));
          Admissions.rememberSelection();
        }});
    {{/if}}

    $("listPresents").fixedTableHeaders();
    $("allPresents").fixedTableHeaders();
  });
</script>

{{mb_include module=admissions template=inc_prompt_modele type=admissions}}

<table class="main">
  <tr>
    <td colspan="2">
      <div style="min-width: 125px;float: left;">
        <a href="#legend" onclick="Admissions.showLegend()" class="button search me-tertiary me-dark">Légende</a>
        {{if "astreintes"|module_active}}{{mb_include module=astreintes template=inc_button_astreinte_day date=$date}}{{/if}}
      </div>
      <span style="float: right">
        <form name="selType" method="get">
          {{foreach from=$sejour->_specs.type_pec->_list item=_type_pec}}
            <label>
              {{$_type_pec}} <input type="checkbox" name="type_pec[]" value="{{$_type_pec}}" checked onclick="PatientsPresents.reloadFullPresents();" />
            </label>
          {{/foreach}}

          <button type="button" name ="filter_sejours" onclick="Admissions.selectSejours('presents');" class="search me-tertiary">{{tr}}admissions-action-Admission type{{/tr}}</button>

          <input type="checkbox" name="_active_filter_services" title="Prendre en compte le filtre sur les services"
                 onclick="$V(this.form.active_filter_services, this.checked ? 1 : 0); this.form.filter_services.disabled = !this.checked;"
                 {{if $enabled_service == 1}}checked{{/if}} />
          <input type="hidden" name="active_filter_services" onchange="PatientsPresents.reloadFullPresents();" value="{{$enabled_service}}" />
          <button type="button" name="filter_services" onclick="Admissions.selectServices('listPresents');" class="search me-tertiary"
                  {{if $enabled_service == 0}}disabled{{/if}}>Services
          </button>

          <select name="prat_id" onchange="PatientsPresents.reloadFullPresents();">
            <option value="">&mdash; Tous les praticiens</option>
            {{foreach from=$prats item=_prat}}
              <option value="{{$_prat->_id}}" {{if $_prat->_id == $sejour->praticien_id}}selected{{/if}}>{{$_prat}}</option>
            {{/foreach}}
          </select>

          <label>
            <input type="checkbox" name="only_entree_reelle" {{if $only_entree_reelle}}checked{{/if}} onchange="PatientsPresents.reloadFullPresents();" /> Présents avec entrée réelle
          </label>
        </form>
        <a href="#" onclick="PatientsPresents.printPlanning()" class="button print me-tertiary" style="display: none;">Imprimer</a>
        <a href="#" onclick="Admissions.choosePrintForSelection()" class="button print me-tertiary">{{tr}}CCompteRendu-print_for_select{{/tr}}</a>
        <button type="button" class="list me-tertiary" onclick="PatientsPresents.vueGlobalePresent();">{{tr}}mod-admissions-tab-vw_presents_by_services{{/tr}}</button>

        {{mb_include module=hospi template=inc_send_prestations type=admissions}}
      </span>
    </td>
  </tr>
  <tr>
    <td id="left-column">
      <div id="allPresents" class="me-align-auto"></div>
    </td>
    <td style="width: 100%">
      <div id="listPresents" class="me-align-auto"></div>
    </td>
  </tr>
</table>
