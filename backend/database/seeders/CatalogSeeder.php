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
                [
                    'name' => 'Cadena cubana fina',
                    'material' => 'Oro laminado',
                    'base_price' => 32000,
                    'description' => 'Cadena cubana fina, ideal para uso diario o combinar con dije.',
                    'imagen' => 'cadena-cubana-fina.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 10],
                    ],
                ],
                [
                    'name' => 'Cadena con dije 32',
                    'material' => 'Oro laminado',
                    'base_price' => 42000,
                    'description' => 'Cadena fina con dije numerado, el regalo personalizado perfecto.',
                    'imagen' => 'cadena-con-dije-32.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 6],
                    ],
                ],
                [
                    'name' => 'Cadena caracol enrollada',
                    'material' => 'Oro laminado',
                    'base_price' => 30000,
                    'description' => 'Cadena caracol enrollada, textura trenzada y mucho brillo.',
                    'imagen' => 'cadena-caracol-enrollada.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 9],
                    ],
                ],
                [
                    'name' => 'Cadena singapur mediana',
                    'material' => 'Oro laminado',
                    'base_price' => 34000,
                    'description' => 'Cadena singapur mediana, más presencia que la versión fina.',
                    'imagen' => 'cadena-singapur-mediana.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 8],
                    ],
                ],
                [
                    'name' => 'Cadena doble grumet',
                    'material' => 'Oro laminado',
                    'base_price' => 34000,
                    'description' => 'Cadena doble grumet, eslabones dobles entrelazados.',
                    'imagen' => 'cadena-doble-grumet.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 7],
                    ],
                ],
                [
                    'name' => 'Cadena tourbillon',
                    'material' => 'Oro laminado',
                    'base_price' => 33000,
                    'description' => 'Cadena tourbillon, clásica y versátil para todos los días.',
                    'imagen' => 'cadena-tourbillon.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 10],
                    ],
                ],
                [
                    'name' => 'Cadena rosario fino',
                    'material' => 'Oro laminado',
                    'base_price' => 35000,
                    'description' => 'Cadena rosario fino, delicada y elegante.',
                    'imagen' => 'cadena-rosario-fino.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 8],
                    ],
                ],
                [
                    'name' => 'Cadena fígaro 3x1',
                    'material' => 'Oro laminado',
                    'base_price' => 27000,
                    'description' => 'Cadena fígaro 3x1, el diseño alternado clásico.',
                    'imagen' => 'cadena-figaro-3x1.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 9],
                    ],
                ],
                [
                    'name' => 'Cadena punto peruano gruesa',
                    'material' => 'Oro laminado',
                    'base_price' => 52000,
                    'description' => 'Cadena punto peruano gruesa, una pieza statement.',
                    'imagen' => 'cadena-punto-peruano-gruesa.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 5],
                    ],
                ],
                [
                    'name' => 'Cadena rosario XL',
                    'material' => 'Oro laminado',
                    'base_price' => 40000,
                    'description' => 'Cadena rosario XL, eslabones grandes y mucha presencia.',
                    'imagen' => 'cadena-rosario-xl.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 6],
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
                [
                    'name' => 'Pulsera cubana',
                    'material' => 'Oro laminado',
                    'base_price' => 25000,
                    'description' => 'Pulsera cubana, más fina que la versión gruesa.',
                    'imagen' => 'pulsera-cubana.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 10],
                    ],
                ],
                [
                    'name' => 'Pulsera fígaro 1x1',
                    'material' => 'Oro laminado',
                    'base_price' => 23000,
                    'description' => 'Pulsera fígaro 1x1, diseño alternado clásico.',
                    'imagen' => 'pulsera-figaro-1x1.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 9],
                    ],
                ],
                [
                    'name' => 'Pulsera tourbillon gruesa',
                    'material' => 'Oro laminado',
                    'base_price' => 26000,
                    'description' => 'Pulsera tourbillon gruesa, más presencia en la muñeca.',
                    'imagen' => 'pulsera-tourbillon-gruesa.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 8],
                    ],
                ],
                [
                    'name' => 'Pulsera panther',
                    'material' => 'Oro laminado',
                    'base_price' => 26000,
                    'description' => 'Pulsera panther, eslabones planos y brillo continuo.',
                    'imagen' => 'pulsera-panther.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 7],
                    ],
                ],
                [
                    'name' => 'Pulsera chapita',
                    'material' => 'Oro laminado',
                    'base_price' => 26000,
                    'description' => 'Pulsera chapita, estilo clásico atemporal.',
                    'imagen' => 'pulsera-chapita.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 8],
                    ],
                ],
                [
                    'name' => 'Pulsera gucci',
                    'material' => 'Oro laminado',
                    'base_price' => 23000,
                    'description' => 'Pulsera gucci, eslabones entrelazados tipo mariner.',
                    'imagen' => 'pulsera-gucci.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 10],
                    ],
                ],
                [
                    'name' => 'Pulsera paris',
                    'material' => 'Oro laminado',
                    'base_price' => 23000,
                    'description' => 'Pulsera paris, delicada y versátil.',
                    'imagen' => 'pulsera-paris.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 9],
                    ],
                ],
                [
                    'name' => 'Pulsera cubana aplastada',
                    'material' => 'Oro laminado',
                    'base_price' => 23000,
                    'description' => 'Pulsera cubana aplastada, eslabones planos y brillantes.',
                    'imagen' => 'pulsera-cubana-aplastada.jpg',
                    'variantes' => [
                        ['talle' => 'Único', 'stock' => 8],
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
                if (Product::where('slug', Str::slug($data['name']))->exists()) {
                    continue;
                }

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
