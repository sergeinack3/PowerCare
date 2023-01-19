{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $date_move < $sejour->entree_prevue || $date_move > $sejour->sortie_prevue}}
  <div class="small-warning">
    L'intervention du {{$date_move|date_format:$conf.datetime}} n'est pas dans les bornes du séjour <br/>({{mb_value object=$sejour field=entree_prevue}} &rarr; {{mb_value object=$sejour field=sortie_prevue}})
  </div>
{{/if}}

{{if !$sejour->_id}}
  <div class="small-warning">
    Un nouveau séjour sera créé.
  </div>
{{/if}}

<script type="text/javascript">
  checkDates = function(form) {
    if ($V(form.sortie_prevue) < '{{$date_move}}' || $V(form.entree_prevue) > '{{$date_move}}') {
      alert("La date d'intervention est toujours en dehors des dates prévues du séjour");
      return false;
    }
    return true;
  }
</script>

<form name="editSejour" method="post"
  onsubmit="if (checkDates(this)){
    {{if $callback}}
      return onSubmitFormAjax(this);
    {{else}}
      afterModifSejour(); return false;
    {{/if}}
    }">
  <input type="hidden" name="m" value="dPplanningOp" />
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  <input type="hidden" name="_check_bounds" value="0" />
  {{if $callback}}
    <input type="hidden" name="callback" value="{{$callback}}" />
  {{/if}}
  
  {{mb_key object=$sejour}}
  {{mb_field object=$sejour field=patient_id hidden=1}}
  {{mb_field object=$sejour field=praticien_id hidden=1}}
  {{mb_field object=$sejour field=group_id hidden=1}}
  {{mb_field object=$sejour field=charge_id hidden=1}}
  {{mb_field object=$sejour field=type hidden=1}}
  {{mb_field object=$sejour field=type_pec hidden=1}}
  {{mb_field object=$sejour field=recuse hidden=1}}
  {{mb_field object=$sejour field=annule hidden=1}}
  <table class="form">
    <tr>
      <th colspan="2" class="title">
        Date de l'intervention : {{$date_move|date_format:$conf.datetime}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$sejour field=entree_prevue}}
      </th>
      <td>
        {{mb_field object=$sejour field=entree_prevue form=editSejour register=true}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$sejour field=sortie_prevue}}
      </th>
      <td>
        {{mb_field object=$sejour field=sortie_prevue form=editSejour register=true}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
