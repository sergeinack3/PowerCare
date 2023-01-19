<?xml version="1.0" encoding="UTF-8"?>

<!--                  -=<<o#%@O[ CS8ModeleEnTete.sch ]O@%#o>>=-
    
    Règles de contrôle de l'en-tête CDA  d'un certificat CS8
    
    Historique :
    25/07/11 : CRI : Création
    
-->


<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="CS8ModeleEnTete">
    <p>Conformité de l'en-tête CDA au modèle du CS8</p>
    <rule context="cda:ClinicalDocument">
        <assert test="./cda:templateId[@root='1.2.250.1.213.1.1.1.5.1']"> 
            Erreur de conformité CS8 :
            L'élément ClinicalDocument/templateId doit être présent 
            avec @root="1.2.250.1.213.1.1.1.5.1".</assert>
        <assert test="cda:templateId[@root='1.3.6.1.4.1.19376.1.7.3.1.1.13.1']"> 
            Erreur: Le template parent "QRPH Health birth summary" (1.3.6.1.4.1.19376.1.7.3.1.1.13.1) doit être présent.
        </assert>
        <assert test="cda:templateId[@root='1.2.250.1.213.1.1.1.5']"> 
            Erreur: Le template parent "Certificat de Santé de l'Enfant" (1.2.250.1.213.1.1.1.5) doit être présent.
        </assert>
        
        <assert test="./cda:code[@code='CERT_DECL' and @codeSystem='1.2.250.1.213.1.1.4.12']"> 
            Erreur de conformité CS8 : 
            L'élément code doit avoir @code ="CERT_DECL" et @codeSystem = "1.2.250.1.213.1.1.4.12"/>. </assert>
    </rule>
</pattern>
