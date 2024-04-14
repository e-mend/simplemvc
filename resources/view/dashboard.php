<?php require 'base/header.php';?>

<body>

    <div class="container-fluid vertical-center position-absolute top-0 start-0" id="app">
        <div class="container top-0 position-fixed">
            <div class="row d-flex justify-content-between bg-primary py-3 py-md-3 rounded
            z-3">
                <div class="col-6 col-md-2 px-0">
                    <a href="http://crm.sjpinfo.com.br/authentication/login" target="_blank">
                        <img src="img/logo2.png" id="logo-header" class="ms-md-3">
                    </a> 
                </div> 
                <div class="d-flex col-6 col-md-10 justify-content-end">
                    <div class="dropdown col-6 col-md-4 px-0">
                        <button class="btn btn-primary dropdown-toggle
                        w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Menu
                        </button>
                        <ul class="dropdown-menu w-100 text-center">
                            <li>
                                <div @click="loadOptions('main')" class="dropdown-item btn">
                                    Menu
                                </div>
                            </li>
                            <li v-if="this.blocked || this.permissions['can_read_inventory']">
                                <div @click="loadOptions('inventory')" class="dropdown-item btn">
                                    Inventário
                                </div>
                            </li>
                            <li v-if="this.blocked || this.permissions['can_read_post']">
                                <div @click="loadOptions('safe')" class="dropdown-item btn">
                                    Cofre
                                </div>
                            </li>
                            <li class="this.blocked || !this.permissions['admin']">
                                <div @click="loadOptions('users')" class="dropdown-item btn">
                                    Usuários
                                </div>
                            </li>
                            <li class=" this.blocked || !this.permissions['admin']">
                                <div @click="loadOptions('settings')" class="dropdown-item btn">
                                    Configurações
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="btn btn-primary col-6 col-md-1" @click="logout">
                        Sair
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>
                </div>
                <div class="" id="loading-scheme">
                    <div class="progress primary-border" role="progressbar" 
                    aria-label="Basic example" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar" 
                        :style="{width: loading + '%'}"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container" id="dashboard">
            <!-- Main -->
            <div class="" v-if="option === 'main'">
                <div class="row d-flex justify-content-between py-3 py-md-3 rounded
                border z-3 text-center">
                    <div class="col-12 fs-5">
                        Bem vindo ao iSecurity, 
                        <h5 class="card-title placeholder-glow d-inline" v-if="!this.user">
                            <span class="placeholder col-1"></span>
                        </h5>
                        <h5 class="card-title d-inline" v-else>
                            {{ user.username }}
                        </h5>
                    </div>
                </div>
                <div class="row d-flex justify-content-between py-3 py-md-3 rounded
                    z-3">
                    <div @click="loadOptions('inventory')" :class="{disabled: this.blocked || !this.permissions['can_read_inventory']}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Inventário
                        <i class="fa-solid fa-laptop"></i>
                    </div>
                    <div @click="loadOptions('safe')" :class="{disabled: this.blocked || !this.permissions['can_read_post']}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Cofre
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div @click="loadOptions('users')" :class="{disabled: this.blocked || !this.permissions['admin']}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Usuários
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div @click="loadOptions('settings')" :class="{disabled: this.blocked || !this.permissions['admin']}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Configurações
                        <i class="fa-solid fa-wrench"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-between py-3 py-md-3 rounded
                    z-3">
                   
                </div>
            </div>
            <!-- Inventory -->
            <div class="" v-if="option === 'inventory'">
                <div class="row d-flex justify-content-between bg-primary py-3 py-md-3 rounded
                    z-3">
                    Inventário Aqui
                </div>
            </div>
            <!-- Safe -->
            <div class="" v-if="option === 'safe'">
                <div class="row d-flex justify-content-between bg-primary py-3 py-md-3 rounded
                    z-3">
                    Cofre Aqui
                </div>
            </div>
            <!-- Users -->
            <div class="" v-if="option === 'users'">
                <div class="row d-flex justify-content-between py-3 py-md-3 rounded
                    border z-3 text-center">
                    <div class="col-12 fs-5">
                        Lista de Usuários
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center">
                    <div class="btn btn-primary col-12 col-md-6 text-center py-3 fs-5"
                    @click="searchModalOpen = !searchModalOpen">
                        Procurar Usuário
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div class="btn btn-secondary col-12 col-md-6 border-white text-center py-3 fs-5">
                        Convidar Usuário
                        <i class="fa-solid fa-share-nodes"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center" v-if="searchModalOpen">
                    <div class="btn btn-primary col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getUsers('all')"
                    :class="{'opacity-25': this.userSearch.all}" v-if="this.permissions['admin']">
                        Todos
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div class="btn btn-primary col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getUsers('new')"
                    :class="{'opacity-25': this.userSearch.new}" v-if="this.permissions['admin']">
                        Novos
                        <i class="fa-solid fa-fire"></i>
                    </div>
                    <div class="btn btn-danger col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getUsers('deleted')"
                    :class="{'opacity-25': this.userSearch.deleted}" v-if="this.permissions['admin']">
                        Desabilitados
                        <i class="fa-solid fa-circle-minus"></i>
                    </div>
                    <div class="btn btn-dark col-6 col-md-3 text-center py-3 fs-5"
                    @click="getUsers('favorites')"
                    :class="{'opacity-25': this.userSearch.favorites}" v-if="this.permissions['admin']">
                        Favoritos
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center">
                    <div v-for="user in users" :key="user.id" class="bg-primary rounded text-white
                    text-center py-1 fs-5">
                        <div class="row d-flex justify-content-between">
                            <div class="col-12 col-md-3">
                                <div class="image-container mx-auto">
                                    <img class="" src="https://w.wallhaven.cc/full/p9/wallhaven-p9x6ep.jpg" alt="">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 text-start d-flex text-break">
                                {{ user.first_name }} {{ user.last_name }}
                                <br>
                                {{ user.username }}
                                <br>
                                {{ user.email }}
                            </div>
                            <div class="col-6 col-md-3 mt-1">
                                <div class="col-12">
                                    <div class="btn btn-primary text-center p-3 fs-5" 
                                    @click="toggleFavorite(user.id)" v-if="user.favorite">
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                    <div class="btn btn-primary text-center p-3 fs-5" 
                                    @click="toggleFavorite(user.id)" v-if="!user.favorite">
                                        <i class="fa-regular fa-star"></i>
                                    </div>
                                    <div class="btn btn-primary text-center p-3 fs-5" 
                                    @click="userModal(user.id)">
                                        <i class="fa-solid fa-pencil"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-12 text-start d-flex text-break">
                                <div class="btn btn-primary disabled text-center" v-if="user.isNew">
                                    <i class="fa-solid fa-fire"></i>
                                </div>
                                <div class="btn btn-danger disabled text-center" v-if="user.is_deleted">
                                    <i class="fa-solid fa-circle-minus"></i>
                                </div> 
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div id="userModal" class="modal fade" id="staticBackdrop"
                data-bs-backdrop="static" data-bs-keyboard="false" 
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Editar Usuário</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row d-flex justify-content-center mb-2">
                                <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                    <label for="last_name">Usuário</label>
                                    <input type="text" class="form-control disabled fs-5" 
                                    id="username" v-model="userToEdit.username">
                                </div>
                                <div class="col-md-3 col-12 form-group fs-5 mb-2">
                                    <label for="first_name">Nome</label>
                                    <input type="text" class="form-control fs-5" 
                                    id="first_name" v-model="userToEdit.first_name">
                                </div>
                                <div class="col-md-3 col-12 form-group fs-5 mb-2">
                                    <label for="last_name">Sobrenome</label>
                                    <input type="text" class="form-control fs-5" 
                                    id="last_name" v-model="userToEdit.last_name">
                                </div>
                                <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                    <label for="email">Email</label>
                                    <input type="text" class="form-control disabled fs-5" 
                                    id="email" v-model="userToEdit.email">
                                </div>
                                <div class="mt-auto mb-2 text-center fs-5 col-md-12 col-12">
                                    <button class="btn btn-primary fs-5 col-md-4 col-12"
                                    @click="updateUser(userToEdit.id)">
                                        Atualizar
                                        <i class="fa-regular fa-thumbs-up"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-between mb-2">
                                <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                    <label for="password">
                                        Nova Senha
                                        <i class="fa-solid fa-circle-info"></i>
                                    </label>
                                    <input type="text" class="form-control disabled fs-5" 
                                    id="password" v-model="userToEdit.password">
                                </div>
                                <div class="text-start fs-5 col-md-6 col-12 mt-auto">
                                    Mude a senha deste usuário ou mande email para que ele mude.
                                </div>
                            </div>
                            <div class="row d-flex justify-content-between mb-3">
                                <div class="text-center col-md-6 col-12">
                                    <button class="btn btn-primary fs-5 col-md-8 col-12">
                                        Mudar senha
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </button>
                                </div>
                                <div class="text-center col-md-6 col-12">
                                    <button class="btn btn-primary fs-5 col-md-8 col-12">
                                        Mandar email
                                        <i class="fa-regular fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-between mb-4 fs-5">
                                <div class="text-center fs-2 col-md-12 col-12">
                                    Cofre
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        :checked="userToEdit.permission['can_read_post']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label">
                                            Ler item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        :checked="userToEdit.permission['can_create_post']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        :checked="userToEdit.permission['can_update_post']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        :checked="userToEdit.permission['can_delete_post']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label">
                                            Apagar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        :checked="userToEdit.permission['can_see_deleted_posts']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label">
                                            Ver item apagado
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">

                                </div>
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        :checked="userToEdit.permission['post_1']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label">
                                            Cofre nível 1
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        :checked="userToEdit.permission['post_2']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Cofre nível 2
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        :checked="userToEdit.permission['post_3']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Cofre nível 3
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-2 col-md-12 col-12">
                                    Inventário
                                    <i class="fa-solid fa-laptop"></i>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        :checked="userToEdit.permission['can_read_inventory']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label">
                                            Ver item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        :checked="userToEdit.permission['can_create_inventory']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        :checked="userToEdit.permission['can_delete_inventory']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Apagar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        :checked="userToEdit.permission['can_update_inventory']"
                                        :disabled="userToEdit.permission['admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-2 col-md-12 col-12">
                                    Geral
                                    <i class="fa-solid fa-gears"></i>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" disabled 
                                        type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Dev
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" disabled type="checkbox" 
                                        :checked="userToEdit"
                                        role="switch" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Admin
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                        disabled checked
                                        type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Usuário comum
                                        </label>
                                    </div>
                                </div> 
                            </div>
                            <div class="row d-flex justify-content-between mb-2">
                                <div v-if="!userToEdit.is_deleted" class="col-md-6 col-12">
                                    <div class="btn btn-danger text-center fs-5 w-100"
                                    :class="{'disabled': userToEdit.permission['admin']}">
                                        Desativar usuário
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                    <div class="text-center fs-5 w-100">
                                        O usuário não terá mais acesso a plataforma.
                                    </div>
                                </div>

                                <div v-else class="col-md-6 col-12">
                                    <div class="btn btn-success text-center fs-5 w-100"
                                    :class="{'disabled': userToEdit.permission['admin']}">
                                        Reativar usuário
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <div class="text-center fs-5 w-100">
                                        O usuário terá acesso a plataforma.
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="btn btn-warning text-center fs-5 w-100"
                                    :class="{'disabled': userToEdit.permission['admin']}">
                                        Deslogar usuário
                                        <i class="fa-solid fa-bolt"></i>
                                    </div>
                                    <div class="text-center fs-5 w-100">
                                        Se o usuário estiver logado, será deslogado automaticamente.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary">Concluir</button>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center">
                    <ul class="pagination justify-content-center">
                        <ul v-for="page in this.userSearch.pagination" :key="page">
                            page
                        </ul>
                    </ul>
                </div>
            </div>
            <!-- Settings -->
            <div class="" v-if="option === 'settings'">
                <div class="row d-flex justify-content-between bg-primary py-3 py-md-3 rounded
                    z-3">
                    Configurações Aqui
                </div>
            </div>
        </div>
        <!-- Warnings -->
        <div class="container position-fixed bottom-0 w-100 z-2">
            <div class="row justify-content-center warnings-container">
                <div v-for="warning in warnings" :key="warning.id" class="warning col-12 col-md-12">
                    <div class="alert text-center animate__animated animate__fadeInUp
                    animate__faster col-md-6 mx-auto
                    d-flex justify-content-between py-3 mb-1 fs-5"  
                    :class="warning.class">
                        <div class="justify-content-center flex-grow-1" v-html="warning.text">
                        </div> 
                        <div class="d-flex" role="button"
                        @click="removeMessage(warning.id)">
                            <i class="fa-solid fa-xmark my-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
                        permission: {
                            
                        }
                    },
                    loadingUsers: false
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
                async updateUser(id) {
                    alert();
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
                        this.userSearch = json['count'] + 1;

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
            beforeMounted() {

            },
            mounted() {
                this.loadingR();
                this.getUserData();
                this.option = 'main';
            
            }
        });
    </script>

</body>

<?php require 'base/footer.php';?>