{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $sumbit_only}}
  <script>
    Main.add(function () {
      ChoiceLit.retourBox('{{$rpu->_ref_reservation->lit_id}}');
    });
  </script>
{{/if}}
{{assign var=use_reservation_box value="dPurgences Placement use_reservation_box"|gconf}}

<form name="Choice_lit" method="post" onsubmit="return ChoiceLit.finish(this.lit_id.value, 1);">
  <input type="hidden" name="chambre_id" value="{{$chambre->_id}}" />

  {{if !$vue_hospi && $use_reservation_box}}
    <table class="main form">
      <tr>
        <th class="category">
          {{tr}}CRPUReservationBox{{/tr}}
          {{if $rpu->_ref_reservation->_id}}
            :
            <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_ref_reservation->_ref_lit->_guid}}')">
              {{$rpu->_ref_reservation->_ref_lit->_view}}
            </span>
          {{/if}}
        </th>
      </tr>
      {{if !$rpu->_ref_reservation->_id}}
        <tr>
          <th>
            {{tr}}CRPUReservationBox.message_resa{{/tr}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_ref_box->_guid}}')">{{$rpu->_ref_box->_view}}</span>?
          </th>
        </tr>
        <tr>
          <td class="button">
            <input type="radio" name="choice_resa" value="1"
                   onchange="ChoiceLit.addReservation('{{$rpu->box_id}}', false);" />{{tr}}Yes{{/tr}}
            <input type="radio" name="choice_resa" value="0" checked="checked"
                   onchange="ChoiceLit.addReservation(null, false);" />{{tr}}No{{/tr}}
          </td>
        </tr>
      {{else}}
        <tr>
          <td class="button">
            <button type="button" class="send"
                    onclick="ChoiceLit.retourBox('{{$rpu->_ref_reservation->lit_id}}');">{{tr}}CRPUReservationBox.retour_box{{/tr}}</button>
          </td>
        </tr>
      {{/if}}
    </table>
  {{/if}}

  <table class="main form">
    <tr>
      <th class="title" colspan="2">{{tr}}CAffectation{{/tr}}: {{$patient->_view}}</th>
    </tr>
    <tr>
      <td>{{tr}}CChambre{{/tr}}</td>
      <td>{{mb_value object=$chambre field=nom}}</td>
    </tr>
    <tr>
      <td>{{mb_title class=CLit field=nom}}</td>
      <td>
        <select name="lit_id">
          {{if $chambre->_ref_lits|@count > 1}}
            <option value="-1">&mdash; Choisir un lit</option>
          {{/if}}
          {{foreach from=$chambre->_ref_lits item=_lit}}
            {{assign var=lit_id value=$_lit->_id}}
            {{assign var=lit_reserve value=false}}
            {{if $reservations_box|@count && isset($reservations_box.$lit_id|smarty:nodefaults)}}
              {{assign var=lit_reserve value=true}}
            {{/if}}
            <option value="{{$lit_id}}"
              {{if !$chambre->is_waiting_room &&
            ((isset($affectations.$lit_id|smarty:nodefaults) && $affectations.$lit_id && !$is_mater) || $lit_reserve)}}
              disabled
              {{/if}}>
              {{$_lit->nom}} {{if $lit_reserve}}({{tr}}CLit-back-reservation_box{{/tr}}){{/if}}
            </option>
          {{/foreach}}
          {{if !$vue_hospi && $use_reservation_box && $rpu->_ref_reservation->_id}}
            <option value="{{$rpu->_ref_reservation->lit_id}}" style="display: none;">
              {{$rpu->_ref_reservation->_ref_lit->_view}}
            </option>
          {{/if}}
        </select>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="submit" onclick="return ChoiceLit.finish(this.form.lit_id.value, 1);"
                {{if !$nb_lit_dispo}}disabled{{/if}}>{{tr}}Save{{/tr}}</button>
        {{if !$vue_hospi && $use_reservation_box}}
          <button type="button" class="cancel" onclick="ChoiceLit.modal.close();">{{tr}}Close{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>