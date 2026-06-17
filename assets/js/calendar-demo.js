const yearInput = document.getElementById('year');
const countryInput = document.getElementById('country');
const regionInput = document.getElementById('region');
let calendar; // FullCalendar-Instanz

// Event Listeners
yearInput.addEventListener('input', loadHolidays);
countryInput.addEventListener('change', () => { // Bei Länderwechsel Regionen neu laden
  loadRegions();
});
regionInput.addEventListener('change', loadHolidays); // Bei Regionenwechsel Feiertage neu laden

/**
 * Lädt die verfügbaren Regionen für das ausgewählte Land und befüllt das Regionen-Dropdown.
 * Anschließend werden die Feiertage geladen.
 */
async function loadRegions() {
  const country = countryInput.value;
  const url = `feiertag.api.php?action=regions&country=${encodeURIComponent(country)}`;

  try {
    const res = await fetch(url);
    const data = await res.json();

    // Vorhandene Optionen löschen
    regionInput.innerHTML = '<option value="">(optional)</option>';

    if (data.regions && Object.keys(data.regions).length > 0) {
      for (const [code, name] of Object.entries(data.regions)) {
        const option = document.createElement('option');
        option.value = code;
        option.textContent = name;
        regionInput.appendChild(option);
      }
    }
    // Nach dem Laden der Regionen die Feiertage laden
    loadHolidays();
  } catch (err) {
    console.error('Fehler beim Laden der Regionen:', err);
    document.getElementById('result').innerHTML = `<p style="color:red;">Fehler beim Laden der Regionen: ${err.message || err}</p>`;
  }
}

function loadHolidays() {
  const year = parseInt(yearInput.value);
  const country = countryInput.value;
  const region = regionInput.value; // Wert aus dem Select-Feld

  // Validierung: Jahr im Bereich 1900 - 2100
  if (isNaN(year) || year < 1900 || year > 2100) {
    document.getElementById('result').innerHTML = `<p style="color:red;">Fehler: Das Jahr muss zwischen 1900 und 2100 liegen.</p>`;
    document.getElementById('apiUrlDisplay').style.display = 'none';
    document.getElementById('jsonData').textContent = '';
    return;
  }

  let url = `feiertag.api.php?year=${encodeURIComponent(year)}&country=${encodeURIComponent(country)}`;
  if (region) {
    url += `&region=${encodeURIComponent(region)}`;
  }

  // Aktuelle API-URL anzeigen
  const urlDisplay = document.getElementById('apiUrlDisplay');
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
  document.getElementById('copyBtn').addEventListener('click', () => {
    navigator.clipboard.writeText(fullUrl).then(() => {
      const btn = document.getElementById('copyBtn');
      const originalText = btn.textContent;
      btn.textContent = 'Kopiert!';
      btn.classList.replace('btn-outline-primary', 'btn-success');
      setTimeout(() => {
        btn.textContent = originalText;
        btn.classList.replace('btn-success', 'btn-outline-primary');
      }, 1500);
    });
  });

  fetch(url)
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP-Fehler! Status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      document.getElementById('jsonData').textContent = JSON.stringify(data, null, 2);

      if (data.error) {
        document.getElementById('result').innerHTML = `<p style="color:red;">Fehler: ${data.error}</p>`;
        return;
      }

      // Tabelle
      let html = `<div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="m-0">Feiertage ${data.year} – ${data.country}${data.region ? ' (' + data.region + ')' : ''}</h3>
                    <button id="csvExportBtn" class="btn btn-sm btn-success">Als CSV exportieren</button>
                  </div>`;
      html += `<table><tr><th>Datum</th><th>Feiertag</th></tr>`;
      
      // Feiertage in ein Array umwandeln und nach Datum (Value) sortieren
      const sortedHolidays = Object.entries(data.holidays).sort((a, b) => a[1].localeCompare(b[1]));

      // Lokale Formatierung basierend auf dem Land
      const locales = { 'DE': 'de-DE', 'AT': 'de-AT', 'CH': 'de-CH', 'PL': 'pl-PL' };
      const locale = locales[data.country] || 'de-DE';

      for (const [name, date] of sortedHolidays) {
        const [y, m, d] = date.split('-');
        const displayDate = new Date(y, m - 1, d).toLocaleDateString(locale);
        html += `<tr><td>${displayDate}</td><td>${name}</td></tr>`;
      }
      html += `</table>`;
      document.getElementById('result').innerHTML = html;

      // CSV Export Event Listener
      document.getElementById('csvExportBtn').addEventListener('click', () => {
        const csvRows = [['Datum', 'Feiertag']];
        sortedHolidays.forEach(([name, date]) => {
          csvRows.push([date, `"${name.replace(/"/g, '""')}"`]);
        });

        // BOM für Excel UTF-8 Support hinzufügen und mit Semikolon trennen
        const csvContent = "\ufeff" + csvRows.map(e => e.join(";")).join("\n");
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        const filename = `feiertage_${data.country}_${data.year}${data.region ? '_' + data.region : ''}.csv`;
        
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      });

      // Kalender-Events erstellen
      const events = Object.entries(data.holidays).map(([name, date]) => ({
        title: name,
        start: date,
        allDay: true,
        backgroundColor: '#ff6666',
        borderColor: '#cc0000',
        textColor: '#fff'
      }));

      // Kalender aktualisieren
      if (calendar) {
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

function initCalendar(events, year) {
  const calendarEl = document.getElementById('calendar');
  calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    initialDate: `${year}-01-01`,
    locale: 'de',
    height: 'auto',
    events: events
  });
  calendar.render();
}

// Beim Laden der Seite zuerst die Regionen laden, dann die Feiertage
loadRegions();