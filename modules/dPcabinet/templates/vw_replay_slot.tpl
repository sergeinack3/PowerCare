{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="replay_slot" method="post" onsubmit="Slot.replaySlot(); return false">
  <table class="form">
    <tr>
      <th class="title">{{tr}}CSlot-correct_slot_consult{{/tr}}</th>
    </tr>
    <tr>
      <td>{{tr}}CSlot-do_you_wantcorrect_slot_consult{{/tr}}</td>
    </tr>
    <tr>
      <td class="button">
        <button type="submit" class="tick">{{tr}}Yes{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
