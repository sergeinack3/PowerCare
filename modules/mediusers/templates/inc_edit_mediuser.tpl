{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin script=password_spec ajax=true}}

{{assign var=module_rpps value="rpps"|module_active}}

<script>
  addSecondaryAccount = function () {
    var url = new Url('mediusers', 'ajax_secondary_users');
    url.addParam('main_user_id', '{{$object->_id}}');
    url.requestModal(600, 600);
  };

  searchUserLDAP = function (object_id) {
    var url = new Url("admin", "ajax_search_user_ldap");
    url.addParam("object_id", object_id);
    url.requestModal(800, 350);
    url.modalObject.observe("afterClose", function () {
      window.urlMediuserEdit.refreshModal();
    });
    window.ldapurl = url;
  };

  showPratInfo = function (type) {
    var ps_types = {{$ps_types|@json}};
    Control.Tabs.getTabAnchor('infos_praticien').setClassName('wrong', !ps_types.include(type));
  };

  loadProfil = function (type) {
    var tabProfil = {{$tabProfil|@json}};

    // Liste des profils dispo pour le type selectionné
    var listProfil = tabProfil[type] || [];

    $A(document.mediuser._profile_id).each(function (input) {
      input.disabled = !listProfil.include(input.value) && input.value;
      input.selected = input.selected && !input.disabled;
    });
  };

  unlinkOrUpdateUserLDAP = function (user_id, action) {
    var url = new Url("admin", "ajax_unlink_update_user_ldap");
    url.addParam("user_id", user_id);
    url.addParam("action", action);
    url.requestUpdate(SystemMessage.id, function () {
      window.urlMediuserEdit.refreshModal();
    });
  };

  duplicateUser = function (form) {
    var login = window.prompt($T('CUser-msg-Please, give a login name'));

    if (login === null) {
      return;
    }

    login = login.trim();

    if (!login) {
      alert($T('common-error-Missing parameter: %s', $T('CUser-user_username-desc')));
      return;
    }

    $V(form.elements._duplicate, '1');
    $V(form.elements._duplicate_username, login);

    form.onsubmit();

    $V(form.elements._duplicate, '');
    $V(form.elements._duplicate_username, '');
  };

  generateToken = function (user_id) {
    var form_user = getForm('mediuser');
    var form = getForm('activate-user-{{$object->_id}}');

    $V(form.email, $V(form_user._user_email));

    Modal.open('activate-user-' + user_id, {showClose: true, title: $T('CUser-label-Generate an activation link')});
  };

  submitActivationForm = function (form) {
    var url = new Url('admin', 'generateActivationToken', 'dosql');
    url.addFormData(form);

    url.requestUpdate('systemMsg', {
      onComplete: function () {
        Control.Modal.close();
      },
      method:     'post'
    });

    return false;
  };

  searchMedecinLink = function (user_id) {
    var url = new Url('mediusers', 'viewFilterSearchDoctors');
    url.addParam('user_id', user_id);
    url.requestModal(800, '70%', {
      onClose: function () {
        Control.Modal.refresh()
      }
    });
  };

  showLinkedMedecins = function (user_id) {
    var url = new Url('mediusers', 'vw_linked_medecins');
    url.addParam('user_id', user_id);
    url.requestModal(800, '70%', {
      onClose: function () {
        Control.Modal.refresh()
      }
    });
  };

  Main.add(function () {
    var form = getForm('mediuser');

      {{if !$object->user_id}}
    form._user_password.addClassName('notNull');
      {{/if}}

    // LDAP linked user cannot change password here
      {{if !$object->user_id || ($object->user_id && !$object->_ref_user->_ldap_linked)}}
    PasswordSpec.init(
            {{$password_configuration|@json}},
      '{{$weak_prop}}', '{{$strong_prop}}', '{{$ldap_prop}}', '{{$admin_prop}}',
    );

    PasswordSpec.registerUsernameField(form._user_username);
    PasswordSpec.registerTypeField(form._user_type);
    PasswordSpec.registerRemoteField(form.remote);
    PasswordSpec.registerPasswordField(form._user_password);
    PasswordSpec.registerPassword2Field(form._user_password2);
    PasswordSpec.registerRandomPasswordGeneratorButton(form.select('.random-pwd-button')[0]);

    PasswordSpec.observe();

    // Initial check for setting current spec
    PasswordSpec.check();
      {{/if}}

      {{if $object->_id}}
    showPratInfo("{{$object->_user_type}}");
      {{/if}}

      {{if $object->_user_type}}
    loadProfil("{{$object->_user_type}}");
      {{/if}}

    CMediuserFunctions.loadView({{$object->_id}});
  });
