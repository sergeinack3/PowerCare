{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=id_close     value=""}}
{{mb_default var=with_buttons value=1}}
{{mb_default var=with_save    value=1}}
{{mb_default var=css          value=""}}

<table class="main" style="{{$css}}">
  <tr>
    <th class="title">
      {{$patient}} -
      {{mb_value object=$patient field=_age}} ({{mb_value object=$patient field=naissance}})
      {{if !$grossesse->datetime_accouchement && $grossesse->active}}
        - {{mb_value object=$grossesse field=_semaine_grossesse}} {{tr}}CDepistageGrossesse-_sa-court{{/tr}}
        + {{mb_value object=$grossesse field=_reste_semaine_grossesse}} j
      {{/if}}
      - {{mb_label object=$grossesse field=terme_prevu}} {{tr}}the{{/tr}} {{mb_value object=$grossesse field=terme_prevu}}
      {{if $grossesse->datetime_accouchement}}
        - {{tr}}CDossierPerinat-accouchement{{/tr}} {{tr}}the{{/tr}} {{mb_value object=$grossesse field=datetime_accouchement}}
      {{/if}}
      <span style="display:inline-block;max-height: 15px;">
        {{mb_include module=patients template=vw_antecedents_allergies}}
      </span>
    </th>
  </tr>
  {{if $with_buttons}}
    <tr>
      <td class="button">
        {{if $with_save}}
          <button type="button" class="save not-printable me-small" onclick="submitAllForms(Control.Modal.close);">
            {{tr}}common-action-Save and close{{/tr}}
          </button>
        {{/if}}
        <button type="button" class="close not-printable me-small" id="{{$id_close}}" onclick="Control.Modal.close();">
          {{tr}}Close{{/tr}}
        </button>
      </td>
    </tr>
  {{/if}}
</table>
