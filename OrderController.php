<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CartItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        /**
         * - First i fetch all oders with their related models o rresources instead of fetching all and looping over them to access their related models
         * This query also adds the count as well instead of creating a variable and increamenting its values each time we loop over items
         * - I get the last added to cart here. At first it was in the loop over orders. I group them by their and return something like => [id => timeStr]
         * 
         * - Now i get all completed orders with thier id and completed_at column fields and structure them using keyBy
         * - calculatedOrders maps through the database. More like looping through without making a query anymore bacause we already initialized that in a variable
         * I first calculate the total amount by using SUM and providing a callback that calculates the sum of by going thru its items with price * quantity
         * At the end i just return the structured data with an additional completed_at key that will be used to sort it later
         * 
         * Now i have reduced the number of DB calls to just 3 (with the first vars we created)
         * 
         * Will suggest we return teh data as paginated instead of sending it all to the frontend.
         * 
         */
        // Fetching alll orders here is bad. I will use eager loading so that i get all the related models as well. Because deep down we are using the same query again and fetching related models which causes N+1 queries. I can even chunk them but for simplicity i will use eager loading.
        // $orders = Order::all();
        // $orderData = [];
        $orders = Order::with(['customer', 'items.product'])->withCount('items')->get();

        $completedOrders = Order::whereIn('id', $orders->pluck('id'))
            ->where('status', 'completed')
            ->select('id', 'completed_at')
            ->get()
            ->keyBy('id');

        // Here i am getting the last added cart item instead of having a nested loop and getting each of them anytime which causes a N + 1. 
        $lastAddedToCart = CartItem::selectRaw('order_id, MAX(created_at) as created_at')
            ->whereIn('order_id', $orders->pluck('id'))
            ->groupBy('order_id')
            ->pluck('created_at', 'order_id');

        
        $calculatedOrders = $orders->map(function ($order) use ($completedOrders, $lastAddedToCart) {
            $totalAmount = $order->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });
            return [
                'order_id' => $order->id,
                'customer_name' => $order->customer->name,
                'total_amount' => $totalAmount,
                'items_count' => $order->items_count,
                'last_added_to_cart' => $lastAddedToCart[$order->id] ?? null,
                'completed_order_exists' => $completedOrders->has($order->id),
                // using the completed_at to sort the orders easily later on
                'completed_at' => $completedOrders->get($order->id)?->completed_at ?? null,
                'created_at' => $order->created_at,
            ];
        });

        $sortedOrders = $calculatedOrders->sortByDesc('completed_at')->values()->all();

        return view('orders.index', ['orders' => $sortedOrders]);


    //     foreach ($orders as $order) {
    //         $customer = $order->customer;
    //         $items = $order->items;
    //         $totalAmount = 0;
    //         $itemsCount = 0;

    //         foreach ($items as $item) {
    //             $product = $item->product;
    //             $totalAmount += $item->price * $item->quantity;
    //             $itemsCount++;
    //         }

    //         $lastAddedToCart = CartItem::where('order_id', $order->id)
    //             ->orderByDesc('created_at')
    //             ->first()
    //             ->created_at ?? null;

    //         $completedOrderExists = Order::where('id', $order->id)
    //             ->where('status', 'completed')
    //             ->exists();

    //         $orderData[] = [
    //             'order_id' => $order->id,
    //             'customer_name' => $customer->name,
    //             'total_amount' => $totalAmount,
    //             'items_count' => $itemsCount,
    //             'last_added_to_cart' => $lastAddedToCart,
    //             'completed_order_exists' => $completedOrderExists,
    //             'created_at' => $order->created_at,
    //         ];
    //     }

    //     usort($orderData, function ($a, $b) {
    //         $aCompletedAt = Order::where('id', $a['order_id'])
    //             ->where('status', 'completed')
    //             ->orderByDesc('completed_at')
    //             ->first()
    //             ->completed_at ?? null;

    //         $bCompletedAt = Order::where('id', $b['order_id'])
    //             ->where('status', 'completed')
    //             ->orderByDesc('completed_at')
    //             ->first()
    //             ->completed_at ?? null;

    //         return strtotime($bCompletedAt) - strtotime($aCompletedAt);
    //     });

    //     return view('orders.index', ['orders' => $orderData]);
    // }
}
}