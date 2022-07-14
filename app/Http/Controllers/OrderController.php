<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * @group Siparişler
 */
class OrderController extends Controller
{
    /**
     * Siparis Listeleme
     *
     * @response [
     * {
     * "id": 1,
     * "customerId": 1,
     * "items": [
     * {
     * "productId": 102,
     * "quantity": 10,
     * "unitPrice": "11.28",
     * "total": "112.80"
     * }
     * ],
     * "total": 112.8,
     * "created_at": "2022-07-12T15:14:16.000000Z",
     * "updated_at": "2022-07-12T15:14:16.000000Z"
     * }
     * ]
     */
    public function listOrders(Request $request)
    {
        return Order::all();
    }

    /**
     * Siparis Ekleme
     *
     * @bodyParam customerId integer required
     * @bodyParam items object[] required
     * @bodyParam items[].productId integer required
     * @bodyParam items[].quantity integer required
     *
     *
     * @param Request $request
     */
    public function addOrder(Request $request)
    {
        $data = $request->validate([
            'customerId' => ['required'], // Normalde bu bilgi giris yapan kullanicidan alinir.
            'items.*.productId' => ['required', 'numeric', 'integer'], // Urun id
            'items.*.quantity' => ['required', 'numeric', 'integer'], // Urun sayisi
        ]);

        $items = $data['items'];

        $customer = Customer::query()->find($data['customerId']);

        if (!$customer) {
            return [
                'error' => 'customer.not_found',
                'message' => __('Müşteri bulunamadı.')
            ];
        }

        $orderTotal = 0;
        $newItems = [];
        foreach ($items as $item) {
            $product = Product::find($item['productId']);

            // Belirtilen urun var mi veritabaninda mevcut mu?
            if (!$product) {
                return [
                    'error' => 'product.not_found',
                    'message' => $item['productId'] . ' ' . __('id ye sahip ürün bulunamadı.'),
                    'data' => [
                        'productId' => $item['productId'] // Hangi urunun hata verdigini bellirten bir veri
                    ]
                ];
            }

            // Belirtilen urunun stogu yeterli mi?
            if ($product['stock'] < $item['quantity']) {
                return [
                    'error' => 'product.stock',
                    'message' => $product->name . ' ' . __('adlı ürün için yeterli stok yok.'),
                    'data' => [
                        'productId' => $product->id // Hangi urunun hata verdigini bellirten bir veri
                    ]
                ];
            }

            $productTotal = ((float)$product->price) * ((float)$item['quantity']); // Urun toplam fiyat

            // Her sey duzgunse
            $newItems[] = [
                'productId' => $product->id,
                'quantity' => $item['quantity'],
                'unitPrice' => (float)$product->price,// Birim fiyat
                'total' => $productTotal
            ];

            $orderTotal += $productTotal;
        }

        $order = new Order(); // Yeni siparis olustur
        $order->customerId = $customer->id;
        $order->items = json_encode($newItems);
        $order->total = $orderTotal;

        $order->save();

        // Urun stogunu guncelle
        foreach ($newItems as $newItem) {
            $product = Product::find($newItem['productId']);

            $product->stock = (integer)$product->stock - (integer)$newItem['quantity'];

            $product->save();
        }

        return $order->toArray();
    }

    /**
     * Siparis Silme
     *
     * @param Request $request
     */
    public function deleteOrder($orderId, Request $request)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return [
                'error' => 'order.not_found',
                'message' => __('Siparis bulunamadi')
            ];
        }

        try {
            $order->delete();
        } catch (\Exception $exception) {
            return [
                'error' => 'unknown.EOR0001', // Belki hata kodu dinamik olarak olusturulabilir.
                'message' => __('Bilinmeyen bir hata oluştu')
            ];
        }

        return [
            'message' => __('Sipariş başarıyla silindi.')
        ];


    }
}
