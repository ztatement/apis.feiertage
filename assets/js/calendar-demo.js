/**
  * JavaScript für die Feiertags-API Demo-Seite.
  * Verwaltet die Interaktion mit dem Benutzer, lädt Daten von der API und aktualisiert die UI.
  */
  const yearInput = document.getElementById('year'); // Input-Feld für das Jahr
  const countryInput = document.getElementById('country'); // Select-Feld für das Land
  const regionInput = document.getElementById('region'); // Select-Feld für die Region
  let calendar; // Variable zur Speicherung der FullCalendar-Instanz
  const toggleJsonBtn = document.getElementById('toggleJsonBtn');
  const jsonDataPre = document.getElementById('jsonData');
  const bottomIcsExportBtn = document.getElementById('bottomIcsExportBtn');

  // Event Listeners
  yearInput.addEventListener('input', loadHolidays);
  countryInput.addEventListener('change', () => { // Bei Länderwechsel Regionen neu laden
    loadRegions();
  });
  regionInput.addEventListener('change', loadHolidays); // Bei Regionenwechsel Feiertage neu laden

  // Event Listener für den ICS-Export-Button am Ende der Seite
  if (bottomIcsExportBtn) {
    bottomIcsExportBtn.addEventListener('click', () => {
      const year = yearInput.value;
      const country = countryInput.value;
      const region = regionInput.value;
      const icsUrl = `ics.php?year=${year}&country=${country}${region ? '&region=' + region : ''}`;
      window.location.href = icsUrl; // Startet den Download mit den aktuellen Filtereinstellungen
    });
  }

  // Logik für das Auf-/Zuklappen der JSON-Rohdaten
  if (toggleJsonBtn) {
    toggleJsonBtn.addEventListener('click', () => {
      const isExpanded = jsonDataPre.classList.toggle('expanded');
      toggleJsonBtn.textContent = isExpanded ? 'Weniger anzeigen' : 'Mehr anzeigen';
    });
  }

/**
  * Lädt die verfügbaren Regionen für das ausgewählte Land und befüllt das Regionen-Dropdown.
  * Anschließend werden die Feiertage geladen.
  */
  async function loadRegions() {
    const country = countryInput.value;
    const url = `feiertag.api.php?action=regions&country=${encodeURIComponent(country)}`;

    // Führt einen asynchronen Fetch-Request aus
    try {
      const res = await fetch(url);
      const data = await res.json();

      // Vorhandene Optionen im Regionen-Dropdown löschen und Standardoption hinzufügen
      regionInput.innerHTML = '<option value="">(optional)</option>';

      // Wenn Regionen vorhanden sind, diese dem Dropdown hinzufügen
      if (data.regions && Object.keys(data.regions).length > 0) {
        for (const [code, name] of Object.entries(data.regions)) {
          const option = document.createElement('option');
          // Setzt den Wert und den angezeigten Text der Option
          option.value = code;
          option.textContent = name;
          regionInput.appendChild(option);
        }
      }
      // Nach dem Laden der Regionen die Feiertage laden
      loadHolidays();
    } catch (err) { // Fehlerbehandlung bei Problemen mit dem Fetch-Request
      console.error('Fehler beim Laden der Regionen:', err);
      document.getElementById('result').innerHTML = `<p style="color:red;">Fehler beim Laden der Regionen: ${err.message || err}</p>`;
    }
  }

