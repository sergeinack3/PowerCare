{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  {{foreach from=$list item=element name='laboratoire'}}
    {{if $element->_class == 'CMondialSanteMessage'}}
      {{mb_include module=mondialSante template=timeline_patient_element}}
    {{elseif $element->_class == 'CMSSanteCDADocument'}}
      {{mb_include module=mssante template=timeline_patient_element}}
    {{/if}}
    {{if !$smarty.foreach.laboratoire.last}}
      <tr>
        <td colspan="2"><hr class="item_separator"/></td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
