<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

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
     * Indirim kurali ekle
     *
     * @bodyParam name string
     * @bodyParam type string product | cart. Example: cart
     * @bodyParam filters json Optional. You can filter by category or product id. Example: {"category":2}
     * @bodyParam rule json required
     * @bodyParam rule.condition string required Example: {cart.total} >= 1000
     * @bodyParam rule.amount string required Example: {cart.total} * 0.1
     * @bodyParam priority integer Priority. Higher priority discounts will be calculated first.
     *
     * @param Request $request
     */
    public function addDiscount(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'type' => ['required', Rule::in(['product', 'cart'])],
            'filters' => ['json'],
            'rule' => ['required', 'json'],
            'priority' => ['required', 'numeric', 'integer'],
        ]);

        if (!isset($data['rule']['condition'])){

        }
    }

    /**
     * Sipariş için indirim hesapla
     *
     * @urlParam $order_id integer Sipariş id (order_id)
     */
    public function calculateDiscount($order_id)
    {
        $order = Order::find($order_id);

        if (!$order) {
            return [
                'error' => 'order.not_found',
                'message' => __('Sipariş bulunamadı.')
            ];
        }

        $calculated = [
            'orderId' => $order->id,
            'discounts' => [],
            'totalDiscount' => 0,
            'discountedTotal' => $order->total
        ];

        /*
         * Get discounts with sorted descending priority and not expired yet.
         */
        $discounts = Discount::query()
            ->where('expires_at', '>', Carbon::now())
            ->orWhere('expires_at', null)
            ->orderByDesc('priority')
            ->get();

        $previousSubtotal = $order->total;
        foreach ($discounts as $discount) {
            $calcDiscount = $this->calculateSingleDiscount($order, $discount, $previousSubtotal);
            if ($calcDiscount) {
                $calculated['discounts'][] = $calcDiscount;
                $calculated['totalDiscount'] += $calcDiscount['discountAmount'];
                $calculated['discountedTotal'] = $calcDiscount['subtotal'];

                $previousSubtotal = $calcDiscount['subtotal'];
            }
        }

        /*
         * Only for debug
         */
//        return [
//            'discountCalculated' => $calculated,
//            'order' => $order,
//            'products' => $order->getProductList(),
//            'discountsAll' => $discounts
//        ];

        return $calculated;

    }

    /**
     * @param Order $order
     * @param Discount $discount
     * @return array
     *
     * Calculate single discount for given order
     */
    private function calculateSingleDiscount(Order $order, Discount $discount, $previousSubtotal)
    {
        $items = $order->items;

        $filteredItems = [];

        // Filter items
        if ($discount->filters != null) {

            foreach ($items as $item) {
                $product = Product::find($item['productId']);

                if (isset($discount->filters['category']) && $discount->filters['category'] == $product->category) {
                    $filteredItems[] = array_merge($item, $product->toArray());
                }

                if (isset($discount->filters['product']) && $discount->filters['product'] == $product->id) {
                    $filteredItems[] = array_merge($item, $product->toArray());
                }

                // todo: Add product property filter
            }

            /*
             * If there is nothing in cart after filtered items it means discount rule doesn't matching;
             */
            if (count($filteredItems) == 0) {
                return null;
            }
        }


        if ($discount->type == 'cart') {
            $varMapCart = [
                '{cart.total}' => $previousSubtotal,
                '{cart.vat}' => $previousSubtotal * 0.18, // This will be a table column. Its just an example.
                '{cart.quantity}' => $this->getItemsQuantity($filteredItems),
                '{cart.cheapest}' => $this->findCheapest($items),
                '{cart.most_expensive}' => $this->findMostExpensive($items),
            ];

            $condition = str_replace(array_keys($varMapCart), array_values($varMapCart), $discount->rule['condition']);

            $amount = str_replace(array_keys($varMapCart), array_values($varMapCart), $discount->rule['amount']);

            $calculatedDiscount = [];
            if (eval('return ' . $condition . ';')) { // eval() yerine özel bir parser kullanılmalıdır.
                $calculatedDiscount['discountReason'] = $discount->name;
                $calculatedDiscount['discountAmount'] = round(eval('return ' . $amount . ';'), 2); // eval() yerine özel bir parser kullanılmalıdır.
                $calculatedDiscount['subtotal'] = $previousSubtotal - $calculatedDiscount['discountAmount'];

                return $calculatedDiscount;
            }

            return null;

        } else if ($discount->type == 'product') {

            $calculatedDiscount = [];
            $calculatedDiscount['discountReason'] = $discount->name;
            $calculatedDiscount['discountAmount'] = 0;

            foreach ($items as $item) {

                $product = Product::find($item['productId']);

                if (!$product)
                    continue;

                $varMapProduct = [
                    '{product.category}' => (integer)$product->category,
                    '{product.quantity}' => (integer)$item['quantity'],
                    '{product.unitPrice}' => (float)$item['unitPrice'],
                    '{product.total}' => (float)$item['total'],
                    '{product.id}' => $product->id,
                ];

                $condition = str_replace(array_keys($varMapProduct), array_values($varMapProduct), $discount->rule['condition']);
                $amount = str_replace(array_keys($varMapProduct), array_values($varMapProduct), $discount->rule['amount']);

                if (eval('return ' . $condition . ';')) { // eval() yerine özel bir parser kullanılmalıdır.
                    $calculatedDiscount['discountAmount'] += round(eval('return ' . $amount . ';'), 2); // eval() yerine özel bir parser kullanılmalıdır.
                }
            }

            $calculatedDiscount['subtotal'] = $previousSubtotal - $calculatedDiscount['discountAmount'];

            if ($calculatedDiscount['discountAmount'] > 0) {
                return $calculatedDiscount;
            }

            return null;

        }


        return null;

    }

    /**
     * @param $items
     * @return mixed
     *
     * Find cheapest item in items array and return price
     */
    private function findCheapest($items)
    {
        $cheapest = $items[0]['unitPrice'];

        foreach ($items as $item) {
            if ($item['unitPrice'] < $cheapest) {
                $cheapest = $item['unitPrice'];
            }
        }

        return $cheapest;
    }

    /**
     * @param $items
     * @return mixed
     *
     * Find most expensive item in items array and return price
     */
    private function findMostExpensive($items)
    {
        $expensive = $items[0]['unitPrice'];

        foreach ($items as $item) {
            if ($item['unitPrice'] > $expensive) {
                $expensive = $item['unitPrice'];
            }
        }

        return $expensive;
    }


    /**
     * @param $items
     * @return int
     *
     * Get total items quantity (not item kind count)
     */
    private function getItemsQuantity($items)
    {
        $quantity = 0;

        foreach ($items as $item) {
            $quantity += $item['quantity'];
        }

        return $quantity;
    }
}
