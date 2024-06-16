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
            times: 0,
            secs: 0,
            warnings: [],
            nextId: 0,
            loading: 0,
            intervalId: null,
            option: '',
            user: {},
            blocked: false,
            permission: {
                'can_read_safe': false,
                'can_create_safe': false,
                'can_update_safe': false,
                'can_disable_safe': false,
                'can_see_disabled_safe': false,
                'safe_1': false,
                'safe_2': false,
                'safe_3': false,
                'can_read_inventory': false,              
                'can_create_inventory': false,
                'can_update_inventory': false,
                'can_disable_inventory': false,
                'can_see_disabled_inventory': false,
                'user': true,
                'admin': false
            },
            searchModalOpen: false,
            showModal: false,
            users: {},
            items: {},
            userSearch: {
                deleted: false,
                new: false,
                favorites: false,
                all: true,
                pagination: 1,
                search: '',
            },
            itemSearch: {
                deleted: false,
                new: false,
                favorites: false,
                all: true,
                pagination: 1,
                search: '',
                type: 'all',
                from: '',
                to: '',
            },
            safeSearch: {
                deleted: false,
                new: false,
                favorites: false,
                all: true,
                pagination: 1,
                search: '',
                type: 'all',
                from: '',
                to: '',
            },
            userToEdit: {
                password: '',
                permission: {
                    'can_read_safe': false,
                    'can_create_safe': false,
                    'can_update_safe': false,
                    'can_disable_safe': false,
                    'can_see_disabled_safe': false,
                    'safe_1': false,
                    'safe_2': false,
                    'safe_3': false,
                    'can_read_inventory': false,              
                    'can_create_inventory': false,
                    'can_update_inventory': false,
                    'can_disable_inventory': false,
                    'can_see_disabled_inventory': false,
                    'user': true,
                    'admin': false
                }
            },
            createNewUser: {
                permission: {
                    'can_read_safe': false,
                    'can_create_safe': false,
                    'can_update_safe': false,
                    'can_disable_safe': false,
                    'can_see_disabled_safe': false,
                    'safe_1': false,
                    'safe_2': false,
                    'safe_3': false,
                    'can_read_inventory': false,              
                    'can_create_inventory': false,
                    'can_update_inventory': false,
                    'can_disable_inventory': false,
                    'can_see_disabled_inventory': false,
                    'user': true,
                    'admin': false,
                    'can_see_disabled_inventory': false,
                },
                email: '',
            },
            upper: false,
            number: false,
            special: false,
            loadingUsers: false,
            loadingItems: false,
            passwordFieldType: 'password',
            links: {},
            posts: {},
            itemToAdd: {
                name: '',
                description: '',
                quantity: 0,
                price: 'R$ 0,00',
                image1: null,
                image2: null,
                image3: null,
                image1Link: null,
                image2Link: null,
                image3Link: null
            },
            itemToEdit: {
                name: '',
                description: '',
                quantity: '',
                price: '',
                image: [

                ],
                image1: null,
                image2: null,
                image3: null,
                image1Link: null,
                image2Link: null,
                image3Link: null
            },
            safeToAdd: {
                title: 'Este é um post de teste',
                description: `Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum scelerisque finibus sodales. Duis sit amet augue fringilla, tristique eros a, bibendum ex. Nam tristique enim nec ultrices venenatis. Suspendisse sodales dui ante, dignissim scelerisque lectus imperdiet eget. Morbi interdum malesuada ante at posuere. Curabitur hendrerit sed justo eget molestie. Phasellus eget libero quis erat placerat aliquet nec nec leo. Praesent convallis turpis enim, sit amet auctor lacus vulputate ut. Cras luctus nunc vel mi hendrerit ullamcorper. Donec non ligula nec urna condimentum vulputate.`,
                file1: null,
                file1Link: null,
                safe1: false,
                safe2: false,
                safe3: false,
                openToAll: false,
                encrypt: false,
                adminOnly: false,
                comments: true,
                autoDelete: false,
                autoDeleteDate: '',
                autoDeleteTime: '',
            },
            imageModalContent: {},
        }
    },
    methods: {
        async createLink(hasEmail = false, qr = false) {
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
                    if(!qr){
                        navigator.clipboard.writeText(json['link']);

                        this.throwWarning(json['message'] + `<i class="fa-solid fa-clipboard"></i>`, 
                        ['alert-success', 'clipboard-copy'], {
                            'data-clipboard-text': json['link']
                        });
                    }
                }else{
                    this.throwWarning(json['message'], ['alert-success']);
                }

                if(qr){
                    this.qrCode(json['link']);
                }

                this.createNewUser.permission = {
                    'can_read_safe': false,
                    'can_create_safe': false,
                    'can_update_safe': false,
                    'can_disable_safe': false,
                    'can_see_disabled_safe': false,
                    'safe_1': false,
                    'safe_2': false,
                    'safe_3': false,
                    'can_read_inventory': false,              
                    'can_create_inventory': false,
                    'can_update_inventory': false,
                    'can_disabled_inventory': false,
                    'can_see_disabled_inventory': false,
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
        async userModal(id, hideWarning = false) {
            $('#user-modal').modal('show');

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

                if(!hideWarning){
                    this.throwWarning(json['message'], ['alert-success']);
                }

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

                this.throwWarning(json['message']+
                ` <i class="fa-${this.users[index].favorite ? 'solid' : 'regular'} fa-star"></i>`, ['alert-success']);
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
        async getUsers(type = 'all', pagination = 1, noAlert = false) {
            this.loadingUsers = true;
            this.loadingR();

            let url = '/getusers';
            let first = true;
            
            if(type !== 'reload' && type !== 'search' && pagination === 1) {
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

            if(this.userSearch['search'].length > 0) {
                url += (first ? '?' : '&') + 'search=' + this.userSearch['search'];
                first = false;
            }

            if(this.userSearch['all']) {
                url = '/getusers?all=true';
                this.userSearch['all'] = false;
                this.userSearch['deleted'] = false;
                this.userSearch['new'] = false;
                this.userSearch['favorites'] = false;
                this.userSearch['search'] = '';
                first = false;
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

                if(!noAlert){
                    this.throwWarning(json['message'], ['alert-success']);
                }

                this.users = json['users'];
                this.userSearch.pagination = json['count'];

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }

            this.loadingUsers = false;
        },
        async foresight() {
            try {
                const response = await fetch('/foresight'); 
                
                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }

                if(json['redirect'] === false) {
                    if(json['type'] === 'reset'){
                        Vue.set(this, 'permission', json['permission']);
                        this.throwWarning(json['message']+ 
                        `<i class="fa-solid fa-circle-notch"></i>`, ['alert-secondary']);
                        return;
                    }
                    this.throwWarning('Nada aconteceu', ['alert-secondary']);
                    return;
                }

                window.location.href = json['redirect'];

                this.throwWarning(json['message']+` <i class="fa-solid fa-location-arrow"></i>`, ['alert-success']);
            } catch (error) {
                this.throwWarning(error.message+` <i class="fa-solid fa-xmark"></i>`, ['alert-danger']);
                window.location.href = "/";
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
                        permission: this.userToEdit['permission'],
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

                this.throwWarning(json['message'], ['alert-success']);
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
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

                this.throwWarning(json['message']+
                ' <i class="fa-solid fa-check"></i>', ['alert-success']);

                this.user = json['user'];
                this.permission = json['user']['permission'];
            } catch (error) {
                this.throwWarning(error.message+
                ' <i class="fa-solid fa-xmark"></i>', ['alert-danger']);
                this.blocked = true;
            }

            this.blocked = false;
        },
        async disableUser(id) {
            try {
                const response = await fetch('/disableuser?id='+id);

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }

                if(json['is_disabled']){
                    this.throwWarning(json['message']+' <i class="fa-solid fa-check"></i>', ['alert-success']);
                }else{
                    this.throwWarning(json['message']+' <i class="fa-solid fa-xmark"></i>', ['alert-danger']);
                }

                this.userModal(id, true);
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
        },
        async getItems(type = 'all', noAlert = true, pagination = 1) {
            this.loadingItems = true;
            this.loadingR();

            let url = '/getitems';
            let first = true;
            
            if(type !== 'reload' && type !== 'search' && pagination === 1) {
                this.itemSearch[type] = !this.itemSearch[type]; 
            }

            if(this.itemSearch['deleted']) {
                url += (first ? '?' : '&') + 'deleted=true';
                first = false;
            }

            if(this.itemSearch['new']) {
                url += (first ? '?' : '&') + 'new=true';
                first = false;
            }

            if(this.itemSearch['favorites']) {
                url += (first ? '?' : '&') +  'favorites=true';
                first = false;
            }

            if(this.itemSearch['search'].length > 0) {
                url += (first ? '?' : '&') + 'search=' + this.itemSearch['search'];
                first = false;
            }

            if(this.itemSearch['from'].length > 0) {
                url += (first ? '?' : '&') + 'from=' + this.itemSearch['from'];
                first = false;
            }

            if(this.itemSearch['to'].length > 0) {
                url += (first ? '?' : '&') + 'to=' + this.itemSearch['to'];
                first = false;
            }

            if(this.itemSearch['all']) {
                url = '/getitems';
                this.itemSearch['all'] = false;
                this.itemSearch['deleted'] = false;
                this.itemSearch['new'] = false;
                this.itemSearch['favorites'] = false;
                this.itemSearch['search'] = '';
                this.itemSearch['from'] = '';
                this.itemSearch['to'] = '';
                first = true;
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

                if(!noAlert){
                    this.throwWarning(json['message'], ['alert-success']);
                }

                this.items = json['items'];
                this.itemSearch.pagination = json['count'];
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }

            this.loadingItems = false;
        },
        async toggleItemFavorite(id) {
            try {
                const index = this.items.findIndex(item => item.id === id);

                if (index === -1) {
                    this.throwWarning('Algo deu errado', ['alert-danger']);
                    return;
                }

                this.items[index].favorite = !this.items[index].favorite;

                const response = await fetch('/toggleitemfavorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        favorite: this.items[index].favorite
                    })
                })

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error('Algo deu errado');
                }

                this.throwWarning(json['message']+
                ` <i class="fa-${this.items[index].favorite ? 'solid' : 'regular'} fa-star"></i>`, ['alert-success']);

            } catch (error) {
                this.throwWarning(error.message+' <i class="fa-solid fa-xmark"></i>', ['alert-danger']);
                this.items[index].favorite = !this.items[index].favorite;
            }
        },
        async disableItem(id) {
            try {
                const response = await fetch('/disableitem?id='+id);

                if(!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }

                if(json['is_disabled']){
                    this.throwWarning(json['message']+' <i class="fa-solid fa-check"></i>', ['alert-success']);
                }else{
                    this.throwWarning(json['message']+' <i class="fa-solid fa-xmark"></i>', ['alert-danger']);
                }

                this.getItems('reload');
            } catch (error) {
                this.throwWarning(error.message+
                    ' <i class="fa-solid fa-xmark"></i>', ['alert-danger']);
            }
        },
        async getItem(id) {
            try {
                const response = await fetch('/getitems?id='+id);
                const json = await response.json();

                if(!json.success) {
                    throw new Error(json['message']);
                }

                this.itemToEdit = json['items'][0];
                this.itemToEdit.price = this.formatPrice(this.itemToEdit['price']);

                if (this.itemToEdit.image) {
                    this.itemToEdit.image.forEach((image, index) => {
                        this.itemToEdit['image'+(index+1)] = image;
                        this.itemToEdit['image'+(index+1)+'Link'] = 
                        'data:image/'+image['extension']+';base64,'+image['base64'];
                    })
                }

            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
        },
        async editItemModal(id) {
            $('#edit-inventory-modal').modal('show');  
            await this.getItem(id);
        },
        addItemModal() {
            $('#inventory-modal').modal('show');
        },
        imageModal(id) {
            const item = this.items.find(item => item.id === id);

            if (item == null) {
                return;
            }

            this.imageModalContent = item;

            $('#image-modal').modal('show');
        },
        formatPriceInput(){
            this.itemToAdd.price = this.formatPrice(this.itemToAdd.price);
        },
        formatEditPrice(){
            this.itemToEdit.price = this.formatPrice(this.itemToEdit.price);
        },
        formatPrice(price) {
            let value = price;

            if(typeof price === 'string'){
                value = price.replace(/[^0-9]/g, '');

                if(value.substring(0, 1) == '0'){
                    value = value.substring(1);
                }
    
                if(value.length <= 3) {
                    let zeros = 3 - value.length;
                    value = '0'.repeat(zeros)+value;
                    value = value.substring(0, 1) + '.' + value.substring(1);
                }else{
                    value = value.substring(0, value.length - 2) + '.' + 
                    value.substring(value.length - 2);
                }
            }

            var formatter = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                maximumFractionDigits: 2,
            });

            return formatter.format(value);
        },
        removeImage(imageKey, type = 1) {
            if(type == 1){
                this.itemToAdd[imageKey] = null;
                this.itemToAdd[imageKey+'Link'] = null;
            }

            if(type == 2){
                this.itemToEdit[imageKey] = null;
                Vue.set(this.itemToEdit, imageKey+'Link', null);
                this.$forceUpdate();
            }
        },
        onFileChange(event, imageKey, type = 1) {
            const file = event.target.files[0];

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                if(type == 1){
                    reader.onload = (e) => {
                        this.itemToAdd[imageKey] = file;
                        this.itemToAdd[imageKey+'Link'] = e.target.result;
                    };
                }

                if(type == 2){
                    reader.onload = (e) => {
                        this.itemToEdit[imageKey] = file;
                        this.itemToEdit[imageKey+'Link'] = e.target.result;
                    };
                }

                const interval = setInterval(() => {
                    this.$forceUpdate();

                    if (reader.readyState === 2) {
                        clearInterval(interval);
                    }
                }, 1000);
                
                reader.readAsDataURL(file);
              }
        },
        async addItem() {
            this.itemToAdd.price = this.itemToAdd.price.replace(/[^0-9]/g, '');

            try {
                const response = await fetch('/additem', {
                    method: 'POST',
                    headers: {
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.itemToAdd.name,
                        quantity: this.itemToAdd.quantity,
                        description: this.itemToAdd.description,
                        price: this.itemToAdd.price
                    })
                });

                if (!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if (!json.success) {
                    throw new Error(json['message']);
                }

                this.upload(json['id']);

                this.throwWarning(json['message'], ['alert-success']);
                this.itemToAdd = {
                    name: '',
                    quantity: 0,
                    description: '',
                    price: 'R$ 0,00',
                    image1: null,
                    image2: null,
                    image3: null
                };

                this.getItems('reload', true, 1);
            } catch (error) {
                this.formatPriceInput();
                this.throwWarning(error.message, ['alert-danger']);
            }
        },
        async upload(id) {
            if (this.itemToAdd.image1 === null 
                && this.itemToAdd.image2 === null 
                && this.itemToAdd.image3 === null) {
                return;
            }

            const formData = new FormData();

            if (this.itemToAdd.image1 != null) {
                formData.append('image1', this.itemToAdd.image1);
            }

            if (this.itemToAdd.image2 != null) {
                formData.append('image2', this.itemToAdd.image2);
            }

            if (this.itemToAdd.image3 != null) {
                formData.append('image3', this.itemToAdd.image3);
            }

            formData.append('id', id);

            try {
                const response = await fetch('/uploadimage', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if (!json.success) {
                    throw new Error(json['message']);
                }

                this.getItems('reload');

                this.throwWarning(json['message'], ['alert-success']);
                this.itemToAdd = {
                    name: '',
                    quantity: 0,
                    description: '',
                    price: 'R$ 0,00',
                    image1: null,
                    image2: null,
                    image3: null
                };
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
        },
        async uploadEditImage() {
            const formData = new FormData();

            if (this.itemToEdit.image1 != null) {
                formData.append('image1', this.itemToEdit.image1);
            }

            if (this.itemToEdit.image2 != null) {
                formData.append('image2', this.itemToEdit.image2);
            }

            if (this.itemToEdit.image3 != null) {
                formData.append('image3', this.itemToEdit.image3);
            }

            formData.append('id', this.itemToEdit.id);

            try {
                const response = await fetch('/uploadedit', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if (!json.success) {
                    throw new Error(json['message']);
                }

                this.getItems('reload');
                this.throwWarning(json['message']+
                ` <i class="fa-solid fa-check"></i>`, ['alert-success']);

                this.itemToEdit['image1'] = null;
                this.itemToEdit['image2'] = null;
                this.itemToEdit['image3'] = null;
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }
        },
        async deleteItem() {
            try {
                const response = await fetch('/deleteitem?id='+this.itemToEdit.id);

                if (!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if (!json.success) {
                    throw new Error(json['message']);
                }

                this.throwWarning(json['message']+
                ` <i class="fa-solid fa-check"></i>`, ['alert-success']);

                $('#edit-inventory-modal').modal('hide');

                this.itemToEdit = {
                    name: '',
                    quantity: 0,
                    description: '',
                    price: 'R$ 0,00',
                    image1: null,
                    image2: null,
                    image3: null
                };
                await this.getItems('reload');
            } catch (error) {
                this.throwWarning(error.message, ['alert-danger']);
            }  
        },
        async updateItem() {
            let price = this.itemToEdit.price.replace(/[^0-9]/g, '');
                try {
                    const response = await fetch('/updateitem', {
                        method: 'POST',
                        headers: {
                            'Content-type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: this.itemToEdit.id,
                            name: this.itemToEdit.name,
                            quantity: this.itemToEdit.quantity,
                            description: this.itemToEdit.description,
                            price: price
                        })
                    });

                    if (!response.ok) {
                        throw new Error('Algo deu errado');
                    }

                    const json = await response.json();

                    if (!json.success) {
                        throw new Error(json['message']);
                    }

                    this.throwWarning(json['message'], ['alert-success']);
                    await this.getItem(this.itemToEdit.id);
                    await this.getItems('reload');
                } catch (error) {
                    this.throwWarning(error.message, ['alert-danger']);
                }
        },
        async addSafe() {
            try {
                const response = await fetch('/addsafe', {
                    method: 'POST',
                    headers: {
                        'Content-type': 'application/json'
                    },
                    body: JSON.stringify(this.safeToAdd)
                });

                if (!response.ok) {
                    throw new Error('Algo deu errado');
                }

                const json = await response.json();

                if (!json.success) {
                    throw new Error(json['message']);
                }

                this.throwWarning(json['message'], ['alert-success']);
                await this.getSafe('reload');
            } catch (error) {
                this.throwWarning(error.message+' <i class="fa-solid fa-check"></i>', ['alert-danger']);
            }
        },
        editSafeModal(id) {
            this.editSafeId = id;
            this.getSafe('reload', false, 1);
            $('#safe-modal').modal('show');
        },
        toggleSafeFavorite(id) {
            this.getSafe('reload', false, 1);
        },
        disableSafe(id) {
            this.getSafe('reload', false, 1);
        },
        addSafeModal() {
            $('#add-safe-modal').modal('show');  
        },
        async getSafe(type = 'all', noAlert = true, pagination = 1) {
            this.loadingItems = true;
            this.loadingR();

            let url = '/getsafe';
            let first = true;
            
            if(type !== 'reload' && type !== 'search' && pagination === 1) {
                this.itemSearch[type] = !this.itemSearch[type]; 
            }

            if(this.itemSearch['deleted']) {
                url += (first ? '?' : '&') + 'deleted=true';
                first = false;
            }

            if(this.itemSearch['new']) {
                url += (first ? '?' : '&') + 'new=true';
                first = false;
            }

            if(this.itemSearch['favorites']) {
                url += (first ? '?' : '&') +  'favorites=true';
                first = false;
            }

            if(this.itemSearch['search'].length > 0) {
                url += (first ? '?' : '&') + 'search=' + this.itemSearch['search'];
                first = false;
            }

            if(this.itemSearch['from'].length > 0) {
                url += (first ? '?' : '&') + 'from=' + this.itemSearch['from'];
                first = false;
            }

            if(this.itemSearch['to'].length > 0) {
                url += (first ? '?' : '&') + 'to=' + this.itemSearch['to'];
                first = false;
            }

            if(this.itemSearch['all']) {
                url = '/getitems';
                this.itemSearch['all'] = false;
                this.itemSearch['deleted'] = false;
                this.itemSearch['new'] = false;
                this.itemSearch['favorites'] = false;
                this.itemSearch['search'] = '';
                this.itemSearch['from'] = '';
                this.itemSearch['to'] = '';
                first = true;
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

                if(!noAlert){
                    this.throwWarning(json['message'], ['alert-success']);
                }

                this.posts = json['posts'];
                this.safeSearch.pagination = json['count'];
            } catch (error) {
                this.throwWarning(error.message+' <i class="fa-solid fa-check"></i>', ['alert-danger']);
            }

            this.loadingItems = false;
        },
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

            if(option === 'safe' && !this.permission['can_read_safe']) {
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

            if(this.option === 'inventory') {
                this.getItems();
            }

            if(this.option === 'safe') {
                this.getSafe();
            }
        },
        qrCode(text){
            $('#qr-modal').modal('show');

            var qrcode = new QRCode(document.getElementById("qrcode"), {
                text: text,
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
            });
        },
    },
    computed: {
        iconClass() {
            return this.passwordFieldType === 'password' ? 'fa fa-eye-slash' : 'fa fa-eye';
        },
        totalPrice() {
            let value = this.itemToAdd.price.replace(/[^0-9]/g, '');

            if(value.substring(0, 1) == '0'){
                value = value.substring(1);
            }

            if(value.length <= 3) {
                let zeros = 3 - value.length;
                value = '0'.repeat(zeros)+value;
                value = value.substring(0, 1) + '.' + value.substring(1);
            }else{
                value = value.substring(0, value.length - 2) + '.' + 
                value.substring(value.length - 2);
            }

            var formatter = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                maximumFractionDigits: 2,
            });

            return formatter.format(value * this.itemToAdd.quantity);
        },
        totalEditPrice() {
            let value = this.itemToEdit.price.replace(/[^0-9]/g, '');

            if(value.substring(0, 1) == '0'){
                value = value.substring(1);
            }

            if(value.length <= 3) {
                let zeros = 3 - value.length;
                value = '0'.repeat(zeros)+value;
                value = value.substring(0, 1) + '.' + value.substring(1);
            }else{
                value = value.substring(0, value.length - 2) + '.' + 
                value.substring(value.length - 2);
            }

            var formatter = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                maximumFractionDigits: 2,
            });

            return formatter.format(value * this.itemToEdit.quantity);
        },
    },
    async mounted() {

        await this.loadingR();
        await this.getUserData();
        this.option = 'main';

        if(this.permission['admin']) {
            return;
        }

        setInterval(() => { 
            this.secs += 1;

            if(this.secs === 20) {
                this.secs = 0;
                this.foresight();
            }
        }, 1000);
    }
});