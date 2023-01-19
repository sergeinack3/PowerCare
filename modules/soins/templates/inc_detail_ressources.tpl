{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if count($list_ressources)}}
  <table class="layout">
    {{foreach from=$list_ressources key=ressource_id item=nb}}
      <tr>
        {{assign var=ressource value=$ressources.$ressource_id}}
        <td class="me-text-align-center">
          {{if $nb_unites}}
            {{$nb}}
          {{/if}}
          {{$ressource->code}}
        </td>

        {{if $show_cost && $total}}
          <td class="me-text-align-center">
            =
          </td>
          <td class="me-text-align-right">
            {{math equation=x*y x=$nb y=$ressource->cout}}
            {{$conf.currency_symbol|html_entity_decode}}
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  </table>
{{/if}}
