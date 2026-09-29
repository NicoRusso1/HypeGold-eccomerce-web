<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Categorías y productos de ejemplo para desarrollo y demo.
     *
     * HypeGold vende cadenas y pulseras. Todas usan fotos reales de HypeGold
     * (public/images/catalog).
     */
    public function run(): void
    {
        $categorias = [
            'Cadenas' => [
                [
                    'name' => 'Cadena cubana',
                    'material' => 'Oro laminado',
                    'base_price' => 45000,
                    'description' => 'Cadena cubana de oro laminado, 50cm.',
                    'imagen' => 'cadena-cubana.jpg',
                    'variantes' => [
                        ['talle' => '45cm', 'stock' => 10],
                        ['talle' => '50cm', 'stock' => 12],
                    ],
                ],
                [
                    'name' => 'Cadena singapur',
                    'material' => 'Oro 18k',
                    'base_price' => 28000,
                    'description' => 'Cadena singapur clásica, brillo continuo.',
                    'imagen' => 'cadena-singapur.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 15],
                    ],
                ],
                [
                    'name' => 'Cadena con dije corona',
                    'material' => 'Oro laminado',
                    'base_price' => 35000,
                    'description' => 'Cadena con dije corona, el ícono de HypeGold.',
                    'imagen' => 'cadena-dije-corona.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 9],
                    ],
                ],
                [
                    'name' => 'Cadena con dije cruz',
                    'material' => 'Oro laminado',
                    'base_price' => 33000,
                    'description' => 'Cadena fina con dije cruz.',
                    'imagen' => 'cadena-dije-cruz.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 11],
                    ],
                ],
            ],
            'Pulseras' => [
                [
                    'name' => 'Pulsera cubana gruesa',
                    'material' => 'Oro laminado',
                    'base_price' => 38000,
                    'description' => 'Pulsera cubana gruesa, oro laminado.',
                    'imagen' => 'pulsera-cubana-gruesa.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 7],
                    ],
                ],
                [
                    'name' => 'Pulsera forcet',
                    'material' => 'Oro 18k',
                    'base_price' => 24000,
                    'description' => 'Pulsera cadena forcet larga.',
                    'imagen' => 'pulsera-forcet.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 12],
                    ],
                ],
                [
                    'name' => 'Pulsera tourbillon fina',
                    'material' => 'Oro laminado',
                    'base_price' => 21000,
                    'description' => 'Pulsera de cadena fina estilo tourbillon.',
                    'imagen' => 'pulsera-tourbillon-fina.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 14],
                    ],
                ],
            ],
        ];

        foreach ($categorias as $categoriaNombre => $productos) {
            $categoria = Category::firstOrCreate(
                ['slug' => Str::slug($categoriaNombre)],
                ['name' => $categoriaNombre, 'description' => "Joyas de la categoría {$categoriaNombre}."],
            );

            foreach ($productos as $data) {
                $product = Product::create([
                    'category_id' => $categoria->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => $data['description'],
                    'material' => $data['material'],
                    'base_price' => $data['base_price'],
                    'active' => true,
                ]);

                foreach ($data['variantes'] as $variante) {
                    $product->variants()->create([
                        'talle' => $variante['talle'],
                        'sku' => strtoupper(Str::slug($data['name'], '')).'-'.strtoupper(Str::random(4)),
                        'stock' => $variante['stock'],
                        'extra_price' => 0,
                    ]);
                }

                $url = $data['imagen']
                    ? asset('images/catalog/'.$data['imagen'])
                    : 'https://picsum.photos/seed/'.$product->slug.'/600/600';

                $product->images()->create(['url' => $url, 'order' => 0]);
            }
        }
    }
}
