== Description ==
De ce ati folosi Notificari SMS?

Simplu – este cel mai simplu si la indemana canal prin care le puteti comunica informatii despre comenzile acestora. SMS-ul ca si metoda de comunicare are o rata de deschidere de 95% si majoritatea sunt citite in 5 secunde de la primirea acestora. S-a constatat ca este de 3 ori mai productiv decat email-ul si pe departe cel mai usor de personalizat. De exemplu, in editarea statusului unui SMS de tip "Comanda finalizata" puteti include un cupon de reducere de 10% la urmatoarea comanda.

Nu este nevoie decat sa deveniti creativi, iar vanzarile dumneavoastra va vor depasi asteptarile!

Oferim o varietate de statusuri comenzi pentru o comunicare neintrerupta cu clientii dumneavoastra.

Caracteristici:

* Usor de instalat
* Usor de personalizat
* Detalii comanda: numar comanda, status comanda
* Setari extinse
* Functioneaza cu Magento 2.0+
* Posibilitatea de a trimite un SMS test catre orice numar (aveti posibilitatatea sa previzualizati notificarea ce urmeaza sa fie expediata)
* Posibilitatea de a trimite mesaje selectiv catre oricare dintre clientii care au plasat comenzi pe site-ul dvs.

== Installation ==  
Acest modul necesita sa aveti instalat Magento.

 1. magento zip se dezarhiveaza  
 2. se creaza un folder in app/code/ numit AnyPlaceMedia (asa cum am scris) si in folderul acesta un folder numit SendSMS. 
 3. continutul zip-ului se pune in acel folder. Adica fisierele finale trebuie sa fie in app/code/AnyPlaceMedia/SendSMS.
 4. configurati modulul din sectiunea Store -> Configurare
 
 Instalare Composer Github:
 
 Se ruleaza in linia de comanda:

composer config repositories.sendSMS-RO-sendsms-magento git git@github.com:sendSMS-RO/sendsms-magento2.4.git  

composer require anyplacemedia/sendsms dev-master  
 
 

