{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_script module=ssr script=csarr ajax=1}}

{{mb_include module=system template=CMbObject_view}}

{{assign var=acte       value=$object}}
{{assign var=activite   value=$acte->_ref_activite_csarr}}
{{assign var=hierarchie value=$activite->_ref_hierarchie}}

<table class="tooltip tbl">
  <tr>
    <td class="text">
      {{mb_include module=system template=inc_field_view object=$activite  prop=libelle}}
      <strong>
        {{mb_label object=$activite field=hierarchie}}
        {{mb_value object=$activite field=hierarchie}}
      </strong>:
      {{mb_value object=$hierarchie field=libelle}}
    </td>
  </tr>
  <tr>
    <td class="button">
      <button class="search" onclick="CsARR.viewActivite('{{$activite->code}}')">{{tr}}CActeCsARR-details_code{{/tr}}</button>
    </td>
  </tr>
</table>
