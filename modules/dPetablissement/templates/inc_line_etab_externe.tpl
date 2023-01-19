{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr id="{{$_etab->_guid}}-row">
  <td>
    {{mb_value object=$_etab field=nom}}
  </td>
  <td class="narrow">
    {{mb_value object=$_etab field=cp}}
  </td>
  <td class="narrow">
    {{mb_value object=$_etab field=ville}}
  </td>
  <td class="narrow">
    {{mb_value object=$_etab field=finess}}
  </td>
  <td class="narrow {{if !$selected}}button{{/if}}">
       <button onclick="Group.editCEtabExterne('{{$_etab->_id}}', '{{$selected}}', Group.reloadEtabExterneLine.curry('{{$_etab->_guid}}', '{{$selected}}'));" title="{{tr}}Edit{{/tr}}">
        <i class="fas fa-edit" style="font-size: 1.2em;"></i>
      </button>
    {{if $selected}}
      <button onclick="Group.selectEtabExterne(this);"
              data-id="{{$_etab->_id}}" data-nom="{{$_etab->nom}}"
              data-adresse="{{$_etab->adresse}}"
              data-adresse_complet="{{$_etab->adresse}} {{$_etab->cp}} {{$_etab->ville}}">
        <i class="fas fa-check" style="color: forestgreen;"></i> {{tr}}common-action-Select{{/tr}}
      </button>
    {{/if}}
  </td>
</tr>
