{{*
 * @package Mediboard\Hl7
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

<table class="tbl tooltip">
  <tr>
    <td class="text">
      {{foreach from=$object->_observations item=_observation}}
        <strong>Code :</strong> {{$_observation.code}} <br />
        <strong>Libelle :</strong> {{$_observation.libelle}} <br />
        <strong>Commentaire :</strong> {{$_observation.commentaire}} <br />
      {{/foreach}}
    </td>
  </tr>
</table>

