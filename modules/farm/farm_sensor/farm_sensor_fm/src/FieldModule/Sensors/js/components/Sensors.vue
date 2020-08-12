<template>
  <div class="container-fluid">
    <br>
    <div
      id="areas-and-location">
      <h4>Sensor Location</h4>

      <!-- We're using a radio button to choose whether areas are selected
      automatically based on device location, or using an autocomplete.
      This will use the useLocalAreas conditional var -->
      <div  v-if="useGeolocation" class="form-item form-item-name form-group">
        <div class="form-check">
          <input
          v-model="useLocalAreas"
          type="radio"
          class="form-check-input"
          id="dontUseGeo"
          name="geoRadioGroup"
          v-bind:value="false"
          checked>
          <label class="form-check-label" for="dontUseGeo">Search areas</label>
        </div>
        <div class="form-check">
          <input
          v-model="useLocalAreas"
          type="radio"
          class="form-check-input"
          id="doUseGeo"
          name="geoRadioGroup"
          v-bind:value="true"
          >
          <label class="form-check-label" for="doUseGeo">Use my location</label>
        </div>
      </div>

      <!-- If using the user's, show a select menu of nearby locations -->
      <div v-if="useLocalAreas" class="form-group">
        <label for="areaSelector">Filter sensors by their current location.</label>
        <select
          @input="selectedAreaTid = $event.target.value"
          class="form-control"
          name="areas">
          <option v-if="localAreas.length < 1" value="">No other areas nearby</option>
          <option v-if="localAreas.length > 0" value="" selected>-- Select an Area --</option>
          <option
            v-for="area in localAreas"
            :value="area.tid"
            v-bind:key="`area-${area.tid}`">
            {{area.name}}
          </option>
        </select>
      </div>

      <!-- If not using the user's location, show a search bar -->
      <farm-autocomplete
        v-if="!useLocalAreas"
        :objects="areas"
        searchKey="name"
        searchId="tid"
        label="Filter sensors by their current location."
        v-on:results="selectedAreaTid = $event">
        <template slot="empty">
          <div class="empty-slot">
            <em>No areas found.</em>
            <br>
            <button
              type="button"
              class="btn btn-light"
              name="button">
              Sync Now
            </button>
          </div>
        </template>
      </farm-autocomplete>

      <!-- Display the areas attached to each log -->
      <div class="form-item form-item-name form-group">
        <ul class="list-group">
          <li
            v-if="selectedAreaTid"
            v-bind:key="`log-${selectedArea.tid}-${Math.floor(Math.random() * 1000000)}`"
            class="list-group-item">
            {{ selectedArea.name }}
            <span class="remove-list-item" @click="selectedAreaTid = null">
              &#x2715;
            </span>
          </li>
        </ul>
      </div>
    </div>
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

const { isNearby } = window.farmOS.utils;

export default {
  name: 'Precipitation',
  components: { LineChart },
  data: () => ({
    useLocalAreas: false,
    localAreas: [],
    selectedAreaTid: null,
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
  props: ['assets', 'areas', 'useGeolocation'],
  created() {
    this.$store.dispatch('updateAssets');
  },
  computed: {
    selectedArea() { return this.areas.find(area => area.tid === this.selectedAreaTid)},
    sensorAssets() { 
      const sensorAssets = this.assets.filter(asset => asset.type === "sensor");

      let filteredAssets = sensorAssets;

      if (this.selectedArea) {
        filteredAssets = sensorAssets.filter(asset => asset.location.find(loc => loc.id === this.selectedArea.tid));
      }

      return filteredAssets;
     },
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
  watch: {
    useLocalAreas() {
      function filterAreasByProximity(position) {
        this.localAreas = this.areas.filter(area => !!area.geofield[0] && isNearby(
          [position.coords.longitude, position.coords.latitude],
          area.geofield[0].geom,
          (position.coords.accuracy),
        ));
      }
      function onError({ message }) {
        const errorPayload = { message, level: 'warning', show: false };
        this.$store.commit('logError', errorPayload);
      }
      // If useLocalAreas is set to true, get geolocation and nearby areas
      if (this.useLocalAreas) {
        const options = {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0,
        };

        const watch = navigator.geolocation.watchPosition(
          filterAreasByProximity.bind(this),
          onError.bind(this),
          options,
        );
        setTimeout(() => {
          navigator.geolocation.clearWatch(watch);
        }, 5000);
      }
    },
  },
};
</script>

<style>
  
</style>