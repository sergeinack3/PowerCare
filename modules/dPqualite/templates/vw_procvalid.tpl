{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $docGed->doc_ged_id}}
  <script>
    function popFile(objectClass, objectId, elementClass, elementId) {
      var url = new Url();
      url.addParam("nonavig", 1);
      url.ViewFilePopup(objectClass, objectId, elementClass, elementId, 0);
    }

    function annuleDoc(oForm, annulation) {
      oForm.elements["ged[annule]"].value = annulation;
      oForm._validation.value = 1;
      oForm.elements["ged[etat]"].value = {{$docGed->etat}};
      oForm.submit();
    }

    function validDoc(oForm) {
      if (oForm.elements["suivi[remarques]"].value == "") {
        alert("{{tr}}CDocGed-msg-refusdoc{{/tr}}");
        oForm.elements["suivi[remarques]"].focus();
      } else {
        oForm.elements["suivi[doc_ged_suivi_id]"].value = "";
        oForm.elements["ged[etat]"].value = {{$docGed|const:'TERMINE'}};
        oForm.elements["suivi[actif]"].value = 1;
        oForm.submit();
      }
    }

    function validDocDirect(oForm) {
      if (oForm.elements["formfile[0]"].value == "") {
        alert("Veuillez selectionner un fichier");
        oForm.elements["formfile[0]"].focus();
      } else {
        validDoc(oForm);
      }
    }

    function refuseDoc(oForm) {
      if (oForm.elements["suivi[remarques]"].value == "") {
        alert("{{tr}}CDocGed-msg-refusdoc{{/tr}}");
        oForm.elements["suivi[remarques]"].focus();
      } else {
        oForm.elements["suivi[doc_ged_suivi_id]"].value = "";
        {{if $docGed->doc_ged_id && $docGed->_lastactif->doc_ged_suivi_id}}
        oForm.elements["ged[doc_theme_id]"].value ={{$docGed->doc_theme_id}};
        oForm.elements["ged[titre]"].value = "{{$docGed->titre}}";
        {{else}}
        oForm.elements["ged[doc_theme_id]"].value = "";
        oForm.elements["ged[doc_categorie_id]"].value = "";
        oForm.elements["ged[doc_chapitre_id]"].value = "";
        oForm.elements["ged[titre]"].value = "";
        {{/if}}
        oForm.elements["ged[etat]"].value = {{$docGed|const:'TERMINE'}};
        oForm.submit();
      }
    }

    function redactionDoc(oForm) {
      oForm.elements["suivi[doc_ged_suivi_id]"].value = "";
      oForm.elements["ged[etat]"].value = {{$docGed|const:'REDAC'}};
      if (oForm.onsubmit()) {
        oForm.submit();
      }
    }
  </script>
{{/if}}

