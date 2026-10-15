<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ReviewRequest;
use App\Http\Resources\AdminReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminReviewController extends Controller
{
    /**
     * Lista las reseñas cargadas, opcionalmente filtradas por producto.
     */
    #[OA\Get(
        path: '/admin/reviews',
        summary: 'Listar reseñas (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Listado de reseñas')],
    )]
    public function index(Request $request)
    {
        $reviews = Review::query()
            ->with('product')
            ->when($request->query('product_id'), fn ($q, $productId) => $q->where('product_id', $productId))
            ->latest()
            ->get();

        return AdminReviewResource::collection($reviews);
    }

    /**
     * Carga una reseña curada para un producto.
     */
    #[OA\Post(
        path: '/admin/reviews',
        summary: 'Crear una reseña (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Reseña creada')],
    )]
    public function store(ReviewRequest $request)
    {
        $review = Review::create($request->validated());

        return (new AdminReviewResource($review->load('product')))->response()->setStatusCode(201);
    }

    /**
     * Actualiza una reseña.
     */
    #[OA\Put(
        path: '/admin/reviews/{review}',
        summary: 'Actualizar una reseña (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'review', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Reseña actualizada')],
    )]
    public function update(ReviewRequest $request, Review $review)
    {
        $review->update($request->validated());

        return new AdminReviewResource($review->load('product'));
    }

    /**
     * Elimina una reseña.
     */
    #[OA\Delete(
        path: '/admin/reviews/{review}',
        summary: 'Eliminar una reseña (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'review', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Reseña eliminada')],
    )]
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->noContent();
    }
}
