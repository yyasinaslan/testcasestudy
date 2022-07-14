<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        // Orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customerId');
            $table->float('total');
            $table->json('items');
            $table->softDeletes();
            $table->timestamps();
        });

        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category');
            $table->text('name');
            $table->float('price');
            $table->bigInteger('stock');
            $table->timestamps();
        });

        // Customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('since');
            $table->float('revenue');
            $table->timestamps();
        });

        // Discounts Rules table
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            /**
             * Discount rule name
             * Discount visible name (same as reason)
             */
            $table->string('name');

            /**
             * Discount type = product | cart
             * cart => Discount will be applied to whole cart
             * product => Product specific rules
             */
            $table->string('type');

            /**
             * Filters for discount
             * Usable filters are: category, product, property
             * Examples:
             * {category:2} => This discount will only work on category that has id=2
             * {product:102} => This will only work for product_id=102
             * {property: {size:"medium"}, product:102} => It will work only specified product and medium size
             */
            $table->json('filters')->nullable();

            /**
             * Discount conditions (rule)
             * Properties must be between curly braces. For example: {cart_total}
             * Usable properties for cart:
             * cart {
             *      total, // Get total value of cart
             *      vat, // Get value added tax amount
             *      quantity, // Get item count of cart
             *      cheapest, // Get the cheapest item in cart
             *      most_expensive // Get the most expensive item in cart
             *  }
             * Examples for cart types:
             * {condition: "{cart.total} >= 1000", amount: "{cart.total} * 0.1"}
             * {condition: "{cart.quantity} >= 1", amount: "{cart.cheapest} * 0.2"}
             *
             * Usable props for product specific:
             * product {
             *      category,
             *      quantity, unitPrice,
             *      total,
             *      id
             *  }
             * Examples for product specific:
             * {condition: "{product.category} == 2 && {product.quantity} == 6", amount: "{product.unitPrice}"}
             *
             */
            $table->json('rule');

            /**
             * Discount expiration date
             */
            $table->timestamp('expired_at')->nullable();

            $table->timestamps();
        });

        /**
         * ============================================================================================================
         */

        // Adding example data
        $customersFile = resource_path('example_data/customers.json');
        $ordersFile = resource_path('example_data/orders.json');
        $productsFile = resource_path('example_data/products.json');

        try {
            // Inserting example data to orders table
            $ordersData = json_decode(file_get_contents($ordersFile));
            foreach ($ordersData as $ordersDatum) {
                $order = new \App\Models\Order();

                $order->id = $ordersDatum->id;
                $order->customerId = $ordersDatum->customerId;
                $order->items = json_encode($ordersDatum->items);
                $order->total = $ordersDatum->total;

                $order->save();
            }

            // Inserting example data to customers table
            $customersData = json_decode(file_get_contents($customersFile));
            foreach ($customersData as $customersDatum) {
                $customer = new \App\Models\Customer();

                $customer->id = $customersDatum->id;
                $customer->name = $customersDatum->name;
                $customer->since = $customersDatum->since;
                $customer->revenue = $customersDatum->revenue;

                $customer->save();
            }

            // Inserting example data to products table
            $productsData = json_decode(file_get_contents($productsFile));

            foreach ($productsData as $productsDatum) {
                $product = new \App\Models\Product();

                $product->id = $productsDatum->id;
                $product->name = $productsDatum->name;
                $product->category = $productsDatum->category;
                $product->price = $productsDatum->price;
                $product->stock = $productsDatum->stock;

                $product->save();
            }

            // Example discount entries

            /*
             * Toplam 1000TL ve üzerinde alışveriş yapan bir müşteri, siparişin tamamından %10 indirim kazanır.
             */
            $discount1 = new \App\Models\Discount();
            $discount1->name = "10 percent over 1000";
            $discount1->type = "cart";
            $discount1->filters = null;
            $discount1->rule = json_encode([
                'condition' => '{cart.total} >= 1000',
                'amount' => '{cart.total} * 0.1'
            ]);
            $discount1->save();

            /*
             * 2 ID'li kategoriye ait bir üründen 6 adet satın alındığında, bir tanesi ücretsiz olarak verilir.
             */
            $discount2 = new \App\Models\Discount();
            $discount2->name = "Buy 5 get 1 in category id 2";
            $discount2->type = "product";
            $discount2->filters = null;
            $discount2->rule = json_encode([
                'condition' => '{product.category} == 2 && {product.quantity} == 6',
                'amount' => '{product.unitPrice}'
            ]);
            $discount2->save();

            /*
             * 1 ID'li kategoriden iki veya daha fazla ürün satın alındığında, en ucuz ürüne %20 indirim yapılır.
             */
            $discount3 = new \App\Models\Discount();
            $discount3->name = "Buy atleast 2 get %20 discount for the cheapest item in category id 2";
            $discount3->type = "cart";
            $discount3->filters = json_encode([
                'category' => 1
            ]);
            $discount3->rule = json_encode([
                'condition' => '{cart.quantity} >= 2',
                'amount' => '{cart.cheapest} * 0.2'
            ]);
            $discount3->save();

        } catch (Exception $exception) {
            Schema::dropIfExists('customers');
            Schema::dropIfExists('products');
            Schema::dropIfExists('orders');
            Schema::dropIfExists('discounts');
            Schema::dropIfExists('categories');

            throw new Exception($exception);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop initial tables if they exist
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('discounts');
        Schema::dropIfExists('categories');
    }
};
