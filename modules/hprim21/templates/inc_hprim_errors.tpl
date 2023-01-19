{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=level value=""}}

<table class="main tbl">
  <tr>
    <th>Ligne</th>
    <th>Entité</th>
    <th></th>
    <th></th>
  </tr>
  {{foreach from=$errors item=_error}}
    {{if $level && ($level == $_error->level)}}
      <tr>
        <td class="narrow">
          {{$_error->line}}
        </td>
        <td class="narrow">
          {{if $_error->entity}}
            <pre style="border: none;">{{$_error->entity->getPathString()}}</pre>
          {{/if}}
        </td>
        <td>
          {{if $_error->code|is_numeric}}
            {{tr}}CHL7v2Exception-{{$_error->code}}{{/tr}}
          {{else}}
            {{$_error->code}}
          {{/if}}
        </td>
        <td>
          {{$_error->data}}
        </td>
      </tr>
    {{/if}}
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">Aucune erreur</td>
    </tr>
  {{/foreach}}
</table>