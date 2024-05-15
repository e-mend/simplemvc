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
            permission: {},
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
            createNewUser: {
                permission: {
                    'can_read_post': false,
                    'can_create_post': false,
                    'can_update_post': false,
                    'can_delete_post': false,
                    'can_see_deleted_posts': false,
                    'post_1': false,
                    'post_2': false,
                    'post_3': false,
                    'can_read_inventory': false,              
                    'can_create_inventory': false,
                    'can_update_inventory': false,
                    'can_delete_inventory': false,
                    'user': true,
                    'admin': false
                },
                email: '',
            },
            upper: false,
            number: false,
            special: false,
            loadingUsers: false,
            passwordFieldType: 'password',
            links: {},
        }
    },
    methods: {
        throwWarning(textMessage, classObject = {
            'alert-danger': true,
            'clipboard-copy': true
        }, config = {}) {
            const newMessage = {
                id: this.nextId++,
                text: textMessage,
                class: classObject,
                config: config
            };

            this.warnings.push(newMessage);

            setTimeout(() => {
                this.removeMessage(newMessage.id);
            }, 5000);
        },
        togglePasswordVisibility() {
            this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
        },
        async createLink(hasEmail = false) {
            try {
                if(!hasEmail) {
                    this.createNewUser.email = '';
                }

                const response = await fetch('/createlink', {
                    method: 'POST',
                    headers: {
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify(this.createNewUser)
                });

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }
                
                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }

                if(json['linkType'] === 'copy') {
                    this.throwWarning(json['message'] + `<i class="fa-solid fa-clipboard"></i>`, 
                    ['alert-success', 'clipboard-copy'], {
                        'data-clipboard-text': json['link']
                    });
                }else{
                    this.throwWarning(json['message'], ['alert-success']);
                }

                this.createNewUser.permission = {
                    'can_read_post': false,
                    'can_create_post': false,
                    'can_update_post': false,
                    'can_delete_post': false,
                    'can_see_deleted_posts': false,
                    'post_1': false,
                    'post_2': false,
                    'post_3': false,
                    'can_read_inventory': false,              
                    'can_create_inventory': false,
                    'can_update_inventory': false,
                    'can_delete_inventory': false,
                    'user': true,
                    'admin': false
                };

                this.inviteModal();
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
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

                this.throwWarning(json['message'], ['alert-success']);
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']); 
            }
        },
        async inviteModal() {
            $('#invite-modal').modal('show');

            try {
                const response = await fetch('/getlinks');

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    this.throwWarning(json['message']);
                    return;
                }

                this.links = json['links'];
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

                json['users'][0]['password'] = '';
                this.userToEdit = json['users'][0];                

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
        },
        async sendEmail(id) {
            try {
                const index = this.users.findIndex(user => user.id === id);

                if (index === -1) {
                    this.throwWarning('Algo deu errado', ['alert-danger']);
                    return;
                }

                const response = await fetch('/sendpasswordemail', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })

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
        isClicked(id) {
            const index = this.warnings.findIndex(message => message.id === id);
            if (index === -1) {
                return;
            }

            let obj = this.warnings[index].class;

            if(Object.values(obj).includes('clipboard-copy')) {
                navigator.clipboard.writeText(this.warnings[index]['config']['data-clipboard-text']);
            }
        },
        copyLink(id) {
            const index = this.links.findIndex(message => message.id === id);
            if (index === -1) {
                return;
            }

            navigator.clipboard.writeText(this.links[index]['link']);
            this.throwWarning(`Link copiado para a área de transferência <i class="fa-solid fa-clipboard"></i>`, 
            ['alert-secondary']);
        },
        loadOptions(option) {
            this.loadingR(true);

            if(this.blocked){
                this.option === 'main';
                return;
            }

            if(option === 'users' && !this.permission['admin']) {
                this.option === 'main';
                return;
            }

            if(option === 'safe' && !this.permission['can_read_post']) {
                this.option === 'main';
                return;
            }

            if(option === 'inventory' && !this.permission['can_read_inventory']) {
                this.option === 'main';
                return;
            }

            this.option = option;

            if(this.option === 'users') {
                this.getUsers();
            }
        },
        async changePermissions() {
            try {
                const response = await fetch('/changepermissions', {
                    method: 'POST',
                    headers: {
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify({
                        permission: this.userToEdit['permissions'],
                        id: this.userToEdit['id']
                    })
                });

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }
            } catch (error) {
                
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

                this.permission = json['user']['permission'];
                console.log(this.permission);

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