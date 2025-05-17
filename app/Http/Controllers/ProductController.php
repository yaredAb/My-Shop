<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'desc');

        //applying search
        if($request->filled('product_name')) {
            $products->where('name', 'like', '%'. $request->product_name . '%');
        }

        //category filter
        if($request->filled('category_id')) {
            $products->where('category_id', $request->category_id);
        }

        $todaysSale = Sale::whereDate('sold_at', today())->sum(DB::raw('price * quantity'));
        return view('products.index', [
            'products' => $products->get(),
            'categories' => Category::all(),
            'todaySale' => $todaysSale
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->merge([
            'has_expiry' => $request->has('has_expiry')
        ]);
        $request->validate([
            'name' => 'required',
            'bought_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'has_expiry' => 'nullable|boolean',
            'expiry_date' => 'nullable|date|required_if:has_expiry, true',
        ]);

//        dd($request->all());

        Product::create([
            'name' => $request->name,
            'bought_price' => $request->bought_price,
            'sale_price' => $request->sale_price,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'has_expiry' => $request->has('has_expiry'),
            'expiry_date' => $request->has('has_expiry') ? $request->expiry_date : null,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('categories', 'product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->merge([
            'has_expiry' => $request->has('has_expiry')
        ]);
        $request->validate([
            'name' => 'required',
            'bought_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'has_expiry' => 'nullable|boolean',
            'expiry_date' => 'nullable|date|required_if:has_expiry, true',
        ]);


        $product->update([
            'name' => $request->name,
            'bought_price' => $request->bought_price,
            'sale_price' => $request->sale_price,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'has_expiry' => $request->has('has_expiry'),
            'expiry_date' => $request->has('has_expiry') ? $request->expiry_date : null,
        ]);
        return redirect()->route('products.index')->with('success', 'Product updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted ');
    }

    public function addToCart(Request $request, Product $product) {
        $cart = session()->get('cart', []);

        if(isset($cart[$product->id])) {
            $cart[$product->id]['quantity']+=1;
        }
        else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->sale_price,
                'quantity' => 1
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', "{$product->name} added to cart");
    }

    public function increaseQuantity($id) {
        $cart = session()->get('cart', []);

        if(isset($cart[$id])) {
            $cart[$id]['quantity'] += 1;
            session()->put('cart', $cart);
        }

        return redirect()->back();
    }

    public function decreaseQuantity($id){
        $cart = session()->get('cart', []);
        if(isset($cart[$id])) {
            $cart[$id]['quantity'] -= 1;

            if($cart[$id]['quantity'] <= 0) {
                unset($cart[$id]);
            }
            session()->put('cart', $cart);
        }

        return redirect()->back();
    }

    public function  updateQuantity(Request $request)
    {
        $cart = session('cart', []);
        $id = $request->input('id');
        $action = $request->input('action');

        if(isset($cart[$id])) {
            if($action == 'increase') {
                $cart[$id]['quantity'] += 1;
            } else if($action == 'decrease') {
                if($cart[$id]['quantity'] > 1) {
                    $cart[$id]['quantity'] -= 1;
                } else{
                    unset($cart[$id]);
                }
            }
            session(['cart' => $cart]);
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        if(!empty($cart)) {
            session()->put('cart_open', true);
        } else{
            session()->forget('cart_open');
        }

        return response()->json([
            'success' => true,
            'total' => $total,
            'cart' => $cart
        ]);
    }

    public function completeSale() {
        $cart = session('cart', []);

        if(empty($cart)) {
            return redirect()->back()->with('error', 'Cart is empty');
        }

        DB::beginTransaction();
        try {
            foreach ($cart as $productId=>$item) {
                Sale::create([
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'sold_at' => now()
                ]);

                $product = Product::findOrFail($productId);
                $product->decrement('quantity', $item['quantity']);

                $updatedProduct = Product::find($productId);
                $message = "⚠️ Low Stock Alert!\nProduct: {$updatedProduct->name}\nRemaining: {$updatedProduct->quantity}";
                try{
                    $this->sendTelegramMessage($updatedProduct, $message);
                } catch(\Exception $e) {
                    log_to_db('error', 'Telegram alert failed');
                }

            }

            DB::commit();

            session()->forget('cart');

            return redirect()->route('product.index')->with('success', 'Sale recorded successfully');
        }catch(\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to complete sale.');
        }
    }

    public function sendTelegramMessage($product, $message) {
        $token = Setting::getValue('telegram_bot_token');
        $chat_id = Setting::getValue('telegram_chat_id');
        if($product->quantity <= $product->stock_threshold) {
            Http::post("https://api.telegram.org/bot". $token . "/sendMessage",[
                'chat_id' => $chat_id,
                'text' => $message
            ]);
        }
    }


    public function listOfProducts() {
        $products = Product::with('category')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('products.listOfProducts', compact('products'));
    }
}
