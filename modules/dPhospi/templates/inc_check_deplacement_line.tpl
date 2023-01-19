{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=sejour value=$_sortie->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}
{{mb_default var=show_age_sexe_mvt value=0}}
{{mb_default var=show_hour_anesth_mvt value=0}}

<tr {{if $sejour->recuse == -1}}class="opacity-70"{{/if}}>
  <td class="not-printable">
    {{assign var=_mouv value=$_sortie}}
    {{if $sens == "entrants"}}
      {{assign var=_mouv value=$_sortie->_ref_prev}}
    {{/if}}
    <form name="Edit-{{$sens}}-{{$_mouv->_guid}}" action="?m={{$m}}" method="post"
          onsubmit="
          {{if !$_mouv->effectue}}
            $V(this.sortie, $V(getForm('change-sortie-{{$sens}}-{{$_mouv->_id}}')._sortie));
          {{/if}}
            return onSubmitFormAjax(this, refreshList.curry(null, null, 'mouvements', null));">
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="dosql" value="do_affectation_aed" />
      {{mb_key object=$_mouv}}
      {{mb_field object=$_mouv field=entree hidden=1}}
      {{mb_field object=$_mouv field=sortie hidden=1}}

      {{if $_mouv->effectue}}
        <input type="hidden" name="effectue" value="0" />
        <button type="button" class="cancel" onclick="this.form.onsubmit();"> Annuler</button>
      {{else}}
        <input type="hidden" name="effectue" value="1" />
        <button type="button" class="tick" onclick="this.form.onsubmit();"> Effectuer</button>
      {{/if}}
    </form>
  </td>
  <td class="text">
    {{assign var=sejour value=$_sortie->_ref_sejour}}
    {{assign var=patient value=$sejour->_ref_patient}}
    <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')"
      {{if !$sejour->entree_reelle}} class="patient-not-arrived"{{/if}}>
      {{$patient}}
    </strong>
    <br />
    {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$sejour->_liaisons_for_prestation}}
  </td>
  {{if $show_age_sexe_mvt}}
    <td>
      {{$patient->sexe|strtoupper}}
    </td>
    <td>
      {{mb_value object=$patient field=_age}}
    </td>
  {{/if}}
  <td class="text">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
  </td>
  
  <td class="text">
    <strong onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">{{$sejour->_motif_complet}}</strong>

    {{assign var=next_op value=$sejour->_ref_next_operation}}
    {{if "dPhospi vue_temporelle infos_interv"|gconf && $next_op && $next_op->_id}}
      <div class="compact" style="padding-top: 5px;">
        {{$next_op->_datetime_best|date_format:$conf.date}} {{$next_op->_datetime_best|date_format:$conf.time}} -
        {{mb_value object=$next_op field=temp_operation}} -
        {{mb_value object=$next_op field=type_anesth}}
      </div>
    {{/if}}
  </td>

  {{if $show_hour_anesth_mvt}}
    {{if $sejour->_ref_curr_operation->_id}}
        {{assign var=op value=$sejour->_ref_curr_operation}}
    {{else}}
        {{assign var=op value=$sejour->_ref_last_operation}}
    {{/if}}
    <td>
      {{if $op->_id}}
        {{$op->_datetime_best|date_format:$conf.time}}
      {{/if}}
    </td>
    <td>
      {{if $op->_id}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$op->_ref_anesth}}
      {{/if}}
    </td>
  {{/if}}

  <td class="text {{if $_mouv->effectue && $sens == "sortants"}}arretee{{/if}}">
    {{$_sortie->_ref_lit}}
  </td>
  
  
  <td class="text {{if $_mouv->effectue && $sens == "entrants"}}arretee{{/if}}">
    {{if $sens == "sortants"}}
      {{$_sortie->_ref_next->_ref_lit}}
    {{/if}}
    {{if $sens == "entrants"}}
      {{$_sortie->_ref_prev->_ref_lit}}
    {{/if}}
  </td>
  
  <td>
    {{if $_mouv->effectue}}
      {{$_mouv->sortie|date_format:$conf.time}}
    {{else}}
      <form name="change-sortie-{{$sens}}-{{$_mouv->_id}}">
        <input type="hidden" name="_sortie" class="dateTime notNull" value="{{$_mouv->sortie}}" />
        <script>
          Main.add(function () {
            Calendar.regField(getForm("change-sortie-{{$sens}}-{{$_mouv->_id}}")._sortie);
          });
        </script>
      </form>
    {{/if}}
  </td>
</tr>
