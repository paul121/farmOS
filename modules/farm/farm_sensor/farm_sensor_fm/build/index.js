import app from 'farmos-client/src/core/app';
import logs from 'farmos-client/src/field-modules/my-logs/module.config';
import sensors from '../src/FieldModule/Sensors/js/module.js';

app('#app', [logs, sensors]);