<?php 

namespace App\Controllers;

use App\Helpers\Secure;
use App\Models\Safe;
use App\Requests\Json;
use App\Requests\Req;
use Carbon\Carbon;
use App\enum\AclRole;
use App\Exceptions\ReachableException;
use App\Exceptions\PermissionException;
use App\Exceptions\RequestException;
use Throwable;
use Exception;

class SafeController extends Controller
{
    private Safe $safe;
    private Secure $secure;
    private const NEW_ITEM_DAYS = 7;

    public function __construct()
    {
        $this->safe = new Safe();
        $this->secure = Secure::getInstance();
    }

    public function disableSafeApi()
    {
        try {
            if(!$this->secure->isLoggedIn() 
            || !$this->secure->hasPermission(AclRole::CAN_DISABLE_SAFE->value)) {
                throw new PermissionException();
            }

            $params = Req::getParams();

            if(!$params){
                throw new RequestException("Parametros invalidos");
            }

            if (!$params['id']){
                throw new ReachableException("Id invalido");
            }

            $item = $this->safe->get([
                'where' => [
                    'safe.id' => $params['id']
                ]
            ])[0];

            if(!$item){
                throw new ReachableException("Item inexistente");
            }

            $update = $this->safe->update([
                'is_disabled' => $item['is_disabled'] === 1 ? false : true
            ], $params['id']);

            if(!$update){
                throw new ReachableException("Erro ao desativar item");
            }

            Json::send([
                'success' => true,
                'message' => $item['is_disabled'] === 1 ? 'Item reativado com sucesso' : 'Item desativado com sucesso',
                'is_disabled' => $item['is_disabled'] === 1
            ]);

        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function getItemsApi()
    {
        try {
            if(!$this->secure->isLoggedIn() 
            || !$this->secure->hasPermission(AclRole::CAN_READ_INVENTORY->value)){
                throw new PermissionException(false);
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
    
                if($params['deleted'] 
                && $this->secure->hasPermission(AclRole::CAN_SEE_DISABLED_INVENTORY->value)){
                    $query['is_disabled'] = 1; 
                }else{
                    $query['is_disabled'] = 0;
                }

                if($params['from']){
                    $query['from'] = $params['from'];
                }

                if($params['to']){
                    $query['to'] = $params['to'];
                }
    
                if($params['favorites']){
                    $query['favorite'] = 1;
                    $query['order'] = 'safe.favorite DESC, safe.created_at DESC';
                }

                if($params['pagination']){
                    $query['offset'] = ($params['pagination'] - 1) * Inventory::OFFSET;
                }else{
                    $query['offset'] = 0;
                }
            }else{
                $query['where'] = [
                    'safe.id' => $params['id']
                ];
            }

            $items = $this->safe->get($query);
            $count = $this->safe->get($query, true);

            foreach ($items as &$item) {
                $item['created_at_formatted'] = Carbon::createFromFormat('Y-m-d H:i:s', $item['created_at'])
                                                ->format('d/m/Y H:i:s');

                if($item['updated_at']){
                    $item['updated_at_formatted'] = Carbon::createFromFormat('Y-m-d H:i:s', $item['updated_at'])
                    ->format('d/m/Y H:i:s');
                }

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
            
        } catch (PermissionException $e) {
            Json::send([
                'success' => true,
                'items' => [],
                'message' => 'Pesquisa concluída',
                'count' => 0
            ]); 
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao fazer a busca',
            ]);
        }
    }

    public function updateItemApi()
    {
        try {
            if (!$this->secure->isLoggedIn()
            || !$this->secure->hasPermission(AclRole::CAN_UPDATE_INVENTORY->value)){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            if (!$json['id']){
                throw new ReachableException("Id inválido");
            }

            if (!$json['name'] || !$this->secure->isValid('name', $json['name'])){
                throw new ReachableException("Revise o nome");
            }

            if (!$json['price'] || !$this->secure->isValid('price', $json['price'])){
                throw new ReachableException("Revise o valor");
            }

            if (!$json['quantity'] || !$this->secure->isValid('quantity', $json['quantity'])){
                throw new ReachableException("Revise a quantidade");
            }

            if ($json['description'] != '' && !$this->secure->isValid('description', $json['description'])){
                throw new ReachableException("Revise a descrição");
            }

            $toUpdate = $this->safe->update([
                'name' => $json['name'],
                'description' => $json['description'],
                'price' => $this->addDotBeforeZeros($json['price']),
                'quantity' => $json['quantity'],
            ], $json['id']);

            if (!$toUpdate){
                throw new ReachableException("Erro ao processar a requisição");
            }

            Json::send([
                'success' => true,
                'message' => 'Item atualizado com sucesso'
            ]);
        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    private function addDotBeforeZeros(string $string): string
    {
        $length = strlen($string);
    
        if ($length < 2) {
            return $string;
        }
        
        $newNumberStr = substr($string, 0, $length - 2) . '.' . substr($string, $length - 2);
        return $newNumberStr;
    }

    public function toggleFavoriteApi()
    {
        try {
            if(!$this->secure->isLoggedIn()
            || !$this->secure->hasPermission(AclRole::ADMIN->value)){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            if(!$json['id']){
                throw new ReachableException("Id inválido");
            }

            $toUpdate = $this->safe->update([
                'favorite' => $json['favorite']
            ], $json['id']);

            if(!$toUpdate){
                throw new ReachableException("Erro ao processar a requisição");
            }

            Json::send([
                'success' => true,
                'message' => $json['favorite'] ? 'Favorito adicionado com sucesso' : 'Favorito removido com sucesso'
            ]);
        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]); 
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function uploadImageApi()
    {
        try {
            if(!$this->secure->isLoggedIn()
            || !$this->secure->hasPermission(AclRole::CAN_CREATE_INVENTORY->value)){
                throw new PermissionException();
            }

            $files = Req::getFiles();

            if (!$files){
                throw new RequestException();
            }

            if(count($files) <= Req::MAX_IMAGES){
                foreach ($files as $file) {
                    if(!Req::validateFile($file)){
                        throw new ReachableException("Imagem inválida");
                    }
                }
            }

            $insert = $this->safe->update([
                'image' => json_encode(Req::getImages()),
            ], $_POST['id']);

            if(!$insert){
                throw new ReachableException("Algo deu errado");
            }

            Json::send([
                'success' => true,
                'message' => 'Imagem enviada com sucesso'
            ]);
        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]); 
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function uploadEditApi()
    {
        try {
            if(!$this->secure->isLoggedIn()
            || !$this->secure->hasPermission(AclRole::CAN_CREATE_INVENTORY->value)){
                throw new PermissionException();
            }

            $files = Req::getFiles();

            if ($files){
                if(count($files) <= Req::MAX_IMAGES){
                    foreach ($files as $file) {
                        if(!Req::validateFile($file)){
                            throw new ReachableException("Imagem inválida");
                        }
                    }
                }
    
                $insert = $this->safe->update([
                    'image' => json_encode(Req::getImages()),
                ], $_POST['id']);
    
                if(!$insert){
                    throw new ReachableException("Algo deu errado");
                }
            }else{
                $insert = $this->safe->update([
                    'image' => null,
                ], $_POST['id']);
    
                if(!$insert){
                    throw new ReachableException("Algo deu errado");
                }
            }
            
            Json::send([
                'success' => true,
                'message' => 'Imagem alterada com sucesso'
            ]);
        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]); 
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }

    public function deleteItemApi()
    {
        try {
            if(!$this->secure->isLoggedIn()
            || !$this->secure->hasPermission(AclRole::SUPER_ADMIN->value)){
                throw new PermissionException();
            }

            $req = Req::getParams();

            if (!$req){
                throw new RequestException();
            }

            if(!$req['id']){
                throw new ReachableException("Id inválido");
            }

            $delete = $this->safe->delete($req['id']);

            if(!$delete){
                throw new ReachableException("Erro ao processar a requisição");
            }

            Json::send([
                'success' => true,
                'message' => 'Item excluído com sucesso'
            ]);
        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }


    public function addItemApi()
    {
        try {
            if(!$this->secure->isLoggedIn()
            || !$this->secure->hasPermission(AclRole::CAN_CREATE_INVENTORY->value)){
                throw new PermissionException();
            }

            $json = Json::getJson();

            if (!$json){
                throw new RequestException();
            }

            if(!$json['name'] || !$this->secure->isValid('name', $json['name'])){
                throw new ReachableException("Revise o nome");
            }

            if(!$json['price'] || !$this->secure->isValid('price', $json['price'])){
                throw new ReachableException("Revise o valor");
            }

            if(!$json['quantity'] || !$this->secure->isValid('quantity', $json['quantity'])){
                throw new ReachableException("Revise a quantidade");
            }

            if($json['description'] != '' && !$this->secure->isValid('description', $json['description'])){
                throw new ReachableException("Revise a descrição");
            }

            $insert = $this->safe->createItem([
                'name' => $json['name'],
                'price' => $this->addDotBeforeZeros($json['price']),
                'quantity' => $json['quantity'],
                'description' => $json['description'],
            ]);

            if(!$insert){
                throw new ReachableException("Algo deu errado");
            }

            Json::send([
                'success' => true,
                'message' => 'Item adicionado com sucesso',
                'id' => $insert
            ]);
        } catch (ReachableException $e) {
            Json::send([
                'success' => false,
                'message' => $e->getMessage(),
            ]); 
        } catch (Throwable $th) {
            Json::send([
                'success' => false,
                'message' => 'Erro ao processar a requisição',
            ]);
        }
    }
}