{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math assign=colspan equation='x + y' x=1 y=$formulae_ex_classes|@count}}

<table class="main tbl">
  <col class="narrow" />

  <tr>
    <th colspan="2">{{tr}}CPatient{{/tr}} / {{tr}}CExObject{{/tr}}</th>

    {{foreach from=$formulae_ex_classes item=_ex_class}}
      <th class="title">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_ex_class->_guid}}');">
          {{$_ex_class}}
        </span>
      </th>
    {{foreachelse}}
      <td class="empty" colspan="{{$colspan}}">
        {{tr}}CExObject.none{{/tr}}
      </td>
    {{/foreach}}
  </tr>

  {{foreach from=$sejours item=_sejour}}
    {{assign var=_sejour_id value=$_sejour->_id}}

    <tr class="alternate">
      <td style="text-align: left;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
          {{$_sejour->_ref_patient}}
        </span>
      </td>

      <td class="narrow" style="text-align: right;">
        {{$_sejour->_ref_patient->_age}}
      </td>

      {{foreach from=$ex_links_by_class.$_sejour_id item=_ex_object_formulae}}
        {{assign var=_ex_object value=$_ex_object_formulae.ex_object}}

        <td style="text-align: center;">
          {{if $_ex_object}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_ex_object->_guid}}');">
            {{$_ex_object_formulae.result}}
          </span>
          {{/if}}
        </td>
      {{/foreach}}
    </tr>
  {{/foreach}}
</table>