{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=context script=Contextual ajax=true}}

<div class="small-info">{{tr}}mod-context-description{{/tr}}</div>

<div class="small-warning">{{tr}}mod-context-warning_use{{/tr}}</div>

<script>
  Main.add(function() {
    Context.refresh.curry('patient-context');
    Context.refresh.curry('sejour-context');
    Context.refresh.curry('consultation-context');
  });

</script>

<div id="show-context" style="display: none;">
  <div class="Authentication">
    <div class="Authentication-Title">{{tr}}context-Authentication method{{/tr}}</div>
    <div class="Authentication-Choices">
      <label>
        <input type="radio" name="auth" value="basic" checked onchange="Context.updateAuth(this);">
        {{tr}}context-Authentication-Basic{{/tr}}
      </label>
      <label>
        <input type="radio" name="auth" value="token" onchange="Context.updateAuth(this);"/>
          {{tr}}context-Authentication-Token{{/tr}}
      </label>
    </div>
  </div>

  <div>
    <fieldset>
      <legend>{{tr}}context-open_direct{{/tr}}</legend>
      <label for="call-params">{{tr}}Parameters{{/tr}}</label>:
      <textarea id="call-params" rows="6"></textarea>
      <label for="call-url">{{tr}}common-URL{{/tr}}</label>:
      <textarea id="call-url" rows="2"></textarea>
      <div>
        <button class="search me-tertiary" onclick="Context.callModal();">{{tr}}context-open_modale{{/tr}}</button>
        <button class="link me-tertiary" onclick="Context.callOpen();">{{tr}}context-open_onglet{{/tr}}</button>
      </div>
    </fieldset>
  </div>

  <div>

  </div>
  <fieldset>
    <legend>{{tr}}context-token{{/tr}}</legend>
    <label for="token-params">{{tr}}Parameters{{/tr}}</label>:
    <textarea id="token-params" rows="6"></textarea>
    <label for="token-url">{{tr}}context-url_for_token{{/tr}}</label>:
    <textarea id="token-url" rows="2" style="display: none;"></textarea>
    <textarea id="api-url" rows="2"></textarea>
    <label for="token-username">{{tr}}User{{/tr}}</label>:
    <input id="token-username" name="token_username" type="text" />
    <div>
      <button class="new me-tertiary" onclick="Context.tokenize();">{{tr}}context-get_token_user{{/tr}}</button>
    </div>
    <label for="token-response">{{tr}}context-response{{/tr}}</label>
    <textarea id="token-response" rows="2"></textarea>
    <label for="follow-url">{{tr}}context-url_token{{/tr}}</label>:
    <textarea id="follow-url" rows="2"></textarea>
    <div>
      <button class="link me-tertiary" id="follow-button" disabled onclick="Context.follow();">
        {{tr}}context-redirect_destination{{/tr}}
      </button>
    </div>
  </fieldset>
</div>