</script>

{{assign var=configLDAP value=$conf.admin.LDAP.ldap_connection}}

{{assign var=readOnlyLDAP value=null}}
{{if $configLDAP && $object->_ref_user && $object->_ref_user->_ldap_linked}}
    {{assign var=readOnlyLDAP value=true}}
{{/if}}

{{if $object->_id && !$readOnlyLDAP}}
  <div id="activate-user-{{$object->_id}}" style="display: none;">
    <form name="activate-user-{{$object->_id}}" method="post" onsubmit="return submitActivationForm(this);">
      <input type="hidden" name="user_id" value="{{$object->_id}}" />

      <input type="hidden" name="@token" value="{{$activation_token}}"/>

      <table class="main form">
        <tr>
          <td>
            <label>
              <input type="radio" name="type" value="token" checked onclick="this.form.email.disabled = true"/>

              Générer un jeton
            </label>
          </td>
        </tr>

        <tr>
          <td>
            <label>
              <input type="radio" name="type" value="email" onclick="this.form.email.disabled = false"/>

              Envoyer par email à :
            </label>

            <label>
              <input type="text" name="email" value="" size="35" disabled/>
            </label>
          </td>
        </tr>

        <tr>
          <td class="button">
            <button type="submit" class="save">
              Valider
            </button>
          </td>
        </tr>
      </table>
    </form>
  </div>
{{/if}}

{{if $object->_id}}
    {{if $configLDAP}}
      <button class="search" {{if $readOnlyLDAP}}disabled{{/if}} onclick="searchUserLDAP('{{$object->_id}}')">
          {{tr}}CMediusers_search-ldap{{/tr}}
      </button>
        {{if $object->_ref_user && $object->_ref_user->_ldap_linked}}
          <button class="cancel" type="button"
                  onclick="unlinkOrUpdateUserLDAP({{$object->_id}}, 'unlink')">{{tr}}Unlink{{/tr}} du LDAP
          </button>
          <button class="search" type="button"
                  onclick="unlinkOrUpdateUserLDAP({{$object->_id}}, 'update')">{{tr}}Update{{/tr}} à partir du
            LDAP
          </button>
        {{/if}}
    {{/if}}
    {{if 'Ox\Core\CAppUI::conf'|static_call:'dPpatients CPatient function_distinct'}}
      <button class="search" type="button" onclick="searchMedecinLink('{{$object->_id}}');">
          {{tr}}CMediusers_search-medecin{{/tr}}
      </button>
      <button class="search" type="button" {{if !$medecins_back}}disabled{{/if}}
              onclick="showLinkedMedecins('{{$object->_id}}');">
          {{tr}}CMediusers-linked-medecins{{/tr}}
      </button>
    {{/if}}
{{/if}}

{{if $module_rpps && $object->_id && $object->_is_rpps_link_personne_exercice}}
  <div class="small-warning">
      {{tr}}CMediusers-msg-The traits of this user are linked to the health directory{{/tr}}
      {{if $object->rpps}}
        <button type="button" class="unlink notext" onclick="CMediusers.unlinkMedecinAnnuaire('{{$object->_id}}');">
            {{tr}}CMediusers-action-Unlink the RPPS number{{/tr}}
        </button>
      {{/if}}

      {{if $object->_date_version_rpps_link}}
        <br />
        {{tr var1=$object->_date_version_rpps_link|date_format:$conf.date}}CMediusers-msg-Version date of the last synchronization{{/tr}}
      {{/if}}
  </div>
{{/if}}

{{if $readOnlyLDAP}}
  <div class="small-warning">
      {{tr}}CUser_associate-ldap{{/tr}}
  </div>
{{/if}}

{{if $is_robot}}
  <div class="small-info">
      {{tr}}CUser_user-robot{{/tr}}{{if $tag}} : <strong>{{$tag}}</strong>{{/if}}.
  </div>
{{/if}}