/**
  * Lädt die Feiertage basierend auf den ausgewählten Kriterien (Jahr, Land, Region)
  * und aktualisiert die Anzeige (Tabelle, JSON, Kalender).
  */
  function loadHolidays() {
    const year = parseInt(yearInput.value); // Konvertiert den Jahreswert in eine Ganzzahl
    const country = countryInput.value;
    const region = regionInput.value; // Wert aus dem Select-Feld

    // Validierung: Jahr im Bereich 1900 - 2100
    if (isNaN(year) || year < 1900 || year > 2100) { // Prüft, ob das Jahr gültig ist
      document.getElementById('result').innerHTML = `<p style="color:red;">Fehler: Das Jahr muss zwischen 1900 und 2100 liegen.</p>`;
      document.getElementById('apiUrlDisplay').style.display = 'none';
      document.getElementById('jsonData').textContent = '';
      if (toggleJsonBtn) toggleJsonBtn.style.display = 'none';
      return;
    }

    let url = `feiertag.api.php?year=${encodeURIComponent(year)}&country=${encodeURIComponent(country)}`;
    if (region) { // Fügt die Region nur hinzu, wenn sie ausgewählt ist
      url += `&region=${encodeURIComponent(region)}`;
    }

    // Aktuelle API-URL anzeigen
    const urlDisplay = document.getElementById('apiUrlDisplay');
    // Erstellt eine vollständige URL, um sie im Link anzuzeigen
    // `window.location.href` stellt sicher, dass die Basis-URL korrekt ist
    const fullUrl = new URL(url, window.location.href).href;

    urlDisplay.innerHTML = `
      <div class="d-flex justify-content-between align-items-center">
        <div style="word-break: break-all;">
          <strong>API-Link:</strong> <a href="${fullUrl}" target="_blank">${fullUrl}</a>
        </div>
        <button class="btn btn-sm btn-outline-primary ms-3" id="copyBtn" style="white-space: nowrap;">Kopieren</button>
      </div>
    `;
    urlDisplay.style.display = 'block';

    // Event Listener für den Kopieren-Button
    // Muss hier hinzugefügt werden, da der Button dynamisch erzeugt wird
    document.getElementById('copyBtn').addEventListener('click', () => {
      navigator.clipboard.writeText(fullUrl).then(() => {
        const btn = document.getElementById('copyBtn');
        const originalText = btn.textContent;
        btn.textContent = 'Kopiert!';
        btn.classList.replace('btn-outline-primary', 'btn-success');
        // Setzt den Button nach 1,5 Sekunden auf den Originalzustand zurück
        setTimeout(() => {
          btn.textContent = originalText;
          btn.classList.replace('btn-success', 'btn-outline-primary');
        }, 1500);
      });
    });

    fetch(url)
      .then(res => { // Verarbeitet die Antwort des Servers
        if (!res.ok) {
          throw new Error(`HTTP-Fehler! Status: ${res.status}`);
        }
        return res.json();
      })
      .then(data => { // Verarbeitet die JSON-Daten
        document.getElementById('jsonData').textContent = JSON.stringify(data, null, 2);

        // Zeige den Button an, sobald Daten geladen wurden
        if (toggleJsonBtn) toggleJsonBtn.style.display = 'block';

        if (data.error) {
          document.getElementById('result').innerHTML = `<p style="color:red;">Fehler: ${data.error}</p>`;
          return;
        }

        // Tabelle
        // Erstellt die HTML-Struktur für die Feiertagstabelle
        let html = `<div class="d-flex justify-content-between align-items-center mb-2">
                      <h3 class="m-0">Feiertage ${data.year} – ${data.country}${data.region ? ' (' + data.region + ')' : ''}</h3>
                      <div class="btn-group">
                        <button id="csvExportBtn" class="btn btn-sm btn-success">CSV Export</button>
                        <button id="icsExportBtn" class="btn btn-sm btn-info">ICS Export</button>
                      </div>
                    </div>`;
        html += `<table><tr><th>Datum</th><th>Feiertag</th></tr>`;

        // Feiertage in ein Array umwandeln und nach Datum (Value) sortieren
        // `a[1].date` greift auf das Datum im Objekt `{ date: "...", type: "..." }` zu
        const sortedHolidays = Object.entries(data.holidays).sort((a, b) => a[1].date.localeCompare(b[1].date));

        // Lokale Formatierung basierend auf dem Land
        // Definiert die Locales für die Datumsformatierung
        const locales = { 'DE': 'de-DE', 'AT': 'de-AT', 'CH': 'de-CH', 'PL': 'pl-PL' };
        const locale = locales[data.country] || 'de-DE';

        for (const [name, info] of sortedHolidays) {
          const date = info.date;
          // Konvertiert das ISO-Datum in ein Date-Objekt und formatiert es länderspezifisch
          const [y, m, d] = date.split('-');
          const displayDate = new Date(y, m - 1, d).toLocaleDateString(locale);
          html += `<tr class="row-${info.type}"><td>${displayDate}</td><td>${name} <small>(${info.type})</small></td></tr>`;
        }
        html += `</table>`;
        document.getElementById('result').innerHTML = html;

        // CSV Export Event Listener
        // Muss hier hinzugefügt werden, da der Button dynamisch erzeugt wird
        document.getElementById('csvExportBtn').addEventListener('click', () => {
          const csvRows = [['Datum', 'Feiertag']];
          sortedHolidays.forEach(([name, info]) => {
            // Fügt das Datum und den Feiertagsnamen hinzu, escapet Anführungszeichen im Namen
            csvRows.push([info.date, `"${name.replace(/"/g, '""')}"`]); 
          });

          // BOM für Excel UTF-8 Support hinzufügen und mit Semikolon trennen
          const csvContent = "\ufeff" + csvRows.map(e => e.join(";")).join("\n");
          const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
          const link = document.createElement("a");
          const url = URL.createObjectURL(blob);
          const filename = `feiertage_${data.country}_${data.year}${data.region ? '_' + data.region : ''}.csv`;
          // Erstellt einen unsichtbaren Link und simuliert einen Klick, um den Download zu starten

          link.setAttribute("href", url);
          link.setAttribute("download", filename);
          link.style.visibility = 'hidden';
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        });

        // ICS Export Event Listener
        // Muss hier hinzugefügt werden, da der Button dynamisch erzeugt wird
        document.getElementById('icsExportBtn').addEventListener('click', () => {
          const icsUrl = `ics.php?year=${year}&country=${country}${region ? '&region=' + region : ''}`;
          window.location.href = icsUrl; // Leitet den Browser zur ICS-Datei um, um den Download zu starten
        });

        // Kalender-Events erstellen
        // Gruppiert Feiertage nach Datum, um Überlappungen zu erkennen und entsprechend zu färben
        // Gruppierung nach Datum für Mehrfarbigkeit bei Überlappung
        const eventsByDate = {};
        Object.entries(data.holidays).forEach(([name, info]) => {
          if (!eventsByDate[info.date]) eventsByDate[info.date] = [];
          eventsByDate[info.date].push({ name, type: info.type });
        });

        const events = [];
        for (const [date, list] of Object.entries(eventsByDate)) {
          // Erstellt einen Titel für das Kalenderereignis, der alle Feiertage an diesem Tag enthält
          const title = list.map(h => h.name).join(' / ');
          let className = 'event-national';

          // Logik für Farbanpassung
          const hasNational = list.some(h => h.type === 'national');
          const hasRegional = list.some(h => h.type === 'regional');
          // Bestimmt die CSS-Klasse basierend auf dem Typ der Feiertage

          if (hasNational && hasRegional) {
            className = 'event-combined';
          } else if (hasRegional) {
            className = 'event-regional';
          }

          events.push({
            title: title,
            start: date,
            allDay: true,
            classNames: [className] // Weist die entsprechende CSS-Klasse zu
          });
        }

        // Kalender aktualisieren
        if (calendar) {
          // Wenn der Kalender bereits existiert, Events entfernen und neue hinzufügen
          // und zum aktuellen Jahr springen
          calendar.removeAllEvents();
          calendar.addEventSource(events);
          calendar.gotoDate(`${year}-01-01`);
        } else {
          initCalendar(events, year);
        }
      })
      .catch(err => {
        document.getElementById('result').innerHTML = `<p style="color:red;">Fehler beim Laden der Feiertage: ${err.message || err}</p>`;
      });
  }

/**
  * Initialisiert die FullCalendar-Instanz.
  * @param {Array} events Die Liste der Kalenderereignisse.
  * @param {number} year Das initiale Jahr für den Kalender.
  */
  function initCalendar(events, year) {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      initialDate: `${year}-01-01`,
      locale: 'de', // Setzt die Sprache des Kalenders auf Deutsch
      height: 'auto', // Passt die Höhe des Kalenders automatisch an
      events: events // Die zu rendernden Ereignisse
    });
    calendar.render();
  }

  // Beim Laden der Seite zuerst die Regionen laden, dann die Feiertage
  loadRegions();
