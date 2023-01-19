{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="selectBloc" method="get" action="?">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="vw_reveil" />
  <span id="heure">{{$tnow|date_format:$conf.time}}</span> - {{$date|date_format:$conf.longdate}}
  <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
  <select name="bloc_id" onchange="this.form.submit();">
    <option value="" disabled>&mdash; {{tr}}CBlocOperatoire.select{{/tr}}</option>
    {{foreach from=$blocs_list item=_bloc}}
    <option value="{{$_bloc->_id}}" {{if $_bloc->_id == $bloc->_id}}selected{{/if}}>
      {{$_bloc}}
    </option>
    {{foreachelse}}
    <option value="" disabled>{{tr}}CBlocOperatoire.none{{/tr}}</option>
    {{/foreach}}
  </select>

  {{if $conf.dPplanningOp.COperation.use_poste && $bloc->_ref_sspis|@count}}
    <select name="sspi_id" onchange="refreshTabReveil(window.reveil_tabs.activeContainer.id);" style="display: none;">
      <option value="" disabled>&mdash; {{tr}}CSSPI.select{{/tr}}</option>
      {{foreach from=$bloc->_ref_sspis item=_sspi}}
        <option value="{{$_sspi->_id}}" {{if $_sspi->_id == $sspi_id}}selected{{/if}}>{{$_sspi->_view}}</option>
      {{/foreach}}
    </select>
  {{/if}}
</form>

<script>
  Main.add(function() {
    Calendar.regField(getForm("selectBloc").date, null, {noView: true});
  });
</script>
 