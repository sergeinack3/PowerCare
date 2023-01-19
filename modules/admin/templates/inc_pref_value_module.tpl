{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=modtab value="-"|explode:$value}}
{{if count($modtab) == 1}}
  {{tr}}module-{{$modtab.0}}-court{{/tr}}
{{else}}
  {{tr}}mod-{{$modtab.0}}-tab-{{$modtab.1}}{{/tr}}
{{/if}}