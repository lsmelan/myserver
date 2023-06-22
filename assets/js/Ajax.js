export default class Ajax {
    constructor() {
        const _self = this;

        _self.makeRequest();
        _self.addEventListeners(_self);
    }

    addEventListeners(_self) {
        document.getElementById('server_filter_form_storage').addEventListener('change', function(event) {
            _self.makeRequest();
        });

        document.getElementById('server_filter_form_hdd').addEventListener('change', function(event) {
            _self.makeRequest();
        });

        document.getElementById('server_filter_form_location').addEventListener('change', function(event) {
            _self.makeRequest();
        });

        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="server_filter_form[ram][]"]');
        for (let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener('change', function(event) {
                _self.makeRequest();
            });
        }
    }

    makeRequest() {
        const filters = this.getFilters();

        fetch('/api/servers?per_page=15' + filters, {
            method: 'GET'
        })
            .then(response => response.json())
            .then(data => {
                if (undefined !== data.servers) {
                    this.renderList(data.servers);
                    this.renderPagination(data.total_servers, data.per_page);
                }
            })
            .catch(error => {
                console.error(error);
            });
    }

    getFilters() {
        let filters = '';

        const page = document.getElementById('server_filter_form_page').value;
        filters += '&page=' + page;

        const storageOptions = document.getElementById('server_filter_form_storage').value.split(',');
        for (let i = 0; i < storageOptions.length; i++) {
            if (storageOptions[i]) {
                filters += '&filtersOr[]=' + 'storage_index:' + storageOptions[i];
            }
        }

        const hdd = document.getElementById('server_filter_form_hdd').value;
        if (hdd) {
            filters += '&filters[]=' + 'hdd_index:' + hdd;
        }

        const location = document.getElementById('server_filter_form_location').value;
        if (location) {
            filters += '&filters[]=' + 'location_index:' + location;
        }

        const ramOptions = document.querySelectorAll('input[type="checkbox"][name="server_filter_form[ram][]"]');
        for (let i = 0; i < ramOptions.length; i++) {
            if (ramOptions[i].checked) {
                filters += '&filtersOr[]=' + 'ram_index:' + ramOptions[i].value;
            }
        }

        return filters;
    }

    renderList(data) {
        const listDiv = document.getElementById('list');
        listDiv.innerHTML = '';

        data.forEach(function(item) {
            const listItem = document.createElement('tr');
            listItem.className = 'list-item';

            listItem.innerHTML = `
            <tr>
                <td>${item.model}</td>
                <td>${item.ram}</td>
                <td>${item.hdd}</td>
                <td>${item.location}</td>
                <td>${item.price}</td>
            </tr>
        `;

            listDiv.appendChild(listItem);
        });
    }

    renderPagination(totalItems, itemsPerPage) {
        const _self = this;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const pagDiv = document.getElementById('pagination');
        pagDiv.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = 'list-inline-item';
            li.innerHTML = `<a class="text-decoration-none text-black js-set-page" href="#">${i}</a>`;
            pagDiv.appendChild(li);
        }

        const pages = document.querySelectorAll('.js-set-page');
        for (let i = 0; i < pages.length; i++) {
            pages[i].addEventListener('click', function(event) {
                event.preventDefault();
                const element = document.getElementById('server_filter_form_page');
                element.value = this.innerText;
                _self.makeRequest();
            });
        }
    }
}
