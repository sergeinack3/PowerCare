{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=required_uf_soins value="dPplanningOp CSejour required_uf_soins"|gconf}}

<script>
  Main.add(function() {
    {{if $sejour_collision}}
      SuiviGrossesse.toggleFieldsHospitalize('{{$sejour_collision->_id}}');
    {{/if}}
  });
</script>

<form name="hospitaliserPatiente" method="post" onsubmit="submitAll(); submitConsultWithChrono(64); return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="maternite" />
  <input type="hidden" name="dosql" value="hospitalizeParturiente" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />

  {{if $sejour_collision}}
    <input type="hidden" name="sejour_id_merge" value="{{$sejour_collision->_id}}">
    {{mb_field object=$sejour_collision field=praticien_id hidden=true}}
    {{mb_field object=$sejour_collision field=uf_soins_id hidden=true}}
  {{/if}}

  {{mb_include module=planningOp template=inc_choose_sejour_merge_or_futur}}

  <table class="form">
    {{if !$count_collision}}
      {{mb_include module=maternite template=inc_modalites_accouchement}}
    {{/if}}

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="tick" onclick="this.form.onsubmit();">
          {{if $count_collision}}
            {{tr}}Merge{{/tr}}
          {{else}}
            {{tr}}Confirm{{/tr}}
          {{/if}}
        </button>

        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
