{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Sejour -->
<th>Hospi</th>
<th>Entrée</th>
{{if !$_compact}}
  <th>Chambre</th>
{{else}}
  <th>Lit</th>
{{/if}}
{{if $prestation->_id}}
  <th>{{$prestation}}</th>
{{/if}}
{{if !$_compact}}
  {{if $_show_comment_sejour}}
    <th>Rques</th>
  {{/if}}
  {{if $_convalescence}}
    <th>Conva.</th>
  {{/if}}
{{else}}
  {{if $_show_comment_sejour && $_convalescence}}
    <th>Rques / Conva.</th>
  {{elseif $_show_comment_sejour}}
    <th>Rques</th>
  {{elseif $_convalescence}}
    <th>Conva.</th>
  {{/if}}
{{/if}}
