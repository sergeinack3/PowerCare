{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$rpu->_id || $view_mode == "medical"}}
  {{mb_return}}
{{/if}}

<fieldset class="me-small">
  <legend>
    Attentes
  </legend>

  {{mb_include module="urgences" template="inc_vw_rpu_attente"}}
</fieldset>