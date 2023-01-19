{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == "pregnancy"}}
<table class="main layout">
  <tr>
    <td>
      <span class="type_item circled">
        {{tr}}CGrossesse-date_dernieres_regles{{/tr}}
      </span>
    </td>
  </tr>
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
        {{$item}}
      </span>
    </td>
  </tr>
</table>
{{/if}}

{{if $type == "expected_term"}}
  <table class="main layout">
    <tr>
      <td>
        <span class="type_item circled">
          {{tr}}CGrossesse-terme_prevu{{/tr}}
        </span>
      </td>
    </tr>
    <tr>
      <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
        {{$item}}
      </span>
      <br />
      {{tr}}CGrossesse-multiple-desc{{/tr}} : {{mb_value object=$item field=multiple}}
      </td>
    </tr>
  </table>
{{/if}}
