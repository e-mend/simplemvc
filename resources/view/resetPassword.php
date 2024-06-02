<?php require 'base/header.php';?>

<body>
    <div id="bgVideo">
            <video src="video/sky.mp4" autoplay loop muted class="w-100" type="video/mp4">
            </video>
    </div>
    <div class="container-fluid vertical-center position-absolute top-0 start-0" id="app">
        <div class="login-form p-4 rounded-lot bg-primary shadow-lg z-2">
            <div class="bg-primary p-2 rounded fs-1 text-center user-select-none mb-1">
                <h1 class="text-white">
                    iSecurity
                    <i class="fa-solid fa-shield-halved"></i>
                </h1>
            </div>
            <div>
                <div class="form-group fs-5 mb-2 d-flex align-items-center">
                    <input :type="type" class="form-control fs-5 flex-grow-1" 
                    v-model="passForm.password" id="password" placeholder="Senha"
                    @input="passwordEnter">
                    <span 
                        class="toggle-password my-auto 
                         rounded col-1 mx-1" 
                        @click="togglePasswordVisibility">
                        <i :class="iconClass" class="text-white fs-3"></i>
                    </span>
                </div>
                <div class="d-flex justify-content-center my-2"
                    v-if="passForm.password.length > 0">
                    <div class="btn text-white animate__pulse animate__infinite
                    animate__slower" 
                    :class="{'animate__animated': upper, 'btn-primary': upper}">
                        A-Z
                    </div>
                    <div class="btn text-white animate__pulse animate__infinite
                    animate__slower" 
                    :class="{'animate__animated': number, 'btn-primary': number}">
                        0-9
                    </div>
                    <div class="btn text-white animate__pulse animate__infinite white
                    animate__slower" 
                    :class="{'animate__animated': special, 'btn-primary': special}">
                        @$!%*?&
                    </div>
                </div>
                <div class="form-group fs-5 mb-2 d-flex align-items-center">
                    <input :type="type" class="form-control fs-5 flex-grow-1" 
                    v-model="passForm.confirmPassword" id="confirmPassword" placeholder="Confirme a Senha">
                    <span 
                        class="toggle-password my-auto 
                         rounded col-1 mx-1" 
                        @click="togglePasswordVisibility">
                        <i :class="iconClass" class="text-white"></i>
                    </span>
                </div>
                <button @click="changePassword" class="btn btn-primary p-3 fs-5 w-100 shadow"
                :disabled="blocked">
                    Mudar senha
                    <div v-if="blocked" class="spinner-border spinner-border-small" role="status">
                    </div>
                </button>
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
                    passForm: {
                        password: '',
                        confirmPassword: ''
                    },
                    warnings: [],
                    type: 'password',
                    nextId: 0,
                    blocked: false,
                    special: false,
                    number: false,
                    upper: false
                }
            },
            methods: {
                togglePasswordVisibility() {
                    this.type = this.type === 'password' ? 'text' : 'password';
                },
                passwordEnter() {
                    let upperRegex = /[A-Z]/g;

                    if(upperRegex.test(this.passForm.password)) {
                        this.upper = true;
                    }else{
                        this.upper = false;
                    }

                    let numberRegex = /[\d]/g;

                    if(numberRegex.test(this.passForm.password)) {
                        this.number = true;
                    }else{
                        this.number = false;
                    }

                    let specialRegex = /[@$!%*?&]/g;

                    if(specialRegex.test(this.passForm.password)) {
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
                async changePassword() {
                    this.blocked = true;

                    const currentUrl = window.location.href;
                    const url = new URL(currentUrl);
                    const params = new URLSearchParams(url.search);

                    try {
                        const response = await fetch('/changepassword', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                password: this.passForm.password,
                                confirmPassword: this.passForm.confirmPassword,
                                token: params.get('token')
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }

                        const json = await response.json();

                        if(!json.success) {
                            throw new Error(json['message']);
                        }

                        this.throwWarning(
                        `Redirecionando...
                        <i class="fa-solid fa-check"></i>`, 
                        ['alert-success']);

                        window.location.href = json['redirect'];
                        return;
                        
                        this.blocked = false;

                    } catch (error) {
                        console.error('There was a problem with the fetch operation:', error);

                        this.throwWarning(error.message +
                        ` <i class="fa-solid fa-circle-exclamation"></i>`);
                        this.blocked = false;
                    }
                }
            },
            computed: {
                iconClass() {
                    return this.type === 'password' ? 'fa fa-eye-slash' : 'fa fa-eye';
                }
            },
            mounted() {

            }
        });
    </script>

</body>

<?php require 'base/footer.php';?>