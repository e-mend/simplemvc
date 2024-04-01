<?php require 'base/header.php';?>

<body>
    <div class="container-fluid vertical-center" id="app">
        <div class="login-form p-4 rounded bg-primary shadow">
            <div class="bg-primary p-2 rounded fs-1 text-center animate__infinite
                    animate__animated animate__pulse">
                <h1 class="primary-color">
                    iSecurity
                    <i class="fa-solid fa-shield-halved"></i>
                </h1>
            </div>
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" class="form-control" id="username" placeholder="...">
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" class="form-control" id="password" placeholder="...">
            </div>
            <button @click="throwError" class="btn btn-primary p-2 my-2 w-100 shadow">
                Login
            </button>
            <div class="bg-primary rounded text-center mt-1">
                <div class="primary-color fs-3">
                    Um app
                    <img src="img/logo2.png" id="logo">
                </div>
            </div>
        </div>
        <div class="container position-fixed bottom-0 w-100">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6">
                    <div v-if="wrongLogin" class="alert alert-danger text-center">
                        Usuário ou senha inválidos <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const app = new Vue({
            el: '#app',
            data() {
                return {
                    wrongLogin: false
                }
            },
            methods: {
                throwError() {
                    this.wrongLogin = !this.wrongLogin;
                }
            },
            mounted() {
            
            }
        });
    </script>

</body>

<?php require 'base/footer.php';?>