{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=enum var=mozaic_disposition values="1x2|2x1|2x2|2x3|3x2|3x3"}}
{{mb_include template=inc_pref spec=bool var=show_file_view}}
{{mb_include template=inc_pref spec=enum var=choose_sort_file_date values="ASC|DESC"}}

{{if "mbHost"|module_active}}
  {{mb_include template=inc_pref spec=bool var=upload_mbhost}}
{{/if}}
