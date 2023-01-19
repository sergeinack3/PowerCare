{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(
    function() {
      SejourMultiple.fillSlots();
      {{if $type == 'seances'}}
        var form = getForm('setSeancesFrequency');
        Calendar.regField(form.elements['start']);
        Calendar.regField(form.elements['end']);
        Calendar.regField(form.elements['entree']);
        Calendar.regField(form.elements['sortie']);
      {{/if}}
    }
  );
</script>

<table class="main">
  <tr>
    <th class="title">
      {{if $type == 'seances'}}
        <button type="button" class="agenda notext" style="float: right;" onclick="SejourMultiple.showFrequency();">{{tr}}CSejour-action-add_frequency{{/tr}}</button>
      {{/if}}
      <button type="button" class="add notext"   style="float: right;" onclick="SejourMultiple.addSlot();">{{tr}}CSejour-add_new{{/tr}}</button>
      <button type="button" class="erase notext" style="float: right;" onclick="SejourMultiple.removeSlots()">{{tr}}CSejour-erase_all{{/tr}}</button>
      <button type="button" class="tick"         style="float: right;" onclick="SejourMultiple.validateSlots();">{{tr}}Validate{{/tr}}</button>
      Séjours multiples
    </th>
  </tr>
  <tr>
    <td id="sejours_area"></td>
  </tr>
</table>

{{if $type == 'seances'}}
  {{assign var=heure_entree value="dPplanningOp CSejour default_hours heure_entree_jour"|gconf}}
  {{assign var=heure_sortie value="dPplanningOp CSejour default_hours heure_sortie_ambu"|gconf}}

  <div id="seancesFrequency" style="display: none;">
    <form name="setSeancesFrequency" method="post" action="?" onsubmit="return SejourMultiple.setFrequency(this);">
      <table class="form">
        <tr>
          <th>
            <label for="setSeancesFrequency_entree">
              {{tr}}CSejour-_heure_entree{{/tr}}
            </label>
          </th>
          <td>
            <input type="hidden" name="entree" class="time notNull" value="{{$heure_entree|str_pad:2:0:$smarty.const.STR_PAD_LEFT|cat:':00:00'}}">
          </td>
          <th>
            <label for="setSeancesFrequency_sortie">
              {{tr}}CSejour-_heure_sortie{{/tr}}
            </label>
          </th>
          <td>
            <input type="hidden" name="sortie" class="time notNull" value="{{$heure_sortie|str_pad:2:0:$smarty.const.STR_PAD_LEFT|cat:':00:00'}}">
          </td>
        </tr>
        <tr>
          <th>
            <label for="setSeancesFrequency_start">
              {{tr}}date.From_long{{/tr}}
            </label>
          </th>
          <td>
            <input id="setSeancesFrequency_start" type="hidden" name="start" class="date notNull" value="">
          </td>
          <th>
            <label for="setSeancesFrequency_end">
              {{tr}}date.To_long{{/tr}}
            </label>
          </th>
          <td>
            <input id="setSeancesFrequency_end" type="hidden" name="end" class="date notNull" value="">
          </td>
        </tr>
        <tr>
          <th>
            <label for="setSeancesFrequency_end_frequency">
              {{tr}}Frequency{{/tr}}
            </label>
          </th>
          <td>
            {{tr}}Every{{/tr}} <input id="setSeancesFrequency_end_frequency" type="text" class="notNull" name="frequency" value="" size="2"> {{tr}}common-days{{/tr}}
          </td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td class="button" colspan="4">
            <button type="button" class="tick" onclick="this.form.onsubmit();">
              {{tr}}Apply{{/tr}}
            </button>
          </td>
        </tr>
      </table>
    </form>
  </div>
{{/if}}