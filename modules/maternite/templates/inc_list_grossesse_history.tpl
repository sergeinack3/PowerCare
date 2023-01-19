{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  printFicheGrossesse = function (dossier_anesth_id) {
    new Url("cabinet", "print_fiche")
      .addParam("dossier_anesth_id", dossier_anesth_id)
      .addParam("print", true)
      .popup(700, 500, "printFiche");
  };
</script>

{{if $app->user_prefs.UISTYLE != "tamm"}}
  {{assign var=mod_tamm value=false}}
{{else}}
  {{assign var=mod_tamm value=true}}
{{/if}}

<table class="tbl">
  <tr>
    {{if !$mod_tamm}}
      <th>{{tr}}CSejour{{/tr}}(s)</th>
    {{/if}}
    <th>{{tr}}CConsultation{{/tr}}s</th>
  </tr>
  <tr>
    {{if !$mod_tamm}}
    <td>
      <table class="layout">
        {{foreach from=$grossesse->_ref_sejours item=_sejour}}
          <tr>
            <td class="text compact">
              {{if !$_sejour->entree_reelle}}
                <button type="button" class="tick notext"
                        onclick="Admissions.validerEntree('{{$_sejour->_id}}', null, DossierMater.reloadHistorique.curry('{{$grossesse->_id}}'))">
                  {{tr}}CSejour-admit{{/tr}}
                </button>
              {{/if}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">{{$_sejour}}</span>
              {{foreach from=$_sejour->_ref_operations item=_op}}
              <p style="padding-left:10px;"><span
                  onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">&mdash; {{$_op}}</span></p>
              {{/foreach}}N
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td class="empty">{{tr}}CSejour.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    {{/if}}
    <td>
      <table class="layout">
        {{foreach from=$grossesse->_ref_consultations item=_consultation}}
          <tr>
            <td class="text compact">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_consultation->_guid}}');">
                {{tr}}{{$_consultation->_class}}{{/tr}} du {{$_consultation->_date|date_format:$conf.date}} - {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consultation->_ref_praticien}} {{if $_consultation->_ref_consult_anesth->_id}}
                  <img src="images/icons/anesth.png" alt="" />
                {{/if}}
              </span>
              {{if $_consultation->_ref_consult_anesth->_id}}
                <br />
                <button type="button" class="print" onclick="printFicheGrossesse('{{$_consultation->_ref_consult_anesth->_id}}')">
                  Imprimer la fiche anesthésie
                </button>
              {{/if}}
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td class="empty">{{tr}}CConsultation.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>
