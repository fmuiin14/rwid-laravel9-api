<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $slug = 'data:list';
        $cached = Redis::get('products:'.$slug);

        if(isset($cached)) {
            $products = json_decode($cached, FALSE);

            // return response()->json([
            //     'status_code' => 200,
            //     'message' => 'data dari redis',
            //     'data' => $posts
            // ]);
            return new ProductResource(true, 'Data REDIS', $products);
        } else {
            $products = Product::orderBy("id", "asc")->get();
            Redis::set('products:data:list', $products, 'EX', 120);

            // return response()->json([
            //     'status_code' => 200,
            //     'message' => 'data dari db',
            //     'data' => $posts
            // ]);
            return new ProductResource(true, 'Data DB', $products);
        }


    }

    public function store (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $image_path = $request->file('image')->store('products', 'public');


        $post = Product::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image_path
        ]);

        return new ProductResource(true, 'Data Post Berhasil Ditambahkan', $post);
    }

    public function show (Product $product)
    {
        return new ProductResource(true, 'Data Post Ditemukan', $product);
    }

    public function update (Request $request, Product $product)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'description'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('products', 'public');

            //delete old image
            Storage::delete('public/'.$product->image);

            $product->update([
                'title'     => $request->title,
                'description'   => $request->description,
                'image'     => $image_path
            ]);
        } else {
            $product->update([
                'title'     => $request->title,
                'description'   => $request->description,
            ]);
        }
        return new ProductResource(true, 'Data Product Berhasil Diubah!', $product);
    }

    public function destroy(Product $product)
    {
        Storage::delete('public/'.$product->image);

        $product->delete();

        return new ProductResource(true, 'Data Produk Berhasil Dihapus!', null);
    }
}
