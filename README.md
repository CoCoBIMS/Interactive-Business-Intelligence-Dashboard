# Cortina Consult - Business Intelligence Dashboard

Ein sicheres, authentifiziertes Web-Dashboard zur Anzeige von BI-Reports und Churn-Analysen.

## Projektstruktur

```
business-intelligence-dashboard/
├── .htaccess                      # Apache Authentifizierung
├── .htpasswd                      # Passwort-Datei (auf Server erstellen!)
├── api.php                        # Sichere Daten-API
├── dashboard_index.html           # Startseite mit Links
├── dashboard_bi_report.html       # Clockodo BI Report Dashboard
├── dashboard_churn_sentiment.html # Churn Sentiment Dashboard
├── data/                          # DATEN HIERHER!
│   ├── bi_reports/                # BI Report CSV-Dateien
│   │   ├── bi_YYYYMMDD_YYYYMMDD_summary.csv
│   │   ├── bi_YYYYMMDD_YYYYMMDD_kunden.csv
│   │   ├── bi_YYYYMMDD_YYYYMMDD_mitarbeiter.csv
│   │   ├── bi_YYYYMMDD_YYYYMMDD_forecast.csv
│   │   └── bi_YYYYMMDD_YYYYMMDD_pauschalen_deckung.csv
│   └── churn_sentiment_analysis.csv
└── logs/                          # Cron-Job Logs
```

---

## Benötigte Daten-Dateien

### 1. BI Report Dateien (data/bi_reports/)

Diese Dateien werden von `clockodoBI.py` generiert.

#### bi_YYYYMMDD_YYYYMMDD_summary.csv

Zusammenfassung nach Kategorie.

| Spalte | Beschreibung | Beispiel |
|--------|--------------|----------|
| Kategorie | Projektkategorie (a, av, b, c, unknown) | b |
| Label | Lesbare Bezeichnung | Beratung nach Aufwand (b) |
| Stunden | Geleistete Stunden | 194.0 |
| Umsatz_EUR | Umsatz in EUR | 25955.0 |
| Einträge | Anzahl Zeiteinträge | 202 |
| Umsatzrelevant | Ja/Nein | Ja |
| Abrechnungsart | inclusive/hourly/prepaid/unknown | hourly |

#### bi_YYYYMMDD_YYYYMMDD_kunden.csv

Kunden-Übersicht.

| Spalte | Beschreibung |
|--------|--------------|
| Kunde | Kundenname |
| Stunden_Pauschale_a_av | Stunden für Bereitstellung |
| Stunden_Beratung_b | Stunden für Beratung nach Aufwand |
| Stunden_Kontingent_c | Stunden für Budget/Kontingent |
| Stunden_gesamt | Summe aller Stunden |
| Umsatz_Beratung_b | Umsatz aus Beratung |
| Umsatz_Kontingent_c | Umsatz aus Kontingent |
| Umsatz_Gesamt | Gesamtumsatz |
| Interne_Kosten | Kosten intern |
| Deckungsbeitrag | Umsatz - Kosten |
| Deckungsbeitrag_Prozent | DB in Prozent |
| Kosten_Pauschale | Kosten für Pauschale-Leistungen |

#### bi_YYYYMMDD_YYYYMMDD_mitarbeiter.csv

Mitarbeiter-Übersicht.

| Spalte | Beschreibung |
|--------|--------------|
| Mitarbeiter | Name |
| Stunden_a | Stunden Kategorie A |
| Stunden_av | Stunden Kategorie AV |
| Stunden_b | Stunden Beratung |
| Stunden_c | Stunden Kontingent |
| Stunden_gesamt | Summe |
| Umsatz_nach_Aufwand_b | Umsatz Beratung |
| Budget_Verbrauch_c | Umsatz Budget |
| Umsatz_Gesamt | Gesamtumsatz |
| Anteil_Direkt_Prozent | Anteil direkt zurechenbar |
| Interne_Kosten | Kosten |
| Deckungsbeitrag | DB |
| Wochenstunden | Vertragliche Wochenstunden |
| Soll_Stunden | Soll im Zeitraum |
| Ist_Stunden | Tatsächlich gearbeitet |
| Urlaub_Tage | Urlaubstage |
| Krank_Tage | Krankheitstage |
| Auslastung_Prozent | Ist/Soll in % |

#### bi_YYYYMMDD_YYYYMMDD_forecast.csv

Monats-Hochrechnung.

| Spalte | Beschreibung |
|--------|--------------|
| Metrik | Name der Metrik |
| Aktuell | Aktueller Wert |
| Hochrechnung_Monatsende | Prognostizierter Endwert |
| Einheit | EUR, h, Prozent, Tage |

#### bi_YYYYMMDD_YYYYMMDD_pauschalen_deckung.csv

Pauschalen-Deckungsanalyse.

| Spalte | Beschreibung |
|--------|--------------|
| Kunde | Kundenname |
| Lexoffice_Match | Zugeordneter Lexoffice-Kunde |
| Pauschale_EUR | Monatliche Pauschale |
| Stunden_Inklusiv | Inklusiv-Stunden (a/av) |
| Interne_Kosten_EUR | Kosten für Inklusiv-Stunden |
| Deckung_EUR | Pauschale - Kosten |
| Deckung_Prozent | Deckung in % |
| Matched | Ja/Nein |

---

### 2. Churn Sentiment Datei (data/)

Diese Datei wird von `churn_sentiment_analyzer.py` generiert.

#### churn_sentiment_analysis.csv

