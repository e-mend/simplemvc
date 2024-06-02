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
                            <li v-if="!this.blocked && this.permission['can_read_inventory']">
                                <div @click="loadOptions('inventory')" class="dropdown-item btn">
                                    Inventário
                                </div>
                            </li>
                            <li v-if="!this.blocked && this.permission['can_read_post']">
                                <div @click="loadOptions('safe')" class="dropdown-item btn">
                                    Cofre
                                </div>
                            </li>
                            <li v-if="!this.blocked && this.permission['admin']">
                                <div @click="loadOptions('users')" class="dropdown-item btn">
                                    Usuários
                                </div>
                            </li>
                            <li v-if="!this.blocked">
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
                    <div @click="loadOptions('inventory')" :class="{disabled: this.blocked || !this.permission['can_read_inventory']}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Inventário
                        <i class="fa-solid fa-laptop"></i>
                    </div>
                    <div @click="loadOptions('safe')" :class="{disabled: this.blocked || !this.permission['can_read_post']}"
                    class="btn btn-primary col-12 col-md-6 text-white text-center py-3 fs-5">
                        Cofre
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div @click="loadOptions('users')" :class="{disabled: this.blocked || !this.permission['admin']}"
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
            <div class="container-margin" v-if="option === 'inventory'">
                <div class="row d-flex justify-content-between py-3 py-md-3 rounded 
                border z-3 text-center">
                    <div class="col-12 fs-5">
                        Inventário
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center mt-2">
                    <div class="btn btn-primary col-12 col-md-6 text-center py-3 fs-5"
                    @click="searchModalOpen = !searchModalOpen">
                        Procurar Item
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div class="btn btn-secondary col-12 col-md-6 border-white text-center py-3 fs-5"
                    @click="addItemModal"
                    v-if="this.permission['can_create_inventory']">
                        Adicionar Item
                        <i class="fa-solid fa-plus"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center" v-if="searchModalOpen">
                    <div class="rounded col-12 col-md-6 text-center py-3 fs-5">
                        <div class="input-group d-flex justify-content-center">
                            <input @input="getItems('search', true, 1)" type="text" 
                            class="form-control fs-5 rounded" 
                            v-model="itemSearch.search">
                            <i class="fa-solid fa-magnifying-glass mx-2 my-auto"></i>
                        </div>
                    </div>
                    <div class="rounded col-12 col-md-6 text-center py-3 fs-5">
                        <div class="input-group d-flex justify-content-center">
                            <div class="mx-2 my-auto">
                            De:
                            </div>
                            <input @input="getItems('search', false, 1)" type="date"
                            class="form-control fs-5 rounded"
                            v-model="itemSearch.from">
                            <div class="mx-2 my-auto">
                            Até:
                            </div>
                            <input @input="getItems('search', false, 1)" type="date"
                            class="form-control fs-5 rounded"
                            v-model="itemSearch.to">
                        </div>
                    </div>
                    <div class="btn btn-primary col text-center py-3 fs-5" 
                    @click="getItems('all', false)"
                    :class="{'opacity-25': this.itemSearch.all}">
                        Todos
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div class="btn btn-primary col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getItems('new')"
                    :class="{'opacity-25': this.itemSearch.new}">
                        Novos
                        <i class="fa-solid fa-fire"></i>
                    </div>
                    <div class="btn btn-danger col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getItems('deleted', false)"
                    v-if="this.permission['can_see_disabled_inventory']"
                    :class="{'opacity-25': this.itemSearch.deleted}">
                        Desabilitados
                        <i class="fa-solid fa-circle-minus"></i>
                    </div>
                    <div class="btn btn-dark col-6 col-md-3 text-center py-3 fs-5"
                    @click="getItems('favorites', false)"
                    :class="{'opacity-25': this.itemSearch.favorites}">
                        Favoritos
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-center rounded z-3 text-center">
                    <div v-for="item in items" :key="item.id" 
                    class="col-12 col-md-3 bg-light rounded
                    shadow-lg border-dark-subtle
                    card rounded text-black mt-1 text-center 
                    py-1 fs-5 pt-0">
                        <div class="row d-flex rounded">
                            <div :id="'carouselId' + item.id" 
                            class="card-img-top px-0 img-container border-none py-0 carousel slide w-100 btn">
                                <div class="carousel-inner rounded" v-if="item.image">
                                    <div class="carousel-item" 
                                    @click="imageModal(item.id)"
                                    v-for="(image, index) in item.image" 
                                    :class="{active: index === 0}">
                                        <img :src="'data:image/' + image['extension'] + ';base64,' + image['base64']" 
                                        class="d-block w-100 rounded" alt="...">
                                    </div>
                                </div>
                                <div class="carousel-inner" v-else>
                                    <i class="fa-solid fa-dolly fs-big mt-4"></i>
                                </div>
                                <button class="carousel-control-prev" type="button" :data-bs-target="'#carouselId' + item.id"
                                data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" :data-bs-target="'#carouselId' + item.id"
                                data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="col-md-12 col-12 fs-4">
                                    {{ item.name }}
                                </div>
                                <div class="col-md-12 col-12 bg-light rounded text-black text-start">
                                    Descrição:
                                    <br>
                                    {{ item.description }}
                                </div>
                                <div class="col-md-12 col-12 text-start mb-2">
                                    <i class="fa-solid fa-box"></i>
                                    Estoque:
                                    <div class="fs-5 btn btn-light">
                                        {{ item.quantity }}
                                    </div>
                                    <br>
                                    <i class="fa-solid fa-money-bill"></i>
                                    Valor:
                                    <div class="fs-5 btn btn-light">
                                        {{ formatPrice(item.price) }}
                                    </div>
                                </div>
                                <div class="col-md-12 col-12 text-start">
                                    <div class="col-md-12 col-12 text-black fs-5 btn btn-light" 
                                    v-if="item.isNew">
                                        <i class="fa-solid fa-fire fs-4"></i>
                                        Item Novo
                                    </div>
                                    <div class="col-md-12 col-12 text-black fs-5 btn btn-light" 
                                    v-if="item.is_disabled">
                                        <i class="fa-solid fa-trash fs-4"></i>
                                        Item Desabilitado
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="btn text-center p-3 fs-5" 
                                    :class="{'btn-light': item.favorite, 'btn-outline-light text-black': !item.favorite}"
                                    @click="toggleItemFavorite(item.id)" v-if="permission['admin']">
                                        <i class="fa-star"
                                        :class="{'fa-solid': item.favorite, 'fa-regular': !item.favorite}"></i>
                                    </div>
                                    <div class="btn btn-primary text-center p-3 fs-5" 
                                    @click="editItemModal(item.id)"
                                    v-if="permission['can_update_inventory']">
                                        <i class="fa-solid fa-pencil"></i>
                                    </div>
                                    <div class="btn text-center p-3 fs-5"
                                    :class="{'btn-danger': item.is_disabled, 'btn-outline-success': !item.is_disabled}"
                                    @click="disableItem(item.id)" 
                                    v-if="permission['can_disable_inventory']">
                                        <i class="fa-solid" :class="{'fa-toggle-on': item.is_disabled, 'fa-toggle-off': !item.is_disabled}"></i></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center">
                    <ul class="pagination justify-content-center">
                        <button v-for="page in this.itemSearch.pagination"
                        class="btn btn-primary fs-4"
                        @click="getItems('reload', false, page)" :key="page">
                            {{ page }}
                        </button>
                    </ul>
                </div>
                <div id="inventory-modal" class="modal fade" id="staticBackdrop"
                data-bs-backdrop="static" data-bs-keyboard="false" 
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
                v-if="this.permission['can_create_inventory']">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Adicionar Item</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="row d-flex justify-content-between mb-2">
                            
                        </div>
                        <div class="modal-body">
                            <div class="row d-flex justify-content-center mb-2">
                                <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                    <label for="item-name">Nome do produto</label>
                                    <input type="text" class="form-control disabled fs-5" 
                                    id="item-name" v-model="itemToAdd.name">
                                </div>
                                <div class="col-md-3 col-12 form-group fs-5 mb-2">
                                    <label for="price">Preço</label>
                                    <input type="text" class="form-control fs-5" 
                                    id="price" v-model="itemToAdd.price"
                                    @input="formatPriceInput">
                                </div>
                                <div class="col-md-3 col-12 form-group fs-5 mb-2">
                                    <label for="quantity">Quantidade</label>
                                    <input type="text" class="form-control fs-5" 
                                    id="quantity" v-model="itemToAdd.quantity">
                                </div>
                                <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                    <label for="description">Descrição</label>
                                    <textarea class="form-control fs-5"
                                    id="description" v-model="itemToAdd.description"></textarea>
                                </div>
                                <div class="col-md-12 col-12 form-group fs-3 mt-3 text-center">
                                    Total: {{ totalPrice }}
                                </div>
                            </div>
                            <div class="row d-flex justify-content-center mb-2">
                                <div class="col-12 form-group fs-5 position-relative text-center">
                                    <div class="">
                                        Imagem Principal
                                    </div>
                                    <div class="btn btn-danger position-absolute end-0"
                                    v-if="itemToAdd.image1"
                                    @click="removeImage('image1')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="col-12 form-group fs-5 rounded 
                                    btn border shadow">
                                    <label for="image1">
                                    <div v-if="!itemToAdd.image1" class="mt-2">
                                        <i class="fa-solid fa-file-image fs-1"></i>
                                    </div>
                                    <div class="img-container2" v-else>
                                        <img :src="itemToAdd.image1Link" class="w-100">
                                    </div>
                                    <input type="file" class="d-none" 
                                    id="image1" @change="onFileChange($event, 'image1')"
                                    accept="image/*">
                                    </label>
                                </div>
                                <div class="col-12 form-group fs-5 position-relative text-center">
                                    <div class="">
                                        Imagem 2
                                    </div>
                                    <div class="btn btn-danger position-absolute end-0"
                                    v-if="itemToAdd.image2"
                                    @click="removeImage('image2')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="col-12 form-group fs-5 mb-2 rounded 
                                btn border shadow">
                                    <label for="image2">
                                    <div v-if="!itemToAdd.image2" class="mt-2">
                                        <i class="fa-solid fa-file-image fs-1"></i>
                                    </div>
                                    <div class="img-container2" v-else>
                                        <img :src="itemToAdd.image2Link" class="w-100">
                                    </div>
                                    <input type="file" class="d-none" 
                                    id="image2" @change="onFileChange($event, 'image2')"
                                    accept="image/*">
                                    </label>
                                </div>
                                <div class="col-12 form-group fs-5 position-relative text-center">
                                    <div class="">
                                        Imagem 3
                                    </div>
                                    <div class="btn btn-danger position-absolute end-0"
                                    v-if="itemToAdd.image3"
                                    @click="removeImage('image3')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="col-12 form-group fs-5 mb-2 rounded 
                                btn border shadow">
                                    <label for="image3">
                                    <div v-if="!itemToAdd.image3" class="mt-2">
                                        <i class="fa-solid fa-file-image fs-1"></i>
                                    </div>
                                    <div class="img-container2" v-else>
                                        <img :src="itemToAdd.image3Link" class="w-100 rounded">
                                    </div>
                                    <input type="file" class="d-none" 
                                    id="image3" @change="onFileChange($event, 'image3')"
                                    accept="image/*">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" @click="addItem()" class="btn btn-primary">Adicionar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                        </div>
                    </div>
                </div>
                <div id="edit-inventory-modal" class="modal fade" id="staticBackdrop"
                data-bs-backdrop="static" data-bs-keyboard="false" 
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
                v-if="this.permission['can_update_inventory']">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">
                                    Editar item
                                </h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row d-flex justify-content-between py-3 py-md-3 rounded
                                    z-3">
                                    <div class="row d-flex justify-content-center mb-2">
                                        <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-name">Nome</label>
                                            <input type="text" class="form-control disabled fs-5" 
                                            id="edit-product-name" v-model="itemToEdit.name">
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                            <i class="fa-solid fa-user"></i>
                                            Criado em: {{ itemToEdit.created_at_formatted }}
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                            <i class="fa-solid fa-user"></i>
                                            Criado por: {{ itemToEdit.created_by_name }}
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2" 
                                        v-if="itemToEdit.updated_at">
                                            <i class="fa-regular fa-clock"></i>
                                            Atualizado em: {{ itemToEdit.updated_at_formatted }}
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2" 
                                        v-if="itemToEdit.updated_by">
                                            <i class="fa-regular fa-user"></i>
                                            Atualizado por: {{ itemToEdit.updated_by_name }}
                                        </div>
                                        <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-description">Descrição</label>
                                            <textarea class="form-control fs-5"
                                            id="edit-product-description" v-model="itemToEdit.description">
                                            </textarea>
                                        </div>
                                        <div class="col-md-4 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-quantity">Estoque</label>
                                            <input type="text" class="form-control disabled fs-5" 
                                            id="edit-product-quantity" v-model="itemToEdit.quantity">
                                        </div>
                                        <div class="col-md-4 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-price">Valor</label>
                                            <input type="text" class="form-control disabled fs-5" 
                                            id="edit-product-price" v-model="itemToEdit.price">
                                        </div>
                                        <div class="col-md-3 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-total">Total</label>
                                            {{ itemToEdit.total }}
                                        </div>
                                        <div class="mt-auto mb-2 text-center fs-5 col-md-12 col-12">
                                            <button class="btn btn-primary fs-5 col-md-4 col-12"
                                            @click="alert">
                                                Atualizar
                                                <i class="fa-regular fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row d-flex justify-content-center mb-2">
                                        <div class="col-12 form-group fs-5 
                                        position-relative text-center">
                                            <div class="">
                                                Imagem Principal
                                            </div>
                                            <div class="btn btn-danger position-absolute end-0"
                                            v-if="itemToEdit.image1"
                                            @click="removeImage('image1', 2)">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="col-12 form-group fs-5 rounded 
                                            btn border shadow">
                                            <label for="image1">
                                            <div v-if="!itemToEdit.image1" class="mt-2">
                                                <i class="fa-solid fa-file-image fs-1"></i>
                                            </div>
                                            <div class="img-container2" v-else>
                                                <img :src="itemToEdit.image1Link" class="w-100">
                                            </div>
                                            <input type="file" class="d-none" 
                                            id="image1" @change="onFileChange($event, 'image1', 2)"
                                            accept="image/*">
                                            </label>
                                        </div>
                                        <div class="col-12 form-group fs-5 position-relative text-center">
                                            <div class="">
                                                Imagem 2
                                            </div>
                                            <div class="btn btn-danger position-absolute end-0"
                                            v-if="itemToAdd.image2"
                                            @click="removeImage('image2', 2)">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="col-12 form-group fs-5 mb-2 rounded 
                                        btn border shadow">
                                            <label for="image2">
                                            <div v-if="!itemToEdit.image2" class="mt-2">
                                                <i class="fa-solid fa-file-image fs-1"></i>
                                            </div>
                                            <div class="img-container2" v-else>
                                                <img :src="itemToEdit.image2Link" class="w-100">
                                            </div>
                                            <input type="file" class="d-none" 
                                            id="image2" @change="onFileChange($event, 'image2', 2)"
                                            accept="image/*">
                                            </label>
                                        </div>
                                        <div class="col-12 form-group fs-5 position-relative 
                                        text-center">
                                            <div class="">
                                                Imagem 3
                                            </div>
                                            <div class="btn btn-danger position-absolute end-0"
                                            v-if="itemToEdit.image3"
                                            @click="removeImage('image3', 2)">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="col-12 form-group fs-5 mb-2 rounded 
                                        btn border shadow">
                                            <label for="image3">
                                            <div v-if="!itemToEdit.image3" class="mt-2">
                                                <i class="fa-solid fa-file-image fs-1"></i>
                                            </div>
                                            <div class="img-container2" v-else>
                                                <img :src="itemToEdit.image3Link" class="w-100 rounded">
                                            </div>
                                            <input type="file" class="d-none" 
                                            id="image3" @change="onFileChange($event, 'image3', 2)"
                                            accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row d-flex justify-content-between py-3 py-md-3 rounded
                                    z-3">
                                        <div class="mt-auto mb-2 text-center fs-5 col-md-12 col-12">
                                            <button class="btn btn-primary fs-5 col-md-4 col-12"
                                            @click="alert">
                                                Atualizar imagens
                                                <i class="fa-solid fa-image"></i>
                                            </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger"
                                v-if="this.permission['admin']">
                                    Apagar permanentemente <i class="fa-regular fa-trash-can"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" 
                                data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="image-modal" class="modal fade" id="staticBackdrop"
                data-bs-backdrop="static" data-bs-keyboard="false" 
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel"></h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            <div id="image-carousel" 
                                class="card-img-top px-0 img-container h-100 carousel slide w-100">
                                <div class="carousel-inner rounded">
                                    <div class="carousel-item" 
                                    v-for="(image, index) in imageModalContent" 
                                    :class="{active: index === 0}">
                                        <img :src="'data:image/' + image['extension'] + ';base64,' + image['base64']" 
                                        class="d-block w-100 rounded" alt="...">
                                    </div>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#image-carousel"
                                data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#image-carousel"
                                data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <!-- Safe -->
            <div class="container-margin" v-if="option === 'safe'">
                    <div class="row d-flex justify-content-between bg-primary py-3 py-md-3 rounded
                        z-3">
                        Cofre Aqui
                    </div>
            </div>
            <!-- Users -->
            <div class="container-margin" v-if="option === 'users'">
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
                    <div class="btn btn-secondary col-12 col-md-6 border-white text-center py-3 fs-5"
                    @click="inviteModal">
                        Convidar Usuário
                        <i class="fa-solid fa-share-nodes"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center" v-if="searchModalOpen">
                    <div class="rounded col-12 text-center py-3 fs-5" 
                    v-if="this.permission['admin']">
                        <div class="input-group d-flex justify-content-center">
                            <input @input="getUsers('search', 1, true)" type="text" 
                            class="form-control fs-5 rounded" 
                            v-model="userSearch.search">
                            <i class="fa-solid fa-magnifying-glass mx-2 my-auto"></i>
                        </div>
                    </div>
                    <div class="btn btn-primary col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getUsers('all')"
                    :class="{'opacity-25': this.userSearch.all}" v-if="this.permission['admin']">
                        Todos
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div class="btn btn-primary col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getUsers('new')"
                    :class="{'opacity-25': this.userSearch.new}" v-if="this.permission['admin']">
                        Novos
                        <i class="fa-solid fa-fire"></i>
                    </div>
                    <div class="btn btn-danger col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getUsers('deleted')"
                    :class="{'opacity-25': this.userSearch.deleted}" v-if="this.permission['admin']">
                        Desabilitados
                        <i class="fa-solid fa-circle-minus"></i>
                    </div>
                    <div class="btn btn-dark col-6 col-md-3 text-center py-3 fs-5"
                    @click="getUsers('favorites')"
                    :class="{'opacity-25': this.userSearch.favorites}" v-if="this.permission['admin']">
                        Favoritos
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-center rounded z-3 text-center">
                    <div v-for="user in users" :key="user.id" 
                    class="row bg-primary rounded text-white mt-1 text-center py-1 fs-5 justify-content-center">
                        <div class="row d-flex overflow-hidden my-auto">
                            <div class="col-md-2 col-12">
                                {{ user.first_name }} {{ user.last_name }}
                            </div>
                            <div class="col-md-2 col-12">
                                {{ user.username }}
                            </div>
                            <div class="col-md-4 col-12">
                                {{ user.email }}
                            </div>
                            <div class="col-md-2 col-12">
                                <div class="btn btn-primary text-center p-3 fs-5" 
                                @click="toggleFavorite(user.id)" v-if="user.favorite">
                                    <i class="fa-solid fa-star"></i>
                                </div>
                                <div class="btn btn-primary text-center p-3 fs-5" 
                                @click="toggleFavorite(user.id)" v-if="!user.favorite">
                                    <i class="fa-regular fa-star"></i>
                                </div>
                                <div class="btn btn-primary text-center p-3 fs-5" 
                                @click="userModal(user.id, true)">
                                    <i class="fa-solid fa-pencil"></i>
                                </div>
                            </div>
                            <div class="col-md-2 col-2 my-auto">
                                <div class="btn btn-primary disabled text-center my-auto p-3 fs-5" 
                                v-if="user.isNew">
                                    <i class="fa-solid fa-fire"></i>
                                </div>
                                <div class="btn btn-danger disabled text-center my-auto p-3 fs-5" 
                                v-if="user.is_disabled">
                                    <i class="fa-solid fa-circle-minus"></i>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
                <div id="invite-modal" class="modal fade" id="staticBackdrop"
                data-bs-backdrop="static" data-bs-keyboard="false" 
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Convidar Usuário</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        <div class="row d-flex justify-content-between mb-4 fs-5">
                                <div class="text-center fs-2 col-md-12 col-12">
                                    Cofre
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['can_read_post']">
                                        <label class="form-check-label">
                                            Ler item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['can_create_post']">
                                        <label class="form-check-label">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['can_update_post']">
                                        <label class="form-check-label">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['can_disable_post']">
                                        <label class="form-check-label">
                                            Desabilitar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['can_see_disabled_post']">
                                        <label class="form-check-label">
                                            Ver item desabilitado
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">

                                </div>
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['post_1']">
                                        <label class="form-check-label">
                                            Cofre nível 1
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['post_2']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Cofre nível 2
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['post_3']">
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
                                        v-model="createNewUser.permission['can_read_inventory']">
                                        <label class="form-check-label">
                                            Ver item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['can_create_inventory']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['can_disable_inventory']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Desabilitar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['can_update_inventory']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['can_see_disabled_inventory']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Ver item desabilitado
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
                                        <input class="form-check-input" type="checkbox" 
                                        v-model="createNewUser.permission['admin']"
                                        role="switch" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Admin
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                        v-model="createNewUser.permission['user']"
                                        type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Usuário comum
                                        </label>
                                    </div>
                                </div> 
                            </div>
                            <div class="row d-flex justify-content-center mb-4 fs-5">
                                <div class="row text-center d-flex">
                                    <div class="col-12 col-md-6 px-0 mx-0 text-center">
                                        <input type="text" 
                                        class="form-control fs-5" 
                                        v-model="createNewUser.email"
                                        placeholder="E-mail"
                                        type="text">
                                    </div>
                                    <button type="submit" 
                                    class="btn btn-primary col-12 col-md-6 fs-5"
                                    :disabled="blocked || createNewUser.email.length === 0" 
                                    @click="createLink(true)">
                                        Mandar E-mail
                                        <i class="fa-solid fa-envelope-circle-check"></i>
                                    </button>
                                </div>
                                <div class="row text-center d-flex mx-1 my-2">
                                    <button type="submit" class="btn btn-primary col-12 col-md-6 fs-5"
                                    :disabled="blocked" @click="createLink(false, true)">
                                        Criar Qr Code
                                        <i class="fa-solid fa-qrcode"></i>
                                    </button>
                                    <button type="submit" class="btn btn-primary col-12 col-md-6 fs-5"
                                    :disabled="blocked" @click="createLink()">
                                        Criar link
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-center mb-4 fs-5 text-center">
                                <div class="text-center justify-content-center col-md-12 col-12 d-flex fs-3 mb-2">
                                    Ultimos links criados <i class="fa-regular fa-clipboard mx-2 my-auto"></i>
                                </div>
                                <div class="btn text-center row d-flex fs-5 btn-primary"
                                v-for="link in links" @click="copyLink(link.id)"
                                :class="link.disabled_at === null ? 'btn-primary' : 'btn-secondary'">
                                    <div class="col col my-auto">
                                        <i class="fa-solid fa-link fs-5"></i>
                                    </div>
                                    <div class="col my-auto">
                                        {{ link.fullname }}
                                    </div>
                                    <div class="col">
                                        {{ link.created_at }}
                                    </div>
                                    <div class="col-12 col-md-6 overflow-hidden">
                                        {{ link.link }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                        </div>
                    </div>
                </div>
                <div id="user-modal" class="modal fade" id="staticBackdrop"
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
                                    <div class="input-group d-flex">
                                        <input 
                                            :type="passwordFieldType" 
                                            class="form-control rounded fs-5" 
                                            id="password"
                                            @input="passwordEnter"
                                            v-model="userToEdit.password">
                                        <span 
                                            class="toggle-password my-auto rounded mx-2" 
                                            @click="togglePasswordVisibility">
                                            <i :class="iconClass"></i>
                                        </span>
                                    </div>
                                <div class="d-flex justify-content-center mt-2"
                                    v-if="userToEdit.password.length > 0">
                                        <div class="btn animate__pulse animate__infinite
                                        animate__slower" 
                                        :class="{'animate__animated': upper, 'btn-primary': upper}">
                                            A-Z
                                        </div>
                                        <div class="btn animate__pulse animate__infinite
                                        animate__slower" 
                                        :class="{'animate__animated': number, 'btn-primary': number}">
                                            0-9
                                        </div>
                                        <div class="btn animate__pulse animate__infinite white
                                        animate__slower" 
                                        :class="{'animate__animated': special, 'btn-primary': special}">
                                            @$!%*?&
                                        </div>
                                    </div>
                                </div>
                                <div class="text-start fs-5 col-md-6 col-12 mt-auto">
                                    Mude a senha deste usuário ou mande email para que ele mude.
                                </div>
                            </div>
                            <div class="row d-flex justify-content-between mb-3">
                                <div class="text-center col-md-6 col-12">
                                    <button 
                                        class="btn btn-primary fs-5 col-md-8 col-12"
                                        @click="changePassword(userToEdit.id)">
                                        Mudar senha
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </button>
                                </div>
                                <div class="text-center col-md-6 col-12">
                                    <button 
                                    class="btn btn-primary fs-5 col-md-8 col-12"
                                    @click="sendEmail(userToEdit.id)">
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
                                        v-model="userToEdit.permission['can_read_post']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Ver item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="userToEdit.permission['can_create_post']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['can_update_post']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="userToEdit.permission['can_disable_post']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Desabilitar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="userToEdit.permission['can_see_disabled_post']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Ver item desabilitado
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">

                                </div>
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="userToEdit.permission['post_1']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Cofre nível 1
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['post_2']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Cofre nível 2
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['post_3']"
                                        :disabled="userToEdit.permission['super_admin']">
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
                                        v-model="userToEdit.permission['can_read_inventory']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Ver item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['can_create_inventory']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['can_disable_inventory']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Desabilitar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['can_update_inventory']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['can_see_disabled_inventory']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Ver item desabilitado
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
                                        <input class="form-check-input" type="checkbox" 
                                        v-model="userToEdit.permission['admin']"
                                        :disabled="userToEdit.permission['super_admin']"
                                        role="switch" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Admin
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                        v-model="userToEdit.permission['user']"
                                        :disabled="userToEdit.permission['super_admin']"
                                        type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Usuário comum
                                        </label>
                                    </div>
                                </div> 
                            </div>
                            <div class="row d-flex justify-content-between mb-2">
                                <div v-if="!userToEdit.is_disabled" class="col-md-6 col-12">
                                    <div class="btn btn-danger text-center fs-5 w-100"
                                    :class="{'disabled': userToEdit.permission['super_admin']}"
                                    @click="disableUser(userToEdit.id)">
                                        Desativar usuário
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                    <div class="text-center fs-5 w-100">
                                        O usuário não terá mais acesso a plataforma.
                                    </div>
                                </div>

                                <div v-else class="col-md-6 col-12">
                                    <div class="btn btn-success text-center fs-5 w-100"
                                    :class="{'disabled': userToEdit.permission['super_admin']}"
                                    @click="disableUser(userToEdit.id)">
                                        Reativar usuário
                                        <i class="fa-solid fa-check"></i>
                                    </div>
                                    <div class="text-center fs-5 w-100">
                                        O usuário terá acesso a plataforma.
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="btn btn-warning text-center fs-5 w-100"
                                    :class="{'disabled': userToEdit.permission['super_admin']}"
                                    @click="changePermissions()">
                                        Alterar permissões
                                        <i class="fa-solid fa-bolt"></i>
                                    </div>
                                    <div class="text-center fs-5 w-100">
                                        Altere as permissões desse usuário.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center">
                    <ul class="pagination justify-content-center">
                        <button v-for="page in this.userSearch.pagination"
                        class="btn btn-primary fs-4"
                        @click="getUsers('reload', page)" :key="page">
                            {{ page }}
                        </button>
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
            <!-- Warnings -->
            <div class="container position-fixed bottom-0 w-100 warn">
                <div class="row justify-content-center warnings-container">
                    <div v-for="warning in warnings" :key="warning.id" class="warning col-12 col-md-12" 
                    @click="isClicked(warning.id)">
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
            <!-- QR Modal -->
            <div id="qr-modal" class="modal fade" id="staticBackdrop"
                data-bs-backdrop="static" data-bs-keyboard="false" 
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="staticBackdropLabel">QR code</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div id="qrcode" class="d-flex justify-content-center"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" 
                                data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <script src="js/main.js" defer></script>
    
</body>

<?php require 'base/footer.php';?>