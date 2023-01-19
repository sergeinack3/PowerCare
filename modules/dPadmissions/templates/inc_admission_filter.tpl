{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=reload_full_function value='Prototype.emptyFunction'}}
{{mb_default var=reload_lite_function value='Prototype.emptyFunction'}}
{{mb_default var=type                 value='admissions'}}
{{mb_default var=table_id             value='admissions'}}
{{mb_default var=prestations_ponctuelles       value=null}}
{{mb_default var=prestations_p_ids             value=null}}
{{mb_default var=page value=0}}

<div class="me-align-auto me-margin-bottom-4">
  <table class="main layout">
    <tr class="me-row-valign">
      <td>
        <form action="?" name="selType" method="get">
          <input type="hidden" name="page" value="{{$page}}" />
          <input type="hidden" name="date" value="{{$date}}" />
          <input type="hidden" name="filterFunction" value="{{$filterFunction}}" />
          {{if $view == 'admissions'}}
            <input type="hidden" name="selAdmis" value="{{$selAdmis}}" />
            <input type="hidden" name="selSaisis" value="{{$selSaisis}}" />
          {{elseif $view == 'sorties'}}
            <input type="hidden" name="selSortis" value="{{$selSortis}}" />
          {{/if}}

          <input type="hidden" name="order_col" value="{{$order_col}}" />
          <input type="hidden" name="order_way" value="{{$order_way}}" />

          <!-- print -->
          <input type="hidden" name="order_print_way" value="" />
          <input type="hidden" name="group_by_prat" value="1">
          <fieldset style="display: inline-block" class=" me-small me-align-auto me-h100 me-padding-left-4 me-ws-wrap me-padding-bottom-0">
            <legend><i class="fas fa-filter"></i> {{tr}}Filter|pl{{/tr}}</legend>
            <div style="display: inline-block">
              {{foreach from=$sejour->_specs.type_pec->_list item=_type_pec}}
                <label>
                  {{$_type_pec}}
                  <input type="checkbox" class="me-small" name="type_pec[]" value="{{$_type_pec}}" onclick="{{$reload_full_function}}();"
                    {{if $_type_pec|in_array:$type_pec}}checked{{/if}}>
                </label>
              {{/foreach}}
            </div>

            <div class="filter_pipe"></div>

            {{if "dPplanningOp CSejour show_circuit_ambu"|gconf}}
              <div style="display: inline-block">
                {{foreach from=$sejour->_specs.circuit_ambu->_list item=_circuit_ambu}}
                  <label>
                    {{$_circuit_ambu}}
                    <input type="checkbox" class="me-small" name="circuit_ambu[]" value="{{$_circuit_ambu}}"
                           title="{{tr}}{{/tr}}"
                           onclick="{{$reload_full_function}}();"
                           {{if $circuits_ambu && $_circuit_ambu|in_array:$circuits_ambu}}checked{{/if}}>
                  </label>
                {{/foreach}}
              </div>
            {{/if}}

            <div class="filter_pipe"></div>

            <div style="display: inline-block">
              <select name="period" class="me-small" onchange="{{$reload_lite_function}}();">
                <option value=""      {{if !$period}}selected{{/if}}>
                  &mdash; {{tr}}dPAdmission.admission all the day{{/tr}}
                </option>
                <option value="matin" {{if $period == "matin"}}selected{{/if}}>
                  {{tr}}dPAdmission.admission morning{{/tr}}
                </option>
                <option value="soir"  {{if $period == "soir" }}selected{{/if}}>
                  {{tr}}dPAdmission.admission evening{{/tr}}
                </option>
              </select>
            </div>

            <div class="filter_pipe"></div>

            <div style="display: inline-block">
              <button type="button" name="filter_sejours" onclick="Admissions.selectSejours('{{$view}}');" class="search me-tertiary me-small">{{tr}}admissions-action-Admission type{{/tr}}</button>

              {{if $view == 'admissions'}}
                <label title="{{tr}}admissions-Admissions intervention date is entry date{{/tr}}">
                  <input type="checkbox" class="me-small" name="_date_interv_eg_entree" onchange="{{$reload_full_function}}();"
                    {{if $date_interv_eg_entree}}checked="checked"{{/if}}>
                  {{tr}}admissions-Admissions intervention date is entry date-short{{/tr}}
                </label>
              {{/if}}
            </div>

            <div class="filter_pipe"></div>
            <div style="display: inline-block">
              <input type="checkbox" name="_active_filter_services" class="me-small-calendar" title="Prendre en compte le filtre sur les services"
                     onclick="$V(this.form.active_filter_services, this.checked ? 1 : 0); this.form.filter_services.disabled = !this.checked;"
                     {{if $enabled_service == 1}}checked{{/if}} />
              <input type="hidden" name="active_filter_services"
                     onchange="{{$reload_full_function}}();" value="{{$enabled_service}}"/>
              <button type="button" name ="filter_services" class="search me-tertiary me-small" {{if $enabled_service == 0}}disabled{{/if}}
                      onclick="Admissions.selectServices('{{$view}}');">
                Services
              </button>
            </div>

            <div class="filter_pipe"></div>

            <div style="display: inline-block">
              <select name="prat_id" class="me-small" onchange="{{$reload_full_function}}();" style="width: 12em;">
                <option value="">&mdash; {{tr}}CMediusers.praticiens.all{{/tr}}</option>
                {{mb_include module=mediusers template=inc_options_mediuser list=$prats selected=$sejour->praticien_id}}
              </select>
            </div>

            {{if $view == 'sorties'}}
              <div class="filter_pipe"></div>
              <div style="display: inline-block">
                <select name="only_confirmed" class="me-small" onchange="reloadFullSorties();" style="max-width: 12em;">
                  <option value="">&mdash; Toutes les sorties</option>
                  <option value="1" {{if $only_confirmed == "1"}}selected{{/if}}>Confirmées seulement</option>
                  <option value="0" {{if $only_confirmed == "0"}}selected{{/if}}>Non confirmées seulement</option>
                </select>
              </div>

              <div class="filter_pipe"></div>
              <div style="display: inline-block">
                <select class="me-small" name="mode_sortie[]" multiple onchange="reloadFullSorties();" size="3">
                  <option value="all" selected>&mdash; {{tr}}CModeSortieSejour.all{{/tr}}</option>
                  {{foreach from=$sejour->_specs.mode_sortie->_list item=_mode_sortie}}
                    <option value="{{$_mode_sortie}}">{{$_mode_sortie}}</option>
                  {{/foreach}}
                  <option value="">{{tr}}None{{/tr}}</option>
                </select>
              </div>
            {{/if}}

            {{if $app->user_prefs.show_dh_admissions}}
              <div class="filter_pipe"></div>
              <div style="display: inline-block">
                <label>
                  Réglements DH
                  <select class="me-small" name="reglement_dh" onchange="{{$reload_full_function}}();">
                    <option value="all">{{tr}}All{{/tr}}</option>
                    <option value="payed">Réglés</option>
                    <option value="not_payed">Non réglés</option>
                  </select>
                </label>
              </div>
            {{/if}}
            {{if $prestations_ponctuelles}}
              <div class="filter_pipe"></div>
              <div style="display: inline-block">
                <label>
                  {{tr}}CPrestationPonctuelle|pl{{/tr}}
                  <select class="me-small" name="prestations_p_ids" multiple onchange="{{$reload_full_function}}();" size="3">
                    <option value="all" selected>&mdash; {{tr}}CPrestationPonctuelle.all{{/tr}}</option>
                    {{foreach from=$prestations_ponctuelles item=_prestation_poncutelle}}
                      <option value="{{$_prestation_poncutelle->_id}}"
                              {{if $prestations_p_ids && in_array($_prestation_poncutelle->_id, $prestations_p_ids)}}selected{{/if}}>
                        {{$_prestation_poncutelle}}
                      </option>
                    {{/foreach}}
                  </select>
                </label>
              </div>
            {{/if}}
            {{if $view == "admissions" && "notifications"|module_active }}
            <div class="filter_pipe"></div>
            <div style="display: inline-block">
              <select name="status" class="me-small" onchange="{{$reload_full_function}}();">
                <option value="">&mdash; {{tr}}CNotification-_status-desc{{/tr}}</option>
                    {{foreach from=$status_list item=_status}}
                      <option value="{{$_status}}">{{tr}}CAbstractMessage.status.{{$_status}}{{/tr}}</option>
                    {{/foreach}}
              </select>
            </div>
            {{/if}}
          </fieldset>
        </form>
      </td>
      <td style="text-align: right;">
        <div class="me-display-flex me-flex-column me-align-items-stretch">
          <fieldset style="float: right" class=" me-small me-align-auto me-float-none me-flex-grow-1 me-ws-wrap">
            <legend>{{tr}}Printing|pl{{/tr}}</legend>
            <button type="button" class="button print me-tertiary me-small"
                    onclick="Modal.open('preparePrintPlanning', {width: '500px', height: '150px'});">
              {{tr}}Print{{/tr}}
            </button>
            <div id="preparePrintPlanning" style="display: none;">
              <form name="print_filter_option" method="get" style="text-align: center">
                <h2>{{tr}}dPAdmission.admission impression options{{/tr}}</h2>
                <p>
                  {{tr}}dPAdmission.admission group by prat{{/tr}}
                  <label>
                    <input type="radio" name="group_by_prat" value="1" checked="checked"
                           onchange="$V(getForm('selType').group_by_prat, this.value);"/>
                    {{tr}}Yes{{/tr}}
                  </label>
                  <label>
                    <input type="radio" name="group_by_prat" value="0"
                           onchange="$V(getForm('selType').group_by_prat, this.value);"/>
                    {{tr}}No{{/tr}}
                  </label>
                </p>
                <p>
                  <label>
                    {{tr}}dPAdmission.admission ordonnate{{/tr}}
                    <select name="order_by" onchange="$V(getForm('selType').order_print_way, this.value);">
                      <option value="">{{tr}}dPAdmission.admission praticien name{{/tr}}</option>
                      <option value="patient_name">{{tr}}CPatient-nom-desc{{/tr}}</option>
                      <option value="entree_prevue">{{tr}}dPAdmission.admission heure prevue{{/tr}}</option>
                      <option value="entre_reelle">{{tr}}dPAdmission.admission heure reelle{{/tr}}</option>
                    </select>
                  </label>
                </p>
                <p>
                  <button class="cancel me-tertiary" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
                  <button type="button" onclick="printPlanning()" class="button print me-primary">{{tr}}Print{{/tr}}</button>
                </p>
              </form>
            </div>
            <button type="button" class="print me-tertiary me-block me-small"
                    onclick="Admissions.printRecouvrement('inputs');" title="Recouvrement des dépassements d'honoraires">
              Recouvrement DP
            </button>
            <a href="#" class="button print me-tertiary me-block me-small" onclick="{{if $view === 'admissions'}}Admissions.printGlobal();{{elseif $view === 'sorties'}}Admissions.printSortiesGlobal();{{/if}}"
               style="float: right;"
               title="{{tr}}common-Overall print{{/tr}}">{{tr}}common-Overall print{{/tr}}</a>
          </fieldset>
        </div>
      </td>
    </tr>
  </table>
</div>
