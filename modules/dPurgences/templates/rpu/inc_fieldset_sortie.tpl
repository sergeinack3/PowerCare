{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=show_fields_sortie_rpu value="dPurgences CRPU show_fields_sortie_rpu"|gconf}}

{{if $view_mode == "infirmier" || !$rpu->_id}}
  {{mb_return}}
{{/if}}

{{if !$rpu->_ref_sejour->mode_sortie}}
  <script>
    Main.add(function() {
      Fields.init();
    });
  </script>
{{/if}}

<fieldset class="me-small" {{if !$show_fields_sortie_rpu}}style="display: none;"{{/if}}>
  <legend>
    Sortie
  </legend>
  {{mb_include module=urgences template=inc_form_sortie mode_pec_med=1 width_th="10em"}}
</fieldset>
