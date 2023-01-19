{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<p style="font-weight: bold; font-size: 1.1em;">
  {{$ex_object->_ref_ex_class->name}}
</p>
<hr style="border-color: #333; margin: 4px 0;" />
{{mb_include module=forms template=inc_vw_ex_object ex_object=$ex_object}}
