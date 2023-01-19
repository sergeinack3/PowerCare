{{*
 * @package Mediboard\soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=id}}

<script>
  Main.add(() => {
    ExObject.loadExObjects('{{$sejour->_class}}', '{{$sejour->_id}}', '{{$id}}', 0);
  })
</script>

{{if $patient}}
  <table class="tbl me-no-align me-no-box-shadow">
    <tr>
      <th class="title" colspan="2">
        <h2 style="color: #fff; font-weight: bold;">
          <span style="font-size: 0.7em;" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
            {{$patient}}
          </span>
          <span style="font-size: 0.7em;"
                onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')"> - {{tr}}CSejour{{/tr}} {{$sejour->_shortview}}
          </span>
        </h2>
      </th>
    </tr>
  </table>
{{/if}}

<div id="{{$id}}"></div>
