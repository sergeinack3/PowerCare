{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  selectMailType = function(type) {
    if (type == 'PER') {
      $('type_PER').show();
      $('type_ORG_APP').hide();
    }
    else {
      $('type_PER').hide();
      $('type_ORG_APP').show();
    }
  };

  doSearchRecipient = function(form) {
    var url = new Url('messagerie', 'ajax_search_recipient');

    form.getElements().each(function(elt) {
      url.addParam(elt.name, $V(elt));
    });

    url.requestUpdate('search_results', {
      method: 'post',
      getParameters: {m: 'messagerie', a: 'ajax_search_recipient'}
    });

    return false;
  };

  addResultAddress = function(address) {
    var elt = $('edit-userMail_{{$field}}');
    if ($V(elt) != '') {
      var addresses = $V(elt).split(',');
      addresses.push(address);
      $V(elt, addresses.join(','));
    }
    else {
      $V(elt, address);
    }
    displayAddress('{{$field}}', address);
  };
</script>

<form name="searchRecipient" action="?" method="post" onsubmit="return doSearchRecipient(this);">
  <table class="form">
    <tr>
      <th>
        {{mb_label object=$query field=address_type}}
      </th>
      <td colspan="3">
        {{mb_field object=$query field=address_type onchange="selectMailType(\$V(this));"}}
      </td>
    </tr>
    <tbody id="type_PER">
      <tr>
        <th>{{mb_label object=$query field=first_name}}</th>
        <td>{{mb_field object=$query field=first_name}}</td>
        <th>{{mb_label object=$query field=last_name}}</th>
        <td>{{mb_field object=$query field=last_name}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$query field=national_id}}</th>
        <td>{{mb_field object=$query field=national_id}}</td>
        <th>{{mb_label object=$query field=type_national_id}}</th>
        <td>{{mb_field object=$query field=type_national_id typeEnum='select' emptyLabel=''}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$query field=profession}}</th>
        <td>
          <select name="profession" id="searchRecipient-profession">
            <option value="">&mdash; Sélectionnez une profession</option>
            {{foreach from='Ox\Mediboard\Messagerie\CJeeboxLDAPRecipient'|static:'professions' key=_code item=_text}}
              <option value="{{$_code}}">{{$_text}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    </tbody>
    <tbody id="type_ORG_APP" style="display: none;">
      <tr>
        <th>{{mb_label object=$query field=organization}}</th>
        <td colspan="3">{{mb_field object=$query field=organization}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$query field=structure_id}}</th>
        <td>{{mb_field object=$query field=structure_id}}</td>
        <th>{{mb_label object=$query field=type_structure_id}}</th>
        <td>{{mb_field object=$query field=type_structure_id typeEnum='select' emptyLabel=''}}</td>
      </tr>
    </tbody>
    <tr>
      <th>{{mb_label object=$query field=mail}}</th>
      <td colspan="3">{{mb_field object=$query field=mail}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$query field=city}}</th>
      <td colspan="3">{{mb_field object=$query field=city}}</td>
    </tr>
    <tr>
      <td class="button" style="text-align: center;" colspan="4">
        <button type=button" class="search" onclick="this.form.onsubmit();">{{tr}}Search{{/tr}}</button>
        <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="search_results">
  {{mb_include module=messagerie template=inc_search_recipients_results}}
</div>