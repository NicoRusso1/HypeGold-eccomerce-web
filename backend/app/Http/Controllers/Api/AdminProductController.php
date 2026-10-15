<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\ProductImageRequest;
use App\Http\Requests\Api\Admin\ProductRequest;
use App\Http\Resources\AdminProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AdminProductController extends Controller
{
    /**
     * Lista todos los productos (activos e inactivos) para el panel de administración.
     */
    #[OA\Get(
        path: '/admin/products',
        summary: 'Listar productos (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Listado de productos')],
    )]
    public function index()
    {
        $products = Product::query()
            ->with(['category', 'images'])
            ->withCount('variants')
            ->orderByDesc('id')
            ->paginate(15);

        return AdminProductResource::collection($products);
    }

    /**
     * Muestra un producto con sus variantes e imágenes para editarlo.
     */
    #[OA\Get(
        path: '/admin/products/{product}',
        summary: 'Ver un producto (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Detalle del producto')],
    )]
    public function show(Product $product)
    {
        return new AdminProductResource($product->load(['category', 'variants', 'images']));
    }

    /**
     * Crea un producto con sus variantes.
     */
    #[OA\Post(
        path: '/admin/products',
        summary: 'Crear un producto (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Producto creado')],
    )]
    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        $product = DB::transaction(function () use ($data) {
            $product = Product::create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->slugUnico($data['name']),
                'description' => $data['description'] ?? null,
                'material' => $data['material'],
                'base_price' => $data['base_price'],
                'active' => $data['active'] ?? true,
            ]);

            $this->sincronizarVariantes($product, $data['variants']);

            return $product;
        });

        return (new AdminProductResource($product->load(['category', 'variants', 'images'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Actualiza un producto y sus variantes. Permite desactivarlo sin borrarlo.
     */
    #[OA\Put(
        path: '/admin/products/{product}',
        summary: 'Actualizar un producto (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Producto actualizado')],
    )]
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $product) {
            $product->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->slugUnico($data['name'], $product->id),
                'description' => $data['description'] ?? null,
                'material' => $data['material'],
                'base_price' => $data['base_price'],
                'active' => $data['active'] ?? $product->active,
            ]);

            $this->sincronizarVariantes($product, $data['variants']);
        });

        return new AdminProductResource($product->load(['category', 'variants', 'images']));
    }

    /**
     * Elimina un producto (y en cascada sus variantes, imágenes y reseñas).
     * El historial de pedidos no se ve afectado: los items de pedido guardan
     * su propia copia de nombre/sku y no dependen de que el producto siga
     * existiendo.
     */
    #[OA\Delete(
        path: '/admin/products/{product}',
        summary: 'Eliminar un producto (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 204, description: 'Producto eliminado')],
    )]
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }

    /**
     * Sube una imagen para el producto.
     */
    #[OA\Post(
        path: '/admin/products/{product}/images',
        summary: 'Subir una imagen de producto (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 201, description: 'Imagen subida')],
    )]
    public function uploadImage(ProductImageRequest $request, Product $product)
    {
        $path = $request->file('image')->store('products', 'public');

        $image = $product->images()->create([
            'url' => Storage::disk('public')->url($path),
            'order' => $product->images()->count(),
        ]);

        return response()->json(['data' => [
            'id' => $image->id,
            'url' => $image->url,
            'order' => $image->order,
        ]], 201);
    }

    /**
     * Elimina una imagen del producto.
     */
    #[OA\Delete(
        path: '/admin/products/{product}/images/{image}',
        summary: 'Eliminar una imagen de producto (admin)',
        tags: ['Administración'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'image', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 204, description: 'Imagen eliminada')],
    )]
    public function deleteImage(Product $product, int $image)
    {
        $imagen = $product->images()->findOrFail($image);
        $imagen->delete();

        return response()->noContent();
    }

    private function slugUnico(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $intento = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $intento++;
            $slug = "{$base}-{$intento}";
        }

        return $slug;
    }

    /**
     * @param  array<int, array<string, mixed>>  $variantes
     */
    private function sincronizarVariantes(Product $product, array $variantes): void
    {
        $skus = array_column($variantes, 'sku');

        if (count($skus) !== count(array_unique($skus))) {
            throw ValidationException::withMessages(['variants' => ['Los SKU de las variantes no pueden repetirse.']]);
        }

        foreach ($variantes as $variante) {
            $enUso = ProductVariant::where('sku', $variante['sku'])
                ->when(! empty($variante['id']), fn ($q) => $q->where('id', '!=', $variante['id']))
                ->exists();

            if ($enUso) {
                throw ValidationException::withMessages([
                    'variants' => ["El SKU \"{$variante['sku']}\" ya está en uso por otra variante."],
                ]);
            }
        }

        $idsConservados = [];

        foreach ($variantes as $variante) {
            $atributos = [
                'talle' => $variante['talle'] ?? null,
                'sku' => $variante['sku'],
                'stock' => $variante['stock'],
                'extra_price' => $variante['extra_price'] ?? 0,
            ];

            if (! empty($variante['id'])) {
                $existente = $product->variants()->find($variante['id']);

                if ($existente) {
                    $existente->update($atributos);
                    $idsConservados[] = $existente->id;

                    continue;
                }
            }

            $nueva = $product->variants()->create($atributos);
            $idsConservados[] = $nueva->id;
        }

        $product->variants()->whereNotIn('id', $idsConservados)->delete();
    }
}
