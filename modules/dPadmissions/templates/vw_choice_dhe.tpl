{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="chooseDHE">
  <form name="print_filter" method="get" style="text-align: center" onsubmit="">
    <!-- print -->
    <input type="hidden" name="op_id" value="" />

    <h2>{{tr}}admissions-action-Choose a DHE to print{{/tr}}</h2>
    <p>
      <label>
        {{tr}}admissions-Choice of intervention{{/tr}}
        <select name="choice_dhe" onchange="$V(getForm('print_filter').op_id, this.value);">
          {{foreach from=$sejour->_ref_operations item=_op}}
            <option value="{{$_op->_id}}">{{$_op->_view}} - {{$sejour->_ref_praticien->_view}}</option>
          {{/foreach}}
        </select>
      </label>
      <script>
        Main.add(function () {
          var tab = $$('select[name=choice_dhe] option:first-child');
          getForm('print_filter').op_id.value = tab[0].value;
        });
      </script>
    </p>
    <p><button type="button" onclick="Admissions.printDHE('operation_id', getForm('print_filter').op_id.value); return false;" class="button print">{{tr}}Print{{/tr}}</button>
      <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button> </p>
  </form>
</div>
