{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    $("brouillon").fixedTableHeaders(0.5);
    $("attente_validation").fixedTableHeaders(0.5);
    $("a_envoyer").fixedTableHeaders();
    $("envoye").fixedTableHeaders();
  })
</script>

<tbody class="viewported">
<tr>
  <!--  Brouillon -->
  <td class="viewport width50">
    <div id="brouillon">
        {{if 'a_corriger'|array_key_exists:$affichageDocs}}
            {{mb_include module=board template=inc_list_documents_secretaire brouillon=1 affichageDocs =$affichageDocs.a_corriger}}
        {{else}}
            {{mb_include module=board template=inc_list_documents_secretaire brouillon=1 affichageDocs =null}}
        {{/if}}
    </div>
  </td>
  <!-- En attente de validation du praticien -->
  <td class="viewport width50">
    <div id="attente_validation">
        {{if 'attente_validation_praticien'|array_key_exists:$affichageDocs}}
            {{mb_include module=board template=inc_list_documents_secretaire attente_validation=1 affichageDocs =$affichageDocs.attente_validation_praticien}}
        {{else}}
            {{mb_include module=board template=inc_list_documents_secretaire attente_validation=1 affichageDocs =null}}
        {{/if}}
    </div>

  </td>
</tr>
<tr>
  <!-- à envoyer -->
  <td class="viewport width50">
    <div id="a_envoyer">
        {{if 'a_envoyer'|array_key_exists:$affichageDocs}}
            {{mb_include module=board template=inc_list_documents_secretaire a_envoyer=1 affichageDocs =$affichageDocs.a_envoyer}}
        {{else}}
            {{mb_include module=board template=inc_list_documents_secretaire a_envoyer=1 affichageDocs =null}}
        {{/if}}
    </div>
  </td>
  <!-- Envoyé -->
  <td class="viewport width50">
    <div id="envoye">
        {{if 'envoye'|array_key_exists:$affichageDocs}}
            {{mb_include module=board template=inc_list_documents_secretaire envoye=1 affichageDocs =$affichageDocs.envoye}}
        {{else}}
            {{mb_include module=board template=inc_list_documents_secretaire envoye=1 affichageDocs =null}}
        {{/if}}
    </div>
  </td>
</tr>
</tbody>

