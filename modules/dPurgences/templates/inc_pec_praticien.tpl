{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{**
  * Permet un accès la prise en charge UPATOU, la crée si elle n'existe pas
  *
  * @param $listPrats array|CMediusers Praticiens disponibles
  * @param $rpu CRPU Résumé de passage aux urgences
  *}}

{{assign var=sejour  value=$rpu->_ref_sejour}}
{{assign var=consult value=$rpu->_ref_consult}}

{{mb_default var=type value=""}}
{{mb_default var=ajax_pec value=""}}
{{mb_default var=callback value=""}}
{{mb_default var=tab_mode value="1"}}
{{mb_default var=with_form value="1"}}

<script>
  checkPraticien = function(form) {
    var prat = $V(form._prat_id);
    if (prat == "") {
      alert($T('common-Practitioner.choose_select'));
      return false;
    }
    return true;
  };
</script>

{{if $consult}}
  {{if (!in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && !$sejour->UHCD) ||  $rpu->mutation_sejour_id}}
    <strong>{{mb_value object=$sejour field=type}}</strong>
    <br/>
    <a class="button search" title="Voir le dossier complet du patient" href="?m=patients&tab=vw_full_patients&patient_id={{$sejour->patient_id}}">
      {{tr}}dPpatients-CPatient-Dossier_complet{{/tr}}
    </a>

  {{else}}
    {{if !$consult->_id}}
      {{if $type != "imagerie" && !$sejour->sortie_reelle || $conf.dPurgences.pec_after_sortie}}
        {{if $can->edit}}
          <script>
            Main.add(function() {
              var form = getForm({{if $with_form}}"createConsult-{{$rpu->_id}}"{{else}}"editRPU"{{/if}});
              var field = form._datetime;
              var dates = {
                limit: {
                  start: '{{$sejour->entree|iso_date}}',
                  stop: '{{$sejour->sortie|iso_date}}'
                }
              };

              var datepicker = Calendar.regField(field, dates);
              var view = datepicker.element;
              view.style.width = "16px";

              datepicker.icon.observe("click", function(){
                view.style.width = null;
              });
            });
          </script>

          <form name="createConsult-{{$rpu->_id}}" method="post"
                onsubmit="{{if $ajax_pec}}return onSubmitFormAjax(this, Control.Modal.close){{else}}if (checkForm(this)) { this.submit(); }{{/if}};"
                class="prepared">
            <input type="hidden" name="m" value="cabinet" />
            <input type="hidden" name="dosql" value="do_consult_now" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
            <input type="hidden" name="patient_id" value="{{$sejour->patient_id}}" />
            <input type="hidden" name="charge_id" value="{{$sejour->charge_id}}" />
            <input type="hidden" name="date_at" value="{{$rpu->date_at}}" />
            <input type="hidden" name="ajax" value="{{$ajax_pec}}" />
            <input type="hidden" name="tab_mode" value="{{if $tab_mode}}tab{{else}}a{{/if}}" />
            <input type="hidden" name="callback" value="{{$callback}}" />

            {{assign var=selected_user_id value=$sejour->praticien_id}}

            {{if $app->_ref_user->isUrgentiste()}}
              {{assign var=selected_user_id value=$app->user_id}}
            {{/if}}

            <select name="_prat_id" class="ref notNull" style="width: 15em;">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_mediuser list=$listPrats selected=$selected_user_id}}
            </select>
            <input type="hidden" name="_datetime" value="" class="dateTime" />

            <script>
              checkPraticien = function(oForm){
                var prat = oForm._prat_id.value;
                if (prat == ""){
                  alert($T('common-Practitioner.choose_select'));
                  return false;
                }
                return true;
              }
            </script>
            <button type="button" class="new" onclick="if(checkPraticien(this.form)) {this.form.onsubmit();}">
              {{tr}}CRPU-pec{{/tr}}
            </button>
          </form>
        {{else}}
          &mdash;
        {{/if}}
      {{else}}
        <div class="empty">{{tr}}CRPU-ATU-missing{{/tr}}</div>
      {{/if}}
    {{else}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$consult->_ref_praticien}}
      {{if $can->edit}}
      <a class="button search" title="Prise en charge" href="?m=urgences&{{if $tab_mode}}tab{{else}}dialog{{/if}}=edit_consultation&selConsult={{$consult->_id}}">
        {{tr}}CRPU-see_pec{{/tr}}
      </a>
      {{/if}}
    {{/if}}
  {{/if}}
{{/if}}
