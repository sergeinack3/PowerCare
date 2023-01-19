{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  {{if !$object_id}}
  var oFormAddLineSuivi = getForm("addLineSuivi");
  if (oFormAddLineSuivi) {
    var url = new Url("dPprescription", "httpreq_do_element_autocomplete");
    url.autoComplete(oFormAddLineSuivi.libelle, "line_auto_complete", {
      minChars:      2,
      dropdown:      true,
      updateElement: function (selected) {
        var oFormAddLineElementSuivi = getForm('addLineElementSuivi');
        Element.cleanWhitespace(selected);
        var dn = selected.childNodes;
        $V(oFormAddLineElementSuivi.element_prescription_id, dn[0].firstChild.nodeValue);
        $V(oFormAddLineSuivi.libelle, dn[2].innerHTML.stripTags());
      }
    });
  }
  // Chargement de l'autocomplete des protocoles
  var oFormProtocole = getForm("applyProtocoleSuiviSoins");
  if (oFormProtocole) {
    var url = new Url("dPprescription", "httpreq_vw_select_protocole");
    var autocompleter = url.autoComplete(oFormProtocole.libelle_protocole, "protocole_auto_complete_suivi_soins", {
      dropdown:      true,
      width:         "190px",
      minChars:      2,
      valueElement:  oFormProtocole.elements.pack_protocole_id,
      updateElement: function (selectedElement) {
        var node = $(selectedElement).down('.view');
        $V(oFormProtocole.libelle_protocole, (node.innerHTML).replace("&lt;", "<").replace("&gt;", ">"));
        if (autocompleter.options.afterUpdateElement) {
          autocompleter.options.afterUpdateElement(autocompleter.element, selectedElement);
        }
      },
      callback:
                     function (input, queryString) {
                       return (queryString + "&praticien_id={{$user_id}}");
                     }
    });
  }
  {{/if}}
</script>

<!-- Formulaire d'ajout de prescription -->
<form action="?" method="post" name="addPrescriptionSuiviSoins" onsubmit="return checkForm(this);">
  <input type="hidden" name="m" value="dPprescription" />
  <input type="hidden" name="dosql" value="do_prescription_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="prescription_id" value="" />
  <input type="hidden" name="object_id" value="{{$sejour_id}}" />
  <input type="hidden" name="object_class" value="CSejour" />
  <input type="hidden" name="type" value="sejour" />
  <input type="hidden" name="callback" value="updatePrescriptionId" />
</form>

<!-- Formulaire d'ajout de ligne de prescription -->
<form action="?" method="post" name="addLineElementSuivi" onsubmit="return checkForm(this);">
  <input type="hidden" name="m" value="dPprescription" />
  <input type="hidden" name="dosql" value="do_prescription_line_element_aed" />
  <input type="hidden" name="prescription_line_element_id" value="" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="prescription_id" value="{{$prescription->_id}}" />
  <input type="hidden" name="object_class" value="{{$prescription->object_class}}" />
  <input type="hidden" name="praticien_id" value="{{$user_id}}" />
  <input type="hidden" name="creator_id" value="{{$user_id}}" />
  <input type="hidden" name="debut" value="current" />
  <input type="hidden" name="time_debut" value="current" />
  <input type="hidden" name="element_prescription_id" value="" />
  <input type="hidden" name="commentaire" value="" />
</form>

{{if !$object_id}}
<!-- Selecteur d'elements -->
<table style="width: 100%;" class="form layout">
  <tr>
    <td>
      <form name="addLineSuivi" action="?" method="post">
        <input type="hidden" name="element_id" />
        <fieldset>
          <legend>Catalogue d'éléments</legend>
          <table class="form">
            <tr>
              <th>Element</th>
              <td>
                <input type="text" name="libelle" value="" class="autocomplete" />
                <div style="display:none; height: 120px;" class="autocomplete" id="line_auto_complete"></div>
              </td>
            </tr>
            <tr>
              <th>Commentaire</th>
              <td>
                <input type="text" name="commentaire" style="width: 15em;" />
              </td>
            </tr>
            <tr>
              <td colspan="2" class="button">
                <button type="button" class="submit" onclick="submitLineElement();">{{tr}}Save{{/tr}}</button>
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
    <td>
      <fieldset>
        <legend>Commentaire</legend>
        <table class="form">
          <tr>
            <td style="text-align: center;">
              <form name="addLineCommentMedSuiviSoins" method="post" action=""
                    onsubmit="return onSubmitFormAjax(this, { onComplete: function(){ Control.Modal.close(); Prescription.reload('{{$prescription->_id}}',null,'medicament')} } )">
                <input type="hidden" name="m" value="dPprescription" />
                <input type="hidden" name="dosql" value="do_prescription_line_comment_aed" />
                <input type="hidden" name="del" value="0" />
                <input type="hidden" name="prescription_line_comment_id" value="" />
                <input type="hidden" name="prescription_id" value="{{$prescription->_id}}" />
                <input type="hidden" name="praticien_id" value="{{$user_id}}" />
                <input type="hidden" name="chapitre" value="medicament" />
                <input type="hidden" name="creator_id" value="{{$user_id}}" />
                <input type="hidden" name="debut" value="current" />
                <input type="hidden" name="time_debut" value="current" />

                {{mb_field class=CPrescriptionLineComment field=commentaire}}
                <button class="submit" type="button" onclick="submitLineComment();">Ajouter ce commentaire</button>
              </form>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>Protocole</legend>
        <table class="form">
          <tr>
            <td>
              <form name="applyProtocoleSuiviSoins" method="post" action="?">
                <input type="hidden" name="m" value="dPprescription" />
                <input type="hidden" name="dosql" value="do_apply_protocole_aed" />
                <input type="hidden" name="del" value="0" />
                <input type="hidden" name="prescription_id" value="{{$prescription->_id}}" />
                <input type="hidden" name="praticien_id" value="{{$user_id}}" />
                <input type="hidden" name="_active" value="1" />
                <input type="hidden" name="pack_protocole_id" value="" onchange="submitProtocoleSuiviSoins();" />
                <input type="text" name="libelle_protocole" value="&mdash; Choisir un protocole" class="autocomplete" />
                <div style="display:none; height: 120px;" class="autocomplete" id="protocole_auto_complete_suivi_soins"></div>
              </form>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>
{{else}}
<table class="form">
  <tr>
    <td>
      {{if $object->_class == 'CPrescriptionLineComment'}}
      <form name="editComment" method="post" action="?"
            onsubmit="return onSubmitFormAjax(this, { onComplete: function(){ Control.Modal.close(); Soins.loadSuivi('{{$sejour_id}}'); } } )">
        <input type="hidden" name="m" value="dPprescription" />
        <input type="hidden" name="dosql" value="do_prescription_line_comment_aed" />
        {{mb_key object=$object}}
        <fieldset>
          <legend>
            {{mb_label object=$object field=commentaire}}
          </legend>
          {{mb_field object=$object field=commentaire}}
        </fieldset>
        <button class="submit" type="button" onclick="this.form.onsubmit();">Modifier ce commentaire</button>
      </form>
      {{else}}
      <form name="editElement" method="post" action="?"
            onsubmit="return onSubmitFormAjax(this, { onComplete: function(){ Control.Modal.close(); Soins.loadSuivi('{{$sejour_id}}'); } })">
        <input type="hidden" name="m" value="dPprescription" />
        <input type="hidden" name="dosql" value="do_prescription_line_element_aed" />
        {{mb_key object=$object}}
        <fieldset>
          <legend>
            {{mb_label object=$object field=commentaire}}
          </legend>
          {{mb_field object=$object field=commentaire}}
        </fieldset>
        <button class="submit" type="button" onclick="this.form.onsubmit();">Modifier ce commentaire</button>
      </form>
      {{/if}}
    </td>
  </tr>
</table>
  {{/if}}