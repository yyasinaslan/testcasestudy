<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @group Siparişler
 */
class OrderController extends Controller
{
    /**
     * Siparis Listeleme
     *

     */
    public function listOrders(Request $request)
    {

        return [
            'id' => 1,
            'consumerId' => 12,
            'total' => 35.0,
            'items' => [
                [
                    'productId' => 122,
                    'quantity' => 2,
                    'unitPrice' => 10.0,
                    'total' => 20.0
                ],
                [
                    'productId' => 32,
                    'quantity' => 1,
                    'unitPrice' => 15.0,
                    'total' => 15.0
                ]
            ]
        ];
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