<form name="mediuser" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
    {{if !$can->edit}}
      <input name="_locked" value="1" hidden="hidden"/>
    {{/if}}
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="dosql" value="do_mediusers_aed"/>
  <input type="hidden" name="user_id" value="{{$object->_id}}"/>
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="_user_id" value="{{$object->_user_id}}"/>
  <input type="hidden" name="_send_notification" value="0"/>
  <input type="hidden" name="_duplicate" value=""/>
  <input type="hidden" name="_duplicate_username" value=""/>
  <input type="hidden" name="_medecin_id" value="{{$medecin_id}}"/>
  <input type="hidden" name="_personne_exercice_identifiant_structure" value="{{$object->_personne_exercice_identifiant_structure}}"/>

  <table class="main form">
    <tr>
        {{if $object->_id}}
          <th class="title modify text" colspan="2">
              {{mb_include module=system template=inc_object_idsante400 object=$object}}
              {{mb_include module=system template=inc_object_history    object=$object}}
              {{mb_include module=system template=inc_object_uf         object=$object}}
              {{mb_include module=system template=inc_object_idex       object=$object tag=$tag_mediuser}}

              {{tr}}CMediusers-title-modify{{/tr}} '{{$object->_user_username}}'
          </th>
        {{else}}
          <th class="title me-th-new" colspan="2">
              {{tr}}CMediusers-title-create{{/tr}}
          </th>
        {{/if}}
    </tr>
  </table>

  <script>
    Main.add(Control.Tabs.create.curry('tabs-form', true, {
      afterChange: function (container) {
        var tdButtons = $('mediuser_functions_hide');
        if (container.id === 'fonctions' {{if 'appFineClient'|module_active}}|| container.id === 'appfine-ficheps-edit'{{/if}}) {
          tdButtons.hide();
        } else {
          if (!tdButtons.visible()) {
            Control.Modal.refresh();
          }
        }
      }
    }));
  </script>

  <ul id="tabs-form" class="control_tabs">
    <li><a href="#identification">Identification</a></li>
    <li><a href="#infos_praticien">Professionnel de santé</a></li>
    <li><a href="#iconographie">Iconographie</a></li>
    <li><a href="#fonctions">{{tr}}CGroups-back-functions{{/tr}}</a></li>

      {{if $object->_id}}
        <li><a href="#user-security">{{tr}}common-Security{{/tr}}</a></li>
      {{/if}}

    <!-- AppFine Appointment Configuration for Practitioner -->
    {{if 'appFineClient'|module_active}}
      <li>
        <a href="#appfine-ficheps-edit">{{tr}}CMediusers-msg-Appointment AppFine{{/tr}}</a>
      </li>
    {{/if}}
  </ul>

  <table id="identification" class="form" style="display: none;">
    <tr>
      <td colspan="2">
          {{if $conf.admin.CUser.custom_password_recommendations}}
            <div class="small-info text markdown">
                {{$conf.admin.CUser.custom_password_recommendations|markdown|purify}}
            </div>
          {{/if}}
      </td>
    </tr>

      {{mb_include template=inc_identification}}
  </table>

  <table id="infos_praticien" class="form" style="display: none;">
      {{mb_include template=inc_infos_praticien name_form="mediuser"}}
  </table>

  <table id="iconographie" class="form" style="display: none;">
      {{mb_include template=inc_iconographie}}
  </table>

    {{if $object->_id}}
      <div id="user-security" style="display: none;">
          {{mb_include module=admin template=inc_user_security user=$object->_ref_user}}
      </div>
    {{/if}}

  <table class="main form">
    <tr>
      <td class="button" id="mediuser_functions_hide" colspan="2">
          {{if $object->user_id}}
              {{if $is_robot}}
                <button class="modify"
                        type="button"
                        onclick="CMediusers.confirmMediuserEdition(this.form)">
                    {{tr}}Save{{/tr}}
                </button>
              {{else}}
                <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
              {{/if}}
            <button class="duplicate" type="button" onclick="duplicateUser(this.form);">
                {{tr}}Duplicate{{/tr}}
            </button>
              {{if !$readOnlyLDAP}}
                <button type="button" class="change" onclick="generateToken('{{$object->user_id}}');">
                    {{tr}}common-action-Generate account activation link{{/tr}}
                </button>
              {{/if}}
            <button class="trash" type="button"
                    onclick="CMediusers.confirmMediuserDeletion(this.form, '{{$is_robot}}')">
                {{tr}}Delete{{/tr}}
            </button>
          {{else}}
            <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
          {{/if}}

        <a class="button search" style="display: inline-block; float:right; "
           href="?m=admin&tab=view_edit_users&user_username={{$object->_user_username}}&user_id={{$object->_id}}">
            {{tr}}CMediusers_administer{{/tr}}
        </a>
      </td>
    </tr>
  </table>
</form>

<div id="fonctions" style="display: none;">
</div>

<!-- AppFine Appointment Configuration for Practitioner -->
{{if 'appFineClient'|module_active}}
<div
  id="appfine-ficheps-edit"
  style="display: none"
>
    {{mb_include module=appFineClient template=fiche_ps/inc_edit_fiche_ps}}
</div>
{{/if}}
