<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    authorTime.sch :
    Contenu :
        Règles de contrôle de la date et heure de contribution d'un auteur au document 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="authorTime" is-a="IVL_TS">
    <p>Conformité de la date et heure de contribution d'un auteur au document, nullFlavor autorisé</p>
    <param name="elt" value="cda:author/cda:time"/>
    <param name="vue_elt" value="'author/time'"/>
    <param name="nullFlavor" value="1"/>
</pattern>