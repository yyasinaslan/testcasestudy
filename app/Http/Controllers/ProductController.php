<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;


/**
 * @group Ürünler
 */
class ProductController extends Controller
{
    /**
     * Ürünleri Listele
     *
     * Ürünler için şimdilik sadece listeleme özelliği bulunmaktadır.
     *
     * @response [
     * {
     * "id": 100,
     * "category": 1,
     * "name": "Black&Decker A7062 40 Parça Cırcırlı Tornavida Seti",
     * "price": 120.75,
     * "stock": 10,
     * "created_at": "2022-07-12T15:14:16.000000Z",
     * "updated_at": "2022-07-12T15:14:16.000000Z"
     * }
     * ]
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listProducts(Request $request)
    {
        $products = Product::all();

        return $products;
    }
}
