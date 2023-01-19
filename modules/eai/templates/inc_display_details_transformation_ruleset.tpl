{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="2" class="title">
      {{tr}}CTransformationRuleSet.details{{/tr}}
    </th>
  </tr>
  <tr>
    <th>{{mb_label object=$transf_ruleset field="name"}}</th>
    <th>{{mb_label object=$transf_ruleset field="description"}}</th>
  </tr>
  {{if $transf_ruleset->_id !== null}}
  <tr>
    <td>{{$transf_ruleset->name}}</td>
    <td>{{$transf_ruleset->description}}</td>
  </tr>
  {{else}}
    <tr>
      <td colspan="2">{{tr}}mod-eai-tab-please_select_ruleset{{/tr}}</td>
    </tr>
  {{/if}}
</table>