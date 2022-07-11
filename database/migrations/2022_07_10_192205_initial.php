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

        // Discounts table
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Discount visible name (same as reason)
            $table->string('type'); // Type = product | cart
            $table->float('revenue');
            $table->timestamps();
        });


        // Adding example data
        $customersFile = resource_path('example_data/customers.json');
        $ordersFile = resource_path('example_data/orders.json');
        $productsFile = resource_path('example_data/products.json');

        try {
            // Inserting example data to orders table
            $ordersData = json_decode(file_get_contents($ordersFile));
            foreach ($ordersData as $ordersDatum) {
                $order = new \App\Models\Order();

                $order->customerId = $ordersDatum->customerId;
                $order->items = json_encode($ordersDatum->items);
                $order->total = $ordersDatum->total;

                $order->save();
            }

            // Inserting example data to customers table
            $customersData = json_decode(file_get_contents($customersFile));
            foreach ($customersData as $customersDatum) {
                $customer = new \App\Models\Customer();

                $customer->name = $customersDatum->name;
                $customer->since = $customersDatum->since;
                $customer->revenue = $customersDatum->revenue;

                $customer->save();
            }

            // Inserting example data to products table
            $productsData = json_decode(file_get_contents($productsFile));

            foreach ($productsData as $productsDatum) {
                $product = new \App\Models\Product();

                $product->name = $productsDatum->name;
                $product->category = $productsDatum->category;
                $product->price = $productsDatum->price;
                $product->stock = $productsDatum->stock;

                $product->save();
            }

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
