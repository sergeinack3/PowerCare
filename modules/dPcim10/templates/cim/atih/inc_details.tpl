{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $code|instanceof:'Ox\Mediboard\Cim10\Atih\CCodeCIM10ATIH'}}
  {{mb_include module=cim10 template=cim/atih/inc_details_code}}
{{elseif $code|instanceof:'Ox\Mediboard\Cim10\Atih\CCIM10CategoryATIH'}}
  {{mb_include module=cim10 template=cim/atih/inc_details_category category=$code}}
{{/if}}