<?php require 'base/header.php';?>

<body>
    <div class="container-fluid vertical-center position-absolute top-0 start-0" id="app">
        <div class="container top-0 position-fixed z-1000">
            <div class="row d-flex justify-content-between bg-primary py-3 py-md-3 rounded">
                <div class="col-6 col-md-2 px-0">
                    <a href="http://crm.sjpinfo.com.br/authentication/login" target="_blank">
                        <img src="<?= getPublicPath() ?>img/logo2.png" id="logo-header" class="ms-md-3">
                    </a> 
                </div> 
                <div class="d-flex col-6 col-md-10 justify-content-end">
                    <div class="dropdown col-6 col-md-4 px-0">
                        <button class="btn btn-primary dropdown-toggle fs-5
                        w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Menu
                        </button>
                        <ul class="dropdown-menu w-100 text-center">
                            <li>
                                <div @click="loadOptions('main')" class="dropdown-item btn fs-5">
                                    Menu
                                </div>
                            </li>
                            <li v-if="!this.blocked && this.permission['can_read_inventory']">
                                <div @click="loadOptions('inventory')" class="dropdown-item btn fs-5">
                                    Inventário
                                </div>
                            </li>
                            <li v-if="!this.blocked && this.permission['can_read_safe']">
                                <div @click="loadOptions('safe')" class="dropdown-item btn fs-5">
                                    Cofre
                                </div>
                            </li>
                            <li v-if="!this.blocked && this.permission['admin']">
                                <div @click="loadOptions('users')" class="dropdown-item btn fs-5">
                                    Usuários
                                </div>
                            </li>
                            <li v-if="!this.blocked">
                                <div @click="loadOptions('settings')" class="dropdown-item btn fs-5">
                                    Configurações
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="btn btn-primary col-6 col-md-1 fs-5" @click="logout">
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
                    <div @click="loadOptions('safe')" :class="{disabled: this.blocked || !this.permission['can_read_safe']}"
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
                <div class="row d-flex justify-content-start rounded z-3 text-center">
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
                                <div class="py-4 fs-5 position-relative
                                col-md-12 col-12 bg-light text-black text-start overflow-hidden size-max">
                                    Descrição:
                                    <br>
                                    {{ item.description }}
                                    <div class="position-absolute bottom-0 end-0 w-100 h-25 text-center blur-background"
                                    v-if="item.description.length > 100"
                                    role="button"
                                    @click="imageModal(item.id)">
                                        <span class="contenty">
                                            <i class="fs-3 fa-solid fa-angles-down"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-12 col-12 text-start">
                                    <i class="fa-solid fa-box"></i>
                                    Estoque:
                                    <div class="fs-5 btn btn-light">
                                        {{ item.quantity }}
                                    </div>
                                </div>
                                <div class="col-md-12 col-12 text-start mb-1">
                                    <i class="fa-solid fa-money-bill"></i>
                                    Valor:
                                    <div class="fs-5 btn btn-light">
                                        {{ formatPrice(item.price) }}
                                    </div>
                                </div>
                                <div class="col-md-12 col-12 text-start">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                    Valor Bruto:
                                    <div class="fs-5 btn btn-light">
                                        {{ formatPrice(item.price * item.quantity) }}
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
                                    id="description" v-model="itemToAdd.description"
                                    rows="5"></textarea>
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
                                        <i class="fa-solid fa-xmark" role="button"></i>
                                    </div>
                                </div>
                                <div class="form-group mt-2 fs-5 rounded 
                                    btn border shadow"
                                    :class="itemToAdd.image1 ? 'col-md-8 col-12' : 'col-md-2 p-0 col-12'">
                                    <label for="image1" class="w-100 p-3"
                                            role="button">
                                        <div v-if="!itemToAdd.image1" class="">
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
                                <div class="col-12 form-group mt-4 fs-5 position-relative text-center">
                                    <div class="">
                                        Imagem 2
                                    </div>
                                    <div class="btn btn-danger position-absolute end-0"
                                    v-if="itemToAdd.image2"
                                    @click="removeImage('image2')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="form-group mt-2 fs-5 rounded 
                                    btn border shadow"
                                    :class="itemToAdd.image2 ? 'col-md-8 col-12' : 'col-md-2 p-0 col-12'">
                                    <label for="image2" class="w-100 p-3"
                                            role="button">
                                    <div v-if="!itemToAdd.image2" class="">
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
                                <div class="col-12 form-group mt-4 fs-5 position-relative text-center">
                                    <div class="">
                                        Imagem 3
                                    </div>
                                    <div class="btn btn-danger position-absolute end-0"
                                    v-if="itemToAdd.image3"
                                    @click="removeImage('image3')">
                                        <i class="fa-solid fa-xmark"></i>
                                    </div>
                                </div>
                                <div class="form-group mt-2 fs-5 rounded 
                                    btn border shadow"
                                    :class="itemToAdd.image3 ? 'col-md-8 col-12' : 'col-md-2 p-0 col-12'">
                                    <label for="image3" class="w-100 p-3"
                                            role="button">
                                    <div v-if="!itemToAdd.image3" class="">
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
                                <button type="button" class="btn-close" 
                                data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row d-flex justify-content-center py-3 py-md-3 rounded z-3">
                                    <div class="row d-flex justify-content-center mb-2">
                                        <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-name">Nome</label>
                                            <input type="text" class="form-control disabled fs-5" 
                                            id="edit-product-name" v-model="itemToEdit.name">
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                            <i class="fa-solid fa-clock"></i>
                                            Criado em: {{ itemToEdit.created_at_formatted }}
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                            <i class="fa-solid fa-user"></i>
                                            Criado por: {{ itemToEdit.created_by_name }}
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2" 
                                        v-if="itemToEdit.updated_at">
                                            <i class="fa-regular fa-clock"></i>
                                            Atualizado última vez em: 
                                            <br>
                                            {{ itemToEdit.updated_at_formatted }}
                                        </div>
                                        <div class="col-md-6 col-12 form-group fs-5 mb-2" 
                                        v-if="itemToEdit.updated_by">
                                            <i class="fa-regular fa-user"></i>
                                            Atualizado última vez por: 
                                            <br>
                                            {{ itemToEdit.updated_by_name }}
                                        </div>
                                        <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-description">Descrição</label>
                                            <textarea class="form-control fs-5"
                                            rows="5"
                                            id="edit-product-description" v-model="itemToEdit.description">
                                            </textarea>
                                        </div>
                                        <div class="col-md-4 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-quantity">Estoque</label>
                                            <input type="text" class="form-control fs-5" 
                                            id="edit-product-quantity" v-model="itemToEdit.quantity">
                                        </div>
                                        <div class="col-md-4 col-12 form-group fs-5 mb-2">
                                            <label for="edit-product-price">Valor</label>
                                            <input type="text" class="form-control fs-5" 
                                            id="edit-product-price" v-model="itemToEdit.price"
                                            @input="formatEditPrice()">
                                        </div>
                                        <div class="col-md-3 col-12 form-group mb-2">
                                            <label for="edit-product-total">Total</label>
                                            <div class="fs-4 font-weight-bold">
                                                {{ totalEditPrice }}
                                            </div>
                                        </div>
                                        <div class="mt-auto mb-2 text-center fs-5 col-md-12 col-12">
                                            <button class="btn btn-primary fs-5 col-md-4 col-12"
                                            @click="updateItem()">
                                                Atualizar
                                                <i class="fa-regular fa-thumbs-up"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row d-flex justify-content-center mb-2">
                                        <div class="col-12 form-group fs-5 mt-4
                                        position-relative text-center">
                                            <div class="">
                                                Imagem Principal
                                            </div>
                                            <div class="btn btn-danger position-absolute end-0"
                                            v-if="itemToEdit.image1 != null"
                                            @click="removeImage('image1', 2)">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="form-group fs-5 rounded 
                                            btn border shadow"
                                            :class="itemToEdit.image1 ? 'col-md-8 col-12' : 'col-md-2 p-0 col-12'">
                                            <label for="image1Edit" class="w-100 p-3"
                                            role="button">
                                            <div v-if="itemToEdit.image1 == null" class="mt-2">
                                                <i class="fa-solid fa-file-image fs-1"></i>
                                            </div>
                                            <div class="" v-else>
                                                <img :src="this.itemToEdit.image1Link"
                                            class="w-100">
                                            </div>
                                            <input type="file" class="d-none" 
                                            id="image1Edit" @change="onFileChange($event, 'image1', 2)"
                                            accept="image/*">
                                            </label>
                                        </div>
                                        <div class="col-12 form-group mt-4 
                                        fs-5 position-relative text-center">
                                            <div class="">
                                                Imagem 2
                                            </div>
                                            <div class="btn btn-danger position-absolute end-0"
                                            v-if="itemToEdit.image2 != null"
                                            @click="removeImage('image2', 2)">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="form-group fs-5 mb-2 rounded 
                                        btn border shadow"
                                        :class="itemToEdit.image2 ? 'col-md-8 col-12' : 'col-md-2 p-0 col-12'">
                                            <label for="image2Edit" class="w-100 p-3"
                                            role="button">
                                            <div v-if="itemToEdit.image2 == null" class="mt-2">
                                                <i class="fa-solid fa-file-image fs-1"></i>
                                            </div>
                                            <div class="" v-else>
                                                <img :src="itemToEdit.image2Link" 
                                                 class="w-100">
                                            </div>
                                            <input type="file" class="d-none" 
                                            id="image2Edit" @change="onFileChange($event, 'image2', 2)"
                                            accept="image/*">
                                            </label>
                                        </div>
                                        <div class="col-12 form-group mt-4 
                                        fs-5 position-relative text-center">
                                            <div class="">
                                                Imagem 3
                                            </div>
                                            <div class="btn btn-danger position-absolute end-0"
                                            v-if="itemToEdit.image3 != null"
                                            @click="removeImage('image3', 2)">
                                                <i class="fa-solid fa-xmark"></i>
                                            </div>
                                        </div>
                                        <div class="form-group fs-5 mb-2 rounded 
                                        btn border shadow"
                                        :class="itemToEdit.image3 ? 'col-12 col-md-8' : 'col-md-2 p-0 col-12'">
                                            <label for="image3Edit" class="w-100 p-3"
                                            role="button">
                                            <div v-if="itemToEdit.image3 == null" class="mt-2">
                                                <i class="fa-solid fa-file-image fs-1"></i>
                                            </div>
                                            <div class="" v-else>
                                                <img :src="itemToEdit.image3Link" 
                                            class="w-100 rounded">
                                            </div>
                                            <input type="file" class="d-none" 
                                            id="image3Edit" @change="onFileChange($event, 'image3', 2)"
                                            accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="row d-flex justify-content-between py-3 py-md-3 rounded
                                    z-3">
                                        <div class="mt-auto mb-2 text-center fs-5 col-md-12 col-12">
                                            <button class="btn btn-primary fs-5 col-md-4 col-12"
                                            @click="uploadEditImage()">
                                                Atualizar imagens
                                                <i class="fa-solid fa-image"></i>
                                            </button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger fs-5"
                                v-if="this.permission['super_admin']"
                                @click="deleteItem()">
                                    Apagar permanentemente <i class="fa-regular fa-trash-can"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" 
                                data-bs-dismiss="modal">Fechar</button>
                            </div>
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
                            <div class="row d-flex justify-content-between fs-5">
                                Descrição: 
                                <br>{{ imageModalContent.description }}
                            </div>
                            <div id="image-carousel" 
                                class="card-img-top px-0 img-container h-100 carousel slide w-100">
                                <div class="carousel-inner rounded">
                                    <div class="carousel-item" 
                                    v-for="(image, index) in imageModalContent.image" 
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
            <!-- Safe -->
            <div class="container-margin" v-if="option === 'safe'">
                <div class="row d-flex justify-content-between py-3 py-md-3 rounded 
                border z-3 text-center">
                    <div class="col-12 fs-5">
                        Cofre
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center mt-2">
                    <div class="btn btn-primary col-12 col-md-6 text-center py-3 fs-5"
                    @click="searchModalOpen = !searchModalOpen">
                        Procurar Post
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div class="btn btn-secondary col-12 col-md-6 border-white text-center py-3 fs-5"
                    @click="addSafeModal"
                    v-if="this.permission['can_create_safe']">
                        Criar Post
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center" v-if="searchModalOpen">
                    <div class="rounded col-12 col-md-6 text-center py-3 fs-5">
                        <div class="input-group d-flex justify-content-center">
                            <input @input="getSafe('search', true, 1)" type="text" 
                            class="form-control fs-5 rounded" 
                            v-model="safeSearch.search">
                            <i class="fa-solid fa-magnifying-glass mx-2 my-auto"></i>
                        </div>
                    </div>
                    <div class="rounded col-12 col-md-6 text-center py-3 fs-5">
                        <div class="input-group d-flex justify-content-center">
                            <div class="mx-2 my-auto">
                            De:
                            </div>
                            <input @input="getSafe('search', false, 1)" type="date"
                            class="form-control fs-5 rounded"
                            v-model="safeSearch.from">
                            <div class="mx-2 my-auto">
                            Até:
                            </div>
                            <input @input="getSafe('search', false, 1)" type="date"
                            class="form-control fs-5 rounded"
                            v-model="safeSearch.to">
                        </div>
                    </div>
                    <div class="btn btn-primary col text-center py-3 fs-5" 
                    @click="getSafe('all', false)"
                    :class="{'opacity-25': safeSearch.all}">
                        Todos
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                    <div class="btn btn-primary col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getSafe('new')"
                    :class="{'opacity-25': safeSearch.new}">
                        Novos
                        <i class="fa-solid fa-fire"></i>
                    </div>
                    <div class="btn btn-danger col-6 col-md-3 text-center py-3 fs-5" 
                    @click="getSafe('deleted', false)"
                    v-if="permission['can_see_disabled_safe']"
                    :class="{'opacity-25': safeSearch.deleted}">
                        Desabilitados
                        <i class="fa-solid fa-circle-minus"></i>
                    </div>
                    <div class="btn btn-dark col-6 col-md-3 text-center py-3 fs-5"
                    @click="getSafe('favorites', false)"
                    :class="{'opacity-25': safeSearch.favorites}">
                        Favoritos
                        <i class="fa-solid fa-star"></i>
                    </div>
                </div>
                <div class="row d-flex justify-content-start rounded z-3 text-center">
                    <div v-for="post in posts" :key="post.id" 
                    class="col-12 col-md-12 bg-light rounded
                    shadow-lg border-dark-subtle
                    card rounded text-black mt-1 text-center 
                    py-1 fs-5 pt-0">
                        <div class="row d-flex rounded">
                            <div class="card-body">
                                <div class="col-md-12 col-12 fs-4">
                                    {{ post.title }}
                                </div>
                                <div class="py-4 fs-5 position-relative
                                col-md-12 col-12 bg-light text-black text-start">
                                    {{ post.body }}
                                </div>
                                <div class="col-md-12 col-12 text-start">
                                    <i class="fa-solid fa-clock"></i>
                                    {{ post.created_at }}
                                </div>
                                <div class="col-md-12 col-12 text-start mb-1">
                                    <i class="fa-solid fa-mobile"></i>
                                    <i class="fa-solid fa-computer"></i>
                                    <i class="fa-solid fa-laptop"></i>
                                    <i class="fa-solid fa-desktop"></i>
                                    <i class="fa-solid fa-mobile-button"></i>
                                    <div v-if="post.created_by === user.id">
                                        Eu
                                    </div>
                                    <div v-else>
                                        {{ post.created_by_name }}
                                    </div>
                                </div>
                                <div class="col-md-12 col-12 text-start">
                                    <i class="fa-solid fa-trash"></i>
                                    {{ post.disabled_at }}
                                </div>
                                <div class="col-md-12 col-12 text-start">
                                    <i class="fa-solid fa-key"></i>
                                    {{ post.disabled_by }}
                                </div>
                                <div class="col-md-12 col-12 text-start">
                                    <div class="col-md-12 col-12 text-black fs-5 btn btn-light" 
                                    v-if="post.isNew">
                                        <i class="fa-solid fa-fire fs-4"></i>
                                        Post Novo
                                    </div>
                                    <div class="col-md-12 col-12 text-black fs-5 btn btn-light" 
                                    v-if="post.is_disabled">
                                        <i class="fa-solid fa-trash fs-4"></i>
                                        Post Desabilitado
                                    </div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div class="btn text-center p-3 fs-5" 
                                    :class="{'btn-light': post.favorite, 'btn-outline-light text-black': !post.favorite}"
                                    @click="togglePostFavorite(post.id)" v-if="permission['admin']">
                                        <i class="fa-star"
                                        :class="{'fa-solid': post.favorite, 'fa-regular': !post.favorite}"></i>
                                    </div>
                                    <div class="btn btn-primary text-center p-3 fs-5" 
                                    @click="editPostModal(post.id)"
                                    v-if="permission['can_update_post']">
                                        <i class="fa-solid fa-pencil"></i>
                                    </div>
                                    <div class="btn text-center p-3 fs-5"
                                    :class="{'btn-danger': post.is_disabled, 'btn-outline-success': !post.is_disabled}"
                                    @click="disablePost(post.id)" 
                                    v-if="permission['can_disable_post']">
                                        <i class="fa-solid" :class="{'fa-lock': item.is_disabled, 'fa-lock-open': !item.is_disabled}"></i></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row d-flex justify-content-between rounded z-3 text-center">
                    <ul class="pagination justify-content-center">
                        <button v-for="page in this.safeSearch.pagination"
                        class="btn btn-primary fs-4"
                        @click="getSafe('reload', false, page)" :key="page">
                            {{ page }}
                        </button>
                    </ul>
                </div>
                <div id="add-safe-modal" class="modal fade" id="staticBackdrop"
                data-bs-backdrop="static" data-bs-keyboard="false" 
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true"
                v-if="this.permission['can_create_safe']">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Adicionar Post</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="row d-flex justify-content-between mb-2">
                            
                        </div>
                        <div class="modal-body">
                            <div class="row d-flex justify-content-center mb-2">
                                <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                    <label for="safe-title">Título</label>
                                    <input type="text" class="form-control disabled fs-5" 
                                    id="safe-title" v-model="safeToAdd.title">
                                </div>
                                <div class="col-md-12 col-12 form-group fs-5 mb-2">
                                    <label for="safe-description">Descrição</label>
                                    <textarea class="form-control fs-5"
                                    id="safe-description" v-model="safeToAdd.description"
                                    rows="5"></textarea>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-center mb-2">
                                <div class="col-md-12 col-12 fs-4 form-group fs-5 mb-2 form-check form-switch text-center">
                                        Privacidade <i class="fa-solid fa-share-nodes"></i>
                                </div>
                                <div class="col-md-4 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100" id="encrypt"
                                    :class="{'btn-dark': !safeToAdd.encrypt, 
                                            'btn-warning': safeToAdd.encrypt}"
                                    @click="safeToAdd.encrypt = !safeToAdd.encrypt">
                                        <span v-if="safeToAdd.encrypt">Encriptado</span>
                                        <span v-else>Desencriptado</span>
                                        <i class="fa-solid"
                                        :class="{'fa-lock': safeToAdd.encrypt, 
                                            'fa-lock-open': !safeToAdd.encrypt}"></i>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100" id="openToPublic"
                                    :class="{'btn-dark': !safeToAdd.openToAll, 
                                            'btn-success': safeToAdd.openToAll}"
                                    @click="safeToAdd.openToAll = !safeToAdd.openToAll">
                                        <span v-if="safeToAdd.openToAll">Aberto para todos</span>
                                        <span v-else>Fechado</span>
                                        <i class="fa-solid"
                                        :class="{'fa-user-lock': !safeToAdd.openToAll, 
                                            'fa-unlock-keyhole': safeToAdd.openToAll}"></i>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100"
                                    :class="{'btn-dark': !safeToAdd.adminOnly, 
                                            'btn-primary': safeToAdd.adminOnly || safeToAdd.openToAll}"
                                    @click="safeToAdd.adminOnly = !safeToAdd.adminOnly">
                                        <span v-if="safeToAdd.openToAll">Todos com link</span>
                                        <span v-else-if="safeToAdd.adminOnly && !safeToAdd.openToAll">Apenas Admins</span>
                                        <span v-else>Usuários</span>
                                        <i class="fa-solid"
                                        :class="{'fa-user-group': !safeToAdd.adminOnly || safeToAdd.openToAll, 
                                            'fa-users-gear': safeToAdd.adminOnly && !safeToAdd.openToAll}"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-center mb-2"
                            v-if="!safeToAdd.openToAll && !safeToAdd.adminOnly">
                                <div class="col-md-12 col-12 fs-4 form-group fs-5 mb-2 text-center">
                                        Níveis de usuário <i class="fa-solid fa-user-secret"></i>
                                </div>
                                <div v-if="permission['safe_1']"
                                class="col-md-4 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100"
                                        :class="{'btn-dark': !safeToAdd.safe1, 
                                                'btn-primary': safeToAdd.safe1}"
                                        @click="safeToAdd.safe1 = !safeToAdd.safe1">
                                            Nível 1
                                    </div>
                                </div>
                                <div v-if="permission['safe_2']"
                                class="col-md-4 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100"
                                        :class="{'btn-dark': !safeToAdd.safe2, 
                                                'btn-primary': safeToAdd.safe2}"
                                        @click="safeToAdd.safe2 = !safeToAdd.safe2">
                                            Nível 2
                                    </div>
                                </div>
                                <div v-if="permission['safe_3']"
                                class="col-md-4 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100"
                                        :class="{'btn-dark': !safeToAdd.safe3, 
                                                'btn-primary': safeToAdd.safe3}"
                                        @click="safeToAdd.safe3 = !safeToAdd.safe3">
                                            Nível 3
                                    </div>
                                </div>
                            </div>
                            <div class="row d-flex justify-content-center mb-2" v-if="permission['admin']">
                                <div class="col-md-12 col-12 fs-4 form-group fs-5 mb-2 text-center">
                                    Configurações da postagem <i class="fa-solid fa-user-shield"></i>
                                </div>
                                <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100"
                                    :class="{'btn-dark': !safeToAdd.comments, 
                                            'btn-primary': safeToAdd.comments}"
                                    @click="safeToAdd.comments = !safeToAdd.comments">
                                        Comentários
                                        <i class="fa-solid"
                                        :class="{'fa-comments': safeToAdd.comments, 
                                        'fa-comment-slash': !safeToAdd.comments}"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 form-group fs-5 mb-2">
                                    <div class="btn fs-5 w-100"
                                    :class="{'btn-dark': !safeToAdd.autoDelete, 
                                            'btn-danger': safeToAdd.autoDelete}"
                                    @click="safeToAdd.autoDelete = !safeToAdd.autoDelete">
                                        Auto deletar
                                        <i class="fa-solid"
                                        :class="{'fa-trash': safeToAdd.autoDelete,
                                        'fa-minus': !safeToAdd.autoDelete}"></i>
                                    </div>
                                    <div class="d-flex justify-content-center" v-if="safeToAdd.autoDelete">
                                        <input type="date" class="form-control" v-model="safeToAdd.autoDeleteDate">
                                        <input type="time" class="form-control" v-model="safeToAdd.autoDeleteTime">
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="row d-flex justify-content-center mb-2">
                                <div class="col-12 form-group fs-5 position-relative text-center">
                                    <div class="">
                                        Anexar arquivo
                                    </div>
                                    <div class="btn btn-danger position-absolute end-0"
                                    v-if="safeToAdd.file1"
                                    @click="removeFile('file1')">
                                        <i class="fa-solid fa-xmark" role="button"></i>
                                    </div>
                                </div>
                                <div class="form-group mt-2 fs-5 rounded 
                                    btn border shadow col-md-2 p-0 col-12">
                                    <label for="file1" class="w-100 p-3"
                                            role="button">
                                        <div class="">
                                            <i class="fa-solid -image fs-1"
                                            :class="{'fa-file-circle-check': safeToAdd.file1, 
                                                'fa-file-shield': !safeToAdd.file1}"></i>
                                        </div>
                                        <input type="file" class="d-none" 
                                        id="file1" @change="onFileChange($event, 'file1', 3)"
                                        accept="">
                                    </label>
                                </div>
                            </div> -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" @click="addSafe()" class="btn btn-primary">Adicionar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                        </div>
                    </div>
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
                                        v-model="createNewUser.permission['can_read_safe']">
                                        <label class="form-check-label">
                                            Ler item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['can_create_safe']">
                                        <label class="form-check-label">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['can_update_safe']">
                                        <label class="form-check-label">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['can_disable_safe']">
                                        <label class="form-check-label">
                                            Desabilitar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="createNewUser.permission['can_see_disabled_safe']">
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
                                        v-model="createNewUser.permission['safe_1']">
                                        <label class="form-check-label">
                                            Cofre nível 1
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['safe_2']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Cofre nível 2
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="createNewUser.permission['safe_3']">
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
                                <div class="text-center row d-flex fs-5 border rounded">
                                    <div class="col col-1 my-auto">
                                    </div>
                                    <div class="col-3 my-auto overflow-hidden">
                                        url
                                    </div>
                                    <div class="col">
                                        Criado em
                                    </div>
                                    <div class="col">
                                        Usado em
                                    </div>
                                </div>
                                <div class="btn text-center row d-flex fs-5"
                                v-for="link in links" @click="copyLink(link.id)"
                                :class="link.is_disabled === 1 ? 'border disabled' : 'btn-primary'">
                                    <div class="col col-1 my-auto">
                                        <i class="fa-solid fa-link fs-5"></i>
                                    </div>
                                    <div class="col-3 my-auto overflow-hidden">
                                        {{ link.link }}
                                    </div>
                                    <div class="col">
                                        {{ link.created_at }}
                                    </div>
                                    <div class="col">
                                        {{ link.disabled_at }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Fechar
                            </button>
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
                                        v-model="userToEdit.permission['can_read_safe']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Ver item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="userToEdit.permission['can_create_safe']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Criar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['can_update_safe']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Modificar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="userToEdit.permission['can_disable_safe']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Desabilitar item
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center fs-5 col-md-6 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                        v-model="userToEdit.permission['can_see_disabled_safe']"
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
                                        v-model="userToEdit.permission['safe_1']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label">
                                            Cofre nível 1
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['safe_2']"
                                        :disabled="userToEdit.permission['super_admin']">
                                        <label class="form-check-label" for="flexSwitchCheckDefault">
                                            Cofre nível 2
                                        </label>
                                    </div>
                                </div> 
                                <div class="text-center fs-5 col-md-3 col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                        v-model="userToEdit.permission['safe_3']"
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

    <script src="<?= getPublicPath() ?>js/main.js" defer></script>
    
</body>

<?php require 'base/footer.php';?>