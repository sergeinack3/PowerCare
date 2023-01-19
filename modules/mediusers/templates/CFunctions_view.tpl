{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=function value=$object}}

<table class="tbl tooltip">
  <tr>
    <th class="title text">
      {{mb_include module=system template=inc_object_idsante400 object=$function}}
      {{mb_include module=system template=inc_object_history object=$function}}
      {{*mb_include module=system template=inc_object_notes object=$function*}}
      {{$function}}
    </th>
  </tr>
  {{if $function->soustitre}}
  <tr>
    <td>
      <strong>{{mb_value object=$function field=soustitre}}</strong>
    </td>
  </tr>
  {{/if}}
  <tr>
    <td>
      <strong>{{mb_value object=$function->_ref_group}}</strong>
    </td>
  </tr>
  {{if $function->adresse}}
  <tr>
    <td>
      {{mb_value object=$function field=adresse}}
    </td>
  </tr>
  {{/if}}
  {{if $function->cp || $function->ville}}
  <tr>
    <td>
      {{mb_value object=$function field=cp}} {{mb_value object=$function field=ville}}
    </td>
  </tr>
  {{/if}}
  <tr>
    <td>
      {{mb_label object=$function field=tel}} :
      {{mb_value object=$function field=tel}}
    </td>
  </tr>
  <tr>
    <td>
      {{mb_label object=$function field=fax}} :
      {{mb_value object=$function field=fax}}
    </td>
  </tr>

  <tr>
    <td>
      {{mb_label object=$function field=siret}} :
      {{mb_value object=$function field=siret}}
    </td>
  </tr>
</table>

