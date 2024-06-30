<?php require 'base/header.php';?>

<body>
    <div id="bgVideo">
        <video src="<?= getPublicPath() ?>video/sky.mp4" autoplay loop muted class="w-100" type="video/mp4">
        </video>
    </div>
    <div class="container-fluid vertical-center position-absolute top-0 start-0" id="app">
        <div class="login-form p-4 rounded-lot bg-primary shadow-lg primary-border z-2">
            <div class="bg-primary p-2 rounded text-center
                    user-select-none mb-1">
                <h1 class="text-white fs-5">
                    <i class="fa-solid fa-robot fs-1"></i>
                    <br>
                    {{ typedText }}
                </h1>
            </div>
            <div v-if="!this.validateEmail">
                <div class="form-group fs-5 mb-2 d-flex">
                    <input type="text" class="form-control fs-5" id="name" 
                    @focus="changeText('name')"
                    v-model="createUserForm.firstName" placeholder="Nome">
                    <input type="text" class="form-control fs-5" id="surname" 
                    @focus="changeText('name')"
                    v-model="createUserForm.lastName" placeholder="Sobrenome">
                </div>
                <div class="form-group fs-5 mb-2">
                    <input type="text" class="form-control fs-5" id="username" 
                    @focus="changeText('username')"
                    v-model="createUserForm.username" placeholder="Usuário">
                </div>
                <div class="form-group fs-5 mb-2">
                    <input type="text" class="form-control fs-5" 
                    @focus="changeText('email')"
                    v-model="createUserForm.email" id="email" placeholder="Email">
                </div>
                <div class="form-group fs-5 mb-2">
                    <div class="d-flex">
                        <input :type="passwordFieldType" class="form-control fs-5" 
                        @focus="changeText('password')"
                        @input="passwordEnter"
                        v-model="createUserForm.password" id="password" placeholder="Senha">
                        <span 
                            class="toggle-password my-2 rounded mx-2" 
                            @click="togglePasswordVisibility" role="button">
                            <i :class="iconClass" class="text-white fs-4"></i>
                        </span>
                    </div>
                    <div class="d-flex justify-content-center mt-1"
                    v-if="createUserForm.password.length > 0">
                        <div class="btn btn-primary animate__pulse animate__infinite
                        animate__slower text-white" 
                        :class="{'animate__animated': upper}">
                            A-Z
                        </div>
                        <div class="btn btn-primary animate__pulse animate__infinite
                        animate__slower text-white" 
                        :class="{'animate__animated': number}">
                            0-9
                        </div>
                        <div class="btn btn-primary animate__pulse animate__infinite white
                        animate__slower text-white" 
                        :class="{'animate__animated': special}">
                            @$!%*?&
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <div class="form-group fs-5 mb-2">
                    <input type="text" class="form-control fs-5" id="pin" v-model="pin" 
                    placeholder="PIN">
                </div>
            </div>
            <button @click="buttonMega" class="btn btn-primary gradient p-3 fs-5 w-100 shadow"
            :disabled="blocked">
                Continuar
                <div v-if="blocked" class="spinner-border spinner-border-small" role="status">
                </div>
            </button>
            <div class="bg-primary rounded text-center mt-1 user-select-none">
                <div class="text-white fs-3">
                    Um app
                    <a href="http://crm.sjpinfo.com.br/authentication/login" target="_blank">
                        <img src="<?= getPublicPath() ?>img/logo2.png" id="logo" >
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

        const robot = {
            initial: `Bem-vindo ao novo sistema de inventário.
                    Por favor crie sua preencha as seguintes informações.`,
            username: `Aqui nesse campo você pode digitar seu novo usuário,
            escolha um legal!`,
            password: `Aqui nesse campo você pode digitar sua nova senha,
            escolha uma com pelo menos uma letra maiuscula, uma minuscula, um número e um @$!%*?!`,
            email: `Aqui nesse campo você pode digitar seu novo email,
            vamos valida-lo logo logo.`,
            validate: `Certo, agora verifique seu email.`,
            emailExists: `Parece que o email que inseriu ja existe, 
            digite outro para continuar.`,
            usernameExists: `O username que inseriu é utilizado por outro usuario, 
            digite outro para continuar.`,
            name: `Aqui nesse campo você pode digitar seu nome.`,
        };

        const app = new Vue({
            el: '#app',
            data() {
                return {
                    message: '',
                    createUserForm: {
                        username: 'MadAdmin',
                        password: 'Cox@1243',
                        email: 'paiva.gabriel911@gmail.com',
                        firstName: 'Gabriel',
                        lastName: 'Paiva',
                        token: '',
                    },
                    warnings: [],
                    nextId: 0,
                    blocked: false,
                    typedText: '',
                    fullText: robot['initial'],
                    currentIndex: 0,
                    typingInterval: null,
                    upper: false,
                    number: false,
                    special: false,
                    validateEmail: false,
                    pin: '',
                    passwordFieldType: 'password',
                }
            },
            methods: {
                async buttonMega() {
                  if(this.validateEmail){
                    return this.validatePin();
                  }  

                  return this.updateUser();
                },
                togglePasswordVisibility() {
                    this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
                },
                changeText(text) {
                    this.typedText = '';
                    this.fullText = robot[text];
                    this.currentIndex = 0;

                    this.typeText();
                },
                typeText() {
                    if (this.typingInterval) {
                        clearInterval(this.typingInterval);
                    }

                    this.typingInterval = setInterval(() => {
                        this.typedText += this.fullText[this.currentIndex];
                        this.currentIndex++;

                        if (this.currentIndex >= this.fullText.length) {
                            clearInterval(this.typingInterval);
                        }
                    }, 50);
                },
                passwordEnter() {
                    let upperRegex = /[A-Z]/g;

                    if(upperRegex.test(this.createUserForm.password)) {
                        this.upper = true;
                    }else{
                        this.upper = false;
                    }

                    let numberRegex = /[\d]/g;

                    if(numberRegex.test(this.createUserForm.password)) {
                        this.number = true;
                    }else{
                        this.number = false;
                    }

                    let specialRegex = /[@$!%*?&]/g;

                    if(specialRegex.test(this.createUserForm.password)) {
                        this.special = true;
                    }else{
                        this.special = false;
                    }
                },
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
                removeMessage(id) {
                    const index = this.warnings.findIndex(message => message.id === id);
                    if (index !== -1) {
                        this.warnings.splice(index, 1);
                    }
                },
                async validatePin() {
                    this.blocked = true;

                    try {
                        const response = await fetch('/validatenewemail', {
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
                        this.throwWarning(error.message+
                        `<i class="fa-solid fa-circle-exclamation"></i>`);

                        this.blocked = false;
                    }
                },
                async updateUser() {
                    this.blocked = true;

                    const currentUrl = window.location.href;
                    const url = new URL(currentUrl);
                    const params = new URLSearchParams(url.search);

                    this.createUserForm.token = params.get('token');

                    try {
                        const response = await fetch('/newuser', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.createUserForm)
                        });

                        if (!response.ok) {
                            throw new Error('Algo deu errado');
                        }

                        const json = await response.json();
                        
                        if(json['success'] === false) {
                            this.throwWarning(
                            json['message']+`
                            <i class="fa-solid fa-circle-exclamation"></i>`);
                        }else{
                            this.throwWarning(
                            `Por favor verifique o pin enviado para ${this.createUserForm.email}
                            <i class="fa-solid fa-check"></i>`,
                            ['alert-success']);

                            this.validateEmail = true;
                            this.changeText('validate');
                        }

                        this.blocked = false;
                    } catch (error) {
                        this.throwWarning(error.message+
                        `<i class="fa-solid fa-circle-exclamation"></i>`);

                        this.blocked = false;
                    }
                }
            },
            mounted() {
                this.typeText();

                if(this.validateEmail){
                    this.changeText('validate');
                }
            },
            computed: {
                iconClass() {
                    return this.passwordFieldType === 'password' ? 'fa fa-eye-slash' : 'fa fa-eye';
                }
            },
            beforeDestroy() {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
            }
        });
    </script>

</body>

<?php require 'base/footer.php';?>