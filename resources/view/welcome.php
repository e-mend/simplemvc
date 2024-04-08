<?php require 'base/header.php';?>

<body>
    <div id="bgVideo">
            <video src="video/sky.mp4" autoplay loop muted class="w-100" type="video/mp4">
            </video>
    </div>
    <div class="container-fluid vertical-center position-absolute top-0 start-0" id="app">
        <div class="login-form p-4 rounded-lot bg-primary shadow-lg primary-border z-2">
            <div class="position-absolute ">
                123
            </div>
            <div class="bg-primary p-2 rounded text-center
                    user-select-none mb-1">
                <h1 class="primary-color fs-5">
                    <i class="fa-solid fa-robot"></i>
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <br>
                    {{ typedText }}
                </h1>
            </div>
            <div class="form-group fs-5 mb-2">
                <input type="text" class="form-control fs-5" id="username" 
                @focus="changeText('username')"
                v-model="loginForm.username" placeholder="Usuário">
            </div>
            <div class="form-group fs-5 mb-2">
                <input type="text" class="form-control fs-5" 
                @focus="changeText('email')"
                v-model="loginForm.email" id="email" placeholder="Email">
            </div>
            <div class="form-group fs-5 mb-2">
                <input type="password" class="form-control fs-5" 
                @focus="changeText('password')"
                v-model="loginForm.password" id="password" placeholder="Senha">
            </div>
            <button @click="login" class="btn btn-primary p-3 fs-5 w-100 shadow"
            :disabled="blocked">
                Login
                <div v-if="blocked" class="spinner-border spinner-border-small" role="status">
                </div>
            </button>
            <div class="bg-primary rounded text-center mt-1 user-select-none">
                <div class="primary-color fs-3">
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

        const robot = {
            initial: `Olá, parece que você é o primeiro por aqui!
                    Por favor configure a conta do admin.`,
            username: `Aqui nesse campo você pode digitar seu novo usuário,
            mas se quiser deixar assim mesmo, não precisa mexer, tá?`,
            password: `Aqui nesse campo você pode digitar sua nova senha,
            escolha uma legal!`,
            email: `Aqui nesse campo você pode digitar seu novo email,
            vamos validar logo logo.`
        };

        const app = new Vue({
            el: '#app',
            data() {
                return {
                    message: '',
                    loginForm: {
                        username: '',
                        password: '',
                        email: ''
                    },
                    warnings: [],
                    nextId: 0,
                    blocked: false,
                    typedText: '',
                    fullText: robot['initial'],
                    currentIndex: 0,
                    typingInterval: null
                }
            },
            methods: {
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
                passwordTest() {
                    
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
                            throw new Error('Network response was not ok');
                        }

                        const json = await response.json();

                        if(!json.success) {
                            throw new Error('Server response was not ok');
                        }
                        
                        this.throwWarning(
                            `Login realizado com sucesso
                            <i class="fa-solid fa-check"></i>`, 
                            ['alert-success']);

                        window.location.href = json['redirect'];
                    } catch (error) {
                        console.error('There was a problem with the fetch operation:', error);

                        this.throwWarning(`Ocorreu um erro ao realizar o login 
                        <i class="fa-solid fa-circle-exclamation"></i>`);

                        this.blocked = false;
                    }
                }
            },
            mounted() {
                if(<?= $_SESSION['premature'] ? 'true' : 'false'; ?>) {
                    this.throwWarning(
                    `Algo deu errado <i class="fa-solid fa-circle-exclamation"></i>`, 
                    ['alert-danger']);
                }

                this.typeText();
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