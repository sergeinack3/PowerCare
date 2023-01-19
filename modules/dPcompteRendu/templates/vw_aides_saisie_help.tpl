{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="2">
      Documentation des aides � la saisie
    </th>
  </tr>
  <tr>
    <td colspan="2" class="button">
      <img src="modules/dPcompteRendu/images/helped_field.png" />
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      Ces zones de texte proposent des <strong>aides � la saisie lors de la frappe</strong>.
      <div class="small-info text">
        L'activation de la frappe automatique peut �tre param�tr�e dans vos pr�f�rences, partie mod�les :
        <strong>{{tr}}pref-aideAutoComplete{{/tr}}</strong>
      </div>
      Ces aides sont des textes pr�-enregistr�s qui permettent de ne pas resaisir du texte fr�quement utilis�.<br />
      <strong>Lors du passage de la souris sur cette zone</strong>, une barre d'outils s'affiche, vous proposant 
      plusieurs options pour utiliser ou g�rer vos aides � la saisie.
      <div class="small-info text">
        Vous pouvez choisir de n'afficher cette barre qu'avec un double clic dans vos pr�f�rences, partie mod�les :
        <strong>{{tr}}pref-aideShowOver{{/tr}}</strong>
      </div>
    </td>
  </tr>
  <tr>
    <th colspan="2">
      Barre d'outils
    </th>
  </tr>
  <tr>
    <th>
      <i class="me-icon down me-dark"></i>
    </th>
    <td class="text">
      <strong>Afficher toutes les aides � la saisie</strong> possibles dans ce contexte
    </td>
  </tr>
  <tr>
    <th>
      <img src="images/icons/new.png" />
    </th>
    <td class="text">
      <strong>Cr�er une nouvelle aide</strong> � la saisie � partir du texte pr�sent dans le champ de texte, 
      avec la possibilit� de choisir l'intitul� et de modifier le texte. Une cr�ation rapide (en un clic) peut �tre disponible :
      <ul>
        <li>
          <img src="images/icons/group.png" /> 
          enregistrer la nouvelle aide parmi celles de <strong>votre �tablissement</strong>
        </li>
        <li>
          <img src="images/icons/user-function.png" /> 
          enregistrer la nouvelle aide parmi celles de <strong>votre fonction ou cabinet</strong>
        </li>
        <li>
          <img src="images/icons/user.png" /> 
          enregistrer la nouvelle aide parmi <strong>les v�tres</strong>
        </li>
      </ul>
      <div class="small-info text">
        L'acc�s � la cr�ation rapide peut �tre param�tr� dans vos pr�f�rences, partie mod�les :
        <strong>{{tr}}pref-aideFastMode{{/tr}}</strong>
      </div>
    </td>
  </tr>
  <tr>
    <th>
      <img src="images/icons/user-glow.png" />
    </th>
    <td class="text">
      Indique si les aides � la saisie qui seront propos�es sont celles de 
      l'utilisateur connect� (si entour� en rouge) ou celles du "responsable" du contexte.
      <div class="small-info text">
        L'acc�s au contexte peut �tre param�tr�e dans vos pr�f�rences, partie mod�les :
        <strong>{{tr}}pref-aideOwner{{/tr}}</strong>
      </div>
    </td>
  </tr>
  <tr>
    <th>
      <img src="images/icons/timestamp.png" />
    </th>
    <td class="text">
      Ins�rer l'heure courante avec le nom de l'utilisateur connect�.
      <div class="small-info text">
        L'activation de la frappe automatique peut �tre param�tr�e dans vos pr�f�rences, partie mod�les :
        <strong>{{tr}}pref-aideTimestamp{{/tr}}</strong>
      </div>
    </td>
  </tr>
  <tr>
    <th>
      <i class="me-icon save me-primary"></i>
    </th>
    <td class="text">
      Enregistrer le champ en train d'�tre saisi (uniquement disponible si le champs ne s'enregistre pas automatiquement).
    </td>
  </tr>
</table>
