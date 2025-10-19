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
                        'slug' => 'double-breasted-wool-coat',
                        'name' => 'DOUBLE-BREASTED WOOL COAT',
                        'category' => 'coats',
                        'gender' => 'women',
                        'description' => 'Coat made of wool. High neck and long sleeves with shoulder pads. Front flap pockets and false double-welt pockets. Front double-breasted button closure.',
                        'color_palette' => [
                            'primary' => '',
                            'secondary' => ''
                        ],
                        'price' => 129.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.zara.com/us/en/double-breasted-wool-coat-p09130397.html?v1=488723513',
                        'marketplace' => 'Zara',
                        'image_url' => 'brand/zara/1.jpg',
                        'metadata' => [
                            'materials' => ['wool', 'polyester', 'acrylic', 'polyamide'],
                            'care' => '',
                            'fit' => '',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'zara-pleated-wide-leg-trouser',
                        'name' => 'DOUBLE-BREASTED SHORT COAT',
                        'category' => 'bottom',
                        'gender' => 'women',
                        'description' => 'Fluid wide-leg trousers with front pleats and side seam pockets.',
                        'color_palette' => ['primary' => '#1E1E1E', 'secondary' => '#C5C6C7'],
                        'price' => 79.90,
                        'currency' => 'USD',
                        'product_url' => 'https://www.zara.com/us/en/double-breasted-short-coat-p03046297.html',
                        'marketplace' => 'zara-us',
                        'image_url' => 'brand/zara/2.jpg',
                        'metadata' => [
                            'materials' => ['polyester', 'viscose'],
                            'care' => 'Machine wash cold',
                            'fit' => 'relaxed',
                        ],
                        'is_active' => true,
                    ],
                    [
                        'slug' => 'zara-leather-chelsea-boot',
                        'name' => 'SHOPPER BAG',
                        'category' => 'shoes',
                        'gender' => 'unisex',
                        'description' => 'Classic leather Chelsea boot with elastic side gores and stacked heel.',
                        'color_palette' => ['primary' => '#3C2A21', 'secondary' => '#111111'],
                        'price' => 59.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.zara.com/us/en/shopper-bag-p16039610.html',
                        'marketplace' => 'zara-us',
                        'image_url' => 'brand/zara/3.jpg',
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
                'products' => [],
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
                        'name' => 'Yung aralash matodan bumazey shim',
                        'category' => 'outerwear',
                        'gender' => 'women',
                        'description' => 'Double-faced wool coat with drop shoulders and tie belt closure.',
                        'color_palette' => ['primary' => '#C9C2B8', 'secondary' => '#3A3A3A'],
                        'price' => 349.00,
                        'currency' => 'USD',
                        'product_url' => 'https://www.massimodutti.com/us/en/relaxed-wool-coat-p2001.html',
                        'marketplace' => 'massimo-dutti-us',
                        'image_url' => 'brands/massimo-dutti/1.jpg',
                        'metadata' => [
                            'materials' => ['wool', 'polyamide'],
                            'care' => 'Dry clean only',
                            'fit' => 'relaxed',
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
                'products' => [],
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
