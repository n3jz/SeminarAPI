document.addEventListener('DOMContentLoaded', function() {
    const buildingSelect = document.getElementById('buildingSelect');
    const meterButtons = document.getElementById('meterButtons');

    // Initialize the building data and set up event listeners
    init();

    function init() {
        fetch('/charts/JS/fetch_buildings_and_meters.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                populateBuildingSelect(data);
                if (data.length > 0) {
                    loadMeters(data[0].building_id);  // Load meters for the first building initially
                }
            })
            .catch(error => {
                console.error('Error loading buildings:', error);
                alert('Failed to load building data.');
            });
    }

    function populateBuildingSelect(data) {
        buildingSelect.innerHTML = '';
        data.forEach(building => {
            let option = document.createElement('option');
            option.value = building.building_id;
            option.textContent = building.building_name;
            buildingSelect.appendChild(option);
        });
        buildingSelect.addEventListener('change', function() {
            loadMeters(this.value);
        });
    }

    function loadMeters(buildingId) {
        fetch(`/charts/JS/fetch_buildings_and_meters.php?building_id=${buildingId}`)
            .then(response => response.json())
            .then(data => {
                const building = data.find(b => b.building_id === buildingId);
                meterButtons.innerHTML = '';  // Clear previous buttons
                if (building && building.meters) {
                    building.meters.forEach(meter => createMeterButton(meter));
                }
            })
            .catch(error => {
                console.error('Error loading meters:', error);
                alert('Failed to load meter data.');
            });
    }

    function createMeterButton(meter) {
        let button = document.createElement('button');
        button.textContent = `Load ${meter.meter_name}`;
        button.classList.add('meter-button');
        button.onclick = () => loadMeterData(meter.meter_id);
        meterButtons.appendChild(button);
    }

    function loadMeterData(meterId) {
        fetch(`/charts/JS/fetch_meter_data.php?meter_id=${meterId}`)
            .then(response => response.json())
            .then(data => {
                renderPowerChart(data);
                renderEnergyChart(data);
            })
            .catch(error => console.error('Error loading meter data:', error));
    }

    function renderPowerChart(data) {
        const powerOptions = {
            chart: {
                type: 'line',
                height: '100%'
            },
            series: [{
                name: 'Average Power',
                data: data.average_power.map(item => ({x: new Date(item.timestamp), y: item.average_power}))
            }],
            xaxis: {
                type: 'datetime',
                title: {
                    text: 'Timestamp'
                }
            },
            yaxis: {
                title: {
                    text: 'Average Power (Watts)'
                }
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy HH:mm'
                }
            }
        };
        var powerChart = new ApexCharts(document.querySelector("#powerChart"), powerOptions);
        powerChart.render();
    }

    function renderEnergyChart(data) {
        const energyOptions = {
            chart: {
                type: 'line',
                height: '100%'
            },
            series: [{
                name: 'Total Energy',
                data: data.total_energy.map(item => ({x: new Date(item.timestamp), y: item.total_energy}))
            }],
            xaxis: {
                type: 'datetime',
                title: {
                    text: 'Timestamp'
                }
            },
            yaxis: {
                title: {
                    text: 'Total Energy (kWh)'
                }
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy HH:mm'
                }
            }
        };
        var energyChart = new ApexCharts(document.querySelector("#energyChart"), energyOptions);
        energyChart.render();
    }
});
