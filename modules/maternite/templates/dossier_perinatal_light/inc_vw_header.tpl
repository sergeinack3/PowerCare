{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=css value=""}}

<table class="main" style="{{$css}}">
  <tr>
    <th class="title">
      <div class="container_header">
        <div class="">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">{{$patient}}</span>
          <br />
          <span class="text_info_patiente grey_color_info_patiente">
            {{mb_value object=$patient field=_age}} - {{mb_value object=$patient field=naissance}}
          </span>
        </div>

        <div class="item_atcd" style="display:inline-block; max-height: 15px;">
          {{mb_include module=patients template=vw_antecedents_allergies}}
        </div>

        <div class="">
          <span class="label_header">{{mb_label object=$grossesse field=terme_prevu}}</span>
          <br />
          {{mb_value object=$grossesse field=terme_prevu}}
        </div>
        <div>
          {{if !$grossesse->datetime_accouchement}}
          <span class="label_header">
            {{tr}}CDepistageGrossesse-_sa-court{{/tr}}
          </span>
            <br />
            {{mb_value object=$grossesse field=_semaine_grossesse}} {{tr}}CDepistageGrossesse-_sa-court{{/tr}} +
            {{mb_value object=$grossesse field=_reste_semaine_grossesse}} j
          {{else}}
            <span class="label_header">{{tr}}CDossierPerinat-accouchement{{/tr}}</span>
            <br />
            {{mb_value object=$grossesse field=datetime_accouchement}}
          {{/if}}
        </div>
      </div>
    </th>
  </tr>
</table>
