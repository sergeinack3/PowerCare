{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPbloc script=edit_planning}}

<script>
  refreshList = function() {
    var form = getForm('selectFrm');
    var url = new Url('board', 'ajax_vw_month');
    if ($V(form.chir_id)) {
      url.addParam('praticien_id', $V(form.chir_id));
    }
    if ($V(form.function_id)) {
      url.addParam('function_id', $V(form.function_id));
    }
    url.addParam('date', $V(form.date));
    url.requestUpdate('month_calendar_board');
  };

  Main.add(function() {
    var oform = getForm('selectFrm');
    DateFormat.MONTH_NAMES = Control.DatePicker.Language['fr'].months;
    Calendar.regField(oform.date, null, {dateFormat: 'MMM yyyy'});
    refreshList();
  });
</script>

<div class="me-margin-4">
  <form name="selectFrm" action="?" method="get" onsubmit="return false">
    <input type="hidden" name="m" value="{{$m}}" />

    {{if $listPrat|@count}}
      <label for="chir_id">Praticien</label>
      <select name="chir_id" style="width: 20em;" onchange="if (this.form.function_id) {this.form.function_id.selectedIndex=0;} refreshList();">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$listPrat item=curr_prat}}
          <option class="mediuser" style="border-color: #{{$curr_prat->_ref_function->color}};"
            value="{{$curr_prat->user_id}}" {{if ($prat->_id == $curr_prat->user_id) && !$function_id}} selected="selected" {{/if}}>
            {{$curr_prat->_view}}
            {{if $curr_prat->adeli && ($curr_prat->isSecondary() || $curr_prat->_ref_secondary_users|@count)}}
              &mdash; {{mb_value object=$curr_prat field=adeli}}
            {{/if}}
          </option>
        {{/foreach}}
      </select>
    {{/if}}

    {{if $listFunc|@count}}
      <label for="function_id" title="Filtrer les protocoles d'une fonction">Fonction</label>
        <select name="function_id" style="width: 20em;" onchange="if (this.form.chir_id) { this.form.chir_id.selectedIndex=0; } refreshList();">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$listFunc item=curr_function}}
            <option class="mediuser" style="border-color: #{{$curr_function->color}};"
                    value="{{$curr_function->_id}}" {{if $curr_function->_id == $function_id}}selected="selected"{{/if}}>
              {{$curr_function->_view}}
            </option>
          {{/foreach}}
        </select>
    {{/if}}

      <label for="date">Mois</label>
      <input type="hidden" name="date" class="date" value="{{$date}}" onchange="refreshList();" />

      <button type="button" class="change notext" onclick="refreshList();">{{tr}}Refresh{{/tr}}</button>
  </form>
</div>

<div id="month_calendar_board" style="width: 100%;" class="me-align-auto">
</div>

<script>
  //$("month_calendar_board").fixedTableHeaders(1.0);
  ViewPort.SetAvlHeight("month_calendar_board", 1.0);
</script>