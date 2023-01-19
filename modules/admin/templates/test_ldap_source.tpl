{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>Authentification</legend>

  <table class="main layout">
    <col style="width: 30%;" />

    <tr>
      <td>
        <table class="main form">
          <tr>
            <th>DN ou RDN LDAP</th>
            <td>
              <input type="text" name="ldaprdn" id="ldaprdn" value="" />
            </td>
          </tr>

          <tr>
            <th>Mot de passe associé</th>
            <td>
              <input type="password" name="ldappass" id="ldappass" value="" />
            </td>
          </tr>

          <tr>
            <td colspan="2">
              <button type="button" class="tick" onclick="LDAPSource.bind('{{$source->_id}}', $('ldaprdn'), $('ldappass'));">
                {{tr}}utilities-source-ldap-bind{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      </td>

      <td id="test-ldap-bind-{{$source->_id}}"></td>
    </tr>
  </table>
</fieldset>

<fieldset>
  <legend>Recherche</legend>

  <table class="main layout">
    <col style="width: 30%;" />

    <tr>
      <td style="vertical-align: top;">
        <table class="main form">
          <tr>
            <td>
              <button type="button" class="tick"
                      onclick="LDAPSource.search('{{$source->_id}}', $('ldaprdn'), $('ldappass'), $('filter'), $('attributes'));">
                {{tr}}utilities-source-ldap-search{{/tr}}
              </button>
            </td>
          </tr>

          <tr>
            <td>Filtre de recherche</td>
          </tr>

          <tr>
            <td>
              <textarea name="filter" id="filter" rows="3">{{if $source->isAlternativeBinding()}}(cn=*){{else}}(samaccountname=*){{/if}}</textarea>
            </td>
          </tr>

          <tr>
            <td>Attributs retournés (ex : mail, sn, cn)</td>
          </tr>

          <tr>
            <td>
              <textarea name="attributes" id="attributes" rows="3">{{if $source->isAlternativeBinding()}}cn, dn, sn, givenname, mail{{else}}samaccountname, useraccountcontrol, sn, givenname, mail{{/if}}</textarea>
            </td>
          </tr>
        </table>
      </td>

      <td id="test-ldap-search-{{$source->_id}}"></td>
    </tr>
  </table>
</fieldset>