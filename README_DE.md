![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate Modul für WooCommerce

[![Build Status](https://travis-ci.org/cardgate/woocommerce.svg?branch=master)](https://travis-ci.org/cardgate/woocommerce)

## Support

Dieses Plug-in ist geeignet für WooCommerce Version **3.x** - **7.x**

## Vorbereitung

Um dieses Plug-in zu verwenden sind Zugangsdate zur CardGate RESTful API notwendig.  
Gehen Sie zu [Mein CardGate](https://my.cardgate.com/) und fragen Sie Ihre Zugangsdaten an, oder kontaktieren Sie Ihren Accountmanager.

## Installation

1. Gehen Sie zu Ihrem **WordPress Adminbereich**.

2. Deinstallieren und **entfernen** Sie das CardGate Plugin.

3. Installieren Sie das neue Plugin genau wie bei der **Erstinstallation**.

4. Die vorherigen Einstellungen bleiben unverändert.    
   **Achtung**: Bei einem Update von **Woocommerce 2.x** nach **Woocommerce 3.x**,    
   müssen Sie auch die **Merchant ID** und den **API key** in das CardGate Plug-in einfüllen.

5. Plug-in Einstellungen:  
   Gehen Sie zu [**Mein CardGate**](https://my.cardgate.com/).    
   *Achtung**: Um zu dem **Testmodus** zu gelangen, klicken Sie auf Ihren **User Avatar** oben rechts und wählen Sie **Staging**.    
   Gehen Sie zu **Verwaltung, Websites** und klicken Sie auf die ID Ihrer Seite, welche Sie installieren möchten.  
   Unter **Einstellungen Webseiten**, klicken Sie auf den Button **Einstellung** und wählen Sie das Plug-in aus, dass Sie verwenden möchten.    
   
## Erstinstallation

1. Für dies Plugin muss **WordPress version 4.4 oder höher** installiert sein  
   und **WooCommerce Plugin 3.x oder höher**.

2. Gehen zum, **WordPress Adminbereich**, selektieren Sie **Plugins**, und klicken Sie dann **Plugin hinzufügen**.  

3. Füllen Sie das Suchfeld ein mit **cardgate**.

4. Klicken Sie dann auf **installieren** und auf **aktivieren**.

5. Plug-in Einstellungen:  
   Gehen Sie zu [**Mein CardGate**](https://my.cardgate.com/).  
   *Achtung**: Um zu dem **Testmodus** zu gelangen, klicken Sie auf Ihr **User Avatar** oben rechts und wählen Sie **Staging** aus.  
   Gehen Sie zu **Verwaltung, Websites** und klicken Sie auf die ID Ihrer Seite, welche Sie installieren möchten.  
   Unter **Einstellungen Webseiten**, klicken Sie auf den Button **Einstellung** und wählen Sie das Plugin aus, dass Sie verwenden möchten.  

## Configuration

Für dieses Plugin muss **WordPress Version 4.4 oder höher** installiert sein  
und **WooCommerce Plugin 3.x oder höher**.

1. Gehen zum, **WordPress Adminbereich**, selektieren Sie **Plug-ins**, und klicken Sie dann **Plug-in hinzufügen**.  

2. Füllen Sie das Suchfeld ein mit **cardgate**.

3. Klicken Sie dann auf **installieren** und auf **aktivieren**. 

4. Plug-in Einstellungen:  
   Gehen Sie zu [**Mein CardGate**](https://my.cardgate.com/).  
   *Achtung**: Um zu dem **Testmodus** zu gelangen, klicken Sie auf Ihr **User Avatar** oben rechts und wählen Sie **Staging**.  
   Gehen Sie zu **Verwaltung, Websites** und klicken Sie auf die ID Ihrer Seite, welche Sie installieren möchten.  
   Unter **Einstellungen Webseiten**, klicken Sie auf den Button **Einstellung** und wählen Sie das Plugin aus, dass Sie verwenden möchten.  

5. Auf der linken Seite in Ihrem WordPress-Berich, bitte **CardGate, Settings** auswählen.  

6. Die Einstellungen sollten nun sichtbar sein und können, falls gewünscht, manuell bearbeitet werden.

7. Selektieren Sie in Ihrem Adminbereich **WooCommerce, Einstellungen, Checkout**.  
   Ihnen werden jetzt die CardGate Zahlungsmethoden angezeigt.  
   **Achtung**: Die CardGate Zahlungsmethoden werden nur sichtbar in WooCommerce, wenn die **Site ID** und der **Hash Key** korrekt eingefüllt sind.

8. Wählen Sie nun eine **Zahlungsmethode** aus und stellen Sie diese korrekt ein.  
   Wiederholen Sie den Vorgang **Für jede Zahlungsart**, die Sie aktivieren möchten.

9. Wenn Sie mit dem **Testen** abgeschlossen haben, gehen Sie dann zu Ihren CardGate Einstellungen  
   und schalten Sie den **Testmodus** in den **Livemodus** um und **speichern** Sie die Einstellung.
 
10. **Achtung**: Die Einstellungen für den  **Livemodus** können anders als die Einstellungen des  **Testmodus** sein. Siehe auch Punkt 4. 

## Abonnements

Das CardGate Plug-in unterstützt **wiederkehrende Zahlungen** über das
[WooCommerce Subscriptions](https://github.com/pronamic/woocommerce-subscriptions) Plug-in.
Die Abonnement-Funktionalität ist nur aktiv, wenn **WooCommerce Subscriptions installiert und
aktiviert** ist; ohne dieses Plug-in verhalten sich die CardGate Zahlungsmethoden wie normale
Einmalzahlungsmethoden.

### Unterstützte Zahlungsmethoden

Abonnements sind für die folgenden CardGate Zahlungsmethoden verfügbar:

- **Kreditkarte**
- **iDEAL**
- **Bancontact**
- **SEPA-Lastschrift (Direct Debit)**
- **Sofort / Online Ueberweisen**
- **Przelewy24**

Alle anderen CardGate Zahlungsmethoden können nur für **einmalige** Bestellungen verwendet werden;
WooCommerce Subscriptions bietet sie im Checkout nicht an, wenn der Warenkorb ein Abonnementprodukt
enthält.

### Unterstützte Abonnement-Optionen

Für die oben genannten Zahlungsmethoden unterstützt das Plug-in:

- **Automatische Verlängerungen** – Verlängerungsbestellungen werden automatisch von CardGate belastet.
- **Kündigung**, **Aussetzung** und **Reaktivierung** eines Abonnements.
- **Betragsänderungen** und **Datumsänderungen** bei einem bestehenden Abonnement.
- **Änderung der Zahlungsmethode** durch den **Kunden** sowie durch den **Shop-Administrator**.
- **Mehrere Abonnements** in einer Bestellung.

### Wie es funktioniert

1. Bei der **ersten (Eltern-)Zahlung** wird die Transaktion bei CardGate als **recurring** markiert.
   Der Kunde autorisiert während dieser Zahlung das Mandat bzw. das Token.
2. Die CardGate Transaktions-ID dieser ersten Zahlung wird bei der Bestellung und beim Abonnement
   gespeichert und dient als **Elterntransaktion** für alle folgenden Verlängerungen.
3. Wenn WooCommerce Subscriptions eine **Verlängerungszahlung** einplant, stellt das Plug-in einen
   Job in den **Action Scheduler** ein, der mit WooCommerce mitgeliefert wird. Der eigentliche Aufruf
   an CardGate erfolgt im Hintergrund, sodass ein langsamer API-Aufruf niemals einen Timeout des
   Checkouts oder des Subscriptions-Workers verursachen kann.
4. Die Verlängerungsbestellung erhält den Status **in Wartestellung (on-hold)**, bis der CardGate
   **Callback (notify)** die Zahlung bestätigt. Erst danach wird die Bestellung auf
   **in Bearbeitung/abgeschlossen** gesetzt. Kann die wiederkehrende Transaktion nicht erstellt
   werden oder meldet der Callback einen Fehlschlag, wird die Verlängerungsbestellung auf
   **fehlgeschlagen** gesetzt und WooCommerce Subscriptions übernimmt den erneuten Versuch.
5. Wenn ein Kunde die Zahlungsmethode eines Abonnements ändert, wird eine **Transaktion mit Betrag
   null** erstellt, um das Mandat erneut zu autorisieren. Die neue Transaktions-ID ersetzt die
   gespeicherte ID.

### Anforderungen und Hinweise für Abonnements

- **WooCommerce Subscriptions** muss installiert und aktiviert sein.
- **WP-Cron muss ausgeführt werden können**, da Verlängerungen vom Action Scheduler verarbeitet
  werden, der über `wp-cron.php` gestartet wird. Auf Seiten mit wenig Verkehr oder wenn
  `DISABLE_WP_CRON` gesetzt ist, sendet das Plug-in eine gedrosselte (maximal einmal pro Minute)
  Loopback-Anfrage an `wp-cron.php`, damit wartende Verlängerungen nicht liegen bleiben. Wenn Sie
  WP-Cron deaktiviert haben, stellen Sie sicher, dass ein **System-Cron** mindestens **alle 5
  Minuten** `wp-cron.php` (oder `wp cron event run --due-now`) aufruft.
- Die **CardGate Callback-URL** muss öffentlich erreichbar sein; Verlängerungen werden erst
  abgeschlossen, nachdem der Callback empfangen wurde.
- Stellen Sie sicher, dass wiederkehrende Zahlungen **für Ihre Website in
  [Mein CardGate](https://my.cardgate.com/)** für jede Zahlungsmethode aktiviert sind, die Sie für
  Abonnements verwenden möchten.
- Wartende Verlängerungsjobs können unter **WooCommerce, Status, Geplante Aktionen** eingesehen
  werden (Gruppe `cardgate`, Aktion `cardgate_process_recurring_payment`). Beim Deaktivieren des
  Plug-ins werden die wartenden CardGate Verlängerungsjobs entfernt.
- Testen Sie Abonnements zuerst im **Testmodus**; Verlängerungsdaten können mit den
  Testeinstellungen von WooCommerce Subscriptions verkürzt werden.

## Anforderungen

Keine weiteren Anforderungen.