{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Psycho-social-conclusion-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="title me-text-align-center" colspan="2">Conclusion</th>
    </tr>
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=situation_accompagnement}}</th>
      <td>
        {{mb_field object=$dossier field=situation_accompagnement
        style="width: 12em;" emptyLabel="CDossierPerinat.situation_accompagnement."}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$dossier field=rques_accompagnement}}</th>
      <td>
        {{if !$print}}
          {{mb_field object=$dossier field=rques_accompagnement form=Psycho-social-conclusion-`$dossier->_guid`}}
        {{else}}
          {{mb_value object=$dossier field=rques_accompagnement}}
        {{/if}}
      </td>
    </tr>
  </table>
</form>
