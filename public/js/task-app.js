new Vue({
    el: '#task-app',
    data: {
        loading: false,
        tasks: [],
        formMode: false,
        formData: {},
        errors: {},
        currentPage: 1,
        total: 0,
        hasMorePages: true,
        filters: {
            minDate: null,
            maxDate: null
        }
    },
    methods: {
        loadTasks() {
            let vm = this;

            let reqParams = {
                page: vm.currentPage
            };

            if (vm.filters.minDate) {
                reqParams['minDate'] = vm.filters.minDate;
            }

            if (vm.filters.maxDate) {
                reqParams['maxDate'] = vm.filters.maxDate;
            }

            vm.loading = true;

            axios.get('/task', {
                params: reqParams
            })
                .then(function(res) {
                    vm.tasks = res.data.items;
                    vm.total = res.data.total;
                    vm.currentPage = res.data.currentPage;
                    vm.hasMorePages = res.data.hasMorePages;
                    vm.loading = false;
                })
                .catch(vm.handleError);
        },

        createTask() {
            let vm = this;

            vm.errors = {};
            vm.formMode = true;
            vm.formData = new TaskEntity({});
        },

        cancelForm() {
            let vm = this;

            vm.formMode = false;
            vm.formData = {};
            vm.errors = {};
        },

        saveTask() {
            let vm = this;

            vm.loading = true;

            axios.post('/task', vm.formData)
                .then(function() {
                    vm.currentPage = 1;
                    vm.loadTasks();
                    vm.cancelForm();
                    vm.loading = false;
                })
                .catch(vm.handleError);
        },

        handleError(err) {
            let vm = this;

            if (err.response && err.response.status === 422) {
                let errorBag = {};

                for (let key in err.response.data.errors) {
                    if (!err.response.data.errors.hasOwnProperty(key)) {
                        continue;
                    }

                    errorBag[key] = err.response.data.errors[key];
                }

                vm.errors = errorBag;
            }

            vm.loading = false;
        },

        nextPage() {
            let vm = this;

            vm.currentPage = vm.currentPage + 1;
            vm.loadTasks();
        },

        prevPage() {
            let vm = this;

            vm.currentPage = vm.currentPage - 1;
            vm.loadTasks();
        },

        clearFilters() {
            let vm = this;

            vm.filters.minDate = null;
            vm.filters.maxDate = null;

            vm.loadTasks();
        }
    },

    computed: {
        hasFilters() {
            let vm = this;

            return vm.filters.minDate || vm.filters.maxDate;
        },

        downloadPdfLink() {
            let vm = this;
            let reqParams = {};

            if (vm.filters.minDate) {
                reqParams['minDate'] = vm.filters.minDate;
            }

            if (vm.filters.maxDate) {
                reqParams['maxDate'] = vm.filters.maxDate;
            }

            if (Object.keys(reqParams).length) {
                return '/download-pdf?' + new URLSearchParams(vm.filters).toString();
            }

            return '/download-pdf';
        }
    },

    mounted() {
        let vm = this;

        vm.$watch('filters', function() {
            vm.currentPage = 1;
            vm.loadTasks();
        }, {deep: true});

        vm.loadTasks();
    }
});

function TaskEntity(data) {
    this.id = data.id || null;
    this.title = data.title || '';
    this.comment = data.comment || '';
    this.createdAt = data.createdAt || moment().format('YYYY-MM-DD');
    this.timeSpent = data.timeSpent || 60;
}
