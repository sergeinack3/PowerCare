{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('purge-CPlageOp');
    Calendar.regField(form.elements.purge_start_date);
    form.elements.purge_limit.addSpinner({min: 1});
  });

  purgeSome = function(form, just_count) {
    var url = new Url('bloc', 'do_purge_plagesop', 'dosql');
    url.addElement(form.elements.purge_start_date);
    url.addElement(form.elements.practitioner_id);
    url.addElement(form.elements.purge_limit);

    if (just_count) {
      url.addParam('just_count', 1);
    }

    // Give some rest to server
    var onComplete = $('clean_plage_auto').checked ? purgeSome.curry(form, just_count) : Prototype.emptyFunction;
    url.requestUpdate("resultCleanPlages", {method: 'post', onComplete: function () { onComplete.delay(2); } });
  }
</script>

<div class="small-info">
  {{tr}}CPlageOp-msg-Only CPlageOp without COperation will be removed.{{/tr}}
</div>

<form name="purge-CPlageOp" method="post">
  <table class="form">
    <tr>
      <th>{{tr}}common-Start date{{/tr}}</th>
      <td>
        <input type="hidden" name="purge_start_date" value="{{$purge_start_date}}" class="notNull" />
      </td>

      <td rowspan="3" class="greedyPane" id="resultCleanPlages"></td>
    </tr>

    <tr>
      <th>{{tr}}common-Practitioner{{/tr}}</th>
      <td>
        <select name="practitioner_id" style="width: 14em;">
          <option value="">&mdash; {{tr}}common-Practitioner|all{{/tr}}</option>

          {{foreach from=$practitioners item=_prat}}
            <option value="{{$_prat->_id}}">{{$_prat}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{tr}}bloc-Ranges to be processed at each passage{{/tr}}</th>
      <td>
        <input type="text" name="purge_limit" value="{{$purge_limit}}" size="3" />

        <label>
          {{tr}}common-Auto{{/tr}}
          <input type="checkbox" name="auto" id="clean_plage_auto" />
        </label>

        <button type="button" class="info" onclick="purgeSome(this.form, true);">{{tr}}common-action-Count{{/tr}}</button>
        <button type="button" class="trash" onclick="purgeSome(this.form);">{{tr}}common-action-Purge{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>