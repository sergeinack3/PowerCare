{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$print}}
  <form name="Tox-dossier-{{$dossier->_guid}}" method="post"
        onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$dossier}}
    {{mb_key   object=$dossier}}
    <input type="hidden" name="_count_changes" value="0" />
    <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
    <table class="form me-no-box-shadow me-no-align">
      <tr>
        <th class="title me-padding-left-0" colspan="2">{{mb_label object=$dossier field=rques_toxico}}</th>
      </tr>
      <tr>
        <td colspan="2" class="me-padding-left-0">
          {{mb_field object=$dossier field=rques_toxico form=Tox-dossier-`$dossier->_guid`}}
        </td>
      </tr>
    </table>
  </form>
{{else}}
  <table class="form me-no-box-shadow me-no-align">
    <tr>
      <th class="title me-padding-left-0" colspan="2">{{mb_label object=$dossier field=rques_toxico}}</th>
    </tr>
    <tr>
      <td colspan="2" class="me-padding-left-0">
        {{mb_value object=$dossier field=rques_toxico}}
      </td>
    </tr>
  </table>
{{/if}}

