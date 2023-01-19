<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    authorPersonName.sch :
    Contenu :
        Contrôle du nom du responsable du document 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="authorPersonName" is-a="personName">
    <p>Conformité du nom d'une personne auteur, nullFlavor autorisé</p>
    <param name="elt" value="cda:assignedAuthor/cda:assignedPerson/cda:name"/>
    <param name="vue_elt" value="'assignedAuthor/assignedPerson/name'"/>
    <param name="nullFlavor" value="1"/>
</pattern>