<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Helpers\View;
use App\Models\Inventory;
use App\Requests\Json;
use App\Requests\Req;
use Exception;
use Carbon\Carbon;
use App\Helpers\Mailer;
use App\enum\AclRole;

class InventoryController extends Controller
{
    private Inventory $inventory;
    private Secure $secure;
    private const NEW_ITEM_DAYS = 7;

    public function __construct()
    {
        $this->inventory = new Inventory();
        $this->secure = Secure::getInstance();
    }

    public function disableItemApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::CAN_DELETE_INVENTORY->value)) {
                throw new Exception("Não autorizado");
            }

            $params = Req::getParams();

            if (!$params['id']){
                throw new Exception("Id invalido");
            }

            $item = $this->inventory->get([
                'where' => [
                    'id' => $params['id']
                ]
            ])[0];

            if(!$item){
                throw new Exception("Id invalido");
            }

            $update = $this->inventory->update([
                'is_deleted' => $item['is_deleted'] === 1 ? false : true
            ], $params['id']);

            if(!$update){
                throw new Exception("Erro ao processar a requisição");
            }

            Json::send([
                'success' => true,
                'message' => $item['is_deleted'] === 1 ? 'Item desativado com sucesso' : 'Item reativado com sucesso',
                'is_disabled' => $item['is_deleted'] === 1
            ]);

        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function getItemsApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::CAN_READ_INVENTORY->value)){
                throw new Exception("Não autorizado");
            }

            $params = Req::getParams();
            $query = [];

            if(!$params['id']){
                if($params['search'] && $this->secure->isValid('search', $params['search'])){
                    $query['search'] = $params['search'];
                }

                if($params['new']){
                    $query['days'] = Carbon::now()->subDays(self::NEW_ITEM_DAYS)->format('Y-m-d H:i:s');
                }
    
                if($params['deleted'] && $this->secure->hasPermission(AclRole::CAN_SEE_DELETED_INVENTORY->value)){
                    $query['is_deleted'] = 1; 
                }else{
                    $query['is_deleted'] = 0;
                }

                if($params['from']){
                    $query['from'] = $params['from'];
                }

                if($params['to']){
                    $query['to'] = $params['to'];
                }
    
                if($params['favorites']){
                    $query['favorite'] = 1;
                    $query['order'] = 'favorite DESC, created_at DESC';
                }

                if($params['pagination']){
                    $query['offset'] = ($params['pagination'] - 1) * Inventory::OFFSET;
                }else{
                    $query['offset'] = 0;
                }
            }else{
                $query['where'] = [
                    'id' => $params['id']
                ];
            }

            $items = $this->inventory->get($query);
            $count = $this->inventory->get($query, true);

            foreach ($items as &$item) {
                $item['created_at_formatted'] = Carbon::createFromFormat('Y-m-d H:i:s', $item['created_at'])
                                                ->format('d/m/Y H:i:s');
                $item['isNew'] = Carbon::createFromFormat('Y-m-d H:i:s', $item['created_at'])
                                ->diffInDays(Carbon::now()) <= Inventory::NEW_ITEM_DAYS;
                if($item['image']){
                    $item['image'] = json_decode($item['image'], true);
                }
            }

            Json::send([
                'success' => true,
                'items' => $items,
                'message' => 'Pesquisa concluída',
                'count' => ceil($count / Inventory::OFFSET)
            ]);
            
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function toggleFavoriteApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::ADMIN->value)){
                throw new Exception("Não autorizado");
            }

            $json = Json::getJson();

            if(!$json['id']){
                throw new Exception("Id inválido");
            }

            $toUpdate = $this->inventory->update([
                'favorite' => $json['favorite']
            ], $json['id']);

            if(!$toUpdate){
                throw new Exception("Erro ao processar a requisição");
            }

            Json::send([
                'success' => true,
                'message' => $json['favorite'] ? 'Favorito adicionado com sucesso' : 'Favorito removido com sucesso'
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function uploadImageApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::CAN_CREATE_INVENTORY->value)){
                throw new Exception("Não autorizado");
            }

            $files = Req::getFiles();

            if(count($files) <= Req::MAX_IMAGES){
                foreach ($files as $file) {
                    if(!Req::validateFile($file)){
                        throw new Exception("Imagem inválida");
                    }
                }
            }

            $insert = $this->inventory->update([
                'image' => json_encode(Req::getImages()),
            ], $_POST['id']);

            if(!$insert){
                throw new Exception("Erro ao processar a requisição");
            }

            Json::send([
                'success' => true,
                'message' => 'Imagem enviada com sucesso'
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage(),
                'insert' => $insert
            ]);
        }
    }

    public function addItemApi()
    {
        try {
            if(!$this->secure->isLoggedIn() || !$this->secure->hasPermission(AclRole::CAN_CREATE_INVENTORY->value)){
                throw new Exception("Não autorizado");
            }

            $json = Json::getJson();


            if(!$json['name'] || !$this->secure->isValid('name', $json['name'])){
                throw new Exception("Revise o nome");
            }

            if(!$json['price'] || !$this->secure->isValid('price', $json['price'])){
                throw new Exception("Revise o valor");
            }

            if(!$json['quantity'] || !$this->secure->isValid('quantity', $json['quantity'])){
                throw new Exception("Revise a quantidade");
            }

            if($json['description'] != '' && !$this->secure->isValid('description', $json['description'])){
                throw new Exception("Revise a descrição");
            }

            $insert = $this->inventory->createItem([
                'name' => $json['name'],
                'price' => $json['price'],
                'quantity' => $json['quantity'],
                'description' => $json['description'],
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_by' => $_SESSION['user']['id'],
            ]);

            Json::send([
                'success' => true,
                'message' => 'Item adicionado com sucesso',
                'id' => $insert
            ]);
        } catch (\Throwable $th) {
            Json::send([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
