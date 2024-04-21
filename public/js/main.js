function getCookie(name) {
    let cookieValue = null;
    if (document.cookie && document.cookie !== '') {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            if (cookie.substring(0, name.length + 1) === (name + '=')) {
                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                break;
            }
        }
    }
    return cookieValue;
}

const app = new Vue({
    el: '#app',
    data() {
        return {
            warnings: [],
            nextId: 0,
            loading: 0,
            intervalId: null,
            option: '',
            user: {},
            blocked: false,
            permissions: {},
            searchModalOpen: false,
            showModal: false,
            users: {},
            userSearch: {
                deleted: false,
                new: false,
                favorites: false,
                all: true,
                pagination: 1
            },
            userToEdit: {
                password: '',
                permission: {
                    
                }
            },
            upper: false,
            number: false,
            special: false,
            loadingUsers: false,
            passwordFieldType: 'password'
        }
    },
    methods: {
        throwWarning(textMessage, classObject = {
            'alert-danger': true
        }) {
            const newMessage = {
                id: this.nextId++,
                text: textMessage,
                class: classObject
            };

            this.warnings.push(newMessage);

            setTimeout(() => {
                this.removeMessage(newMessage.id);
            }, 5000);
        },
        togglePasswordVisibility() {
            this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
        },
        passwordEnter() {
            let upperRegex = /[A-Z]/g;

            if(upperRegex.test(this.userToEdit.password)) {
                this.upper = true;
            }else{
                this.upper = false;
            }

            let numberRegex = /[\d]/g;

            if(numberRegex.test(this.userToEdit.password)) {
                this.number = true;
            }else{
                this.number = false;
            }

            let specialRegex = /[@$!%*?&]/g;

            if(specialRegex.test(this.userToEdit.password)) {
                this.special = true;
            }else{
                this.special = false;
            }
        },
        async changePassword(id) {
            try {
                const index = this.users.findIndex(user => user.id === id);

                if (index === -1) {
                    this.throwWarning('Algo deu errado', ['alert-danger']);
                    return;
                }

                const response = await fetch('/updatepassword', {
                    method: 'POST',
                    headers: {
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        password: this.userToEdit.password
                    })
                });

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }
                
                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }

                this.throwWarning(json['message'], ['alert-success']);

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
            
        },
        async updateUser(id) {
            try {
                const index = this.users.findIndex(user => user.id === id);

                if (index === -1) {
                    this.throwWarning('Algo deu errado', ['alert-danger']);
                    return;
                }

                const response = await fetch('/updateuser', {
                    method: 'POST',
                    headers: {
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify(this.userToEdit)
                });

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }

                console.log(json);

                this.throwWarning(json['message'], ['alert-success']);
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']); 
            }
        },
        async userModal(id) {
            $('#userModal').modal('show');

            try {
                const response = await fetch('/getusers?id='+id);

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    this.throwWarning(json['message']);
                    return;
                }

                this.throwWarning(json['message'], ['alert-success']);

                json['users'][0]['permission'] = json['users'][0]['permission'].reduce((obj, item, index) => {
                    obj[item] = true;
                    return obj;
                }, {});

                this.userToEdit = json['users'][0];   
                console.log(this.userToEdit);                 

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
        },
        async toggleFavorite(id) {
            try {
                const index = this.users.findIndex(user => user.id === id);

                if (index === -1) {
                    this.throwWarning('Algo deu errado', ['alert-danger']);
                    return;
                }

                this.users[index].favorite = !this.users[index].favorite;

                const response = await fetch('/togglefavorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        favorite: this.users[index].favorite
                    })
                })

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error('Algo deu errado');
                }

                this.throwWarning(json['message'], ['alert-success']);

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
                this.users[index].favorite = !this.users[index].favorite;
            }
        },
        async logout() {
            try {
                const response = await fetch('/logout');

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error('Erro no servidor');
                }

                this.throwWarning(json['message'], ['alert-success']);

                window.location.href = '/';
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
                this.blocked = true;
            }
        },
        async getUsers(type = 'all', pagination = 1) {
            this.loadingUsers = true;
            this.loadingR();

            let url = '/getusers';
            let first = true;
            
            if(type !== 'reload' && pagination === 1) {
                this.userSearch[type] = !this.userSearch[type]; 
            }

            if(this.userSearch['deleted']) {
                url += (first ? '?' : '&') + 'deleted=true';
                first = false;
            }

            if(this.userSearch['new']) {
                url += (first ? '?' : '&') + 'new=true';
                first = false;
            }

            if(this.userSearch['favorites']) {
                url += (first ? '?' : '&') +  'favorites=true';
                first = false;
            }

            if(this.userSearch['all']) {
                url = '/getusers?all=true';
                this.userSearch['all'] = false;
                this.userSearch['deleted'] = false;
                this.userSearch['new'] = false;
                this.userSearch['favorites'] = false;
            }

            url += (first ? '?' : '&') + 'pagination=' + pagination;

            try {
                const response = await fetch(url);

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    this.throwWarning(json['message']);
                    return;
                }

                this.throwWarning(json['message'], ['alert-success']);
                this.users = json['users'];
                this.userSearch.pagination = json['count'];

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }

            this.loadingUsers = false;
        },
        removeMessage(id) {
            const index = this.warnings.findIndex(message => message.id === id);
            if (index !== -1) {
                this.warnings.splice(index, 1);
            }
        },
        loadOptions(option) {
            this.loadingR(true);
            this.option = option;  

            if(this.option === 'users') {
                this.getUsers();
            }
        },
        loadingR(force = false) {
            if(force) {
                clearInterval(this.intervalId);
                this.intervalId = null;
                this.loading = 0;
            }

            if(this.intervalId != null) {
                return;
            }

            this.intervalId = setInterval(() => {
                this.loading += 5;

                if(this.loading > 100) {
                    clearInterval(this.intervalId);
                    this.intervalId = null;
                }
            }, 100);
        },
        async getUserData() {
            this.blocked = true;
            try {
                const response = await fetch('/userdata');

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error('Erro no servidor');
                }

                this.throwWarning(json['message'], ['alert-success']);

                this.user = json['user'];

                const perm = json['user']['permission'].reduce((obj, item, index) => {
                    obj[item] = true;
                    return obj;
                }, {});

                this.permissions = perm;
                console.log(this.permissions);

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
                this.blocked = true;
            }

            this.blocked = false;
        }
    },
    computed: {
        iconClass() {
            return this.passwordFieldType === 'password' ? 'fa fa-eye-slash' : 'fa fa-eye';
        }
    },
    beforeMounted() {


    },
    mounted() {
        this.loadingR();
        this.getUserData();
        this.option = 'main';
    
    }
});