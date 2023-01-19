{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function popFile(objectClass, objectId, elementClass, elementId) {
    var url = new Url();
    url.addParam("nonavig", 1);
    url.ViewFilePopup(objectClass, objectId, elementClass, elementId, 0);
  }
</script>

<table class="main">
  <tr>
    <td colspan="2">
      <a class="button new me-primary" href="?m=qualite&tab=vw_procencours&doc_ged_id=0">
        {{tr}}CDocGed.create{{/tr}}
      </a>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
      <!-- Liste des procédures terminées -->
      
      {{if $procTermine|@count}}
        <table class="form">
          <tr>
            <th class="title" colspan="5">
              {{tr}}Informations{{/tr}}
            </th>
          </tr>
          <tr>
            <th class="category">{{tr}}Date{{/tr}}</th>
            <th class="category">{{tr}}_CDocGed_demande{{/tr}}</th>
            <th class="category">{{tr}}CDocGed-etat{{/tr}}</th>
            <th class="category">{{tr}}CDocGedSuivi-remarques{{/tr}}</th>
            <th class="category"></th>
          </tr>
          {{foreach from=$procTermine item=currProc}}
            <tr>
              <td class="text">
                {{$currProc->_lastentry->date|date_format:$conf.datetime}}
              </td>
              <td class="text">
                {{if $currProc->_lastactif->doc_ged_suivi_id}}
                  {{tr}}_CDocGed_revision{{/tr}} {{$currProc->_reference_doc}}
                {{else}}
                  {{tr}}_CDocGed_new{{/tr}}
                {{/if}}
              </td>

              {{if $currProc->_lastactif->doc_ged_suivi_id && $currProc->_lastactif->doc_ged_suivi_id>$currProc->_firstentry->doc_ged_suivi_id}}
              <td class="text">
                <strong>{{tr}}_CDocGed_accepte{{/tr}}</strong>
                {{else}}
              <td class="text" style="color: #f00;">
                <strong>{{tr}}_CDocGed_refuse{{/tr}}</strong>
                {{/if}}
              </td>
              <td class="text">
                {{$currProc->_lastentry->remarques|nl2br}}
              </td>
              <td class="text">
                <form name="ProcInfos{{$currProc->doc_ged_id}}Frm" action="?m={{$m}}" method="post">
                  <input type="hidden" name="dosql" value="do_docged_aed" />
                  <input type="hidden" name="m" value="{{$m}}" />
                  <input type="hidden" name="ged[doc_ged_id]" value="{{$currProc->doc_ged_id}}" />
                  <input type="hidden" name="ged[user_id]" value="" />
                  <input type="hidden" name="_validation" value="1" />
                  {{if $currProc->_lastactif->doc_ged_suivi_id && $currProc->_lastactif->doc_ged_suivi_id>$currProc->_firstentry->doc_ged_suivi_id}}
                    <input type="hidden" name="del" value="0" />
                    <button type="submit" class="tick">
                      {{tr}}OK{{/tr}}
                    </button>
                  {{else}}
                    <input type="hidden" name="del" value="1" />
                    <button type="submit" class="trash">
                      {{tr}}OK{{/tr}}
                    </button>
                  {{/if}}
                </form>
              </td>
            </tr>
          {{/foreach}}
        </table>
        <br />
        <br />
      {{/if}}
      
      <!-- Liste des procédures en cours de rédaction -->
      
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
                <form name="ProcDem{{$currProc->doc_ged_id}}Frm" action="?m={{$m}}" method="post">
                  <input type="hidden" name="dosql" value="do_docged_aed" />
                  <input type="hidden" name="m" value="{{$m}}" />
                  <input type="hidden" name="ged[doc_ged_id]" value="{{$currProc->doc_ged_id}}" />
                  <input type="hidden" name="del" value="0" />
                  {{assign var="date_proc" value=$currProc->_lastentry->date|date_format:"%d %b %Y à %Hh%M"}}
                  <a href="?m=qualite&tab=vw_procencours&doc_ged_id={{$currProc->doc_ged_id}}">
                    {{if $currProc->_lastactif->doc_ged_suivi_id}}
                      {{tr}}_CDocGed_revision{{/tr}} {{$currProc->_reference_doc}}
                    {{else}}
                      {{tr}}_CDocGed_new{{/tr}}
                    {{/if}}
                  </a>
                </form>
              </td>
              <td class="text">
                <a href="?m=qualite&tab=vw_procencours&doc_ged_id={{$currProc->doc_ged_id}}">
                  {{$currProc->_ref_group->_view}}
                </a>
              </td>
              <td class="text">
                <a href="?m=qualite&tab=vw_procencours&doc_ged_id={{$currProc->doc_ged_id}}">
                  {{$currProc->_ref_user->_view}}
                </a>
              </td>
              <td class="text">
                {{$currProc->_lastentry->date|date_format:$conf.datetime}}
              </td>
              <td class="text">
                {{$currProc->_lastentry->remarques|nl2br}}
              </td>
            </tr>
          {{/foreach}}
        </table>
        <br />
        <br />
      {{/if}}
      
      <!-- Liste des procédures demandées en attente -->
      
      <table class="form">
        <tr>
          <th class="title" colspan="5">{{tr}}_CDocGed_attente_demande{{/tr}}</th>
        </tr>
        <tr>
          <th class="category">{{tr}}CDocGed-titre{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-_reference_doc{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-group_id{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-doc_theme_id{{/tr}}</th>
          <th class="category">{{tr}}CDocGed-etat{{/tr}}</th>
        </tr>
        {{foreach from=$procEnCours item=currProc}}
          <tr>
            <td class="text">
              <a href="?m=qualite&tab=vw_procencours&doc_ged_id={{$currProc->doc_ged_id}}">
                {{$currProc->titre}}
              </a>
            </td>
            <td>
              <a href="?m=qualite&tab=vw_procencours&doc_ged_id={{$currProc->doc_ged_id}}">
                {{$currProc->_reference_doc}}
              </a>
            </td>
            <td class="text">
              <a href="?m=qualite&tab=vw_procencours&doc_ged_id={{$currProc->doc_ged_id}}">
                {{$currProc->_ref_group->text}}
              </a>
            </td>
            <td class="text">{{$currProc->_ref_theme->nom}}</td>
            <td>{{$currProc->_etat_actuel}}</td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="5" class="empty">
              {{tr}}CDocGed.none{{/tr}}
            </td>
          </tr>
        {{/foreach}}
      </table>

    </td>
    <td class="halfPane">
      <form name="ProcEditFrm" action="?m={{$m}}" method="post" enctype="multipart/form-data" onsubmit="return checkForm(this)">
        <input type="hidden" name="dosql" value="do_docged_aed" />
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="del" value="0" />

        <input type="hidden" name="ged[doc_ged_id]" value="{{$docGed->doc_ged_id}}" />
        <input type="hidden" name="ged[user_id]" value="{{$app->user_id}}" />
        <input type="hidden" name="ged[annule]" value="{{$docGed->annule|default:"0"}}" />

        <input type="hidden" name="suivi[user_id]" value="{{$app->user_id}}" />
        <input type="hidden" name="suivi[actif]" value="0" />
        <input type="hidden" name="suivi[file_id]" value="" />

        <table class="form">
          <tr>
            <!-- Procédure demandée ou terminée -->
            {{if $docGed->doc_ged_id && ($docGed->etat == $docGed|const:'DEMANDE' || $docGed->etat==$docGed|const:'TERMINE')}}
              <th class="title modify" colspan="2">
                <input type="hidden" name="ged[etat]" value="{{$docGed|const:'DEMANDE'}}" />
                <input type="hidden" name="suivi[etat]" value="{{$docGed|const:'DEMANDE'}}" />
                {{if $docGed->etat==$docGed|const:'TERMINE'}}
                  <input type="hidden" name="suivi[doc_ged_suivi_id]" value="" />
                  {{tr}}CDocGed-title-modify-demande{{/tr}}
                {{else}}
                  <input type="hidden" name="suivi[doc_ged_suivi_id]" value="{{$docGed->_lastentry->doc_ged_suivi_id}}" />
                  {{tr}}CDocGed-title-modify{{/tr}}
                {{/if}}
              </th>
              <!-- Procédure en cours de rédaction -->
            {{elseif $docGed->doc_ged_id && $docGed->etat==$docGed|const:'REDAC'}}
              <th class="title modify" colspan="2">
                <input type="hidden" name="ged[etat]" value="{{$docGed|const:'VALID'}}" />
                <input type="hidden" name="suivi[etat]" value="{{$docGed|const:'REDAC'}}" />
                <input type="hidden" name="suivi[doc_ged_suivi_id]" value="" />
                {{tr}}CDocGed-msg-etatredac_REDAC{{/tr}}
              </th>
              <!-- Procédure validée -->
            {{elseif $docGed->doc_ged_id}}
              <th class="title modify" colspan="2">
                {{tr}}CDocGed-title-valid{{/tr}}
              </th>
            {{else}}
              <!-- Nouvelle procedure -->
              <th class="title me-th-new" colspan="2">
                <input type="hidden" name="ged[annule]" value="0" />
                <input type="hidden" name="ged[etat]" value="{{$docGed|const:'DEMANDE'}}" />
                <input type="hidden" name="suivi[etat]" value="{{$docGed|const:'DEMANDE'}}" />
                <input type="hidden" name="suivi[doc_ged_suivi_id]" value="" />
                {{tr}}CDocGed-title-create{{/tr}}
              </th>
            {{/if}}
          </tr>
          <!-- Nouvelle procédure, procédure terminée ou demandée -->
          {{if $docGed->etat==$docGed|const:'TERMINE' || $docGed->etat==$docGed|const:'DEMANDE' || !$docGed->doc_ged_id}}
            <tr>
              <th>{{tr}}Date{{/tr}}</th>
              <td>
                {{assign var=lastentry value=$docGed->_lastentry}}
                {{mb_field object=$lastentry field=date form="ProcEditFrm" register="true"}}
              </td>
            </tr>
            <tr>
              <th>{{tr}}CDocGedSuivi-doc_ged_suivi_id-court{{/tr}}</th>
              <td>
                {{if $docGed->doc_ged_id && $docGed->_lastactif->doc_ged_suivi_id}}
                  {{tr}}_CDocGed_revision{{/tr}} {{$docGed->_reference_doc}}
                  <br />
                  {{tr}}CDocGed-doc_theme_id{{/tr}} : {{$docGed->_ref_theme->nom}}
                {{else}}
                  {{tr}}_CDocGed_new{{/tr}}
                {{/if}}
              </td>
            </tr>
            <tr>
              <th>
                <label for="ged[group_id]" title="{{tr}}CDocGed-group_id-desc{{/tr}}">{{tr}}CDocGed-group_id{{/tr}}</label>
              </th>
              <td colspan="2">
                <select class="{{$docGed->_props.group_id}}" name="ged[group_id]">
                  <option value="">Tous</option>
                  {{foreach from=$etablissements item=curr_etab}}
                    <option
                      value="{{$curr_etab->group_id}}" {{if ($docGed->doc_ged_id && $docGed->group_id==$curr_etab->group_id) || (!$docGed->doc_ged_id && $g==$curr_etab->group_id)}} selected="selected"{{/if}}>
                      {{$curr_etab->text}}
                    </option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th><label for="suivi[remarques]"
                         title="{{tr}}CDocGedSuivi-remarques-desc{{/tr}}">{{tr}}CDocGedSuivi-remarques{{/tr}}</label></th>
              <td>
                <textarea name="suivi[remarques]"
                          class="notNull {{$docGed->_lastentry->_props.remarques}}">{{$docGed->_lastentry->remarques}}</textarea>
              </td>
            </tr>
            <tr>
              <td colspan="2" class="button">
                {{if $docGed->doc_ged_id && $docGed->etat!=$docGed|const:'TERMINE'}}
                  <button class="modify" type="submit">
                    {{tr}}Save{{/tr}}
                  </button>
                  {{assign var="date_proc" value=$docGed->_lastentry->date|date_format:"%d %b %Y à %Hh%M"}}
                  <button class="trash" type="button"
                          onclick="confirmDeletion(this.form, {typeName:'{{tr escape="javascript"}}CDocGed.one{{/tr}}',objName:'{{$date_proc|smarty:nodefaults|JSAttribute}}'})"
                          title="{{tr}}Delete{{/tr}}">
                    {{tr}}Delete{{/tr}}
                  </button>
                {{else}}
                  <button class="modify" type="submit">
                    {{tr}}Create{{/tr}}
                  </button>
                {{/if}}
              </td>
            </tr>
            <!-- Procédure en cours de rédaction -->
          {{elseif $docGed->etat==$docGed|const:'REDAC'}}
            <tr>
              <th>{{tr}}CDocGed.one{{/tr}}</th>
              <td>
                {{$docGed->_reference_doc}}
                <input type="hidden" name="object_class" value="CDocGed" />
                <input type="hidden" name="object_id" value="{{$docGed->doc_ged_id}}" />
                <input type="hidden" name="file_category_id" value="" />
              </td>
            </tr>
            <tr>
              <th>{{tr}}CDocGed-titre{{/tr}}</th>
              <td>{{$docGed->titre}}</td>
            </tr>
            <tr>
              <th>{{tr}}_CDocGed_validBy{{/tr}}</th>
              <td class="text">{{$docGed->_lastentry->_ref_user->_view}} ({{$docGed->_lastentry->date|date_format:"%d %B %Y - %Hh%M"}}
                )
              </td>
            </tr>
            {{if $docGed->_lastentry->file_id}}
              <tr>
                <th>{{tr}}_CDocGed_lastfile{{/tr}}</th>
                <td>
                  <a href="#" onclick="popFile('{{$docGed->_class}}','{{$docGed->_id}}','CFile','{{$docGed->_lastentry->file_id}}')"
                     title="{{tr}}CFile-msg-viewfile{{/tr}}">
                    {{thumbnail file_id=$docGed->_lastentry->file_id profile=small alt="-" style="max-width:64px; max-height:64px;"}}
                  </a>
                </td>
              </tr>
            {{/if}}
            <tr>
              <th>{{tr}}_CDocGed_lastcomm{{/tr}}</th>
              <td class="text">
                {{$docGed->_lastentry->remarques|nl2br}}
              </td>
            </tr>
            <tr>
              <th><label for="formfile[0]">{{tr}}CFile{{/tr}}</label></th>
              <td>
                <input type="file" name="formfile[0]" size="0" class="notNull str" />
              </td>
            </tr>
            <tr>
              <th><label for="suivi[remarques]"
                         title="{{tr}}CDocGedSuivi-remarques-desc{{/tr}}">{{tr}}CDocGedSuivi-remarques{{/tr}}</label></th>
              <td>
                <textarea name="suivi[remarques]" class="notNull {{$docGed->_lastentry->_props.remarques}}"></textarea>
              </td>
            </tr>
            <tr>
              <td colspan="2" class="button">
                <button class="modify" type="submit">
                  {{tr}}Add{{/tr}}
                </button>
              </td>
            </tr>
            <!-- Procédure validée -->
          {{else}}
            <tr>
              <td class="button text" colspan="2">
                <br />{{tr}}_CDocGed_valid{{/tr}}
                <br />
                <a href="#" onclick="popFile('{{$docGed->_class}}','{{$docGed->_id}}','CFile','{{$docGed->_lastentry->file_id}}')"
                   title="{{tr}}CFile-msg-viewfile{{/tr}}">
                  {{thumbnail file_id=$docGed->_lastentry->file_id profile=small alt="-" style="max-width:64px; max-height:64px;"}}
                </a>
                <br />{{$docGed->_reference_doc}}
                <br />{{$docGed->_lastentry->date|date_format:"%d %B %Y - %Hh%M"}}
              </td>
            </tr>
          {{/if}}
        </table>
      </form>
    </td>
  </tr>
</table>