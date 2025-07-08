document.addEventListener('DOMContentLoaded', function () {
    const app = {
        // --- Configuração e Estado ---
        config: {
            apiUrl: './api/index.php',
            tableName: '', 
            primaryKey: '',
            columns: [],
            alwaysHideInTable: ['senha', 'password'], 
        },
        state: {
            data: [],
            pagination: {},
            editingId: null,
            sortBy: '1',
            sortOrder: 'ASC',
        },

        // --- Elementos da UI ---
        elements: {
            tableTitle: document.getElementById('table-title'),
            tableHead: document.getElementById('table-head'),
            tableBody: document.getElementById('table-body'),
            modal: document.getElementById('form-modal'),
            modalTitle: document.getElementById('modal-title'),
            form: document.getElementById('data-form'),
            formFields: document.getElementById('form-fields'),
            addRecordButton: document.getElementById('add-record-btn'),
            paginationControls: document.getElementById('pagination-controls'),
            loadingIndicator: document.getElementById('loading'),
            // Botões do cabeçalho do modal
            saveButton: document.getElementById('save-header-btn'),
            cancelButton: document.getElementById('cancel-header-btn'),
        },

        // --- Inicialização ---
        init() {
            this.detectTableName();
            this.addEventListeners();
            this.fetchSchema();
        },

        detectTableName() {
            const path = window.location.pathname;
            const parts = path.split('/').filter(p => p);
            this.config.tableName = parts.pop() || 'usuarios';
            const title = `CRUD | ${this.capitalize(this.config.tableName)}`;
            document.title = title;
            this.elements.tableTitle.textContent = title;
        },

        addEventListeners() {
            this.elements.addRecordButton.addEventListener('click', () => this.openModal());
            this.elements.cancelButton.addEventListener('click', () => this.closeModal());
            this.elements.saveButton.addEventListener('click', () => this.elements.form.requestSubmit());
            this.elements.form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        },

        // --- Lógica da API ---
        async fetchSchema() {
            this.showLoading();
            try {
                const url = `${this.config.apiUrl}?action=schema&table=${this.config.tableName}`;
                const response = await fetch(url);
                const result = await this.handleApiResponse(response);

                if (result.status === 'success') {
                    this.config.columns = result.data;
                    this.config.primaryKey = this.findPrimaryKey(result.data);
                    this.state.sortBy = this.config.primaryKey || '1';
                    this.renderFormFields();
                    this.fetchData();
                } else {
                    this.showError('Error fetching schema: ' + result.message);
                }
            } catch (error) {
                this.showError(error.message);
            } finally {
                this.hideLoading();
            }
        },

        async fetchData(page = 1) {
            this.showLoading();
            try {
                const url = `${this.config.apiUrl}?action=read&table=${this.config.tableName}&page=${page}&sortBy=${this.state.sortBy}&sortOrder=${this.state.sortOrder}`;
                const response = await fetch(url);
                const result = await this.handleApiResponse(response);

                if (result.status === 'success') {
                    this.state.data = result.data;
                    this.state.pagination = result.pagination;
                    this.renderTable();
                    this.renderPagination();
                } else {
                    this.showError('Error fetching data: ' + result.message);
                }
            } catch (error) {
                this.showError(error.message);
            } finally {
                this.hideLoading();
            }
        },

        async handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(this.elements.form);
            const data = Object.fromEntries(formData.entries());

            if (data.senha && data.senha === '') {
                delete data.senha;
            }

            const url = this.state.editingId
                ? `${this.config.apiUrl}?action=update&table=${this.config.tableName}&id=${this.state.editingId}`
                : `${this.config.apiUrl}?action=create&table=${this.config.tableName}`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });
                const result = await this.handleApiResponse(response);

                if (result.status === 'success') {
                    this.closeModal();
                    this.fetchData(this.state.pagination.page);
                } else {
                    this.showError('Save error: ' + result.message);
                }
            } catch (error) {
                this.showError(error.message);
            }
        },

        async deleteRecord(id) {
            if (!confirm('Are you sure you want to delete this record?')) return;

            try {
                const url = `${this.config.apiUrl}?action=delete&table=${this.config.tableName}&id=${id}`;
                const response = await fetch(url, { method: 'GET' });
                const result = await this.handleApiResponse(response);

                if (result.status === 'success') {
                    this.fetchData(this.state.pagination.page);
                } else {
                    this.showError('Delete error: ' + result.message);
                }
            } catch (error) {
                this.showError(error.message);
            }
        },

        // --- Renderização da UI ---
        renderTable() {
            this.elements.tableHead.innerHTML = '';
            const headerRow = document.createElement('tr');
            this.getVisibleTableColumns().forEach(col => {
                const th = document.createElement('th');
                th.textContent = col.label || this.capitalize(col.Field);
                th.style.cursor = 'pointer';
                th.addEventListener('click', () => this.setSort(col.Field));
                if (this.state.sortBy === col.Field) {
                    th.innerHTML += this.state.sortOrder === 'ASC' ? ' &#9650;' : ' &#9660;';
                }
                headerRow.appendChild(th);
            });
            const actionsTh = document.createElement('th');
            actionsTh.textContent = 'Ações';
            headerRow.appendChild(actionsTh);
            this.elements.tableHead.appendChild(headerRow);

            this.elements.tableBody.innerHTML = '';
            this.state.data.forEach(row => {
                const tr = document.createElement('tr');
                this.getVisibleTableColumns().forEach(col => {
                    const td = document.createElement('td');
                    td.textContent = row[col.Field];
                    tr.appendChild(td);
                });

                const actionsTd = document.createElement('td');
                const editButton = document.createElement('button');
                editButton.innerHTML = `<i class="fas fa-edit"></i>`;
                editButton.className = 'btn btn-sm btn-primary me-1';
                editButton.title = 'Editar';
                editButton.addEventListener('click', () => this.openModal(row));
                actionsTd.appendChild(editButton);

                const deleteButton = document.createElement('button');
                deleteButton.innerHTML = `<i class="fas fa-trash"></i>`;
                deleteButton.className = 'btn btn-sm btn-danger';
                deleteButton.title = 'Deletar';
                deleteButton.addEventListener('click', () => this.deleteRecord(row[this.config.primaryKey]));
                actionsTd.appendChild(deleteButton);

                tr.appendChild(actionsTd);
                this.elements.tableBody.appendChild(tr);
            });
        },

        renderFormFields() {
            this.elements.formFields.innerHTML = '';
            this.getVisibleFormColumns().forEach(col => {
                if (col.Extra === 'auto_increment') return;

                const label = document.createElement('label');
                label.textContent = col.label || this.capitalize(col.Field);
                label.setAttribute('for', col.Field);

                const field = this.createInputField(col);

                this.elements.formFields.appendChild(label);
                this.elements.formFields.appendChild(field);
            });
        },

        renderPagination() {
            const { page, totalPages } = this.state.pagination;
            this.elements.paginationControls.innerHTML = '';
            if (totalPages <= 1) return;

            const createPageItem = (text, pageNum, isDisabled = false, isActive = false) => {
                const li = document.createElement('li');
                li.className = `page-item ${isDisabled ? 'disabled' : ''} ${isActive ? 'active' : ''}`;
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.innerHTML = text;
                if (!isDisabled) {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.fetchData(pageNum);
                    });
                }
                li.appendChild(a);
                return li;
            };

            const ul = document.createElement('ul');
            ul.className = 'pagination';

            ul.appendChild(createPageItem('Anterior', page - 1, page === 1));

            const pagesToShow = new Set();
            const window = 2;

            pagesToShow.add(1);
            for (let i = Math.max(2, page - window); i <= Math.min(totalPages - 1, page + window); i++) {
                pagesToShow.add(i);
            }
            pagesToShow.add(totalPages);

            let lastPage = 0;
            pagesToShow.forEach(p => {
                if (lastPage > 0 && p > lastPage + 1) {
                    ul.appendChild(createPageItem('...', -1, true));
                }
                ul.appendChild(createPageItem(p, p, false, p === page));
                lastPage = p;
            });

            ul.appendChild(createPageItem('Próxima', page + 1, page === totalPages));

            this.elements.paginationControls.appendChild(ul);
        },

        // --- Manipulação do Modal ---
        openModal(record = null) {
            this.elements.form.reset();
            this.elements.saveButton.innerHTML = '<i class="fas fa-check"></i>';
            this.elements.cancelButton.innerHTML = '<i class="fas fa-times"></i>';

            if (record) {
                this.state.editingId = record[this.config.primaryKey];
                this.elements.modalTitle.textContent = `Editar ${this.capitalize(this.config.tableName)}`;
                this.getVisibleFormColumns().forEach(col => {
                    if (this.elements.form[col.Field]) {
                        this.elements.form[col.Field].value = record[col.Field] || '';
                    }
                });
            } else {
                this.state.editingId = null;
                this.elements.modalTitle.textContent = `Adicionar ${this.capitalize(this.config.tableName)}`;
            }
            this.elements.modal.style.display = 'block';
        },

        closeModal() {
            this.elements.modal.style.display = 'none';
        },

        // --- Helpers & Utilitários ---
        getVisibleTableColumns() {
            return this.config.columns.filter(col => 
                !col.hidden && !this.config.alwaysHideInTable.includes(col.Field.toLowerCase())
            );
        },

        getVisibleFormColumns() {
            return this.config.columns.filter(col => !col.hidden);
        },

        findPrimaryKey(columns) {
            const pk = columns.find(c => c.Key === 'PRI');
            return pk ? pk.Field : null;
        },

        createInputField(column) {
            const fieldType = (column.type || '').toLowerCase();
            const field = fieldType === 'textarea' 
                ? document.createElement('textarea') 
                : document.createElement('input');

            if (fieldType !== 'textarea') {
                field.type = this.determineInputType(column);
            }

            field.id = column.Field;
            field.name = column.Field;
            if (column.required) {
                field.required = true;
            }
            return field;
        },

        determineInputType(column) {
            if (column.type) return column.type;

            const dbType = column.Type.toLowerCase();
            if (dbType.includes('int')) return 'number';
            if (dbType.includes('date')) return 'date';
            if (dbType.includes('datetime') || dbType.includes('timestamp')) return 'datetime-local';
            if (column.Field.toLowerCase().includes('email')) return 'email';
            if (column.Field.toLowerCase().includes('senha') || column.Field.toLowerCase().includes('password')) return 'password';
            return 'text';
        },

        setSort(column) {
            if (this.state.sortBy === column) {
                this.state.sortOrder = this.state.sortOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                this.state.sortBy = column;
                this.state.sortOrder = 'ASC';
            }
            this.fetchData();
        },

        async handleApiResponse(response) {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        },

        showLoading() {
            this.elements.loadingIndicator.style.display = 'block';
        },

        hideLoading() {
            this.elements.loadingIndicator.style.display = 'none';
        },

        showError(message) {
            console.error(message);
            alert(message);
        },

        capitalize(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
    };

    app.init();
});