<table class="main me-align-auto" style="width: 99%;">
  <tr>
    <!-- Contexte patient -->
    <td style="width: 50%; vertical-align: top;">
      <fieldset id="patient-context">
        <legend>{{tr}}context-views_patient{{/tr}}</legend>

        <form name="patient-context" method="post">

          {{tr}}context-choose_contexte_patient{{/tr}}<br/><br/>
          <div>
            <label>
              <input name="patient-context-type" type="radio" value="IPP" onclick="Context.refresh('patient-context')">
              {{tr}}context-search_by_ipp{{/tr}}
            </label>

            <div style="padding-left: 2em;">
              <input type="text" placeholder="IPP" name="ipp" value=""/>
            </div>
          </div>

          <div>
            <label>
              <input name="patient-context-type" value="traits" type="radio" onclick="Context.refresh('patient-context')">
              {{tr}}context-search_by_traits{{/tr}}<br/>
            </label>

            <div style="padding-left: 2em;">
              <input type="text" placeholder="Nom" name="name" value=""/><br/>
              <input type="text" placeholder="Prénom" name="firstname" value=""/><br/>
              <input type="text" placeholder="Naissance AAAA-MM-JJ" name="birthdate" value=""/><br/>
            </div>
          </div>

        </form>

        <br/>{{tr}}context-view_options{{/tr}}<br/><br/>

        <div style="padding-left: 2em;">
          <button class="search me-tertiary" onclick="Context.show('patient-context', 'patient');">
            {{tr}}context-fiche_patient{{/tr}}
          </button><br />
          <button class="search me-tertiary" onclick="Context.show('patient-context', 'full_patient');">
            {{tr}}dPpatients-CPatient-Dossier_complet{{/tr}}
          </button><br />
          <button class="search me-tertiary" onclick="Context.show('patient-context', 'sejour');">
            {{tr}}CSejour.create{{/tr}}
          </button><br />
          <button class="search me-tertiary" onclick="Context.show('patient-context', 'intervention');">
            {{tr}}COperation.create{{/tr}}
          </button><br />
          <button class="search me-tertiary" onclick="Context.show('patient-context', 'documents');">
            {{tr}}mod-dPpatients-tab-ajax_add_doc{{/tr}}
          </button><br />
          <button class="search me-tertiary" onclick="Context.show('patient-context', 'oxCabinet_timeline');">
            {{tr}}context-patient-timeline{{/tr}}
          </button><br />
          <button class="search me-tertiary" onclick="Context.show('patient-context', 'module_dentaire');">
                {{tr}}module-dentaire-long{{/tr}}
          </button><br />
        </div>
      </fieldset>
    </td>

    <!-- Contexte séjour et consultation -->
    <td style="width: 50%; vertical-align: top;">
      <fieldset id="sejour-context">
        <legend>{{tr}}context-views_sejour{{/tr}}</legend><br/>

        <form name="sejour-context" method="post">
          <div>
            <label>
              <input name="sejour-context-type" type="radio" onclick="Context.refresh('sejour-context')">
              {{tr}}context-search_by_nda{{/tr}}
            </label>

            <div style="padding-left: 2em;">
              <input type="text" placeholder="NDA" name="nda" value=""/>
            </div>
          </div>
        </form>

        <br/>{{tr}}context-view_options{{/tr}}<br/><br/>

        <div style="padding-left: 2em;">
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'soins');">
            {{tr}}CSejour-action-Folder care{{/tr}}
          </button><br />
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'constantes_medicales');">
            {{tr}}context-constantes_medicales{{/tr}}
          </button><br />
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'prescription_pre_admission');">
            {{tr}}context-prescription_pre_admission{{/tr}}
          </button><br />
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'prescription');">
            {{tr}}context-prescription{{/tr}}
          </button><br />
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'prescription_sortie');">
            {{tr}}context-prescription_sortie{{/tr}}
          </button><br />
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'labo'        );">
            {{tr}}context-labo{{/tr}}
          </button><br />
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'ecap_ssr');">
            {{tr}}context-ecap_ssr{{/tr}}
          </button><br />
          <button class="search me-tertiary me-dark" onclick="Context.show('sejour-context', 'prestations');">
            {{tr}}context-prestations{{/tr}}
          </button><br />
        </div>
      </fieldset>

      {{if "oxCabinet"|module_active}}
        <fieldset id="consultation-context">
          <legend>{{tr}}context-views_consultation{{/tr}}</legend><br/>

          <form name="consultation-context" method="post">
            <div>
              <label>
                <input name="consultation-context-type" type="radio" onclick="Context.refresh('consultation-context'); Contextual.disabledButton('create-consultation-context');">
                  {{tr}}context-search_by_identifiant{{/tr}}
              </label>

              <div style="padding-left: 2em;">
                <input type="text" placeholder="{{tr}}context-identifiant{{/tr}}" name="consultation_id" value=""/>
              </div>
            </div>

            <div>
              <label>
                <input name="consultation-context-type" value="traits" type="radio" onclick="Context.refresh('consultation-context'); Contextual.disabledButton('consultation-context');">
                  {{tr}}context-consultation-By strict lines Creation of a consultation{{/tr}}<br/>
              </label>

              <div style="padding-left: 2em;">
                <input type="text" placeholder="ID patient" name="patient_id" value=""/><br/>
                <input type="text" placeholder="Numéro Finess" name="numero_finess" value=""/><br/>
                <input type="text" placeholder="RPPS Praticien" name="rpps_praticien" value=""/><br/>
              </div>
            </div>
          </form>

          <br/>{{tr}}context-view_options{{/tr}}<br/><br/>

          <div style="padding-left: 2em;">
            <button class="search me-tertiary me-dark consultation-context" onclick="Context.show('consultation-context', 'oxCabinet_appointment');">
                {{tr}}context-consultation-prescription and medical folder{{/tr}}
            </button><br />
            <button class="search me-tertiary me-dark create-consultation-context" onclick="Context.show('consultation-context', 'oxCabinet_appointment');">
                {{tr}}context-consultation-Creation of a consultation with Prescription TAMM{{/tr}}
            </button><br />
            <button class="search me-tertiary me-dark consultation-context" onclick="Context.show('consultation-context', 'oxCabinet_consultation');">
                {{tr}}context-consultation-consultation_tamm{{/tr}}
            </button><br />
          </div>
        </fieldset>
      {{/if}}
    </td>
  </tr>
</table>

