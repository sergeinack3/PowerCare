{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  endowmentDuplicateCallback = function (id) {
    getForm("filter-endowments").onsubmit();
    Control.Modal.close();
  }
</script>

<form name="duplicate_endowment" action="" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$endowment}}
  {{mb_key   object=$endowment}}
  <input type="hidden" name="callback" value="endowmentDuplicateCallback" />
  <table class="form">
    <tr>
      <th class="title" colspan="2">Duplication de la dotation {{$endowment}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$endowment field="_duplicate_to_service_id"}}</th>
      <td>
        <select name="_duplicate_to_service_id" class="{{$endowment->_props._duplicate_to_service_id}}">
          {{foreach from=$groups item=_group}}
            <optgroup label="{{$_group->_view}}">
              {{foreach from=$_group->_ref_services item=_service}}
                <option value="{{$_service->_id}}">{{$_service}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="duplicate" type="submit">{{tr}}Duplicate{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>