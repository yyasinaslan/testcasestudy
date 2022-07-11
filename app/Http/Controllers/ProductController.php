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
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listProducts(Request $request)
    {
        $products = Product::all();

        return $products;
    }
}
