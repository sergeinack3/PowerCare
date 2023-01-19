{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPprescription"|module_active}}
  {{mb_script module="dPprescription" script="prescription"}}
{{/if}}

{{if !$documents|@count && $count_prescription == 0}}
  <div class="small-info">
    Il n'y a aucun document pour cette consultation
  </div>
{{else}}
  {{if $prescription_preadm->_id}}
    <table class="form">
      <tr>
        <th class="title">Prescription de pre-admission</th>
      </tr>
      <tr>
        <td style="text-align: center">
          <button class="print" type="button" onclick="Prescription.printOrdonnance('{{$prescription_preadm->_id}}')">Imprimer</button>
        </td>
      </tr>
    </table>
  {{/if}}

  {{if $prescription_sejour->_id}}
    <table class="form">
      <tr>
        <th class="title">Prescription de séjour</th>
      </tr>
      <tr>
       <td style="text-align: center">
           <button class="print" type="button" onclick="Prescription.printOrdonnance('{{$prescription_sejour->_id}}')">Imprimer</button>
       </td>
      </tr>
    </table>
  {{/if}}

  {{if $prescription_sortie->_id}}
  <table class="form">
    <tr>
      <th class="title">Prescription de sortie</th>
    </tr>
    <tr>
     <td style="text-align: center">
         <button class="print" type="button" onclick="Prescription.printOrdonnance('{{$prescription_sortie->_id}}')">Imprimer</button>
     </td>
    </tr>
  </table>
  {{/if}}

  {{if $documents.counter > 0}}
  <form name="selectDocsFrm" action="?" method="get" {{if $use_moebius}}target="_blank"{{/if}}>
    <input type="hidden" name="consultation_id" value="{{$consult->consultation_id}}" />
    {{if $use_moebius}}
      <input type="hidden" name="m" value="compteRendu" />
      <input type="hidden" name="raw" value="print_docs" />
    {{else}}
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="dialog" value="1" />
      <input type="hidden" name="a" value="print_docs" />
    {{/if}}

    <table class="main form">
      <tr>
        <th class="category" colspan="2">
          Veuillez choisir le nombre de documents à imprimer
        </th>
      </tr>
      {{foreach from=$documents.docs item=curr_doc}}
      <tr>
        <th>
          {{$curr_doc->nom}}
        </th>
        <td>
          <input name="nbDoc[{{$curr_doc->compte_rendu_id}}]" type="text" size="2" value="1" />
          <script type="text/javascript">
            $(getForm("selectDocsFrm").elements['nbDoc[{{$curr_doc->compte_rendu_id}}]']).addSpinner({min:0});
          </script>
        </td>
      </tr>
      {{/foreach}}

      {{foreach from=$documents.files item=curr_doc}}
        <tr>
          <th>
            {{$curr_doc->file_name}}
          </th>
          <td>
            <input name="nbFile[{{$curr_doc->_id}}]" type="text" size="2" value="1" />
            <script type="text/javascript">
              $(getForm("selectDocsFrm").elements['nbFile[{{$curr_doc->_id}}]']).addSpinner({min:0});
            </script>
          </td>
        </tr>
      {{/foreach}}
      <tr>
        <td class="button" colspan="2">
          <button type="submit" class="print">
            {{tr}}Print{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
  {{/if}}
{{/if}}
