document.addEventListener('DOMContentLoaded', function() {
    const buildingSelect = document.getElementById('buildingSelect');
    const meterButtons = document.getElementById('meterButtons');
    const chartCanvas = document.getElementById('chart');

    // Initial fetch to load buildings and their meters
    init();

    // Event listener for changing buildings
    buildingSelect.addEventListener('change', function() {
        loadMeters(this.value);
    });

    function init() {
        fetch('/charts/JS/fetch_buildings_and_meters.php')
            .then(response => response.json())
            .then(data => {
                populateBuildingSelect(data);
                // Automatically load meters for the first building if any
                if (data.length > 0) {
                    loadMeters(data[0].building_id);
                }
            })
            .catch(error => {
                console.error('Error loading buildings:', error);
                alert('Failed to load building data.');
            });
    }

    function populateBuildingSelect(data) {
        buildingSelect.innerHTML = ''; // Clear previous entries
        data.forEach(building => {
            let option = document.createElement('option');
            option.value = building.building_id;
            option.textContent = building.building_name;
            buildingSelect.appendChild(option);
        });
    }

    function loadMeters(buildingId) {
        // Find the selected building's data
        fetch('/charts/JS/fetch_buildings_and_meters.php')
            .then(response => response.json())
            .then(data => {
                const building = data.find(b => b.building_id == buildingId);
                meterButtons.innerHTML = ''; // Clear previous buttons
                if (building && building.meters) {
                    building.meters.forEach(meter => {
                        createMeterButton(meter);
                    });
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
        button.classList.add('meter-button'); // Optionally add some styling class
        button.onclick = () => loadMeterData(meter.meter_id);
        meterButtons.appendChild(button);
    }

    function loadMeterData(meterId) {
        fetch(`/charts/JS/fetch_meter_data.php?meter_id=${meterId}`)
        .then(response => response.json())
        .then(data => {
            renderChart(data);
        })
        .catch(error => console.error('Error loading meter data:', error));
    }
    

        
    function renderChart(data) {
        const options = {
            chart: {
                type: 'line', // Example: 'line' chart
                height: '100%',
                width: '100%'
            },
            series: [{
                name: 'Average Power',
                data: data.average_power.map(item => ({x: item.timestamp, y: item.average_power}))
            }, {
                name: 'Total Energy',
                data: data.total_energy.map(item => ({x: item.timestamp, y: item.total_energy}))
            }],
            xaxis: {
                type: 'datetime',
                title: {
                    text: 'Timestamp'
                }
            },
            yaxis: [{
                title: {
                    text: 'Average Power (Watts)'
                }
            }, {
                opposite: true,
                title: {
                    text: 'Total Energy (kWh)'
                }
            }],
            tooltip: {
                shared: true,
                intersect: false,
                x: {
                    format: 'dd MMM yyyy HH:mm'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    }
});

