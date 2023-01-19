{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPpatients" script="autocomplete" ajax=1}}

<script>
  var prat_nb = 1;
  var sec_nb = 1;

  addUser = function(type) {
    switch (type) {
      case 'prat':
        $('prat_'+prat_nb).show();
        if (prat_nb <= {{$max_prat}}) {
          prat_nb++;
        }
        break;

      case 'sec':
        $('sec_'+sec_nb).show();
        if (sec_nb <= {{$max_sec}}) {
          sec_nb++;
        }
        break;
    }
  };

  changePagePrimaryUsers = function(function_id) {
    var url = new Url("mediusers", "ajax_list_mediusers");
    url.addParam("function_id", function_id);
    url.requestUpdate("listUsers");
  };

  updateNb = function(type, value) {
    var nb = (type == 'sec') ? value :  value-1 ;
    if (nb >= {{$max_prat}}) {
      return;
    }

    for(var a = 0; a<(nb); a++) {
      addUser(type);
    }
  };

  cleanForm = function(oform) {
    var inputs = getForm(oform).select('input','textarea');
    inputs.each(function(elt) {
      if (elt.type != "hidden") {
        $V(elt, "");
      }
    });
  };

  Main.add(function () {
    InseeFields.initCPVille("edit_do_create_cabinet", "cp", "ville", null, null, "tel");
    addUser('prat');
  });
</script>

<style>
  .field_cabinet {
    background:url('') no-repeat left top;
  }

  .cat_cabinet_creator {
    clear:both;
    margin:30px;
    padding-left:90px;
    background-repeat: no-repeat;
    background-position: center left;
    background-size: 100px;
  }

  .cat_cabinet_creator fieldset{
    float:left;
    margin:20px;
    min-height: 150px;
  }

  .cabinet_cabinet {
    background-image: url('modules/dPcabinet/images/big/door.png');
  }

  .cabinet_prat {
    background-image: url('modules/dPcabinet/images/big/bag.png');
  }

  .cabinet_sec {
    min-height: 100px;
    background-image: url('modules/dPcabinet/images/big/phone.png');
  }
</style>
<form method="post" name="edit_do_create_cabinet" onsubmit="return onSubmitFormAjax(this, null)">
  {{mb_configure module=$m}}

  <fieldset class="cat_cabinet_creator cabinet_cabinet">
    <legend>Cabinet</legend>

    <fieldset>
      <h3>Données principales</h3>
      <table class="form">
        <tr>
          <th>{{mb_label object=$function field="text"}}</th>
          <td>{{mb_field object=$function field="text"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$function field="color"}}</th>
          <td>{{mb_field object=$function field="color" form="edit_do_create_cabinet"}}</td>
        </tr>
      </table>
    </fieldset>


    <fieldset>
      <h3>Coordonnées géographiques</h3>
      <table class="form">
        <tr>
          <th>{{mb_label object=$function field="adresse"}}</th>
          <td>{{mb_field object=$function field="adresse"}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$function field="cp"}}</th>
          <td>{{mb_field object=$function field="cp"}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$function field="ville"}}</th>
          <td>{{mb_field object=$function field="ville"}}</td>
        </tr>
      </table>
    </fieldset>

    <fieldset>
      <h3>Coordonnées téléphoniques</h3>
      <table class="form">
        <tr>
          <th>{{mb_label object=$function field="tel"}}</th>
          <td>{{mb_field object=$function field="tel"}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$function field="fax"}}</th>
          <td>{{mb_field object=$function field="fax"}}</td>
        </tr>
      </table>
    </fieldset>

    <fieldset>
      <h3>Options</h3>
      <table class="form">
        <tr>
          <th>{{mb_label object=$function field="compta_partagee"}}</th>
          <td>{{mb_field object=$function field="compta_partagee"}}</td>
        </tr>

        <tr>
          <th>{{mb_label object=$function field="consults_events_partagees"}}</th>
          <td>{{mb_field object=$function field="consults_events_partagees"}}</td>
        </tr>
      </table>
    </fieldset>

    <fieldset>
      <h3>Membres</h3>
      <table class="form">
        <tr>
          <th>Nombre de praticien</th>
          <td><input type="text" name="_nb_prat" onchange="updateNb('prat', this.value);"></td>
        </tr>

        <tr>
          <th>Nombre de secrétaires</th>
          <td><input type="text" name="_nb_sec" onchange="updateNb('sec', this.value);"></td>
        </tr>

      </table>
    </fieldset>
  </fieldset>

  <fieldset class="cat_cabinet_creator cabinet_prat">
    <legend>Praticiens</legend>
    <button class="add" type="button" onclick="addUser('prat')">{{tr}}Add{{/tr}} {{tr}}CMediusers{{/tr}}</button><br/>
    Fonction :
    <select name="profile_prat">
      {{foreach from=$profiles_prat item=_profile}}
        <option value="{{$_profile->_id}}">{{$_profile}}</option>
      {{/foreach}}
    </select>
    {{foreach from=$praticiens key=num item=_prat}}
      <div id="prat_{{$num+1}}" style="display: none;">
        <fieldset>
          <legend>Praticien {{$num+1}}</legend>
          <table class="form">
            <tr>
              <th>Nom</th>
              <td><input type="text" name="user[prat][{{$num}}][lastname]" placeholder="Nom praticien"/></td>
            </tr>
            <tr>
              <th>Prénom</th>
              <td><input type="text" name="user[prat][{{$num}}][firstname]" placeholder="Prénom praticien"/></td>
            </tr>
            <tr>
              <th>@</th>
              <td><input type="text" name="user[prat][{{$num}}][email]" placeholder="adresse email"/></td>
            </tr>
            <tr>
              <th><i class="me-icon phone me-dark"></i></th>
              <td><input type="text" name="user[prat][{{$num}}][tel]" placeholder="telephone"></td>
            </tr>
          </table>
        </fieldset>
      </div>
    {{/foreach}}
  </fieldset>

  <fieldset class="cat_cabinet_creator cabinet_sec">
    <legend>Secrétariat</legend>
    <button class="add" type="button" onclick="addUser('sec')">{{tr}}Add{{/tr}} {{tr}}CMediusers{{/tr}}</button><br/>
    Fonction :
    <select name="profile_sec">
      {{foreach from=$profiles_sec item=_profile}}
        <option value="{{$_profile->_id}}">{{$_profile}}</option>
      {{/foreach}}
    </select>
    {{foreach from=$secretaires key=num item=_sec}}
      <div id="sec_{{$num+1}}" style="display: none;">
        <fieldset>
          <legend>Secrétaire {{$num+1}}</legend>
          <table class="form">
            <tr>
              <th>Nom</th>
              <td><input type="text" name="user[sec][{{$num}}][lastname]" placeholder="Nom praticien"/></td>
            </tr>
            <tr>
              <th>Prénom</th>
              <td><input type="text" name="user[sec][{{$num}}][firstname]" placeholder="Prénom praticien"/></td>
            </tr>
            <tr>
              <th>@</th>
              <td><input type="text" name="user[sec][{{$num}}][email]" placeholder="adresse email"/></td>
            </tr>
            <tr>
              <th><i class="me-icon phone me-dark"></i></th>
              <td><input type="text" name="user[sec][{{$num}}][tel]" placeholder="telephone"></td>
            </tr>
          </table>
        </fieldset>
      </div>
    {{/foreach}}
  </fieldset>

  <p style="text-align: center;"><button class="save button" type="submit">{{tr}}Save{{/tr}}</button><button class="cleanup" type="button" onclick="cleanForm(this.form)">Réinitialiser</button></p>
</form>

<div>
  <h2>Résultats</h2>
  <div id="response"></div>
  <div id="listUsers"></div>
</div>
