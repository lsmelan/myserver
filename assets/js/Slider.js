import noUiSlider from "nouislider";
import 'nouislider/dist/nouislider.css';

export default class Slider {
    constructor() {
        const arbitraryValuesSlider = document.getElementById('arbitrary-values-slider');
        const arbitraryValuesForSlider = [
            '0', '250GB', '500GB', '1TB', '2TB', '3TB', '4TB', '8TB', '12TB', '24TB', '48TB', '72TB'
        ];

        const format = {
            to: function (value) {
                return arbitraryValuesForSlider[Math.round(value)];
            },
            from: function (value) {
                return arbitraryValuesForSlider.indexOf(value);
            }
        };

        noUiSlider.create(arbitraryValuesSlider, {
            // start values are parsed by 'format'
            start: ['0', '72TB'],
            range: { min: 0, max: arbitraryValuesForSlider.length - 1 },
            step: 1,
            tooltips: true,
            format: format,
            pips: { mode: 'steps', format: format, density: 50 },
        });

        arbitraryValuesSlider.noUiSlider.on('change', function (values, handle, unencoded) {
            const start = ~~unencoded[0];
            const end = ~~unencoded[1];

            const range = arbitraryValuesForSlider.slice(start, end + 1);
            const element = document.getElementById('server_filter_form_storage');
            element.value = range.join(',');
            const event = new Event('change');
            element.dispatchEvent(event);
        });
    }
}
