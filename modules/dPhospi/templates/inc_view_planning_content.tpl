{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$curr_prat.sejours item=curr_sejour}}
  <tr>
    {{assign var=suffixe value="_content"}}
    {{mb_include module=hospi template=inc_planning/$col1$suffixe}}
    {{mb_include module=hospi template=inc_planning/$col2$suffixe}}
    {{mb_include module=hospi template=inc_planning/$col3$suffixe}}
  </tr>
{{/foreach}}