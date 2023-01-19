{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_duree_prevue        value=0}}
{{mb_default var=_duree_prevue_heure  value=0}}
{{mb_default var=show_sejour_multiple value=1}}
{{mb_default var=form_name            value="editSejour"}}
{{mb_default var=rank                 value=""}}

{{if "maternite"|module_active}}
  {{if !$sejour->_id && $sejour->grossesse_id && !$_duree_prevue && !$_duree_prevue_heure}}
    {{assign var=_duree_prevue value=$sejour->_duree_prevue}}
    {{if $_duree_prevue == ""}}
      {{assign var=_duree_prevue value=0}}
    {{/if}}
    {{assign var=_duree_prevue_heure value=$sejour->_duree_prevue_heure}}
  {{/if}}
{{/if}}

{{mb_default var=heure_entree_jour value="dPplanningOp CSejour default_hours heure_entree_jour"|gconf}}
{{mb_default var=min_entree_jour   value="dPplanningOp CSejour default_hours min_entree_jour"|gconf}}
{{mb_default var=heure_sortie_ambu value="dPplanningOp CSejour default_hours heure_sortie_ambu"|gconf}}

<tr>
  <th>{{mb_label object=$sejour field="_date_entree_prevue"}}</th>
  <td colspan="2">
    <input type="hidden" name="_rank_sejour_multiple" value="{{$rank}}" />
    <script>
      Main.add(function() {
        var form = getForm("{{$form_name}}");
        var dates = {
          current: {
            start: "{{$sejour->_date_entree_prevue}}",
            stop: "{{$sejour->_date_sortie_prevue}}"
          },
          spots: []
        };

        {{assign var=dhe_date_min value='dPplanningOp CSejour dhe_date_min'|gconf}}
        {{assign var=dhe_date_max value='dPplanningOp CSejour dhe_date_max'|gconf}}
        {{if $dhe_date_min || $dhe_date_max}}
        dates.limit = {};
        {{if $dhe_date_min}}
        dates.limit.start = '{{$dhe_date_min|iso_date}}';
        {{/if}}
        {{if $dhe_date_max}}
        dates.limit.stop = '{{$dhe_date_max|iso_date}}';
        {{/if}}
        {{/if}}

        // Object.value takes the internal functions too :(
        var dates_operations = {{$sejour->_dates_operations|@json}};
        $H(dates_operations).each(function(p){
          if (!Object.isFunction(p.value))
            dates.spots.push(p.value);
        });

        Calendar.regField(form.entree_reelle, dates);
        Calendar.regField(form.sortie_reelle, dates);

        // Constraints make intervention moving fastidious
        //  dates.limit = {
        //    start: null,
        //    stop: dates.spots.first()
        //  };

        Calendar.regField(form._date_entree_prevue, dates);

        // Constraints make intervention moving fastidious
        //  dates.limit = {
        //    start: dates.spots.last(),
        //    stop: null
        //  };

        Calendar.regField(form._date_sortie_prevue, dates);
      });
    </script>

    {{mb_field object=$sejour form=$form_name field=_date_entree_prevue canNull=false
               onchange="Value.synchronize(this, 'editSejourEasy', false); OccupationServices.updateOccupation(); modifSejour(this.form); updateSortiePrevue(this.form); reloadSejours();"}}
    à
    <select name="_hour_entree_prevue" onchange="Value.synchronize(this, 'editSejourEasy', false); updateHeureSortie(this.form); checkHeureSortie(this.form); reloadSejours();">
      {{foreach from=$conf.dPplanningOp.CSejour.heure_deb|range:$conf.dPplanningOp.CSejour.heure_fin item=hour}}
        <option value="{{$hour}}" {{if $sejour->_hour_entree_prevue == $hour || (!$sejour->_id && $hour == $heure_entree_jour)}}selected{{/if}}>{{$hour}}</option>
      {{/foreach}}
    </select> h
    <select name="_min_entree_prevue" onchange="Value.synchronize(this, 'editSejourEasy', false); updateHeureSortie(this.form); checkHeureSortie(this.form); reloadSejours();">
      {{foreach from=0|range:59:$conf.dPplanningOp.CSejour.min_intervalle item=min}}
        <option value="{{$min}}" {{if $sejour->_min_entree_prevue == $min || (!$sejour->_id && $min <= $min_entree_jour)}}selected{{/if}}>{{$min}}</option>
      {{/foreach}}
    </select> min

    {{if !$sejour->_id && !$mode_operation && $show_sejour_multiple}}
      <button type="button" class="agenda notext me-tertiary" onclick="modalMultipleSejours();">Séjours multiples</button>
    {{/if}}
  </td>
  <td>
    {{if $can->admin}}
      (admin: {{mb_value object=$sejour field=entree_prevue}})
    {{/if}}
  </td>
