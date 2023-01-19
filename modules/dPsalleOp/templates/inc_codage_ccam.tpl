{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  clotureActivite = function(object_id, object_class) {
    var url = new Url("dPsalleOp", "ajax_cloture_activite");
    url.addParam("object_id", object_id);
    url.addParam("object_class", object_class);
    url.requestModal(500, 300);
  };
</script>

<table class="form">
  {{if $subject->_coded}}
    {{if $subject->_class == "CConsultation"}}
      <tr>
        <td colspan="10">
          <div class="small-info">{{tr}}CCodable-codage_closed{{/tr}}</div>
         </td>
      </tr>
    {{else}}
      <tr>
        {{assign var=config value='dPsalleOp COperation modif_actes'|gconf}}
        {{if strpos($config, 'sortie_sejour') !== false}}
          {{assign var=config value='sortie_sejour'}}
        {{/if}}
        <td {{if 'dPsalleOp CActeCCAM allow_send_acts_room'|gconf && $config == 'facturation'}}
              colspan="5" class="halfPane text"
            {{else}}
              colspan="10" class="text"
            {{/if}}>
          <div class="small-info">
            Les actes ne peuvent plus être modifiés pour la raison suivante : {{tr}}{{$subject->_coded_message}}{{/tr}}
            <br />
            Veuillez contacter le PMSI pour toute modification.
          </div>
        </td>
        {{if 'dPsalleOp CActeCCAM allow_send_acts_room'|gconf && $config == 'facturation'}}
          <script>
            Main.add(function () {
              PMSI.loadExportActes('{{$subject->_id}}', '{{$subject->_class}}', 1, 'dPsalleOp');
            });
          </script>

          <td class="halfPane">
            <fieldset>
              <legend>Validation du codage</legend>
              <div id="export_{{$subject->_class}}_{{$subject->_id}}">

              </div>
            </fieldset>
          </td>
        {{/if}}
      </tr>
    {{/if}}
  {{/if}}
  {{if !$subject->_canRead}}
    <tr>
      <td colspan="10" class="text">
        <div class="small-info">Vous n'avez pas les droits nécessaires pour coder les actes</div>
      </td>
    </tr>
  {{elseif !$subject->_coded}}
    <tr>
      <td class="text">
        {{mb_include module=salleOp template=inc_manage_codes}}
      </td>
    </tr>
  {{else}}
    {{mb_script module=pmsi script=PMSI ajax=true}}
    {{mb_script module=ccam script=CCodageCCAM ajax=true}}
    {{mb_include module=pmsi template=inc_codage_actes show_ngap=false read_only=true}}
  {{/if}}
</table>