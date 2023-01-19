{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=massive_qualifier ajax=$ajax}}
{{mb_script module=ameli script=INSi ajax=$ajax}}

<script>
  Main.add(() => {
    MassiveQualifier.init('{{$patients|smarty:nodefaults|@json_encode}}');
  });
</script>

<div id="massive_qualify_area" class="me-margin-8">
  <div id="massive_qualify_state" class="me-float-right">
    {{tr var1=$patients|@count}}MassiveQualiferService-Patients to treat{{/tr}}
  </div>

  <button class="play notext" onclick="MassiveQualifier.play();"></button>
  <button class="pause notext" disabled onclick="MassiveQualifier.pause();"></button>
  <button class="media_stop notext" disabled onclick="MassiveQualifier.stop();"></button>

  <table class="tbl">
    <tr>
      <th class="narrow">{{tr}}MassiveQualiferService-Elapsed time{{/tr}}</th>
      <td id="elapsed_time"></td>
    </tr>
    <tr>
      <th>{{tr}}MassiveQualiferService-Average treatment time{{/tr}}</th>
      <td id="average_time"></td>
    </tr>
    <tr>
      <th>{{tr}}MassiveQualiferService-Estimated time of end treatment{{/tr}}</th>
      <td id="estimated_end_time"></td>
    </tr>
    <tr>
      <th>{{tr}}MassiveQualiferService-Current patient in progress{{/tr}}</th>
      <td id="current_patient" class="text"></td>
    </tr>
    <tr>
      <th>{{tr}}MassiveQualiferService-Last patient treated{{/tr}}</th>
      <td id="last_patient" class="text"></td>
    </tr>
  </table>

  <progress id="qualifier_progress" value="0" max="100" class="me-w100 me-margin-top-8"></progress>
</div>