| Spalte | Beschreibung | Typ |
|--------|--------------|-----|
| epic_schluessel | Jira Epic Key | CC-12345 |
| client_name | Kundenname | Firma GmbH |
| epic_status | Epic Status | Open, Done, DSB-Manage |
| dpms_leistung | Leistungsart | Datenschutz |
| **ist_churned** | Bereits abgewandert? | True/False |
| hat_kuendigung_erwaehnung | Kündigung erwähnt? | True/False |
| conversation_count | Anzahl Konversationen | 46 |
| has_conversations | Hat Konversationen? | True/False |
| has_jira_data | Hat Jira-Daten? | True/False |
| has_dpms_data | Hat DPMS-Daten? | True/False |
| data_quality | Datenqualität | high/medium/low |
| analyzed_at | Analyse-Zeitstempel | ISO 8601 |
| tage_seit_letztem_ticket | Tage ohne Ticket | 311 |
| dpms_tage_seit_letzter_aktivitaet | Tage DPMS inaktiv | 17 |
| frustration_level | Frustrations-Score | 1-10 |
| tone_deterioration | Ton-Verschlechterung | 1-10 |
| escalation_signals | Eskalations-Signale | 1-10 |
| positive_signals | Positive Signale | 0-10 |
| relationship_health | Beziehungsgesundheit | 1-10 |
| **churn_risk_score** | Abwanderungsrisiko | 1-100 |
| **overall_status** | Status-Bewertung | OK/ACHTUNG/KRITISCH |
| **client_phase** | Kundenphase | ONBOARDING/AKTIV/WARTUNG/RUHEND |
| kuendigung_kontext | Kontext der Kündigung | Text oder null |
| cortina_service_aktiv | Cortina aktiv? | true/false |
| key_concerns | Hauptprobleme | Text, getrennt durch \| |
| recommended_actions | Empfehlungen | Text, getrennt durch \| |
| summary_de | Zusammenfassung | Text auf Deutsch |

---

## Daten-Synchronisation

### Option A: Manuell kopieren

Kopieren Sie die CSV-Dateien aus dem JiraDPMS-Projekt:

```bash
# BI Reports
cp /pfad/zu/JiraDPMS/data/bi_reports/bi_*_*.csv ./data/bi_reports/

# Churn Analyse
cp /pfad/zu/JiraDPMS/data/churn_sentiment_analysis.csv ./data/
```

### Option B: Symbolische Links (empfohlen für lokale Entwicklung)

```bash
# BI Reports Ordner verlinken
ln -s /pfad/zu/JiraDPMS/data/bi_reports ./data/bi_reports

# Churn Datei verlinken
ln -s /pfad/zu/JiraDPMS/data/churn_sentiment_analysis.csv ./data/
```

### Option C: Cron-Job für automatische Synchronisation

```bash
crontab -e
```

Hinzufügen:

```cron
# Stündlich Daten synchronisieren
0 * * * * rsync -av /pfad/zu/JiraDPMS/data/bi_reports/*.csv /pfad/zu/dashboard/data/bi_reports/
0 * * * * cp /pfad/zu/JiraDPMS/data/churn_sentiment_analysis.csv /pfad/zu/dashboard/data/
```

---

## Server-Setup (Apache + PHP)

### 1. .htpasswd erstellen (auf dem Server!)

```bash
cd /var/www/html/dashboard  # Ihr Webverzeichnis
htpasswd -c .htpasswd cortina
# Passwort eingeben: pd2tutp7HWhVtKQWQ84e
```

### 2. .htaccess anpassen

Öffnen Sie `.htaccess` und setzen Sie den korrekten Pfad:

```apache
AuthUserFile /var/www/html/dashboard/.htpasswd
```

### 3. Apache konfigurieren

```bash
# Module aktivieren
sudo a2enmod rewrite auth_basic headers
sudo systemctl restart apache2
```

In der Apache-Config (z.B. `/etc/apache2/sites-available/000-default.conf`):

```apache
<Directory /var/www/html/dashboard>
    AllowOverride All
</Directory>
```

### 4. Berechtigungen

```bash
chmod 644 .htaccess .htpasswd api.php *.html
chmod 755 data/ data/bi_reports/ logs/
chmod 644 data/*.csv data/bi_reports/*.csv
```

---

## Lokales Testen

Für lokales Testen ohne Apache können Sie den PHP-Entwicklungsserver nutzen:

```bash
cd /pfad/zu/business-intelligence-dashboard
php -S localhost:8080
```

**Hinweis:** Der PHP-Entwicklungsserver unterstützt kein .htaccess! Zum Testen der Authentifizierung verwenden Sie Apache.

---

## Zugangsdaten

| Benutzer | Passwort |
|----------|----------|
| cortina | pd2tutp7HWhVtKQWQ84e |

**Nach Einrichtung unbedingt ändern:**

```bash
htpasswd .htpasswd cortina
```

---

## Fehlerbehebung

### Daten werden nicht angezeigt

1. Prüfen Sie ob CSV-Dateien existieren:
   ```bash
   ls -la data/bi_reports/
   ls -la data/churn_sentiment_analysis.csv
   ```

2. Testen Sie die API:
   ```bash
   curl -u cortina:PASSWORT http://localhost/api.php?action=status
   curl -u cortina:PASSWORT http://localhost/api.php?action=list_bi_reports
   ```

### 401 Unauthorized

- Pfad in AuthUserFile prüfen
- .htpasswd Berechtigungen prüfen (644)

### 500 Internal Server Error

- Apache Error-Log prüfen: `tail -f /var/log/apache2/error.log`
- PHP-Fehler prüfen

---

## Lizenz

Proprietary - Cortina Consult GmbH
