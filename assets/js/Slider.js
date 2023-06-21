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
    }
}
