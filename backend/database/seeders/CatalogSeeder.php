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
     */
    public function run(): void
    {
        $categorias = [
            'Anillos' => [
                [
                    'name' => 'Anillo clásico',
                    'material' => 'Oro 18k',
                    'base_price' => 85000,
                    'description' => 'Anillo clásico de oro 18k, ideal para uso diario.',
                    'variantes' => [
                        ['talle' => '16', 'stock' => 5],
                        ['talle' => '18', 'stock' => 8],
                        ['talle' => '20', 'stock' => 3],
                    ],
                ],
                [
                    'name' => 'Anillo solitario',
                    'material' => 'Plata 925',
                    'base_price' => 32000,
                    'description' => 'Anillo solitario en plata 925 con circonia.',
                    'variantes' => [
                        ['talle' => '16', 'stock' => 6],
                        ['talle' => '18', 'stock' => 6],
                    ],
                ],
            ],
            'Cadenas' => [
                [
                    'name' => 'Cadena cubana',
                    'material' => 'Oro laminado',
                    'base_price' => 45000,
                    'description' => 'Cadena cubana de oro laminado, 50cm.',
                    'variantes' => [
                        ['talle' => '45cm', 'stock' => 10],
                        ['talle' => '50cm', 'stock' => 12],
                    ],
                ],
                [
                    'name' => 'Cadena veneciana',
                    'material' => 'Plata 925',
                    'base_price' => 28000,
                    'description' => 'Cadena veneciana clásica en plata 925.',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 15],
                    ],
                ],
            ],
            'Aros' => [
                [
                    'name' => 'Aros gota',
                    'material' => 'Plata 925',
                    'base_price' => 22000,
                    'description' => 'Aros gota en plata 925, livianos y versátiles.',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 20],
                    ],
                ],
                [
                    'name' => 'Aros argolla',
                    'material' => 'Oro laminado',
                    'base_price' => 18000,
                    'description' => 'Argollas medianas de oro laminado.',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 18],
                    ],
                ],
            ],
            'Pulseras' => [
                [
                    'name' => 'Pulsera tenis',
                    'material' => 'Plata 925',
                    'base_price' => 38000,
                    'description' => 'Pulsera tenis con circonias en plata 925.',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 7],
                    ],
                ],
                [
                    'name' => 'Pulsera cadena fina',
                    'material' => 'Oro laminado',
                    'base_price' => 21000,
                    'description' => 'Pulsera de cadena fina, oro laminado.',
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

                $product->images()->create([
                    'url' => 'https://picsum.photos/seed/'.$product->slug.'/600/600',
                    'order' => 0,
                ]);
            }
        }
    }
}