</tr>

<tr>
  <th>{{mb_label object=$sejour field="_duree_prevue"}}</th>
  <td colspan="3" style="vertical-align: middle">
    {{mb_field object=$sejour field="_duree_prevue" increment=true form=$form_name prop="num min|0" size=2 onchange="Value.synchronize(this, 'editSejourEasy', false); updateSortiePrevue(this.form); checkDureeHospi('syncType'); \$('jours_prevus`$rank`').update(parseInt(this.value)+1)" value=$sejour->_id|intval|ternary:$sejour->_duree_prevue:$_duree_prevue}}
    {{tr}}night{{/tr}}(s)
    <span class="duree_prevue_view" {{if $sejour->_duree_prevue >0}}style="display: none;"{{/if}}>
      {{mb_field object=$sejour field="_duree_prevue_heure" increment=true form=$form_name size=2 value=$sejour->_id|ternary:$sejour->_duree_prevue_heure:$_duree_prevue_heure onchange="updateHeureSortie(this.form);"}}
      {{tr}}hour{{/tr}}(s)
    </span>
    - (<span id="dureeEst"></span>)
    <span {{if !"dPplanningOp CSejour fields_display show_days_duree"|gconf}}style="display: none"{{/if}}>
      <span id="jours_prevus{{$rank}}">{{math equation=x+1 x=$sejour->_id|ternary:$sejour->_duree_prevue:$_duree_prevue}}</span> {{tr}}day{{/tr}}(s)
    </span>
    {{if $sejour->entree_reelle && $sejour->sortie_reelle}}
      {{mb_field object=$sejour field=_duree_reelle hidden=true}}
      <input type="hidden" name="_date_entree_reelle" value="{{$sejour->entree_reelle|date_format:"%Y-%m-%d"}}" />
      <input type="hidden" name="_date_sortie_reelle" value="{{$sejour->sortie_reelle|date_format:"%Y-%m-%d"}}" />
    {{/if}}
  </td>
</tr>

<tr>
  <th>{{mb_label object=$sejour field="_date_sortie_prevue"}}</th>
  <td colspan="2">
    {{mb_field object=$sejour form=$form_name field=_date_sortie_prevue canNull=false
    onchange="Sejour.alertSortiePrevue(this); Value.synchronize(this, 'editSejourEasy', false); updateDureePrevue(this.form); modifSejour(this.form); reloadSejours();"}}
    à

    <select name="_hour_sortie_prevue" onchange="updateDureePrevueHeure(this.form); Value.synchronize(this, 'editSejourEasy', false); reloadSejours();">
      {{foreach from=$conf.dPplanningOp.CSejour.heure_deb|range:$conf.dPplanningOp.CSejour.heure_fin item=hour}}
        <option value="{{$hour}}" {{if $sejour->_hour_sortie_prevue == $hour || (!$sejour->_id && $hour == $heure_sortie_ambu)}}selected{{/if}}>{{$hour}}</option>
      {{/foreach}}
    </select> h
    <select name="_min_sortie_prevue"  onchange="Value.synchronize(this, 'editSejourEasy', false); reloadSejours();">
      {{foreach from=0|range:59:$conf.dPplanningOp.CSejour.min_intervalle item=min}}
        <option value="{{$min}}" {{if $sejour->_min_sortie_prevue == $min}}selected{{/if}}>{{$min}}</option>
      {{/foreach}}
    </select> min
  </td>
  <td>
    {{if $can->admin}}
      (admin: {{mb_value object=$sejour field=sortie_prevue}})
    {{/if}}
  </td>
</tr>
