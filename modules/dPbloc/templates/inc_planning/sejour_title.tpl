{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=colspan_sejour value=3}}
{{if $prestation->_id}}
  {{assign var=colspan_sejour value=$colspan_sejour+1}}
{{/if}}
{{if !$_compact}}
  {{if $_show_comment_sejour}}
    {{assign var=colspan_sejour value=$colspan_sejour+1}}
  {{/if}}
  {{if $_convalescence}}
    {{assign var=colspan_sejour value=$colspan_sejour+1}}
  {{/if}}
{{else}}
  {{if $_show_comment_sejour || $_convalescence}}
    {{assign var=colspan_sejour value=$colspan_sejour+1}}
  {{/if}}
{{/if}}

<th class="title" colspan="{{$colspan_sejour}}">Sejour</th>