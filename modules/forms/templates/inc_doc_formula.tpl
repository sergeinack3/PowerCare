{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title">Formules mathématiques</th>
  </tr>
  <tr>
    <th class="category">Opérations mathématiques courantes</th>
  </tr>
  <tr>
    <td>
    <ul>
      <li> Somme : + </li>
      <li> Soustraction : -&nbsp; </li>
      <li> Produit : * (étoile, pas X minuscule) </li>
      <li> Division : / (slash) </li>
      <li> Modulo : %</li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Opérations utilisables avec les dates, date/heure et heure</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Nombre de minutes : Min(valeur) (attention à respecter la casse : "Min" n'est pas équivalent à "min") </li>
      <li> Nombre d'heures : H(valeur) </li>
      <li> Nombre de jours : J(valeur) </li>
      <li> Nombre de semaines: Sem(valeur) </li>
      <li> Nombre de mois: M(valeur) </li>
      <li> Nombre d'années: A(valeur) </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Opérateurs plus avancés</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Racine carrée: sqrt(valeur) </li>
      <li> Logarithme népérien: log(valeur) </li>
      <li> Exponentielle : exp(valeur) </li>
      <li> Valeur absolue : abs(valeur) </li>
      <li> Arrondi supérieur : ceil(valeur) </li>
      <li> Arrondi inférieur : floor(valeur) </li>
      <li> Arrondi approché : round(valeur) </li>
      <li> Factorielle : fac(valeur) </li>
      <li> Sinus : sin(valeur) </li>
      <li> Cosinus : cos(valeur) </li>
      <li> Tangente: tan(valeur) </li>
      <li> Arc sinus: asin(valeur) </li>
      <li> Arc cosinus: acos(valeur) </li>
      <li> Arc tangente: atan(valeur) </li>
      <li> Valeur aléatoire entre 0 et valeur : random(valeur) </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Opérateurs à deux opérandes ou plus</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Exposant (2 valeurs) : pow(base, exposant) </li>
      <li> Maximum : max(valeur1, valeur2, valeurn...) </li>
      <li> Minimum : min(valeur1, valeur2, valeurn...) (attention à respecter la casse : "Min" n'est pas équivalent à "min") </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Opérateurs logiques</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Et logique : A and B </li>
      <li> Ou logique : A or B </li>
      <li> Egal à : A == B </li>
      <li> N'est pas égal à : A != B </li>
      <li> Supérieur ou égal à : A >= B </li>
      <li> Strictement supérieur à : A > B </li>
      <li> Inférieur ou égal à : A <= B </li>
      <li> Strictement inférieur à : A < B </li>
      <li> Condition : if (condition, si vrai, si faux) </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Constantes</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Pi : PI </li>
      <li> La constante d'Euler : E </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Exemples</th>
  </tr>
  <tr>
    <td class="text">
    <p>
      Il est possible d'utiliser les parenthèses comme dans toute formule mathématique. Voici un exemple :
    </p>    <pre>( [Champ test formule] + [Champ choix unique concept] ) * J( [Date A] - [Date B] ) / Sem( DateCourante - [Date B] )</pre>
    <p>
      Elle correspond au produit de la somme du champ <i>Champ test formule</i> et du champ <i>Champ choix unique concept</i> par le nombre de jours entre <i>Date A</i> et <i>Date B</i> le tout divisé par le nombre de semaines depuis la <i>Date B</i>.
    </p></td>
  </tr>
  <tr>
    <th class="title">Concaténation de champs</th>
  </tr>
  <tr>
    <td class="text">
    <p>
      La concaténation consiste à mettre bout à bout des données textuelles ou numériques.
    </p>
    <p>
      Le mode de paramétrage est le même que les formules, à quelques différences près.
    </p>
    <p>
      Seuls les champs de type <i>Texte long</i> peuvent contenir le résultat de la concaténation,
      et seulement les champs de type <i>Texte long</i>, <i>Texte court</i> et les numériques peuvent être utilisés pour y être insérés.
    </p>
    <p>
      L'outil se présente sous la même forme que pour les formules, il est possible d'insérer du texte libre dans la concaténation, des tirets, des retours à la ligne, etc. Voici un exemple de concaténation :&nbsp;
    </p>    <pre>
*********************************
Rapport de fin de formulaire :
 - Valeur du champ test formule : [Champ test formule]
 - Résultat de la formule : [Résultat formule]
*********************************
</pre>
    <p>
      Seules les parties entre crochets sont variables, elles prendront les valeurs du champ.
    </p></td>
  </tr>
</table>