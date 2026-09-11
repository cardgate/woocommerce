![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module voor WooCommerce

[![Build Status](https://travis-ci.org/cardgate/woocommerce.svg?branch=master)](https://travis-ci.org/cardgate/woocommerce)

## Support

Deze plugin is geschikt voor WooCommerce versie  **3.x** - **7.x**

## Voorbereiding

Voor het gebruik van deze module zijn CardGate RESTful gegevens nodig.  
Bezoek hiervoor [Mijn CardGate](https://my.cardgate.com/) en haal daar je gegevens op,  
of neem contact op met je accountmanager.  

## Installatie

1. Ga naar je **WordPress admin**.

2. De-installeer en **verwijder** de CardGate plug-in.

3. Installeer de nieuwe plug-in zoals een **eerste installatie**.

4. De eerdere instellingen blijven hierbij behouden.  
   **LET WEL:** Indien je update van **Woocommerce 2.x** naar **Woocommerce 3.x**,  
   moet je ook de **Merchant ID** en de **API key** invullen bij je Cardgate plug-in.
   
5. Voor de instellingen van de plug-in:  
   Ga naar [Mijn CardGate](https://my.cardgate.com/).  
   **Let Wel:** Voor **TestMode** kiest u na het inloggen van Mijn CardGate, uw gebruikers avatar **Rechts boven** en kies je **Naar Staging**.  
   Ga naar **Beheer, Sites** en klik op de **ID** van de site die je wilt instellen.  
   Kies bij **Technische Koppeling** de knop **Uw Webshop Instellen**, kies uw plug-in type en verstuur de data naar uw site.  

## Eerste installatie

1. Deze plug-in neemt aan dat er minimaal **WordPress versie 4.4 of hoger** geïnstalleerd is,  
   alsook de **WooCommerce plug-in 2.1.x of hoger**.

2. Ga in je **WordPress admin**, naar **Plugins**, en kies **Nieuwe plugin**.

3. Vul **cardgate** in bij het veld **Plugins zoeken...**

4. Klik op de knop **Nu installeren**, en daarna op **Activeren**.

5. Voor de instellingen van de plug-in:  
   Ga naar [Mijn CardGate](https://my.cardgate.com/).  
   **Let Wel:** Voor **TestMode** kiest u na het inloggen bij Mijn CardGate, uw gebruikers avatar **Rechts boven** en kies je **Naar Staging**.  
   Ga naar **Beheer, Sites** en klik op de **ID** van de site die je wilt instellen.  
   Kies bij **Technische Koppeling** de knop **Uw Webshop Instellen**, kies uw plug-in type en verstuur de data naar uw site.  

## Configuratie

Deze plug-in neemt aan dat er minimaal **WordPress versie 4.4 of hoger** geïnstalleerd is,  
alsook de **WooCommerce plug-in 2.1.x of hoger**.

1. Ga in je **WordPress admin**, naar **Plugins**, en kies **Nieuwe plugin**.

2. Vul **cardgate** in bij het veld **Plugins zoeken...**

3. Klik op de knop **Nu installeren**, en daarna op **Activeren**. 

4. Voor de instellingen van de plug-in:  
   Ga naar [Mijn CardGate](https://my.cardgate.com/).  
   **Let Wel:** Voor **TestMode** kiest u na het inloggen bij Mijn CardGate, uw gebruikers avatar **Rechts boven** en kies je **Naar Staging**.  
   Ga naar **Beheer, Sites** en klik op de **ID** van de site die je wilt instellen.  
   Kies bij **Technische Koppeling** de knop **Uw Webshop Instellen**, kies uw plug-in type en verstuur de data naar uw site.  

5. Aan de linkerkant van je WordPress admin, selecteer **CardGate, Instellingen**.

6. De instellingen zouden hier nu correct ingevuld moeten zijn, en kunnen indien gewenst ook handmatig ingevuld worden.

7. In de **admin** selecteer **WooCommerce, Instellingen, Afrekenen**.  
   Hier zie je de CardGate betaalmethoden.  
   **Let op:** De CardGate betaalmethoden zijn alleen zichtbaar in WooCommerce,  
   indien zowel de site ID als de hash key correct zijn ingevuld.  
   
8. Selecteer een **betaalmethode** en stel deze correct in.  
   Herhaal dit voor **iedere betaalmethode** die je wenst te activeren.

9. Zorg ervoor dat je **na het testen** bij de **CardGate instellingen** omschakelt van **Test mode** naar **Live mode** en sla het op (**Save**).

10. **Let Op:** De instellingen voor **Live Mode** kunnen verschillen van **Test Mode**, zie hiervoor **punt 4**.

## Abonnementen

De CardGate plug-in ondersteunt **terugkerende betalingen** via de
[WooCommerce Subscriptions](https://github.com/pronamic/woocommerce-subscriptions) plug-in.
De abonnementsfunctionaliteit is alleen actief wanneer **WooCommerce Subscriptions geïnstalleerd en
geactiveerd is**; zonder deze plug-in gedragen de CardGate betaalmethoden zich als gewone
eenmalige betaalmethoden.

### Ondersteunde betaalmethoden

Abonnementen zijn beschikbaar voor de volgende CardGate betaalmethoden:

- **Creditcard**
- **iDEAL**
- **Bancontact**
- **SEPA Incasso (Direct Debit)**
- **Sofort / Online Ueberweisen**
- **Przelewy24**

Alle andere CardGate betaalmethoden kunnen alleen gebruikt worden voor **eenmalige** orders;
WooCommerce Subscriptions biedt ze niet aan in de checkout wanneer de winkelwagen een
abonnementsproduct bevat.

### Ondersteunde abonnementsopties

Voor de hierboven genoemde betaalmethoden ondersteunt de plug-in:

- **Automatische verlengingen** – verlengingsorders worden automatisch door CardGate belast.
- **Opzeggen**, **opschorten** en **heractiveren** van een abonnement.
- **Bedragwijzigingen** en **datumwijzigingen** op een bestaand abonnement.
- **Wijzigen van de betaalmethode** door zowel de **klant** als de **beheerder** van de webshop.
- **Meerdere abonnementen** in één order.

### Hoe het werkt

1. Bij de **eerste (ouder)betaling** wordt de transactie bij CardGate gemarkeerd als **recurring**.
   De klant autoriseert tijdens die betaling het mandaat/de token.
2. Het CardGate transactie-ID van die eerste betaling wordt opgeslagen bij de order en bij het
   abonnement, en wordt gebruikt als **oudertransactie** voor alle volgende verlengingen.
3. Wanneer WooCommerce Subscriptions een **verlengingsbetaling** inplant, zet de plug-in een taak in
   de **Action Scheduler** die met WooCommerce meegeleverd wordt. De daadwerkelijke aanroep naar
   CardGate gebeurt op de achtergrond, zodat een langzame API-aanroep nooit een time-out van de
   checkout of van de Subscriptions-worker kan veroorzaken.
4. De verlengingsorder krijgt de status **in-wachtrij (on-hold)** totdat de CardGate **callback
   (notify)** de betaling bevestigt. Pas daarna wordt de order op **in behandeling/voltooid** gezet.
   Wanneer de recurring transactie niet aangemaakt kan worden, of de callback een mislukking meldt,
   wordt de verlengingsorder op **mislukt** gezet en handelt WooCommerce Subscriptions de herhaalpoging af.
5. Wanneer een klant de betaalmethode van een abonnement wijzigt, wordt er een **transactie met
   bedrag nul** aangemaakt om het mandaat opnieuw te autoriseren. Het nieuwe transactie-ID vervangt
   het opgeslagen ID.

### Vereisten en aandachtspunten voor abonnementen

- **WooCommerce Subscriptions** moet geïnstalleerd en geactiveerd zijn.
- **WP-Cron moet kunnen draaien**, omdat verlengingen verwerkt worden door de Action Scheduler, die
  door `wp-cron.php` gestart wordt. Op sites met weinig verkeer, of wanneer `DISABLE_WP_CRON` is
  ingesteld, doet de plug-in een afgeknepen (maximaal één keer per minuut) loopback-verzoek naar
  `wp-cron.php`, zodat wachtende verlengingen niet blijven staan. Heb je WP-Cron uitgeschakeld, zorg
  dan dat een **systeem-cron** minimaal **elke 5 minuten** `wp-cron.php` (of
  `wp cron event run --due-now`) aanroept.
- De **CardGate callback-URL** moet publiek bereikbaar zijn; verlengingen worden pas afgerond nadat
  de callback ontvangen is.
- Zorg ervoor dat terugkerende betalingen **voor jouw site zijn ingeschakeld in
  [My CardGate](https://my.cardgate.com/)** voor iedere betaalmethode die je voor abonnementen wilt gebruiken.
- Wachtende verlengingstaken zijn te bekijken onder **WooCommerce, Status, Geplande acties**
  (groep `cardgate`, actie `cardgate_process_recurring_payment`). Bij het deactiveren van de plug-in
  worden de wachtende CardGate verlengingstaken verwijderd.
- Test abonnementen eerst in **Test mode**; verlengingsdatums kunnen verkort worden met de
  testinstellingen van WooCommerce Subscriptions.

## Vereisten

Geen verdere vereisten.
