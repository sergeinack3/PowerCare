{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=mediusers script=export_mediusers ajax=true}}

<h2>Import d'utilisateurs Mediboard.</h2>

<div class="big-info">
  T�l�versez un fichier CSV, encod� en <code>ISO-8859-1</code> (Western Europe),
  s�par� par des point-virgules (<code>;</code>) et
  d�limit� par des guillemets doubles (<code>"</code>).
  <br/>
  La premi�re ligne du fichier doit contenir les champs suivants (noms identiques) s�par�s par un point-virgule (;) :
  <ol>
    <li><strong>nom</strong> : Nom de l'utilisateur</li>
    <li>prenom : Pr�nom de l'utilisateur</li>
    <li>username : Login de connexion</li>
    <li><strong>password</strong> : Mot de passe de l'utilisateur (obligatoire uniquement pour la cr�ation
      d'utilisateurs)
    </li>
    <li>
      <strong>type</strong> (code num�rique) : Type de l'utilisateur
      <button type="button" class="info notext"
              onclick="ExportMediusers.openTypeLibelle();">{{tr}}mod-mediusers-show-type-libelle{{/tr}}</button>
    </li>
    <li><strong>fonction</strong> ({{mb_label class=CFunctions field=text}}) : Nom de la fonction, cr��e si introuvable
    </li>
    <li>
      profil ({{mb_label class=CUser field=user_username}}) : Nom du profil � utiliser pour l'utilisateur, non cr�� si
      introuvable
    </li>
    <li>adeli : Num�ro ADELI de l'utilisateur (ou num�ro EAN)</li>
    <li>rpps : Num�ro RPPS de l'utilisateur (ou num�ro RCC)</li>
    <li>spec_cpam (code � deux chiffres): Code de la sp�cialit� CPAM</li>
    <li>discipline : Nom de la discipline, non cr��e si introuvable</li>
    <li>idex : Identifiants externes de l'utilisateur. Il est possible d'ajouter plusieurs identifiants externes en les
      s�parant par ,
      (ex: 123456,987654,135790). Pour ajouter des tags � ces identifiant il faut ajouter |tag � la fin de l'identifiant
      (ex: 123456|ldap,526554,21156|idx)
    </li>
    <li>acces_local : Acc�s local uniquement, 0 ou 1, par d�faut 1</li>
    <li>actif : L'utilisateur est-il actif (1) ou non (0). L'utilisateur sera actif si ce champs est vide.</li>
    <li>ufm : Liste des codes des unit� fonctionnelles m�dicales auquel l'utilisateur doit �tre li� s�par�s par |
      (ex: ufm1|ufm2)
    </li>
    <li>main_user : Login de l'utilisateur principale (sert pour les multiples ADELI)</li>
    <li>secteur : Secteur de l'utilisateur (1|1dp|2|nc)</li>
    <li>pratique_tarifaire : Pratiques tarifaires de l'utilisateur (none|optam|optamco)</li>
    <li>ccam_context : Contexte CCAM de l'utilisateur (entre 0 et 52)</li>
    <li>num_astreinte : Num�ro de t�l�phone d'astreinte</li>
    <li>num_astreinte_autre : Num�ro de t�l�phone d'astreinte 2</li>
    <li>activite : Activit� de l'utilisateur (liberale, salarie, mixte)</li>
    <li>ufsecondaire : Codes des unit�s fonctionnelles secondaires (s�par�s par |). Les UF ne sont pas cr��es si elles
      n'existent pas.
    </li>
    <li>code_asip : Code de sp�cialit� ASIP de l'utilisateur</li>
    <li>astreinte : Utilisateur en astreinte, 0 = non ou 1 = oui (par d�faut � 0)</li>
    <li>commentaires : Commentaires sur l'utilisateur</li>
    <li>cps : Code de la carte cps</li>
    <li>mail_apicrypt : Adresse mail apycript</li>
    <li>mssante_address : Adresse de messagerie MsSant�</li>
    <li>sexe : Sexe de l'utilisateur</li>
    <li>force_change_pw : Forcer l'utilisateur � changer de mot de passe, 0 = non ou 1 = oui (par d�faut � 0)</li>
    <li>initials : Initials de l'utilisateur</li>
    <li>user_mail : Mail de l'utilisateur</li>
    <li>user_phone : T�l�phone de l'utilisateur</li>
    <li>internal_phone : T�l�phone interne de l'utilisateur</li>
    <li>ldap_id : Identifiant de l'utilisateur dans le LDAP</li>
    <li>color : {{tr}}CCSVImportMediusers-Msg-color-desc{{/tr}}</li>
    <li>deb_activite : {{tr}}CCSVImportMediusers-Msg-deb_activite-desc{{/tr}}</li>
    <li>fin_activite : {{tr}}CCSVImportMediusers-Msg-fin_activite-desc{{/tr}}</li>
    <li>use_bris_de_glace : {{tr}}CCSVImportMediusers-Msg-use_bris_de_glace-desc{{/tr}}</li>
    <li>cab : {{tr}}CCSVImportMediusers-Msg-cab-desc{{/tr}}</li>
    <li>conv : {{tr}}CCSVImportMediusers-Msg-conv-desc{{/tr}}</li>
    <li>zisd : {{tr}}CCSVImportMediusers-Msg-zisd-desc{{/tr}}</li>
    <li>ik : {{tr}}CCSVImportMediusers-Msg-ik-desc{{/tr}}</li>
    <li>titres : {{tr}}CCSVImportMediusers-Msg-titres-desc{{/tr}}</li>
    <li>compte : {{tr}}CCSVImportMediusers-Msg-compte-desc{{/tr}}</li>
    <li>banque_name : {{tr}}CCSVImportMediusers-Msg-banque_id-desc{{/tr}}</li>
    <li>mode_tp_acs : {{tr}}CCSVImportMediusers-Msg-mode_tp_acs-desc{{/tr}}</li>
    <li>allow_change_password : {{tr}}CCSVImportMediusers-Msg-allow_change_password-desc{{/tr}}</li>
  </ol>

    {{mb_include module=system template=inc_import_csv_info_outro}}

  <form method="post" name="import" action="?m=mediusers&a=ajax_import_mediusers_csv" enctype="multipart/form-data"
        onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-import');">
    <input type="hidden" name="m" value="mediusers"/>
    <input type="hidden" name="a" value="ajax_import_mediusers_csv"/>

    <table class="main form">
      <tr>
        <th style="width: 50%;">{{tr}}File{{/tr}}</th>
        <td>
            {{mb_include module=system template=inc_inline_upload paste=false extensions=csv multi=false}}
        </td>
      </tr>

      <tr>
        <th><label for="update">{{tr}}mod-mediusers-import-update{{/tr}}</label></th>
        <td><input type="checkbox" name="update" value="1"/></td>
      </tr>

      <tr>
        <th><label for="dryrun">{{tr}}DryRun{{/tr}}</label></th>
        <td><input type="checkbox" name="dryrun" value="1" checked/></td>
      </tr>

      <tr>
        <td class="button" colspan="2">
          <button class="import" type="submit">{{tr}}Import{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>

  <div id="result-import"></div>
