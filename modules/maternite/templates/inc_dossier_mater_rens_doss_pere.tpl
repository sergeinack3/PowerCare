{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Pere-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />
  <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=activite_pro_pere}}</th>
      <td>
        {{mb_field object=$dossier field=activite_pro_pere
        style="width: 12em;" emptyLabel="CDossierPerinat.activite_pro_pere."}}
      </td>
    </tr>
  </table>
</form>