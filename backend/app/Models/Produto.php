<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'preco',
        'descricao'
    ];

    protected $casts = [
        'preco' => 'decimal:2'
    ];

    /**
     * Relacionamento many-to-many com usuários através da tabela pivot
     */
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_produto');
    }

    /**
     * Scope para buscar por faixa de preço
     */
    public function scopePorFaixaPreco($query, $min, $max)
    {
        return $query->whereBetween('preco', [$min, $max]);
    }
}