<table class="main">
  <tr>
    <td class="halfPane">
      {{if $procDemande|@count}}
        <table class="form">
          <tr>
            <th class="title" colspan="5">
              {{tr}}_CDocGed_attente_demande{{/tr}}
            </th>
          </tr>
          <tr>
            <th class="category">{{tr}}_CDocGed_demande{{/tr}}</th>
            <th class="category">{{tr}}CDocGed-group_id{{/tr}}</th>
            <th class="category">{{tr}}CDocGed-user_id{{/tr}}</th>
            <th class="category">{{tr}}Date{{/tr}}</th>
            <th class="category">{{tr}}CDocGedSuivi-remarques{{/tr}}</th>
          </tr>
          {{foreach from=$procDemande item=currProc}}
            <tr>
              <td class="text">
                <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                  {{if $currProc->_lastactif->doc_ged_suivi_id}}
                    {{tr}}_CDocGed_revision{{/tr}} {{$currProc->_reference_doc}}
                  {{else}}
                    {{tr}}_CDocGed_new{{/tr}}
                  {{/if}}
                </a>
              </td>
              <td class="text">
                <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                  {{$currProc->_ref_group->_view}}
                </a>
              </td>
              <td class="text">
                <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                  {{$currProc->_ref_user->_view}}
                </a>
              </td>
              <td class="text">
                <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                  {{$currProc->_lastentry->date|date_format:$conf.datetime}}
                </a>
              </td>
              {{if $currProc->annule}}
                <td class="text" style="background-color:#f00;">[{{tr}}Cancel{{/tr}}] {{$currProc->_lastentry->remarques|nl2br}}</td>
              {{else}}
                <td>{{$currProc->_lastentry->remarques|nl2br}}</td>
              {{/if}}
            </tr>
          {{/foreach}}
        </table>
        <br />
        <br />
      {{/if}}

      <table class="form">
        <tr>
          <th class="title" colspan="6">{{tr}}_CDocGed_attente{{/tr}}</th>
        </tr>
        <tr>
          <th class="category">{{tr}}CDocGed-titre{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-_reference_doc{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-group_id{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-user_id{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-doc_theme_id{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-etat{{/tr}}</th>
        </tr>
        {{foreach from=$procEnCours item=currProc}}
          <tr>
            <td class="text">
              <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                {{$currProc->titre}}
              </a>
            </td>
            <td>
              <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                {{$currProc->_reference_doc}}
              </a>
            </td>
            <td class="text">
              <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                {{$currProc->_ref_group->_view}}
              </a>
            </td>
            <td class="text">
              <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}">
                {{$currProc->_ref_user->_view}}
              </a>
            </td>
            <td class="text">{{$currProc->_ref_theme->nom}}</td>
            <td class="text">
              {{if $currProc->annule}}
                <span style="background-color:#f00;">[{{tr}}CANCEL{{/tr}}]</span>
              {{/if}}
              {{$currProc->_etat_actuel}}</td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="5" class="empty">
              {{tr}}CDocGed.none{{/tr}}
            </td>
          </tr>
        {{/foreach}}
      </table>

      {{if $procTermine|@count}}
        <br />
        {{if !$procAnnuleVisible}}
          <a class="button edit" href="?m={{$m}}&procAnnuleVisible=1">
            {{tr}}button-CDocGed-viewcancel{{/tr}}
          </a>
        {{else}}
          <a class="button cancel" href="?m={{$m}}&procAnnuleVisible=0">
            {{tr}}button-CDocGed-notviewcancel{{/tr}}
          </a>
          <table class="form">
            <tr>
              <th class="title" colspan="5">
                {{tr}}_CDocGed_cancel{{/tr}}
              </th>
            </tr>
            <tr>
              <th class="category">{{tr}}CDocGed-titre{{/tr}}</th>
              <th class="category">{{tr}}CDocGed-_reference_doc{{/tr}}</th>
              <th class="category">{{tr}}CDocGed-group_id{{/tr}}</th>
              <th class="category">{{tr}}CDocGed-doc_theme_id{{/tr}}</th>
              <th class="category">{{tr}}CDocGed-etat{{/tr}}</th>
            </tr>
            {{foreach from=$procTermine item=currProc}}
              <tr>
                <td class="text">
                  <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}&lastactif=1">
                    {{$currProc->titre}}
                  </a>
                </td>
                <td>
                  <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}&lastactif=1">
                    {{$currProc->_reference_doc}}
                  </a>
                </td>
                <td class="text">
                  <a href="?m=qualite&tab=vw_procvalid&doc_ged_id={{$currProc->doc_ged_id}}&lastactif=1">
                    {{$currProc->_ref_group->text}}
                  </a>
                </td>
                <td class="text">{{$currProc->_ref_theme->nom}}</td>
                <td class="text" style="background-color: #f00;">
                  <form name="ProcRetablirFrm{{$currProc->doc_ged_id}}" method="post">
                    <input type="hidden" name="m" value="{{$m}}" />
                    <input type="hidden" name="dosql" value="do_docged_aed" />
                    <input type="hidden" name="del" value="0" />
                    <input type="hidden" name="_validation" value="1" />

                    <input type="hidden" name="ged[doc_ged_id]" value="{{$currProc->doc_ged_id}}" />
                    <input type="hidden" name="ged[user_id]" value="{{$currProc->user_id}}" />
                    <input type="hidden" name="ged[group_id]" value="{{$currProc->group_id}}" />
                    <input type="hidden" name="ged[annule]" value="0" />
                    <input type="hidden" name="ged[etat]" value="{{$currProc->etat}}" />
                    <button class="change" type="submit">
                      {{tr}}button-CDocGed-retablir{{/tr}}
                    </button>
                    {{if $currProc->etat==$currProc|const:'TERMINE'}}
                      {{tr}}CDocGed-msg-etat_INDISPO{{/tr}}
                    {{else}}
                      <strong>{{tr}}_CDocGed_attente{{/tr}}</strong>
                    {{/if}}
                  </form>
                </td>
              </tr>
            {{/foreach}}
          </table>
        {{/if}}
      {{/if}}
    </td>
    <td class="halfPane">
      {{if $docGed->doc_ged_id}}
        <form name="ProcEditFrm" enctype="multipart/form-data" method="post" onsubmit="return checkForm(this)">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_docged_aed" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="_validation" value="0" />

          <input type="hidden" name="ged[doc_ged_id]" value="{{$docGed->doc_ged_id}}" />
          <input type="hidden" name="ged[user_id]" value="{{$docGed->user_id}}" />
          <input type="hidden" name="ged[group_id]" value="{{$docGed->group_id}}" />
          <input type="hidden" name="ged[annule]" value="{{$docGed->annule|default:"0"}}" />
          <input type="hidden" name="ged[etat]" value="" />

          <input type="hidden" name="suivi[doc_ged_suivi_id]" value="{{$docGed->_lastentry->doc_ged_suivi_id}}" />
          <input type="hidden" name="suivi[user_id]" value="{{$app->user_id}}" />
          <input type="hidden" name="suivi[actif]" value="{{$docGed->_lastentry->actif}}" />
          <input type="hidden" name="suivi[file_id]" value="{{$docGed->_lastentry->file_id}}" />

          {{if $docGed->etat==$docGed|const:'DEMANDE' && !$lastactif}}
            {{assign var=template value=inc_procvalid_demande}}
          {{elseif $docGed->etat==$docGed|const:'REDAC' && !$lastactif}}
            {{assign var=template value=inc_procvalid_redaction}}
          {{elseif $docGed->etat==$docGed|const:'VALID' && !$lastactif}}
            {{assign var=template value=inc_procvalid_validation}}
          {{else}}
            {{assign var=template value=inc_procvalid_termine}}
          {{/if}}

          {{mb_include module=qualite template=$template}}
        </form>
      {{/if}}
    </td>
  </tr>
</table>