{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-import-campaign" method="post" onsubmit="return onSubmitFormAjax(this, {onSuccess: function() {Control.Modal.close();}})">
  {{mb_class object=$campaign}}
  {{mb_key   object=$campaign}}
  <input type="hidden" name="del" value=""/>

  <table class="main form">
    <tr>
      <th>{{mb_title object=$campaign field=name}}</th>
      <th>{{mb_field object=$campaign field=name}}</th>
    </tr>

    {{if ($campaign->_id)}}
      <tr>
        <th>{{mb_title object=$campaign field=creation_date}}</th>
        <th>{{mb_field object=$campaign field=creation_date register=true form='edit-import-campaign'}}</th>
      </tr>

      <tr>
        <th>{{mb_title object=$campaign field=closing_date}}</th>
        <th>{{mb_field object=$campaign field=closing_date register=true form='edit-import-campaign'}}</th>
      </tr>
    {{/if}}

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>