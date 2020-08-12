<template>
  <div class="container-fluid">
    <br>
    <select v-model="selectedAsset" @change="loadValues">
            <option
              v-for="(asset, i) in sensorAssets"
              v-bind:value="asset"
            >
              {{ asset.name }} - {{ asset.type }}
            </option>
    </select>

    <select v-model="selectedValues" @change="updateDateRange">
      <option
        v-for="(info, name) in sensorValues"
        v-bind:value="{name: name, ...info}"
      >
        {{ name }}
      </option>
    </select>
    <farm-date-time-form :timestamp="startTimestamp" @input="updateStart"/>
    <farm-date-time-form :timestamp="endTimestamp" @input="updateEnd"/>
    <button @click="loadData(selectedAsset)" v-bind:disabled="!selectedAsset">Load data</button>
    <br>
    <div>
      <line-chart
        v-if="loaded"
        :styles="styles"
        :chartdata="datasets"
        :options="options"/>
    </div>

  </div>
</template>

<script>
import LineChart from './Chart.vue';

const { parseNotes } = window.farmOS.utils;
export default {
  name: 'Precipitation',
  components: { LineChart },
  data: () => ({
    startTimestamp: Date.now() / 1000,
    endTimestamp: Date.now() / 1000,
    selectedAsset: null,
    selectedValues: [],
    sensorValues: [],
    datasets: null,
    options: null,
    styles: {
      height: '500px',
      position: 'relative',
    },
    loaded: false,
  }),
  props: ['assets'],
  created() {
    this.$store.dispatch('updateAssets');
  },
  computed: {
    sensorAssets() { return this.assets.filter(asset => asset.type === "sensor")},
  },
  methods: {
    updateStart(event) {
      console.log(event);
      this.startTimestamp = event;
    },
    updateEnd(event) {
      console.log(event);
      this.endTimestamp = event;
    },
    updateDateRange() {
      console.log(this.selectedValues);

      this.startTimestamp = this.selectedValues.first;
      this.endTimestamp = this.selectedValues.last;
    },
    loadValues() {
      console.log(this.selectedAsset);

      return this.sensorRequest(this.selectedAsset, '/farm/sensor/listener/values/', [])
      .then(values => {
        console.log(values);
        this.sensorValues = values;
      })
    },
    loadData(asset) {
      console.log("loading data...");

      const name = this.selectedValues.name;

      let params = [];
      params.push({key: 'start', value: this.startTimestamp});
      params.push({key: 'end', value: this.endTimestamp});
      params.push({key: 'name', value: name});

      return this.sensorRequest(asset, '/farm/sensor/listener/', params)
      .then(data => {
        console.log(data)

        this.options = {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            xAxes: [
              {
                type: 'time',
                time: {
                  unit: 'day',
                  displayFormats: {
                    'day': 'll',
                  }
                }
              }
            ]
          }
        }

        this.datasets = {
          datasets: [
            {
              label: name,
              data: [],
              fill: false,
              borderColor: "darkblue",
              backgroundColor: "lightblue",
            }
          ],
        };

        data.forEach(value => {
          this.datasets.datasets[0].data.push({x: parseInt(value.timestamp) * 1000, y: parseFloat(value[name]) });
        })

        this.loaded = true; 
      });
    },
    sensorRequest(asset, path, params) {
      const settings = asset.sensor_settings;
      const public_key = settings.public_key;
      const private_key = settings.private_key;
      if (!(public_key && private_key)) {
        console.log("Not configured");
        return;
      }

      console.log("loading data...");
      let url = new URL(encodeURI(`http://${window.location.hostname}${path}${public_key}`));
      url.searchParams.append('private_key', private_key);

      params.forEach(param => url.searchParams.append(param.key, param.value));

      return fetch(url)
      .then(response => response.json())
    }
  },
};
</script>

<style>
  
</style>