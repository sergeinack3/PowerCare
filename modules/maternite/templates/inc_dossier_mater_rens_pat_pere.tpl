{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $grossesse->pere_id}}
  <form name="Pere-{{$pere->_guid}}" method="post"
        onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$pere}}
    {{mb_key   object=$pere}}
    <input type="hidden" name="_count_changes" value="0" />
    <table class="form me-no-box-shadow me-no-align me-small-form">
      <tr>
        <th class="halfPane">{{mb_label object=$pere field=pays}}</th>
        <td>{{mb_field object=$pere field=pays}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$pere field=_pays_naissance_insee}}</th>
        <td>{{mb_field object=$pere field=_pays_naissance_insee}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$pere field=niveau_etudes}}</th>
        <td>
          {{mb_field object=$pere field=niveau_etudes
          style="width: 12em;" emptyLabel="CPatient.niveau_etudes."}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$pere field=profession}}</th>
        <td>{{mb_field object=$pere field=profession style="width: 10em;" }}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$pere field=csp}}</th>
        <td>{{mb_field object=$pere field=csp}}</td>
      </tr>
    </table>
  </form>
{{else}}
  <table class="form me-no-box-shadow me-no-align">
    <tr>
      <td colspan="2" class="empty">
        Père non renseigné
      </td>
    </tr>
  </table>
{{/if}}