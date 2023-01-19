{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  submitReglementSejour = function() {
    getForm('editDepassementInterv').onsubmit();
    getForm('editReglementFraisSejour').onsubmit();
  };
</script>

<form name="editReglementFraisSejour" method="post" action="?" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$sejour}}
  {{mb_key object=$sejour}}
  <table class="form">
    <tr>
      <th>
        {{mb_label object=$sejour field=frais_sejour}}
      </th>
      <td>
        {{mb_field object=$sejour field=frais_sejour}}
      </td>
      <th>
        {{mb_label object=$sejour field=reglement_frais_sejour}}
      </th>
      <td>
        {{mb_field object=$sejour field=reglement_frais_sejour}}
      </td>
    </tr>
  </table>
</form>

<form name="editDepassementInterv" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$sejour->_ref_last_operation}}
  {{mb_key object=$sejour->_ref_last_operation}}

  <table class="form">
    <tr>
      <th>
        {{mb_label object=$sejour->_ref_last_operation field=depassement}}
      </th>
      <td>
        {{mb_field object=$sejour->_ref_last_operation field=depassement}}
      </td>
      <th>
        {{mb_label object=$sejour->_ref_last_operation field=reglement_dh_chir}}
      </th>
      <td>
        {{mb_field object=$sejour->_ref_last_operation field=reglement_dh_chir}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$sejour->_ref_last_operation field=depassement_anesth}}
      </th>
      <td>
        {{mb_field object=$sejour->_ref_last_operation field=depassement_anesth}}
      </td>
      <th>
        {{mb_label object=$sejour->_ref_last_operation field=reglement_dh_anesth}}
      </th>
      <td>
        {{mb_field object=$sejour->_ref_last_operation field=reglement_dh_anesth}}
      </td>
    </tr>
  </table>
</form>

<div style="text-align: center;">
  <button type="button" class="submit" onclick="submitReglementSejour();">{{tr}}Save{{/tr}}</button>
  <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
</div>