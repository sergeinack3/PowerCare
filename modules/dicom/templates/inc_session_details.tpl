{{*
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-sessions-dicom', true));
</script>

<ul id="tabs-sessions-dicom" class="control_tabs">
  {{foreach from=$session->_messages key=_type item=_pdu}}
    <li>
      <a href="#{{$_type}}">
        {{$_type}}
      </a>
    </li>
  {{/foreach}}
</ul>

{{foreach from=$session->_messages key=_type item=_pdu}}
  <div id="{{$_type}}" style="display: none;">
    <table class="tbl">
      <tr>
        <td>
          {{$_pdu->toString()}}
        </td>
      </tr>
    </table>
  </div>
{{/foreach}}