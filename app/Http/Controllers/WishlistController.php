<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Wishlist",
 *     description="Wishlist management"
 * )
 */
class WishlistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/wishlist",
     *     summary="Get user's wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = Auth::user();
        $wishlist = $user->wishlistProducts()
            ->with(['category', 'images'])
            ->paginate(10);

        return response()->json($wishlist);
    }

    /**
     * @OA\Post(
     *     path="/api/wishlist",
     *     summary="Add product to wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product added to wishlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Product already in wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product already in wishlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $user = Auth::user();

            if ($user->wishlistProducts()->where('product_id', $data['product_id'])->exists()) {
                return response()->json([
                    'message' => 'Product already in wishlist'
                ], 409);
            }

            $user->wishlistProducts()->attach($data['product_id']);

            return response()->json([
                'message' => 'Product added to wishlist'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/wishlist/{product_id}",
     *     summary="Remove product from wishlist",
     *     tags={"Wishlist"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product removed from wishlist")
     *         )
     *     )
     * )
     */
    public function destroy($product_id)
    {
        $user = Auth::user();
        $user->wishlistProducts()->detach($product_id);

        return response()->json([
            'message' => 'Product removed from wishlist'
        ]);
    }
}
