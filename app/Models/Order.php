<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'items' => 'json'
    ];

    public function toArray()
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'items' => $this->items,
            'total' => $this->total,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }


    public function getProductList()
    {
        if (!$this->items) {
            return [];
        }

        $products = [];
        foreach ($this->items as $item) {
            $p = Product::find($item['productId']);
            if ($p) {
                $products[] = $p->toArray();
            }
        }

        return $products;
    }
}
