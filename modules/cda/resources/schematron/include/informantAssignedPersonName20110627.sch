<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    informantAssignedPersonName.sch :
    Contenu :
        Contrôle du nom d'un valideur du document 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="informantAssignedPersonName" is-a="personName">
    <p>Conformité du nom d'un informant agent de santé, nullFlavor autorisé</p>
    <param name="elt" value="cda:informant/cda:assignedEntity/cda:assignedPerson/cda:name"/>
    <param name="vue_elt" value="'informant/assignedEntity/assignedPerson/name'"/>
    <param name="nullFlavor" value="1"/>
</pattern>