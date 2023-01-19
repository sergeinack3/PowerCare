{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=onkeyup value=""}}

{{assign var=module_rpps value="rpps"|module_active}}

{{if $module_rpps && $object->$field && $object->_is_rpps_link_personne_exercice}}
    {{mb_value object=$object field=$field}}
{{else}}
    {{mb_field object=$object field=$field onkeyup=$onkeyup}}
{{/if}}
