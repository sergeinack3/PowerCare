{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="etat_actuel_grossesse">
    {{if $allaitement && $allaitement->_id}}
        {{$allaitement}}
    {{else}}
      <span class="empty">
        {{tr}}CAllaitement.none{{/tr}}
      </span>
    {{/if}}
  <button type="button" class="add notext me-tertiary me-margin-right-10"
          onclick="Allaitement.viewAllaitements('{{$patient->_id}}', '1')">
      {{tr}}mod-maternite-tab-ajax_bind_allaitement{{/tr}}
  </button>
</div>
