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

{{mb_include module=system template=CMbObject_view}}

{{assign var=element_to_presta_ssr value=$object}}
{{assign var=presta_ssr            value=$element_to_presta_ssr->_ref_presta_ssr}}

<table class="tooltip tbl">
  <tr>
    <td class="text">
      <strong>
        {{tr}}CPrestaSSR-libelle-court{{/tr}}
      </strong>:
      {{$presta_ssr->libelle}}
    </td>
  </tr>
  <tr>
    <td class="text">
      <strong>
        {{tr}}CPrestaSSR-type-court{{/tr}}
      </strong>:
      {{$presta_ssr->type}}
    </td>
  </tr>
  <tr>
    <td class="text">
      <strong>
        {{tr}}CPrestaSSR-description-court{{/tr}}
      </strong>:
      {{$presta_ssr->description|smarty:nodefaults}}
    </td>
  </tr>
</table>
