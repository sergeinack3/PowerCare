{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="2">
      Documentation des aides à la saisie
    </th>
  </tr>
  <tr>
    <td colspan="2" class="button">
      <img src="modules/dPcompteRendu/images/helped_field.png" />
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      Ces zones de texte proposent des <strong>aides à la saisie lors de la frappe</strong>.
      <div class="small-info text">
        L'activation de la frappe automatique peut être paramétrée dans vos préférences, partie modèles :
        <strong>{{tr}}pref-aideAutoComplete{{/tr}}</strong>
      </div>
      Ces aides sont des textes pré-enregistrés qui permettent de ne pas resaisir du texte fréquement utilisé.<br />
      <strong>Lors du passage de la souris sur cette zone</strong>, une barre d'outils s'affiche, vous proposant 
      plusieurs options pour utiliser ou gérer vos aides à la saisie.
      <div class="small-info text">
        Vous pouvez choisir de n'afficher cette barre qu'avec un double clic dans vos préférences, partie modèles :
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
      <strong>Afficher toutes les aides à la saisie</strong> possibles dans ce contexte
    </td>
  </tr>
  <tr>
    <th>
      <img src="images/icons/new.png" />
    </th>
    <td class="text">
      <strong>Créer une nouvelle aide</strong> à la saisie à partir du texte présent dans le champ de texte, 
      avec la possibilité de choisir l'intitulé et de modifier le texte. Une création rapide (en un clic) peut être disponible :
      <ul>
        <li>
          <img src="images/icons/group.png" /> 
          enregistrer la nouvelle aide parmi celles de <strong>votre établissement</strong>
        </li>
        <li>
          <img src="images/icons/user-function.png" /> 
          enregistrer la nouvelle aide parmi celles de <strong>votre fonction ou cabinet</strong>
        </li>
        <li>
          <img src="images/icons/user.png" /> 
          enregistrer la nouvelle aide parmi <strong>les vôtres</strong>
        </li>
      </ul>
      <div class="small-info text">
        L'accès à la création rapide peut être paramétré dans vos préférences, partie modèles :
        <strong>{{tr}}pref-aideFastMode{{/tr}}</strong>
      </div>
    </td>
  </tr>
  <tr>
    <th>
      <img src="images/icons/user-glow.png" />
    </th>
    <td class="text">
      Indique si les aides à la saisie qui seront proposées sont celles de 
      l'utilisateur connecté (si entouré en rouge) ou celles du "responsable" du contexte.
      <div class="small-info text">
        L'accès au contexte peut être paramétrée dans vos préférences, partie modèles :
        <strong>{{tr}}pref-aideOwner{{/tr}}</strong>
      </div>
    </td>
  </tr>
  <tr>
    <th>
      <img src="images/icons/timestamp.png" />
    </th>
    <td class="text">
      Insérer l'heure courante avec le nom de l'utilisateur connecté.
      <div class="small-info text">
        L'activation de la frappe automatique peut être paramétrée dans vos préférences, partie modèles :
        <strong>{{tr}}pref-aideTimestamp{{/tr}}</strong>
      </div>
    </td>
  </tr>
  <tr>
    <th>
      <i class="me-icon save me-primary"></i>
    </th>
    <td class="text">
      Enregistrer le champ en train d'être saisi (uniquement disponible si le champs ne s'enregistre pas automatiquement).
    </td>
  </tr>
</table>
