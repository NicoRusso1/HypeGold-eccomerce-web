<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AdminCategoryController extends Controller
{
    /**
     * Lista todas las categorías con la cantidad de productos de cada una.
     */
    #[OA\Get(
        path: '/admin/categories',
        summary: 'Listar categorías (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de categorías')],
    )]
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Crea una categoría.
     */
    #[OA\Post(
        path: '/admin/categories',
        summary: 'Crear una categoría (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Categoría creada')],
    )]
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $this->slugUnico($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        return (new CategoryResource($category->loadCount('products')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Actualiza una categoría.
     */
    #[OA\Put(
        path: '/admin/categories/{category}',
        summary: 'Actualizar una categoría (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Categoría actualizada')],
    )]
    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        $category->update([
            'name' => $data['name'],
            'slug' => $this->slugUnico($data['name'], $category->id),
            'description' => $data['description'] ?? null,
        ]);

        return new CategoryResource($category->loadCount('products'));
    }

    /**
     * Elimina una categoría. No se permite si todavía tiene productos
     * asociados, porque la relación borra los productos en cascada.
     */
    #[OA\Delete(
        path: '/admin/categories/{category}',
        summary: 'Eliminar una categoría (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Categoría eliminada'),
            new OA\Response(response: 422, description: 'La categoría todavía tiene productos'),
        ],
    )]
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['No se puede eliminar una categoría que todavía tiene productos.'],
            ]);
        }

        $category->delete();

        return response()->noContent();
    }

    private function slugUnico(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $intento = 1;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $intento++;
            $slug = "{$base}-{$intento}";
        }

        return $slug;
    }
}
