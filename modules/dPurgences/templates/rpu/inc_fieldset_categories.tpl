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
      {{tr}}CRPU-back-categories_rpu{{/tr}}
  </legend>
  <table class="form">
      {{mb_include module="urgences" template="inc_vw_categorie_rpu"}}
  </table>
</fieldset>
