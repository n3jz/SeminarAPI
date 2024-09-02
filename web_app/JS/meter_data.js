// Preverimo dostop do strani
async function checkLogin() {
    const token = localStorage.getItem('token'); // Žeton je shranjen v localStorage

    if (!token) {
        alert("You need to login first!");
        window.location.href = 'login.html'; // Preusmeritev na prijavno stran, če žeton ni najden
        return;
    }

    try {
        const response = await fetch('/../API/check_login.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();

        if (!result.status) {
            alert(result.message);
            window.location.href = '/../index.html'; // Preusmeritev na prijavno stran, če žeton ni veljaven
        } else {
            console.log("Dostop do strani je dovoljen.");
            inicailizacija(); // Nadaljujemo izvajanje, če je dostop dovoljen
        }
    } catch (error) {
        console.error('There was a problem with the fetch operation:', error);
        alert("Napaka pri preverjanju prijave. Poskusite znova.");
        window.location.href = '/../index.html'; // Preusmeritev na prijavno stran ob napaki
    }
}

document.addEventListener('DOMContentLoaded', function() {
    checkLogin(); // Call checkLogin when the DOM is fully loaded
});

function inicailizacija() {
    // Zacetni in koncni datum
    var vnosKonciDatum = document.getElementById('endDate');
    var vnosZacetniDatum = document.getElementById('startDate');
    var obrazecDatumskoObdobje = document.getElementById('dateRangeForm');

    var zdaj = new Date();
    var vceraj = new Date();
    vceraj.setDate(zdaj.getDate() - 1);

    // Oblikovanje datumov v formatu YYYY-MM-DDTHH:MM 
    function oblikujDatum(datum) {
        return datum.toISOString().slice(0, 16);
    }

    vnosKonciDatum.value = oblikujDatum(zdaj);
    vnosZacetniDatum.value = oblikujDatum(vceraj);

    // Spremenljivke za shranjevanje intanc grafov
    var instancaGrafaMoci = null;
    var instancaGrafaEnergije = null;

    naloziStavbeInStevce(); // Inicailizacija podatkov

    // Funkcija za nalaganje stavb in stevcev
    async function naloziStavbeInStevce() {
        const token = localStorage.getItem('token'); // Pridobivanje žetona iz localStorage
    
        if (!token) {
            alert('Za ogled teh podatkov morate biti prijavljeni.');
            window.location.href = '/index.html'; // Preusmeritev na prijavno stran, če žeton ni najden
            return;
        }
    
        try {
            var odgovor = await fetch('/API/fetch_buildings_and_meters.php', {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token, // Vključitev žetona v Authorization glavo
                    'Content-Type': 'application/json'
                }
            });
    
            if (!odgovor.ok) {
                throw new Error('Odgovor omrežja ni ok');
            }
            var podatki = await odgovor.json();
            izpolniIzborStavb(podatki);
            if (podatki.length > 0) {
                naloziStevce(podatki[0].building_id);
            }
        } catch (napaka) {
            console.error('Napaka pri nalaganju stavb:', napaka);
            alert('Nalaganje podatkov o stavbah ni uspelo.');
        }
    }

    function izpolniIzborStavb(podatki) {
        var izborStavb = document.getElementById('buildingSelect');
        izborStavb.innerHTML = '';
        podatki.forEach(function(stavba) {
            var moznost = document.createElement('option');
            moznost.value = stavba.building_id;
            moznost.textContent = stavba.building_name;
            izborStavb.appendChild(moznost);
        });
        izborStavb.addEventListener('change', function() {
            naloziStevce(izborStavb.value);
        });
    }

    async function naloziStevce(buildingId) {
        const token = localStorage.getItem('token'); // Pridobivanje žetona iz localStorage
    
        if (!token) {
            alert('Za ogled teh podatkov morate biti prijavljeni.');
            window.location.href = '/index.html'; // Preusmeritev na prijavno stran, če žeton ni najden
            return;
        }
    
        try {
            var odgovor = await fetch('/API/fetch_buildings_and_meters.php?building_id=' + buildingId, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token, // Vključitev žetona v Authorization glavo
                    'Content-Type': 'application/json'
                }
            });
    
            var podatki = await odgovor.json();
            var stavba = podatki.find(function(b) {
                return b.building_id === buildingId;
            });
    
            var gumbiStevcov = document.getElementById('meterButtons');
            gumbiStevcov.innerHTML = '';
            if (stavba && stavba.meters) {
                stavba.meters.forEach(function(stevec) {
                    ustvariGumbStevca(stevec);
                });
            }
        } catch (napaka) {
            console.error('Napaka pri nalaganju stevcev:', napaka);
            alert('Nalaganje podatkov o stevcih ni uspelo.');
        }
    }

    function ustvariGumbStevca(stevec) {
        var gumb = document.createElement('button');
        gumb.textContent = 'Merilnik: ' + stevec.meter_name;
        gumb.className = 'gumb-stevec';
        gumb.dataset.meterId = stevec.meter_id; // Shranjvanje meter_id v data atributu
        gumb.addEventListener('click', function() {
            naloziPodatkeStevca(stevec.meter_id);
        });
        var gumbiStevcov = document.getElementById('meterButtons');
        gumbiStevcov.appendChild(gumb);
    }

    async function naloziPodatkeStevca(meterId) {
        var zacetniDatum = vnosZacetniDatum.value;
        var konciDatum = vnosKonciDatum.value;
    
        console.log('Pridobivanje podatkov za meter_id: ' + meterId + ', zacetni_datum: ' + zacetniDatum + ', konci_datum: ' + konciDatum);
    
        const token = localStorage.getItem('token'); // Pridobivanje žetona iz localStorage
    
        if (!token) {
            alert('Za ogled teh podatkov morate biti prijavljeni.');
            window.location.href = '/index.html'; // Preusmeritev na prijavno stran, če žeton ni najden
            return;
        }
    
        try {
            var odgovor = await fetch('/API/fetch_meter_data.php?meter_id=' + meterId + '&start_date=' + encodeURIComponent(zacetniDatum) + '&end_date=' + encodeURIComponent(konciDatum), {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token, // Vključitev žetona v Authorization glavo
                    'Content-Type': 'application/json'
                }
            });
            var podatki = await odgovor.json();
    
            // Unici obstojece grafe pred ponovnim risanjem
            if (instancaGrafaMoci) {
                instancaGrafaMoci.destroy();
            }
            if (instancaGrafaEnergije) {
                instancaGrafaEnergije.destroy();
            }
    
            // Nariši nove grafe
            instancaGrafaMoci = narisiGrafMoci(podatki);
            instancaGrafaEnergije = narisiGrafEnergije(podatki);
        } catch (napaka) {
            console.error('Napaka pri nalaganju podatkov stevca:', napaka);
        }
    }

    function narisiGraf(kontejnerId, nastavitve) {
        var graf = new ApexCharts(document.querySelector(kontejnerId), nastavitve);
        graf.render();
        return graf; // Vrni instanco grafa za kasnejse unicenje
    }

    function narisiGrafMoci(podatki) {
        return narisiGraf('#grafMoci', {
            chart: { type: 'line', height: '100%', toolbar: { show: false } },
            series: [{ name: 'Povprecna Moc', data: podatki.average_power.map(function(item) { return { x: new Date(item.timestamp), y: item.average_power }; }) }],
            xaxis: { type: 'datetime', title: { text: 'Datum in ura' } },
            yaxis: { title: { text: 'Povprečna moč [kW]' } },
            tooltip: { x: { format: 'dd MMM yyyy HH:mm' } }
        });
    }

    function narisiGrafEnergije(podatki) {
        return narisiGraf('#grafEnergije', {
            chart: { type: 'line', height: '100%', toolbar: { show: false } },
            series: [{ name: 'Skupna Energija', data: podatki.total_energy.map(function(item) { return { x: new Date(item.timestamp), y: item.total_energy }; }) }],
            xaxis: { type: 'datetime', title: { text: 'Datum in ura' } },
            yaxis: { title: { text: 'Skupna energija [kWh]' } },
            tooltip: { x: { format: 'dd MMM yyyy HH:mm' } }
        });
    }

    // Event listener za oddajo obrazca za posodobitev grafa
    obrazecDatumskoObdobje.addEventListener('submit', function(event) {
        event.preventDefault();
        // Pridobitev ID stevca iz prvega gumba (ali katerega koli izbranega)
        var meterId = document.querySelector('.gumb-stevec')?.dataset.meterId;
        if (meterId) {
            naloziPodatkeStevca(meterId);
        }
    });
}
