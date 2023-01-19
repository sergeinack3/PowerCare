{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="replay_consultation_slot" method="post" onsubmit="Slot.replayConsultationToSlot(); return false">
  <table class="form">
    <tr>
      <th class="title">{{tr}}CSlot-correct_link_consultations_slot{{/tr}}</th>
    </tr>
    <tr>
      <td>{{tr}}CSlot-do_you_want_correct_link_consultations_slot{{/tr}}</td>
    </tr>
    <tr>
      <td class="button">
        <button type="submit" class="tick">{{tr}}Yes{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
