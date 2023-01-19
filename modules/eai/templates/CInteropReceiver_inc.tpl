{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th>{{mb_label object=$actor field="use_specific_handler"}}</th>
  <td>{{mb_field object=$actor field="use_specific_handler"}}</td>
</tr>

<tr>
  <th>{{mb_label object=$actor field="OID"}}</th>
  <td>{{mb_field object=$actor field="OID"}}</td>
</tr>

<tr>
  <th>{{mb_label object=$actor field="synchronous"}}</th>
  <td>{{mb_field object=$actor field="synchronous"}}</td>
</tr>

<tr>
  <th>{{mb_label object=$actor field="monitor_sources"}}</th>
  <td>{{mb_field object=$actor field="monitor_sources"}}</td>
</tr>

{{mb_include module=$actor->_ref_module->mod_name template="`$actor->_class`_inc"}}
