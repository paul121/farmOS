import Sensors from './components/Sensors';
import SensorsMenuBar from './components/SensorsMenuBar';
import SensorsWidget from './components/SensorsWidget';

export default {
  name: 'sensors',
  label: 'Sensors',
  widget: SensorsWidget,
  routes: [
    {
      name: 'sensors',
      path: '/sensors',
      components: {
        default: Sensors,
        menubar: SensorsMenuBar,
      },
    },
  ],
};