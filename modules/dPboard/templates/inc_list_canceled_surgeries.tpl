{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    if ($('tab-canceled-operations')) {
      Control.Tabs.setTabCount('tab-canceled-operations', {{$surgeries|@count}});
    }
  });
</script>

<table class="tbl main me-small">
  <thead>
    <tr>
      <th>{{tr}}CPatient{{/tr}}</th>
      <th>{{tr}}COperation-_prat_id{{/tr}}</th>
      <th>{{tr}}COperation{{/tr}}</th>
      <th>{{tr}}CSejour{{/tr}}</th>
    </tr>
  </thead>

  <tbody>
    {{foreach from=$surgeries item=_surgery}}
      <tr>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_surgery->_ref_patient->_guid}}')">
            {{$_surgery->_ref_patient}}
          </span>
        </td>
        <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_surgery->_ref_chir}}</td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_surgery->_guid}}')">
            {{$_surgery}}
          </span>
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_surgery->_ref_sejour->_guid}}')">
            {{$_surgery->_ref_sejour->_shortview}}
          </span>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty" colspan="4">{{tr}}COperation.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </tbody>
</table>
