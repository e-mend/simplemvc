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
                            <li>
                                <div @click="loadOptions('inventory')" class="dropdown-item btn">
                                    Inventário
                                </div>
                            </li>
                            <li>
                                <div @click="loadOptions('safe')" class="dropdown-item btn">
                                    Cofre
                                </div>
                            </li>
                            <li>
                                <div @click="loadOptions('users')" class="dropdown-item btn">
                                    Usuários
                                </div>
                            </li>
                            <li>
                                <div @click="loadOptions('settings')" class="dropdown-item btn">
                                    Configurações
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="btn btn-primary col-6 col-md-1">
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
                    z-3" :class="{disabled: this.blocked}">
                    <div @click="loadOptions('inventory')" :class="{disabled: this.blocked}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Inventário
                        <i class="fa-solid fa-laptop"></i>
                    </div>
                    <div @click="loadOptions('safe')" :class="{disabled: this.blocked}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Cofre
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div @click="loadOptions('users')" :class="{disabled: this.blocked}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Usuários
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div @click="loadOptions('settings')" :class="{disabled: this.blocked}"
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
                <div class="row d-flex justify-content-between bg-primary py-3 py-md-3 rounded
                    z-3">
                    Usuários Aqui
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
                    blocked: false
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
                removeMessage(id) {
                    const index = this.warnings.findIndex(message => message.id === id);
                    if (index !== -1) {
                        this.warnings.splice(index, 1);
                    }
                },
                loadOptions(option) {
                    this.loadingR(true);
                    this.option = option;  
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

                        console.log(json['user']);
                        this.user = json['user'];

                        this.blocked = true;
                    } catch (error) {
                        this.throwWarning(error.message, ['alert-danger']);
                        this.blocked = true;
                    }
                }
            },
            beforeMounted() {

            },
            mounted() {
                this.loadingR();
                this.getUserData();
                this.option = 'main';
                
                console.log(this.user);
            }
        });
    </script>

</body>

<?php require 'base/footer.php';?>