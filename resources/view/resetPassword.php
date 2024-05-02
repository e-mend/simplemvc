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
                <div class="form-group fs-5 mb-2">
                    <input :type="type" class="form-control fs-5" 
                    v-model="passForm.password" id="password" placeholder="Senha">
                    <span 
                        class="toggle-password my-auto rounded mx-2" 
                        @click="togglePasswordVisibility">
                        <i :class="iconClass" class="text-white"></i>
                    </span>
                </div>
                <div class="form-group fs-5 mb-2">
                    <input :type="type" class="form-control fs-5" 
                    v-model="passForm.confirmPassword" id="confirmPassword" placeholder="Confirme a Senha">
                    <span 
                        class="toggle-password my-auto rounded mx-2" 
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
                }
            },
            methods: {
                togglePasswordVisibility() {
                    this.type = this.type === 'password' ? 'text' : 'password';
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
                        const response = await fetch('/login', {
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
                            throw new Error('Server response was not ok');
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

                        this.throwWarning(`Ocorreu um erro ao realizar a troca de senha
                        <i class="fa-solid fa-circle-exclamation"></i>`);
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