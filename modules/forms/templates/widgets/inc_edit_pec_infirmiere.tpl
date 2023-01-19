{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editRPU" method="POST" onsubmit="return onSubmitFormAjax(this);">
    {{mb_key object=$rpu}}
    {{mb_class object=$rpu}}

    {{mb_include module=urgences template=rpu/inc_fieldset_pec_inf insert_submit_button=true}}
</form>
