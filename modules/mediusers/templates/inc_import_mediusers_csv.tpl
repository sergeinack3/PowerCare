{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="34">{{$results|@count}} utilisateurs trouvés</th>
  </tr>
  <tr>
    <th>Etat</th>
    <th>
        {{mb_label class=CMediusers field=_user_last_name}}
        {{if array_key_exists("user", $unfound)}}
          <br/>
            {{$unfound.user|@count}} déjà existant(s)
        {{/if}}
    </th>
    <th>{{mb_label class=CMediusers field=_user_first_name}}</th>
    <th>{{mb_label class=CMediusers field=_user_username}}</th>
    <th>
        {{mb_label class=CMediusers field=initials}}
    </th>
    <th>{{mb_label class=CMediusers field=_user_password}}</th>
    <th>
        {{mb_label class=CMediusers field=_user_sexe}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_user_type}}
        {{if array_key_exists("user_type", $unfound)}}
          <br/>
            {{$unfound.user_type|@count}} non trouvé(s)
        {{/if}}
    </th>
    <th>{{mb_label class=CMediusers field=function_id}}</th>
    
    <th>
        {{mb_label class=CMediusers field=_profile_id}}
        {{if array_key_exists("profil", $unfound)}}
          <br/>
            {{$unfound.profil|@count}} non trouvé(s)
        {{/if}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_user_email}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_user_phone}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_internal_phone}}
    </th>

      {{if $conf.ref_pays == 1}}
        <th>{{mb_label class=CMediusers field=adeli}}</th>
        <th>{{mb_label class=CMediusers field=rpps}}</th>
      {{else}}
        <th>{{mb_label class=CMediusers field=ean}}</th>
        <th>{{mb_label class=CMediusers field=rcc}}</th>
      {{/if}}
    <th>
        {{mb_label class=CMediusers field=spec_cpam_id}}
        {{if array_key_exists("spec_cpam", $unfound)}}
          <br/>
            {{$unfound.spec_cpam|@count}} non trouvée(s)
        {{/if}}
    </th>
    <th>
        {{mb_label class=CMediusers field=discipline_id}}
        {{if array_key_exists("discipline_name", $unfound)}}
          <br/>
            {{$unfound.discipline_name|@count}} non trouvée(s)
        {{/if}}
    </th>
    <th>
        {{mb_label class=CIdSante400 field=id400}}
    </th>
    <th>
        {{mb_label class=CMediusers field=remote}}
    </th>
    <th>
        {{mb_label class=CMediusers field=actif}}
    </th>
    <th>
      Unité fonctionnelles
    </th>
    <th>
        {{mb_label class=CMediusers field=main_user_id}}
    </th>
    <th>
        {{mb_label class=CMediusers field=secteur}}
    </th>
    <th>
        {{mb_label class=CMediusers field=pratique_tarifaire}}
    </th>
    <th>
        {{mb_label class=CMediusers field=ccam_context}}
    </th>
    <th>
        {{mb_label class=CMediusers field=astreinte}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_user_astreinte}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_user_astreinte_autre}}
    </th>
    <th>
        {{mb_label class=CMediusers field=cps}}
    </th>
    <th>
        {{mb_label class=CMediusers field=mail_apicrypt}}
    </th>
    <th>
        {{mb_label class=CMediusers field=mssante_address}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_force_change_password}}
    </th>
    <th>
        {{mb_label class=CMediusers field=commentaires}}
    </th>
    <th>
        {{mb_label class=CUser field=_ldap_linked}}
    </th>
    <th>
        {{mb_label class=CMediusers field=color}}
    </th>
    <th>
        {{mb_label class=CMediusers field=deb_activite}}
    </th>
    <th>
        {{mb_label class=CMediusers field=fin_activite}}
    </th>
    <th>
        {{mb_label class=CMediusers field=use_bris_de_glace}}
    </th>
    <th>
        {{mb_label class=CMediusers field=cab}}
    </th>
    <th>
        {{mb_label class=CMediusers field=conv}}
    </th>
    <th>
        {{mb_label class=CMediusers field=zisd}}
    </th>
    <th>
        {{mb_label class=CMediusers field=ik}}
    </th>
    <th>
        {{mb_label class=CMediusers field=titres}}
    </th>
    <th>
        {{mb_label class=CMediusers field=compte}}
    </th>
    <th>
        {{mb_label class=CMediusers field=banque_id}}
    </th>
    <th>
        {{mb_label class=CMediusers field=mode_tp_acs}}
    </th>
    <th>
        {{mb_label class=CMediusers field=_allow_change_password}}
    </th>
  </tr>

    {{foreach from=$results item=_user}}
      <tr>
          {{if array_key_exists('error', $_user) && $_user.error}}
            <td class="text warning compact">
                {{$_user.error}}
            </td>
          {{elseif array_key_exists('found', $_user) && $_user.found && $dryrun}}
            <td class="">
              Essai : retrouvé
            </td>
          {{elseif array_key_exists('found', $_user) && $_user.found}}
            <td class="text ok">
              Retrouvé
            </td>
          {{elseif $dryrun}}
            <td class="">
              Essai
            </td>
          {{else}}
            <td class="text ok">
              OK
            </td>
          {{/if}}
        
        <td
          class="text {{if array_key_exists('user', $unfound) && array_key_exists($_user.nom, $unfound.user)}}warning{{/if}}">
            {{$_user.nom}}
        </td>
        <td class="text">{{$_user.prenom}}</td>
        <td class="text">{{$_user.username}}</td>
        <td class="text">{{if array_key_exists('initials', $_user)}}{{$_user.initials}}{{/if}}</td>
        <td class="text">{{$_user.password}}</td>
        <td class="text">{{if array_key_exists('sexe', $_user)}}{{$_user.sexe}}{{/if}}</td>
        <td
          class="text {{if array_key_exists('type', $unfound) && array_key_exists($_user.type, $unfound.user_type)}}warning{{/if}}">{{$_user.type}}</td>
        <td class="text">{{$_user.fonction}}</td>
        <td
          class="text {{if array_key_exists('profil', $unfound) && array_key_exists($_user.profil, $unfound.profil)}}warning{{/if}}">{{$_user.profil}}</td>
        <td class="text">{{if array_key_exists('user_mail', $_user)}}{{$_user.user_mail}}{{/if}}</td>
        <td class="text">{{if array_key_exists('user_phone', $_user)}}{{$_user.user_phone}}{{/if}}</td>
        <td class="text">{{if array_key_exists('internal_phone', $_user)}}{{$_user.internal_phone}}{{/if}}</td>
        <td class="text">{{$_user.adeli}}</td>
        <td class="text">{{$_user.rpps}}</td>
        <td
          class="text {{if array_key_exists('spec_cpam', $unfound) && array_key_exists($_user.spec_cpam, $unfound.spec_cpam)}}warning{{/if}}">{{$_user.spec_cpam}}</td>
        <td
          class="text {{if array_key_exists('discipline', $unfound) && array_key_exists($_user.discipline, $unfound.discipline)}}warning{{/if}}">{{$_user.discipline}}</td>
        <td
          class="text {{if array_key_exists('idex', $unfound) && array_key_exists($_user.idex, $unfound.idex)}}warning{{/if}}">{{$_user.idex}}</td>
        <td class="text">{{if array_key_exists('acces_local', $_user)}}{{$_user.acces_local}}{{/if}}</td>
        <td class="text">{{if array_key_exists('actif', $_user)}}{{$_user.actif}}{{/if}}</td>
        <td class="text">{{if array_key_exists('ufm', $_user)}}{{$_user.ufm}}{{/if}}</td>
        <td class="text">{{if array_key_exists('main_user', $_user)}}{{$_user.main_user}}{{/if}}</td>
        <td class="text">{{if array_key_exists('secteur', $_user)}}{{$_user.secteur}}{{/if}}</td>
        <td class="text">{{if array_key_exists('pratique_tarifaire', $_user)}}{{$_user.pratique_tarifaire}}{{/if}}</td>
        <td class="text">{{if array_key_exists('ccam_context', $_user)}}{{$_user.ccam_context}}{{/if}}</td>
        <td class="text">{{if array_key_exists('astreinte', $_user)}}{{$_user.astreinte}}{{/if}}</td>
        <td class="text">{{if array_key_exists('num_astreinte', $_user)}}{{$_user.num_astreinte}}{{/if}}</td>
        <td
          class="text">{{if array_key_exists('num_astreinte_autre', $_user)}}{{$_user.num_astreinte_autre}}{{/if}}</td>
        <td class="text">{{if array_key_exists('cps', $_user)}}{{$_user.cps}}{{/if}}</td>
        <td class="text">{{if array_key_exists('mail_apicrypt', $_user)}}{{$_user.mail_apicrypt}}{{/if}}</td>
        <td class="text">{{if array_key_exists('mssante_address', $_user)}}{{$_user.mssante_address}}{{/if}}</td>
        <td class="text">{{if array_key_exists('force_change_pw', $_user)}}{{$_user.force_change_pw}}{{/if}}</td>
        <td class="text">{{if array_key_exists('commentaires', $_user)}}{{$_user.commentaires}}{{/if}}</td>
        <td class="text">{{if array_key_exists('ldap_id', $_user)}}{{$_user.ldap_id}}{{/if}}</td>
        <td class="text">{{if array_key_exists('color', $_user)}}{{$_user.color}}{{/if}}</td>
        <td class="text">{{if array_key_exists('deb_activite', $_user)}}{{$_user.deb_activite}}{{/if}}</td>
        <td class="text">{{if array_key_exists('fin_activite', $_user)}}{{$_user.fin_activite}}{{/if}}</td>
        <td class="text">{{if array_key_exists('use_bris_de_glace', $_user)}}{{$_user.use_bris_de_glace}}{{/if}}</td>
        <td class="text">{{if array_key_exists('cab', $_user)}}{{$_user.cab}}{{/if}}</td>
        <td class="text">{{if array_key_exists('conv', $_user)}}{{$_user.conv}}{{/if}}</td>
        <td class="text">{{if array_key_exists('zisd', $_user)}}{{$_user.zisd}}{{/if}}</td>
        <td class="text">{{if array_key_exists('ik', $_user)}}{{$_user.ik}}{{/if}}</td>
        <td class="text">{{if array_key_exists('titres', $_user)}}{{$_user.titres}}{{/if}}</td>
        <td class="text">{{if array_key_exists('compte', $_user)}}{{$_user.compte}}{{/if}}</td>
        <td class="text">{{if array_key_exists('banque_name', $_user)}}{{$_user.banque_name}}{{/if}}</td>
        <td class="text">{{if array_key_exists('mode_tp_acs', $_user)}}{{$_user.mode_tp_acs}}{{/if}}</td>
        <td
          class="text">{{if array_key_exists('allow_change_password', $_user)}}{{$_user.allow_change_password}}{{/if}}</td>
      </tr>
    {{/foreach}}
</table>
