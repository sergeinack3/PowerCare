{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=edit_planning}}

<script type="text/javascript">

Main.add(function () {
  var opsUpdater = new Url("salleOp", "httpreq_liste_plages");
  opsUpdater.addParam("date", "{{$date}}");
  opsUpdater.periodicalUpdate('listplages', { frequency: 90 });
});

function printFiche() {
  var url = new Url("cabinet", "print_fiche");
  url.addElement(document.editFrmFinish.consultation_id);
  url.popup(700, 500, "printFiche");
}

function printAllDocs() {
  var url = new Url("cabinet", "print_docs");
  url.addElement(document.editFrmFinish.consultation_id);
  url.popup(700, 600, "printDocuments");
}
</script>

{{assign var=consult_anesth value=$operation->_ref_consult_anesth}}
{{assign var=consult value=$consult_anesth->_ref_consultation}}

<table class="main">
  <tr>
    <td style="width: 200px;" id="listplages"></td>
    <td class="greedyPane">
      {{if $op && !$consult_anesth->_id}}
        <table class="form">
          <tr>
            <th class="category">Consultation</th>
          </tr>
          <tr>
            <td>Il n'y a aucune consultation préanesthésique pour cette intervention</td>
          </tr>
        </table>
      {{elseif $op}}
        <form class="watch" name="editFrmFinish" action="?m={{$m}}" method="post" onsubmit="return checkForm(this);">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="dosql" value="do_consultation_aed" />
        <input type="hidden" name="consultation_id" value="{{$consult->consultation_id}}" />
        <input type="hidden" name="chrono" value="{{$consult->chrono}}" />
        </form>
        
        <table class="form">
          <tr>
            <th class="category" colspan="2">
              Consultation
            </th>
          </tr>
          <tr>
            <td>
              Consultation préanesthésique de <strong>{{$consult->_ref_patient->_view}}</strong>
              le {{$consult->_date|date_format:$conf.longdate}}
              par <strong>{{$consult->_ref_chir->_view}}</strong><br />
              Type de Séjour : {{tr}}CSejour.type.{{$operation->_ref_sejour->type}}{{/tr}}
              <br />
              <strong>Intervention :</strong>
              le <strong>{{$operation->_datetime|date_format:$conf.longdate}}</strong>
              par le <strong>Dr {{$operation->_ref_chir->_view}}</strong> (coté {{tr}}COperation.cote.{{$operation->cote}}{{/tr}})<br />
            </td>
            <td class="button">
              <a class="button search" href="?m=cabinet&tab=edit_consultation&selConsult={{$consult->_id}}">
                Voir la consultation
              </a><br />
              <button class="print" type="button" onclick="printFiche()">
                {{tr}}CConsultation-Print the card{{/tr}}
              </button><br />
              <button class="print" type="button" onclick="printAllDocs()">
                Imprimer les documents
              </button> 
            </td>
          </tr>
          <tr>
            <th class="category" colspan="2">
              Informations Anesthésie
            </th>
          </tr>
        </table>
        <div id="InfoAnesth">
          {{mb_include module=cabinet template=inc_consult_anesth/acc_infos_anesth}}
        </div>
        <div id="fdrConsult">
          {{mb_include module=cabinet template=inc_fdr_consult}}
        </div>
      {{/if}}
    </td>
  </tr>
</table>