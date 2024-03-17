<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller {
    //! This method shows products page
    public function index() {
        $products = Product::orderBy('created_at', 'desc')->get(); //* This will get all the products from the database (in descending order of their creation date and time)
        return view('products.index', compact('products'));        //* This will pass the products to the view (resources/views/products/index.blade.php)
    }



    //! This method shows create product page
    public function create() {
        return view('products.create');
    }



    //! This method stores a new product in database
    public function store(Request $request) {
        $rules = [
            'name'        => 'required|min:3|max:100',
            'sku'         => 'required|min:3|max:100',
            'price'       => 'required|numeric',
            'description' => 'required',
        ];

        //? If the user uploads an image, the image will be validated
        if ($request->image != "") {
            $rules['image'] = 'image';
        }

        $validator = validator::make($request->all(), $rules);

        //? If the validation fails, the user will be redirected back to the form
        if ($validator->fails()) {
            return redirect()->route('products.create')->withErrors($validator)->withInput();
        }

        //* If the validation passes, the product will be stored in the database
        $product              = new Product();
        $product->name        = $request->name;
        $product->sku         = $request->sku;
        $product->price       = $request->price;
        $product->description = $request->description;
        $product->save();

        //? If the user uploads an image, the image will be stored in the database
        if ($request->image != "") {
            //? For Storing Image
            $image     = $request->image;
            $ext       = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $ext; //* This will generate a unique name for the image

            //? The image will be moved to the uploads/products folder
            $image->move(public_path('uploads/products'), $imageName);

            //? The image will be stored in the database
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }



    //! This method shows edit product page
    public function edit($id) {
        $product = Product::findOrFail($id);              //* This will get the product from the database
        return view('products.edit', compact('product')); //* This will pass the product to the view (resources/views/products/edit.blade.php)
    }



    //! This method updates a product in database
    public function update(Request $request, $id) {
        $product = Product::findOrFail($id); //* This will get the product from the database

        $rules = [
            'name'        => 'required|min:3|max:100',
            'sku'         => 'required|min:3|max:100',
            'price'       => 'required|numeric',
            'description' => 'required',
        ];

        //? If the user uploads an image, the image will be validated
        if ($request->image != "") {
            $rules['image'] = 'image';
        }

        $validator = validator::make($request->all(), $rules);

        //? If the validation fails, the user will be redirected back to the form
        if ($validator->fails()) {
            return redirect()->route('products.edit', $product->id)->withErrors($validator)->withInput();
        }

        //* If the validation passes, the product will be Update in the database
        $product->name        = $request->name;
        $product->sku         = $request->sku;
        $product->price       = $request->price;
        $product->description = $request->description;
        $product->save();

        //? If the user uploads an image, the image will be stored in the database
        if ($request->image != "") {
                                                                              //? For Deleting Old Image
            File::delete(public_path('uploads/products/' . $product->image)); //* This will delete the old image from the uploads/products folder

            //? For Storing Image
            $image     = $request->image;
            $ext       = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $ext; //* This will generate a unique name for the image

            //? The image will be moved to the uploads/products folder
            $image->move(public_path('uploads/products'), $imageName);

            //? The image will be stored in the database
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }



    //! This method deletes a product from database
    public function destroy($id) {
        $product = Product::findOrFail($id); //* This will get the product from the database

                                                                          //? For Deleting Old Image
        File::delete(public_path('uploads/products/' . $product->image)); //* This will delete the old image from the uploads/products folder

        //? Delete the product from the database
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }
}
