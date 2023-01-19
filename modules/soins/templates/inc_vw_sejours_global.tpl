{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
{{if !$app->user_prefs.use_current_day}}
  Main.add(function() {
      Calendar.regField(getForm('changeDate').date, null, {noView: true});
  });
{{/if}}
</script>

{{if $print}}
  {{mb_include style=mediboard_ext template=open_printable}}
{{else}}

  {{if $select_view}}
    {{if $mode == 'instant'}}
      {{assign var=vwMode value=0}}
    {{else}}
      {{assign var=vwMode value=1}}
    {{/if}}

    <form name="editPrefVueSejour" method="post">
      <input type="hidden" name="m" value="admin" />
      <input type="hidden" name="dosql" value="do_preference_aed" />
      <input type="hidden" name="user_id" value="{{$app->user_id}}" />
      <input type="hidden" name="pref[vue_sejours]" value="standard" />
      <input type="hidden" name="postRedirect" value="m=soins&tab=viewIndexSejour&mode={{$vwMode}}" />
      <button type="submit" class="change notext">Vue par défaut</button>
    </form>
  {{/if}}

  <form name="TypeHospi" method="get" action="?">
    <input type="hidden" name="m" value="soins" />

    {{if $select_view}}
      <input type="hidden" name="tab" value="vwSejours" />
    {{else}}
      <input type="hidden" name="a" value="vwSejours" />
    {{/if}}

    <input type="hidden" name="show_affectation" value="{{$show_affectation}}" />
    <input type="hidden" name="only_non_checked" value="{{$only_non_checked}}" />

    {{if $select_view}}
      <input type="hidden" name="select_view" value="{{$select_view}}" />
      {{if "soins Sejour select_services_ids"|gconf}}
        <button type="button" onclick="Soins.selectServices('ecap');" class="search">Services</button>
        <input type="hidden" name="service_id" value="{{$service_id}}" />
      {{else}}
        <select name="service_id" style="width: 200px;" onchange="this.form.praticien_id.value = 'none'; this.form.function_id.value = ''; if (this.form.discipline_id) { this.form.discipline_id.value = ''; } this.form.submit();">
          <option value="">&mdash; {{tr}}CService{{/tr}}</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if $_service->_id == $service_id}}selected{{/if}}>{{$_service->_view}}</option>
          {{/foreach}}
          <option value="NP" {{if $service_id == "NP"}}selected{{/if}}>Non placés</option>
        </select>
      {{/if}}

      <select name="praticien_id" style="width: 200px;" onchange="this.form.service_id.value = ''; this.form.function_id.value = ''; if (this.form.discipline_id) { this.form.discipline_id.value = ''; } this.form.submit();">
        <option value="none">&mdash; {{tr}}common-Practitioner{{/tr}}</option>
        {{foreach from=$praticiens item=_praticien}}
          <option value="{{$_praticien->_id}}" {{if $_praticien->_id == $praticien_id}}selected{{/if}}>
            {{$_praticien->_view}}
            {{if $_praticien->adeli && ($_praticien->isSecondary() || $_praticien->_ref_secondary_users|@count)}}
              &mdash; {{mb_value object=$_praticien field=adeli}}
            {{/if}}
          </option>
        {{/foreach}}
      </select>

        <select name="function_id" style="width: 200px;" onchange="this.form.praticien_id.value = 'none'; this.form.service_id.value = ''; if (this.form.discipline_id) { this.form.discipline_id.value = ''; } this.form.submit();">
          <option value="">&mdash; {{tr}}CFunction{{/tr}}</option>
          {{foreach from=$functions item=_function}}
            <option value="{{$_function->_id}}" {{if $_function->_id == $function_id}}selected{{/if}}>{{$_function->_view}}</option>
          {{/foreach}}
        </select>
      {{if 'soins dossier_soins display_filter_functions_discipline'|gconf}}
        <select name="discipline_id" onchange="this.form.praticien_id.value = 'none'; this.form.service_id.value = ''; this.form.function_id.value = ''; this.form.submit();" style="width: 200px;">
          <option value="">&mdash; {{tr}}CDiscipline{{/tr}}</option>
          {{foreach from=$listDisciplines item=curr_disc}}
            <option value="{{$curr_disc->discipline_id}}" {{if $curr_disc->discipline_id == $discipline_id }}selected{{/if}}>
              {{$curr_disc->_view}}
            </option>
          {{/foreach}}
        </select>
      {{/if}}

      <select name="mode" onchange="this.form.submit();">
        <option value="instant" {{if $mode == 'instant'}}selected{{/if}}>{{tr}}Instant view{{/tr}}</option>
        <option value="day" {{if $mode == 'day'}}selected{{/if}}>{{tr}}Day view{{/tr}}</option>
      </select>

      {{if $app->_ref_user->isPraticien() && ($dnow == $date)}}
        <button type="button" class="search" title="{{$visites.non_effectuee|@count}} visite(s) non effectuée(s)" id="visites_jour_prat"
                onclick="seeVisitesPrat();" style="float: right;">
          Mes visites
        </button>
      {{/if}}
      <br />
    {{else}}
      <input type="hidden" name="service_id" value="{{$service_id}}" />
      <input type="hidden" name="praticien_id" value="{{$praticien->_id}}" />
      <input type="hidden" name="function_id" value="{{$function->_id}}" />
    {{/if}}

    {{mb_label class="CSejour" field="_type_admission"}}
    <label>
      <input type="radio" name="_type_admission" value="" {{if !$_sejour->_type_admission}}checked{{/if}} onclick="this.form.submit()" /> Tous types
    </label>
    {{assign var=specs value=$_sejour->_specs._type_admission}}
    {{foreach from=$specs->_list item=_type}}
      <label>
        <input type="radio" name="_type_admission" value="{{$_type}}" {{if $_sejour->_type_admission == $_type}}checked{{/if}} onclick="this.form.submit()" />
        {{tr}}CSejour._type_admission.{{$_type}}{{/tr}}
      </label>
    {{/foreach}}

    {{if $app->_ref_user->isInfirmiere() || $app->_ref_user->isAideSoignant() || $app->_ref_user->isSageFemme() || $app->_ref_user->isKine() || $app->_ref_user->isPraticien()}}
      <label style="float: right;">
        Mes patients ({{$count_my_patient}})
        <input type="hidden" name="my_patient" value="{{$my_patient}}" onchange="this.form.submit();"/>
        <input type="checkbox" name="change_patient" value="{{if $my_patient == 1}}0{{else}}1{{/if}}" {{if $my_patient == 1}}checked{{/if}} onchange="$V(this.form.my_patient, this.checked?1:0);"/>
      </label>
    {{/if}}

    <br/>
    <input type="hidden" name="date" value="{{$date}}" onchange="this.form.submit()" />
  </form>
{{/if}}

{{if "soins Sejour select_services_ids"|gconf && $select_view && !$function_id && !$praticien_id && !$discipline_id}}
  {{foreach from=$services_selected item=sejours key=_service_id name=key_service}}
    {{mb_include module=soins template=inc_list_sejours_global service_id=$_service_id service=$services.$_service_id first=$smarty.foreach.key_service.first}}
  {{/foreach}}
{{else}}
  {{mb_include module=soins template=inc_list_sejours_global}}
{{/if}}

{{if $print}}
  {{mb_include style=mediboard_ext template=close_printable}}
{{/if}}
