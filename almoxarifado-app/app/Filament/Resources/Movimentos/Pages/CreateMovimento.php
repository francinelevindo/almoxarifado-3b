<?php

namespace App\Filament\Resources\Movimentos\Pages;

use App\Filament\Resources\Movimentos\MovimentoResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Produto;
use App\Models\Movimento;
use Filament\Notifications\Notification;


class CreateMovimento extends CreateRecord
{
    protected static string $resource = MovimentoResource::class;

    protected function beforeCreate(): void
    {
        // recebe a lista de produtos
        $data = $this->data;

        //selecionando o produto/qtd e tipo pelo id recebido na lista
        $produto = Produto::find($data['produto_id']);
        $quantidade = $data['quantidade'];
        $tipo = $data['tipo'];

        //verificar se é uma saída e se o estoque é suficiente 
        if ($tipo === 's' && $quantidade > $produto->estoque){
            // notificar o usuário sobre o estoque insuficiente 
            Notification::make()
                ->danger()
                ->title('estoque Insuficiente!')
                ->body("o estoque de '{$produto->nome}' é de apenas '{$produto->estoque}' unidade, mas você tentou retirar {$quantidade}.")
                ->send();

            $this->halt(); //impede a criação do movimento
        }

    }
    
    //Hook - Remover ou aumentar o estoque 
    protected function afterCreate(): void
    {
        $movimento = $this->getRecord(); // registro do movimento criado
        $produto = $movimento->produto; //produto relacionado ao movimento

        if ($movimento->tipo === 'e') {
            // entrada : aumentar o estoque 
            $produto->increment('estoque', $movimento->quantidade);
        }else {
            // saída: diminuir o estoque 
            $produto->decrement('estoque', $movimento->quantidade);
        }
    }
}
