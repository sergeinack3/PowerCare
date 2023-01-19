{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="title">Formules math�matiques</th>
  </tr>
  <tr>
    <th class="category">Op�rations math�matiques courantes</th>
  </tr>
  <tr>
    <td>
    <ul>
      <li> Somme : + </li>
      <li> Soustraction : -&nbsp; </li>
      <li> Produit : * (�toile, pas X minuscule) </li>
      <li> Division : / (slash) </li>
      <li> Modulo : %</li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Op�rations utilisables avec les dates, date/heure et heure</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Nombre de minutes : Min(valeur) (attention � respecter la casse : "Min" n'est pas �quivalent � "min") </li>
      <li> Nombre d'heures : H(valeur) </li>
      <li> Nombre de jours : J(valeur) </li>
      <li> Nombre de semaines: Sem(valeur) </li>
      <li> Nombre de mois: M(valeur) </li>
      <li> Nombre d'ann�es: A(valeur) </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Op�rateurs plus avanc�s</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Racine carr�e: sqrt(valeur) </li>
      <li> Logarithme n�p�rien: log(valeur) </li>
      <li> Exponentielle : exp(valeur) </li>
      <li> Valeur absolue : abs(valeur) </li>
      <li> Arrondi sup�rieur : ceil(valeur) </li>
      <li> Arrondi inf�rieur : floor(valeur) </li>
      <li> Arrondi approch� : round(valeur) </li>
      <li> Factorielle : fac(valeur) </li>
      <li> Sinus : sin(valeur) </li>
      <li> Cosinus : cos(valeur) </li>
      <li> Tangente: tan(valeur) </li>
      <li> Arc sinus: asin(valeur) </li>
      <li> Arc cosinus: acos(valeur) </li>
      <li> Arc tangente: atan(valeur) </li>
      <li> Valeur al�atoire entre 0 et valeur : random(valeur) </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Op�rateurs � deux op�randes ou plus</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Exposant (2 valeurs) : pow(base, exposant) </li>
      <li> Maximum : max(valeur1, valeur2, valeurn...) </li>
      <li> Minimum : min(valeur1, valeur2, valeurn...) (attention � respecter la casse : "Min" n'est pas �quivalent � "min") </li>
    </ul></td>
  </tr>
  <tr>
    <th class="category">Op�rateurs logiques</th>
  </tr>
  <tr>
    <td class="text">
    <ul>
      <li> Et logique : A and B </li>
      <li> Ou logique : A or B </li>
      <li> Egal � : A == B </li>
      <li> N'est pas �gal � : A != B </li>
      <li> Sup�rieur ou �gal � : A >= B </li>
      <li> Strictement sup�rieur � : A > B </li>
      <li> Inf�rieur ou �gal � : A <= B </li>
      <li> Strictement inf�rieur � : A < B </li>
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
      Il est possible d'utiliser les parenth�ses comme dans toute formule math�matique. Voici un exemple :
    </p>    <pre>( [Champ test formule] + [Champ choix unique concept] ) * J( [Date A] - [Date B] ) / Sem( DateCourante - [Date B] )</pre>
    <p>
      Elle correspond au produit de la somme du champ <i>Champ test formule</i> et du champ <i>Champ choix unique concept</i> par le nombre de jours entre <i>Date A</i> et <i>Date B</i> le tout divis� par le nombre de semaines depuis la <i>Date B</i>.
    </p></td>
  </tr>
  <tr>
    <th class="title">Concat�nation de champs</th>
  </tr>
  <tr>
    <td class="text">
    <p>
      La concat�nation consiste � mettre bout � bout des donn�es textuelles ou num�riques.
    </p>
    <p>
      Le mode de param�trage est le m�me que les formules, � quelques diff�rences pr�s.
    </p>
    <p>
      Seuls les champs de type <i>Texte long</i> peuvent contenir le r�sultat de la concat�nation,
      et seulement les champs de type <i>Texte long</i>, <i>Texte court</i> et les num�riques peuvent �tre utilis�s pour y �tre ins�r�s.
    </p>
    <p>
      L'outil se pr�sente sous la m�me forme que pour les formules, il est possible d'ins�rer du texte libre dans la concat�nation, des tirets, des retours � la ligne, etc. Voici un exemple de concat�nation :&nbsp;
    </p>    <pre>
*********************************
Rapport de fin de formulaire :
 - Valeur du champ test formule : [Champ test formule]
 - R�sultat de la formule : [R�sultat formule]
*********************************
</pre>
    <p>
      Seules les parties entre crochets sont variables, elles prendront les valeurs du champ.
    </p></td>
  </tr>
</table>