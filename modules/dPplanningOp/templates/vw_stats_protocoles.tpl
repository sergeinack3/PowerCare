{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=stat_protocole register=true}}
{{mb_script module=planningOp script=operation      register=true}}

<script>
  Main.add(function() {
    StatProtocole.form = getForm('filterStats');
    StatProtocole.refreshStats();

    Calendar.regField(StatProtocole.form.debut_stat);
    Calendar.regField(StatProtocole.form.fin_stat);
  });
</script>

<form name="filterStats" method="get">
  <table class="form">
    <tr>
      <th>
        {{tr}}Date{{/tr}}
      </th>
      <td>
        <input type="hidden" name="debut_stat" value="{{$debut_stat}}" onchange="StatProtocole.refreshStats();" />
        &raquo;
        <input type="hidden" name="fin_stat" value="{{$fin_stat}}" onchange="StatProtocole.refreshStats();" />
      </td>
      <th><label for="chir_id" title="Filtrer les protocoles d'un praticien">Praticien</label></th>
      <td>
        <select name="chir_id" style="width: 20em;"
                onchange="if (this.form.function_id || this.form.libelle) { $V(this.form.libelle, '', false); this.form.function_id.selectedIndex = 0; } StatProtocole.refreshStats();">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$praticiens item=_prat}}
              <option class="mediuser" style="border-color: #{{$_prat->_ref_function->color}}; {{if !$_prat->_count_protocoles}}color: #999;{{/if}}"
                      value="{{$_prat->user_id}}" {{if ($chir_id == $_prat->user_id) && !$function_id}}selected{{/if}}>
                  {{$_prat}} ({{$_prat->_count_protocoles}})
              </option>
            {{/foreach}}
        </select>
      </td>
      <th><label for="function_id" title="Filtrer les protocoles d'une fonction">Fonction</label></th>
      <td>
        <select name="function_id" style="width: 20em;"
                onchange="if (this.form.chir_id || this.form.libelle ) { $V(this.form.libelle, '', false); this.form.chir_id.selectedIndex = 0; } StatProtocole.refreshStats();">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$functions item=_function}}
              <option class="mediuser" style="border-color: #{{$_function->color}}; {{if !$_function->_count_protocoles}}color: #999;{{/if}}"
                      value="{{$_function->_id}}" {{if $_function->_id == $function_id}}selected{{/if}}>
                  {{$_function}} ({{$_function->_count_protocoles}})
              </option>
            {{/foreach}}
        </select>
      </td>
    </tr>
  </table>
</form>

<div id="result_stats"></div>