{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if count($actor->_ref_exchanges_sources) > 0}}  
  {{mb_include module=eai template="`$actor->_parent_class`_exchanges_sources_inc"}}
{{/if}}