<?php

namespace Database\Seeders;

use App\Models\Brand\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'slug' => 'zara',
                'name' => 'Zara',
                'description' => 'Spanish fast-fashion powerhouse known for contemporary wardrobe staples.',
                'website_url' => 'https://www.zara.com',
                'logo_url' => 'https://logo.clearbit.com/zara.com',
                'metadata' => [
                    'headquarters' => 'Arteixo, Spain',
                    'style_focus' => ['contemporary', 'casual'],
                ],
                'products' => [
                    [
                        'slug' => 'zara-linen-blend-blazer',
                        'name' => 'Slim Fit Linen Blend Blazer',
                        'category' => 'outerwear',
                        'gender' => 'men',
                        'description' => 'Lightweight linen-blend blazer with peak lapels and a relaxed silhouette.',
                        'color_palette' => ['primary' => '#D8D2C4', 'secondary' => '#2F2F2F'],
                        'price' => 179.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.zara.com/us/en/slim-fit-linen-blend-blazer-p0001.html',
                        'marketplace' => 'zara-us',
                        'image_url' => 'https://static.zara.net/photos///2023/I/0/2/p/0001/000/808/2/w/640/0001000808_1_1_1.jpg',
                        'metadata' => [
                            'materials' => ['linen', 'viscose'],
                            'care' => 'Dry clean only',
                            'fit' => 'slim',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'zara-pleated-wide-leg-trouser',
                        'name' => 'High Waist Pleated Wide-Leg Trouser',
                        'category' => 'bottom',
                        'gender' => 'women',
                        'description' => 'Fluid wide-leg trousers with front pleats and side seam pockets.',
                        'color_palette' => ['primary' => '#1E1E1E', 'secondary' => '#C5C6C7'],
                        'price' => 69.90,
                        'currency' => 'USD',
                        'product_url' => 'https://www.zara.com/us/en/high-waist-pleated-trouser-p0002.html',
                        'marketplace' => 'zara-us',
                        'image_url' => 'https://static.zara.net/photos///2023/I/0/1/p/0002/000/800/2/w/640/0002000800_1_1_1.jpg',
                        'metadata' => [
                            'materials' => ['polyester', 'viscose'],
                            'care' => 'Machine wash cold',
                            'fit' => 'relaxed',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'zara-leather-chelsea-boot',
                        'name' => 'Leather Chelsea Boot',
                        'category' => 'shoes',
                        'gender' => 'unisex',
                        'description' => 'Classic leather Chelsea boot with elastic side gores and stacked heel.',
                        'color_palette' => ['primary' => '#3C2A21', 'secondary' => '#111111'],
                        'price' => 169.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.zara.com/us/en/leather-chelsea-boot-p0003.html',
                        'marketplace' => 'zara-us',
                        'image_url' => 'https://static.zara.net/photos///2023/I/1/1/p/0003/000/700/2/w/640/0003000700_2_1_1.jpg',
                        'metadata' => [
                            'materials' => ['leather', 'rubber'],
                            'care' => 'Spot clean with leather conditioner',
                            'fit' => 'true to size',
                        ],
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'slug' => 'hugo-boss',
                'name' => 'Hugo Boss',
                'description' => 'German luxury fashion house celebrated for tailoring and elevated essentials.',
                'website_url' => 'https://www.hugoboss.com',
                'logo_url' => 'https://logo.clearbit.com/hugoboss.com',
                'metadata' => [
                    'headquarters' => 'Metzingen, Germany',
                    'style_focus' => ['tailoring', 'modern luxury'],
                ],
                'products' => [
                    [
                        'slug' => 'boss-modern-fit-wool-suit',
                        'name' => 'Modern Fit Wool Suit',
                        'category' => 'outerwear',
                        'gender' => 'men',
                        'description' => 'Two-piece virgin wool suit with light stretch and half-canvas construction.',
                        'color_palette' => ['primary' => '#1C1F2A', 'secondary' => '#5A5E68'],
                        'price' => 895.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.hugoboss.com/us/modern-fit-wool-suit-p1001.html',
                        'marketplace' => 'hugoboss-us',
                        'image_url' => 'https://assets.hugoboss.com/is/image/boss/hbeu50489083_001_320',
                        'metadata' => [
                            'materials' => ['virgin wool', 'elastane'],
                            'care' => 'Dry clean only',
                            'fit' => 'modern fit',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'boss-merino-wool-rollneck',
                        'name' => 'Merino Wool Roll-Neck Sweater',
                        'category' => 'top',
                        'gender' => 'men',
                        'description' => 'Fine-gauge merino roll-neck sweater ideal for layering under tailoring.',
                        'color_palette' => ['primary' => '#0F1115', 'secondary' => '#8A8D94'],
                        'price' => 198.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.hugoboss.com/us/merino-rollneck-sweater-p1002.html',
                        'marketplace' => 'hugoboss-us',
                        'image_url' => 'https://assets.hugoboss.com/is/image/boss/hbeu50493346_001_300',
                        'metadata' => [
                            'materials' => ['merino wool'],
                            'care' => 'Hand wash cold',
                            'fit' => 'slim',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'boss-suede-driver',
                        'name' => 'Suede Driver Loafers',
                        'category' => 'shoes',
                        'gender' => 'men',
                        'description' => 'Hand-stitched suede driver loafers with rubber pebble outsole.',
                        'color_palette' => ['primary' => '#6F4E37', 'secondary' => '#2F2A26'],
                        'price' => 248.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.hugoboss.com/us/suede-driver-loafers-p1003.html',
                        'marketplace' => 'hugoboss-us',
                        'image_url' => 'https://assets.hugoboss.com/is/image/boss/hbeu50488854_202_320',
                        'metadata' => [
                            'materials' => ['suede', 'rubber'],
                            'care' => 'Use suede brush to clean',
                            'fit' => 'regular',
                        ],
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'slug' => 'massimo-dutti',
                'name' => 'Massimo Dutti',
                'description' => 'Elegant European brand offering refined essentials and elevated casualwear.',
                'website_url' => 'https://www.massimodutti.com',
                'logo_url' => 'https://logo.clearbit.com/massimodutti.com',
                'metadata' => [
                    'headquarters' => 'Barcelona, Spain',
                    'style_focus' => ['minimalist', 'smart casual'],
                ],
                'products' => [
                    [
                        'slug' => 'massimo-relaxed-wool-coat',
                        'name' => 'Relaxed Double-Faced Wool Coat',
                        'category' => 'outerwear',
                        'gender' => 'women',
                        'description' => 'Double-faced wool coat with drop shoulders and tie belt closure.',
                        'color_palette' => ['primary' => '#C9C2B8', 'secondary' => '#3A3A3A'],
                        'price' => 349.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.massimodutti.com/us/en/relaxed-wool-coat-p2001.html',
                        'marketplace' => 'massimo-dutti-us',
                        'image_url' => 'https://static.massimodutti.net/assets/public/42/b8/e3/relaxed_wool_coat.jpg',
                        'metadata' => [
                            'materials' => ['wool', 'polyamide'],
                            'care' => 'Dry clean only',
                            'fit' => 'relaxed',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'massimo-silk-blend-shirt',
                        'name' => 'Silk Blend Button-Up Shirt',
                        'category' => 'top',
                        'gender' => 'women',
                        'description' => 'Silk blend shirt with concealed placket and fluid drape.',
                        'color_palette' => ['primary' => '#F5F0E8', 'secondary' => '#B8B0A4'],
                        'price' => 129.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.massimodutti.com/us/en/silk-blend-shirt-p2002.html',
                        'marketplace' => 'massimo-dutti-us',
                        'image_url' => 'https://static.massimodutti.net/assets/public/71/2b/a1/silk_blend_shirt.jpg',
                        'metadata' => [
                            'materials' => ['silk', 'viscose'],
                            'care' => 'Hand wash cold',
                            'fit' => 'regular',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'massimo-pleated-midi-skirt',
                        'name' => 'Pleated Midi Skirt',
                        'category' => 'bottom',
                        'gender' => 'women',
                        'description' => 'Calf-length pleated skirt with satin finish and elastic waistband.',
                        'color_palette' => ['primary' => '#3F4A5A', 'secondary' => '#A5AEBE'],
                        'price' => 119.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.massimodutti.com/us/en/pleated-midi-skirt-p2003.html',
                        'marketplace' => 'massimo-dutti-us',
                        'image_url' => 'https://static.massimodutti.net/assets/public/8e/3d/aa/pleated_midi_skirt.jpg',
                        'metadata' => [
                            'materials' => ['polyester'],
                            'care' => 'Machine wash cold',
                            'fit' => 'regular',
                        ],
                        'is_active' => true,
                    ],
                ],
            ],
            [
                'slug' => 'uniqlo',
                'name' => 'Uniqlo',
                'description' => 'Global retailer delivering functional and considered wardrobe basics.',
                'website_url' => 'https://www.uniqlo.com',
                'logo_url' => 'https://logo.clearbit.com/uniqlo.com',
                'metadata' => [
                    'headquarters' => 'Tokyo, Japan',
                    'style_focus' => ['minimalist', 'functional'],
                ],
                'products' => [
                    [
                        'slug' => 'uniqlo-ultra-light-down-jacket',
                        'name' => 'Ultra Light Down Jacket',
                        'category' => 'outerwear',
                        'gender' => 'unisex',
                        'description' => 'Packable ultra-light down jacket with water-repellent finish.',
                        'color_palette' => ['primary' => '#4A6073', 'secondary' => '#BCC7D1'],
                        'price' => 89.90,
                        'currency' => 'USD',
                        'product_url' => 'https://www.uniqlo.com/us/en/ultra-light-down-jacket-p3001.html',
                        'marketplace' => 'uniqlo-us',
                        'image_url' => 'https://image.uniqlo.com/UQ/ST3/us/imagesgoods/400001/item/usgoods_400001_69_01.jpg',
                        'metadata' => [
                            'materials' => ['nylon', 'down'],
                            'care' => 'Machine wash cold, tumble dry low',
                            'fit' => 'regular',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'uniqlo-supima-cotton-tee',
                        'name' => 'Supima Cotton Crew Neck T-Shirt',
                        'category' => 'top',
                        'gender' => 'unisex',
                        'description' => 'Soft Supima cotton tee with reinforced collar and clean finish.',
                        'color_palette' => ['primary' => '#FFFFFF', 'secondary' => '#C5C5C5'],
                        'price' => 19.90,
                        'currency' => 'USD',
                        'product_url' => 'https://www.uniqlo.com/us/en/supima-cotton-crew-tee-p3002.html',
                        'marketplace' => 'uniqlo-us',
                        'image_url' => 'https://image.uniqlo.com/UQ/ST3/us/imagesgoods/401002/item/usgoods_401002_00_01.jpg',
                        'metadata' => [
                            'materials' => ['supima cotton'],
                            'care' => 'Machine wash cold',
                            'fit' => 'regular',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'uniqlo-smart-ankle-pants',
                        'name' => 'Smart Ankle Pants',
                        'category' => 'bottom',
                        'gender' => 'women',
                        'description' => 'Two-way stretch ankle pants with clean front and tapered leg.',
                        'color_palette' => ['primary' => '#2F2F2F', 'secondary' => '#9A9A9A'],
                        'price' => 49.90,
                        'currency' => 'USD',
                        'product_url' => 'https://www.uniqlo.com/us/en/smart-ankle-pants-p3003.html',
                        'marketplace' => 'uniqlo-us',
                        'image_url' => 'https://image.uniqlo.com/UQ/ST3/us/imagesgoods/402003/item/usgoods_402003_09_01.jpg',
                        'metadata' => [
                            'materials' => ['polyester', 'rayon'],
                            'care' => 'Machine wash cold',
                            'fit' => 'tapered',
                        ],
                        'is_active' => true,
                    ],
                ],
            ],
        ];

        foreach ($brands as $brandData) {
            $products = $brandData['products'] ?? [];
            unset($brandData['products']);

            $brand = Brand::query()->updateOrCreate(
                ['slug' => $brandData['slug']],
                $brandData
            );

            foreach ($products as $productData) {
                $brand->products()->updateOrCreate(
                    ['slug' => $productData['slug']],
                    $productData
                );
            }
        }
    }
}
