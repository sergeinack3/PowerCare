{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th>Nom</th>
  </tr>
  {{foreach from=$modeles_etiquettes item=_modele}}
    <tr>
      <td>
        <button type="button" class="tick notext"
                onclick="
                {{if $custom_function}}
                  {{$custom_function}}('{{$object_class}}', '{{$object_id}}', '{{$_modele->_id}}');
                {{else}}
                  ModeleEtiquette.print('{{$object_class}}', '{{$object_id}}', '{{$_modele->_id}}');
                {{/if}}">
        </button>
      </td>
      <td>
        {{$_modele->nom}}
      </td>
    </tr>
  {{/foreach}}
</table>