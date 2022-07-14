<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

/**
 * @group Indirim Kurallari
 */
class DiscountController extends Controller
{

    /**
     * Butun kurallari getir
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return Discount::all();
    }

    /**
     * Sipariş için indirim hesapla
     *
     * @urlParam $order_id integer Sipariş id (order_id)
     */
    public function calculateDiscount($order_id)
    {

    }
}
