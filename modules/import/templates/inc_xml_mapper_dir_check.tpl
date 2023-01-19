{{*
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=dir_ok value=false}}

{{if !$dir_ok}}
  <div class="small-error">
    {{tr}}XmlMapperBuilder-Error-Directory is mandatory{{/tr}}
  </div>

{{else}}

  <div class="small-info">
    {{tr}}XmlMapperBuilder-Msg-Import dir{{/tr}} : {{$dir_path}}
  </div>
{{/if}}
