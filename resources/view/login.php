<?php require 'base/header.php';?>

<body>
    <div id="bgVideo">
            <video src="<?= getPublicPath() ?>video/sky.mp4" autoplay loop muted class="w-100" type="video/mp4">
            </video>
    </div>
    <div class="container-fluid vertical-center position-absolute top-0 start-0" id="app">
        <div class="login-form p-4 rounded-lot bg-primary shadow-lg z-2">
            <div class="bg-primary p-2 rounded fs-1 text-center animate__infinite
                    user-select-none mb-1
                    animate__animated animate__pulse">
                <h1 class="text-white">
                    iSecurity
                    <i class="fa-solid fa-shield-halved"></i>
                </h1>
            </div>
            <div v-if="!this.validateEmail">
                <div class="form-group fs-5 mb-2">
                    <input type="text" class="form-control fs-5" id="username" 
                    v-model="loginForm.username" placeholder="Usuário/Email">
                </div>
                <div class="d-flex">
                    <div class="form-group fs-5 mb-2 col-10">
                        <input :type="passwordFieldType"  class="form-control fs-5" 
                        v-model="loginForm.password" id="password" placeholder="Senha">
                    </div>
                    <span 
                        class="toggle-password my-2 rounded mx-2" 
                        @click="togglePasswordVisibility" role="button">
                        <i :class="iconClass" class="text-white fs-4"></i>
                    </span>
                </div>
                <button @click="login" class="btn btn-primary p-3 fs-5 w-100 shadow"
                :disabled="blocked">
                    Login
                    <div v-if="blocked" class="spinner-border spinner-border-small" role="status">
                    </div>
                </button>
            </div>
            <div v-else>
                <div class="form-group fs-5 mb-2">
                    <input type="text" class="form-control fs-5" id="pin" v-model="pin" placeholder="PIN">
                </div>
                <button @click="validatePin" class="btn btn-primary p-3 fs-5 w-100 shadow"
                :disabled="blocked">
                    Continuar
                    <div v-if="blocked" class="spinner-border spinner-border-small" role="status">
                    </div>
                </button>
            </div>
            <div class="bg-primary rounded text-center mt-1 user-select-none">
                <div class="text-white fs-3">
                    Um app
                    <a href="http://crm.sjpinfo.com.br/authentication/login" target="_blank">
                        <img src="img/logo2.png" id="logo" >
                    </a>
                </div>
            </div>
        </div>
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
                    message: '',
                    loginForm: {
                        username: 'ADsense12',
                        password: 'C0c@C0l4',
                    },
                    warnings: [],
                    nextId: 0,
                    blocked: false,
                    validateEmail: false,
                    pin: '',
                    passwordFieldType: 'password',
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
                    }, 4000);
                },
                togglePasswordVisibility() {
                    this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
                },
                removeMessage(id) {
                    const index = this.warnings.findIndex(message => message.id === id);
                    if (index !== -1) {
                        this.warnings.splice(index, 1);
                    }
                },
                async validatePin() {
                    this.blocked = true;

                    try {
                        const response = await fetch('/validateemail', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                pin: this.pin
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Algo deu errado');
                        }

                        const json = await response.json();
                        
                        if(json['success'] === false) {
                            throw new Error(json['message']);
                        }else{
                            this.throwWarning(
                            json['message']+`
                            <i class="fa-solid fa-check"></i>`,
                            ['alert-success']);

                            window.location.href = json['redirect'];
                        }

                        this.blocked = false;

                    } catch (error) {
                        this.throwWarning(error.message+`<i class="fa-solid fa-circle-exclamation"></i>`);
                        this.blocked = false;
                    }
                },
                async login() {
                    this.blocked = true;

                    try {
                        const response = await fetch('/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.loginForm)
                        });

                        if (!response.ok) {
                            throw new Error('Algo deu errado ao fazer login');
                        }

                        const json = await response.json();

                        if(!json.success) {
                            throw new Error(json['message']);
                        }

                        if(json['redirect'] !== false){
                            this.throwWarning(
                                json['message']+`
                            <i class="fa-solid fa-check"></i>`, 
                            ['alert-success']);

                            window.location.href = json['redirect'];
                            return;
                        }
                        
                        this.validateEmail = true;

                        const pinSend = await fetch('/sendcode');

                        if (!response.ok) {
                            throw new Error('Algo deu errado ao enviar o código');
                        }

                        const json2 = await pinSend.json();

                        if(!json2.success) {
                            throw new Error(json2['message']);
                        }

                        this.throwWarning(
                            `Código enviado
                            <i class="fa-solid fa-check"></i>`, 
                        ['alert-success']);

                        this.blocked = false;
                    } catch (error) {
                        this.throwWarning(error.message+
                        `<i class="fa-solid fa-circle-exclamation"></i>`);

                        this.validateEmail = false;
                        this.blocked = false;
                    }
                }
            },
            computed: {
                iconClass() {
                    return this.passwordFieldType === 'password' ? 'fa fa-eye-slash' : 'fa fa-eye';
                }
            },
            mounted() {
                if(<?= $_SESSION['premature'] ? 'true' : 'false'; ?>) {
                    this.throwWarning(
                    `Algo deu errado <i class="fa-solid fa-circle-exclamation"></i>`, 
                    ['alert-danger']);
                }
            }
        });
    </script>

</body>

<?php require 'base/footer.php';?>