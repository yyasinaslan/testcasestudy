<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * @group Siparişler
 */
class OrderController extends Controller
{
    /**
     * Siparis Listeleme
     */
    public function listOrders(Request $request)
    {
        return Order::all();
    }

    /**
     * Siparis Ekleme
     *
     * @bodyParam consumerId integer required
     * @bodyParam items object[] required
     * @bodyParam items[].productId integer required
     * @bodyParam items[].quantity integer required
     * @bodyParam items[].unitPrice number required
     * @bodyParam items[].total number required
     * @bodyParam total number required
     *
     * @param Request $request
     * @return void
     */
    public function addOrder(Request $request)
    {

    }

    /**
     * Siparis Silme
     *
     * @param Request $request
     * @return void
     */
    public function deleteOrder(Request $request)
    {

    }
}
