{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  // Notification de l'arrivée du patient
  if (!window.Consultations) {
    Consultations = {
      start: function() {
        window.location.reload();
      }
    };
  }

  putArrivee = function(oForm) {
    var today = new Date();
    oForm.arrivee.value = today.toDATETIME(true);
    onSubmitFormAjax(oForm, { onComplete: Consultations.start } );
  }
</script>

{{if !$board}}
  {{if $canCabinet->read}}
    <script>
      Main.add(function() {
        Calendar.regField(getForm("changeView").date, null, {noView: true});
      });
    </script>
  {{/if}}
  <form name="changeView" action="?" method="get">
    <input type="hidden" name="m" value="{{$current_m}}" />
    <input type="hidden" name="tab" value="{{$tab}}" />
    <table class="form">
      <tr>
        <td colspan="6" style="text-align: left; width: 100%; font-weight: bold; height: 20px;">
          <div style="float: right;">{{$hour|date_format:$conf.time}}</div>
          {{$date|date_format:$conf.longdate}}
          {{if $canCabinet->read}}
          <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
          {{/if}}
        </td>
      </tr>
      <tr>
        {{if $canCabinet->read}}
        <th><label for="vue2" title="Type de vue du planning">Type de vue</label></th>
        <td colspan="5">
          <select name="vue2" onchange="this.form.submit()">
            <option value="0" {{if $vue == "0"}}selected="selected"{{/if}}>Tout afficher</option>
            <option value="1" {{if $vue == "1"}}selected="selected"{{/if}}>Cacher les terminées</option>
          </select>
        </td>
        {{/if}}
      </tr>
    </table>
  </form>
{{/if}}

{{assign var=has_consult value=0}}
{{foreach from=$listPlage item=_plage name=foreach_plage}}
  {{if $_plage->_ref_consultations|@count}}
    {{if $smarty.foreach.foreach_plage.first || !$has_consult}}
      {{assign var=first_consult value=$_plage->_ref_consultations|@first}}
      {{if $first_consult->heure > $heure_min}}
        {{math equation="x-y" x=$first_consult->heure|date_format:"%H" y=$heure_min|date_format:"%H" assign=hour}}
        {{math equation="x-y" x=$first_consult->heure|date_format:"%M" y=$heure_min|date_format:"%M" assign=minutes}}
        {{math equation="(60*$hour+$minutes)" assign=nb_creneaux}}
        <td colspan="{{$nb_creneaux}}"></td>
      {{/if}}
    {{/if}}
    {{assign var=open_td value=1}}
    {{assign var=consultations value=$_plage->_ref_consultations}}
    {{foreach from=$consultations item=_consult name=foreach_consult key=key_consult}}
      {{math equation="x+1" x=$key_consult assign=next_key}}
      
      {{if !$smarty.foreach.foreach_consult.first || $has_consult}}
        {{math equation="x-y" x=$_consult->heure|date_format:"%H" y=$save_hour|date_format:"%H" assign=hour}}
        {{math equation="x-y" x=$_consult->heure|date_format:"%M" y=$save_hour|date_format:"%M" assign=minutes}}
        {{math equation="(60*$hour+$minutes)" assign=nb_creneaux}}
        {{if $nb_creneaux > 0}}
          <td colspan="{{$nb_creneaux}}"></td>
        {{/if}}
      {{/if}}
      
      {{math equation="x*y" x=$_plage->freq|date_format:"%M" y=$_consult->duree assign=duree}}
      {{math equation="x+y" x=$_consult->heure|date_format:"%M" y=$duree assign=minutes}}
      {{math equation="floor(x/60)" x=$minutes assign=heure}}
      {{math equation="x-60*y" x=$minutes y=$heure assign=minutes}}
      {{math equation="x+y" x=$_consult->heure|date_format:"%H" y=$heure assign=heure}}
      {{assign var=save_hour value="$heure:$minutes:00"}}

      {{math equation="(x*y)" x=$_plage->freq|date_format:"%M" y=$_consult->duree assign=nb_creneaux}}
      {{if $open_td}}
        <td colspan="{{$nb_creneaux}}">
      {{/if}}
        <table>
          {{mb_include module=cabinet template=inc_detail_consult}}
        </table>
        
      {{if isset($consultations.$next_key|smarty:nodefaults)}}
        {{assign var=next_consult value=$consultations.$next_key}}
        {{if $next_consult->heure == $_consult->heure}}
          {{assign var=open_td value=0}}
        {{else}}
          {{assign var=open_td value=1}}
          </td>
        {{/if}}
      {{else}}
        </td>
      {{/if}}
      {{assign var=has_consult value=1}}
    {{/foreach}}
  {{else}}
    {{assign var=has_consult value=0}}
      {{assign var=save_hour value=$heure_min}}
  {{/if}}
{{/foreach}}
