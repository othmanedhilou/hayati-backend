<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Document;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\Promotion;
use App\Models\ProviderReview;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransportOption;
use App\Models\TransportRoute;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── 1. Demo User ───────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'demo@hayati.ma'],
            [
                'name'     => 'Othman',
                'password' => Hash::make('password'),
                'phone'    => '0661234567',
                'city'     => 'Casablanca',
            ]
        );

        // Skip seeding if data already exists
        if (ServiceCategory::count() > 0) {
            return;
        }

        // ─── 2. Service Categories ──────────────────────────────────────
        $categories = [
            ['name' => 'Plombier',         'name_ar' => 'سباك',     'slug' => 'plombier',      'icon' => 'wrench'],
            ['name' => 'Électricien',      'name_ar' => 'كهربائي',  'slug' => 'electricien',   'icon' => 'zap'],
            ['name' => 'Mécanicien',       'name_ar' => 'ميكانيكي', 'slug' => 'mecanicien',    'icon' => 'settings'],
            ['name' => 'Peintre',          'name_ar' => 'صباغ',     'slug' => 'peintre',       'icon' => 'paintbrush'],
            ['name' => 'Menuisier',        'name_ar' => 'نجار',     'slug' => 'menuisier',     'icon' => 'hammer'],
            ['name' => 'Femme de ménage',  'name_ar' => 'خدامة',    'slug' => 'menage',        'icon' => 'home'],
            ['name' => 'Serrurier',        'name_ar' => 'سراج',     'slug' => 'serrurier',     'icon' => 'key'],
            ['name' => 'Climatisation',    'name_ar' => 'تكييف',    'slug' => 'climatisation', 'icon' => 'thermometer'],
        ];

        foreach ($categories as $cat) {
            ServiceCategory::create($cat);
        }

        // ─── 3. Service Providers ───────────────────────────────────────
        $cities = [
            'Casablanca' => ['lat' => 33.5731, 'lng' => -7.5898],
            'Rabat'      => ['lat' => 34.0209, 'lng' => -6.8416],
            'Marrakech'  => ['lat' => 31.6295, 'lng' => -7.9811],
            'Fès'        => ['lat' => 34.0331, 'lng' => -5.0003],
            'Tanger'     => ['lat' => 35.7595, 'lng' => -5.8340],
        ];

        $firstNames = [
            'Ahmed', 'Youssef', 'Hassan', 'Khalid', 'Rachid', 'Mustapha',
            'Omar', 'Driss', 'Samir', 'Aziz', 'Nabil', 'Brahim',
            'Hamid', 'Abdel', 'Mehdi',
        ];

        $lastNames = [
            'Bennani', 'El Amrani', 'Idrissi', 'Tazi', 'Fassi', 'Alaoui',
            'Berrada', 'Chraibi', 'Lahlou', 'Squalli', 'Zouak', 'Benjelloun',
            'Kettani', 'Senhaji', 'Bouazza',
        ];

        $priceRanges = [
            1 => [150, 500],   // plombier
            2 => [200, 600],   // electricien
            3 => [200, 800],   // mecanicien
            4 => [150, 400],   // peintre
            5 => [200, 700],   // menuisier
            6 => [100, 300],   // menage
            7 => [150, 400],   // serrurier
            8 => [300, 900],   // climatisation
        ];

        $providerIndex = 0;
        $reviewComments = [
            'Très professionnel, je recommande vivement.',
            'Bon travail, prix raisonnable.',
            'Rapide et efficace, merci !',
            'Je recommande, très bon service.',
            'Travail moyen mais correct.',
            'Excellent travail, ponctuel et sérieux.',
            'Satisfait du résultat, bon rapport qualité/prix.',
            'Compétent et honnête, je ferai appel à lui de nouveau.',
            'Travail propre et bien fait.',
            'Un peu cher mais le travail est de qualité.',
        ];

        for ($catId = 1; $catId <= 8; $catId++) {
            $numProviders = ($catId <= 4) ? 4 : 3; // 4 for first 4 categories, 3 for the rest
            $cityNames = array_keys($cities);

            for ($p = 0; $p < $numProviders; $p++) {
                $city = $cityNames[$p % 5];
                $coords = $cities[$city];
                $firstName = $firstNames[$providerIndex % count($firstNames)];
                $lastName = $lastNames[$providerIndex % count($lastNames)];
                $verified = ($providerIndex % 2 === 0);
                $rating = round(rand(35, 50) / 10, 1);
                $phone = '06' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

                [$priceMin, $priceMax] = $priceRanges[$catId];
                $actualMin = rand($priceMin, (int)(($priceMin + $priceMax) / 2));
                $actualMax = rand((int)(($priceMin + $priceMax) / 2), $priceMax);

                $provider = ServiceProvider::create([
                    'category_id'   => $catId,
                    'name'          => "$firstName $lastName",
                    'phone'         => $phone,
                    'description'   => "Professionnel expérimenté basé à $city.",
                    'city'          => $city,
                    'address'       => "Quartier " . ['Maârif', 'Agdal', 'Guéliz', 'Ville Nouvelle', 'Médina'][$p % 5] . ", $city",
                    'latitude'      => $coords['lat'] + (rand(-100, 100) / 10000),
                    'longitude'     => $coords['lng'] + (rand(-100, 100) / 10000),
                    'avg_rating'    => $rating,
                    'total_reviews' => rand(2, 3),
                    'price_min'     => $actualMin,
                    'price_max'     => $actualMax,
                    'image'         => null,
                    'verified'      => $verified,
                    'available'     => true,
                ]);

                // ─── 4. Provider Reviews (2-3 per provider) ─────────────
                $numReviews = rand(2, 3);
                for ($r = 0; $r < $numReviews; $r++) {
                    ProviderReview::create([
                        'user_id'     => $user->id,
                        'provider_id' => $provider->id,
                        'rating'      => rand(3, 5),
                        'comment'     => $reviewComments[($providerIndex + $r) % count($reviewComments)],
                    ]);
                }

                $providerIndex++;
            }
        }

        // ─── 5. Transport Routes ────────────────────────────────────────
        $routes = [
            ['origin' => 'Casablanca', 'destination' => 'Rabat',     'distance_km' => 87],
            ['origin' => 'Casablanca', 'destination' => 'Marrakech', 'distance_km' => 240],
            ['origin' => 'Casablanca', 'destination' => 'Fès',      'distance_km' => 295],
            ['origin' => 'Casablanca', 'destination' => 'Tanger',   'distance_km' => 340],
            ['origin' => 'Rabat',      'destination' => 'Fès',      'distance_km' => 207],
            ['origin' => 'Rabat',      'destination' => 'Marrakech','distance_km' => 330],
            ['origin' => 'Marrakech',  'destination' => 'Fès',      'distance_km' => 530],
            ['origin' => 'Tanger',     'destination' => 'Rabat',    'distance_km' => 250],
        ];

        $createdRoutes = [];
        foreach ($routes as $r) {
            $originCoords = $cities[$r['origin']];
            $destCoords = $cities[$r['destination']];

            $createdRoutes[] = TransportRoute::create([
                'origin'      => $r['origin'],
                'destination' => $r['destination'],
                'origin_lat'  => $originCoords['lat'],
                'origin_lng'  => $originCoords['lng'],
                'dest_lat'    => $destCoords['lat'],
                'dest_lng'    => $destCoords['lng'],
                'distance_km' => $r['distance_km'],
            ]);
        }

        // ─── 6. Transport Options ───────────────────────────────────────
        $routeOptions = [
            // Casa-Rabat
            1 => [
                ['type' => 'train',       'price_min' => 80,  'price_max' => 100, 'duration_minutes' => 60,  'provider_name' => 'ONCF Al Boraq',   'notes' => 'Train à grande vitesse'],
                ['type' => 'taxi',        'price_min' => 250, 'price_max' => 350, 'duration_minutes' => 45,  'provider_name' => 'Grand Taxi',      'notes' => 'Départ gare routière Ouled Ziane'],
                ['type' => 'bus',         'price_min' => 60,  'price_max' => 80,  'duration_minutes' => 90,  'provider_name' => 'CTM',             'notes' => 'Bus climatisé'],
                ['type' => 'covoiturage', 'price_min' => 50,  'price_max' => 70,  'duration_minutes' => 60,  'provider_name' => 'Covoiturage.ma',  'notes' => 'Prix par personne'],
            ],
            // Casa-Marrakech
            2 => [
                ['type' => 'train',       'price_min' => 150, 'price_max' => 200, 'duration_minutes' => 180, 'provider_name' => 'ONCF',            'notes' => 'Train classique'],
                ['type' => 'bus',         'price_min' => 120, 'price_max' => 150, 'duration_minutes' => 240, 'provider_name' => 'CTM / Supratours','notes' => 'Bus climatisé avec pause'],
                ['type' => 'taxi',        'price_min' => 500, 'price_max' => 700, 'duration_minutes' => 150, 'provider_name' => 'Grand Taxi',      'notes' => 'Départ Ouled Ziane'],
            ],
            // Casa-Fès
            3 => [
                ['type' => 'train',       'price_min' => 160, 'price_max' => 220, 'duration_minutes' => 210, 'provider_name' => 'ONCF',            'notes' => 'Train direct'],
                ['type' => 'bus',         'price_min' => 130, 'price_max' => 160, 'duration_minutes' => 270, 'provider_name' => 'CTM',             'notes' => 'Bus climatisé'],
                ['type' => 'taxi',        'price_min' => 550, 'price_max' => 750, 'duration_minutes' => 180, 'provider_name' => 'Grand Taxi',      'notes' => null],
            ],
            // Casa-Tanger
            4 => [
                ['type' => 'train',       'price_min' => 200, 'price_max' => 260, 'duration_minutes' => 130, 'provider_name' => 'ONCF Al Boraq',   'notes' => 'TGV via Rabat'],
                ['type' => 'bus',         'price_min' => 150, 'price_max' => 180, 'duration_minutes' => 300, 'provider_name' => 'CTM',             'notes' => 'Bus climatisé'],
                ['type' => 'taxi',        'price_min' => 600, 'price_max' => 850, 'duration_minutes' => 210, 'provider_name' => 'Grand Taxi',      'notes' => null],
            ],
            // Rabat-Fès
            5 => [
                ['type' => 'train',       'price_min' => 120, 'price_max' => 160, 'duration_minutes' => 150, 'provider_name' => 'ONCF',            'notes' => null],
                ['type' => 'bus',         'price_min' => 100, 'price_max' => 120, 'duration_minutes' => 200, 'provider_name' => 'CTM',             'notes' => null],
                ['type' => 'taxi',        'price_min' => 400, 'price_max' => 550, 'duration_minutes' => 130, 'provider_name' => 'Grand Taxi',      'notes' => null],
            ],
            // Rabat-Marrakech
            6 => [
                ['type' => 'train',       'price_min' => 190, 'price_max' => 250, 'duration_minutes' => 240, 'provider_name' => 'ONCF',            'notes' => 'Via Casa Voyageurs'],
                ['type' => 'bus',         'price_min' => 150, 'price_max' => 180, 'duration_minutes' => 300, 'provider_name' => 'CTM',             'notes' => null],
                ['type' => 'taxi',        'price_min' => 550, 'price_max' => 750, 'duration_minutes' => 200, 'provider_name' => 'Grand Taxi',      'notes' => null],
            ],
            // Marrakech-Fès
            7 => [
                ['type' => 'train',       'price_min' => 250, 'price_max' => 320, 'duration_minutes' => 420, 'provider_name' => 'ONCF',            'notes' => 'Via Casa avec correspondance'],
                ['type' => 'bus',         'price_min' => 200, 'price_max' => 250, 'duration_minutes' => 480, 'provider_name' => 'CTM / Supratours','notes' => 'Trajet long avec pauses'],
                ['type' => 'taxi',        'price_min' => 800, 'price_max' => 1100,'duration_minutes' => 330, 'provider_name' => 'Grand Taxi',      'notes' => null],
            ],
            // Tanger-Rabat
            8 => [
                ['type' => 'train',       'price_min' => 140, 'price_max' => 180, 'duration_minutes' => 100, 'provider_name' => 'ONCF Al Boraq',   'notes' => 'TGV direct'],
                ['type' => 'bus',         'price_min' => 110, 'price_max' => 140, 'duration_minutes' => 210, 'provider_name' => 'CTM',             'notes' => null],
                ['type' => 'taxi',        'price_min' => 450, 'price_max' => 600, 'duration_minutes' => 160, 'provider_name' => 'Grand Taxi',      'notes' => null],
                ['type' => 'covoiturage', 'price_min' => 80,  'price_max' => 110, 'duration_minutes' => 150, 'provider_name' => 'Covoiturage.ma',  'notes' => 'Prix par personne'],
            ],
        ];

        foreach ($routeOptions as $routeIndex => $options) {
            foreach ($options as $opt) {
                TransportOption::create(array_merge($opt, [
                    'route_id' => $createdRoutes[$routeIndex - 1]->id,
                ]));
            }
        }

        // ─── 7. Product Categories ──────────────────────────────────────
        $productCategories = [
            ['name' => 'Alimentaire', 'name_ar' => 'غذائيات',  'slug' => 'alimentaire', 'icon' => 'shopping-basket'],
            ['name' => 'Boissons',    'name_ar' => 'مشروبات',  'slug' => 'boissons',    'icon' => 'coffee'],
            ['name' => 'Hygiène',     'name_ar' => 'نظافة',    'slug' => 'hygiene',     'icon' => 'droplet'],
            ['name' => 'Ménager',     'name_ar' => 'منزلي',    'slug' => 'menager',     'icon' => 'home'],
            ['name' => 'Bébé',        'name_ar' => 'أطفال',    'slug' => 'bebe',        'icon' => 'baby'],
            ['name' => 'Épicerie',    'name_ar' => 'بقالة',    'slug' => 'epicerie',    'icon' => 'shopping-cart'],
        ];

        $createdProdCats = [];
        foreach ($productCategories as $pc) {
            $createdProdCats[] = ProductCategory::create($pc);
        }

        // ─── 8. Stores ─────────────────────────────────────────────────
        $stores = [
            Store::create(['name' => 'BIM',       'logo' => 'bim.png',       'city' => 'Casablanca', 'address' => 'Bd Zerktouni, Maârif',           'latitude' => 33.5880, 'longitude' => -7.6327]),
            Store::create(['name' => 'Marjane',    'logo' => 'marjane.png',   'city' => 'Casablanca', 'address' => 'Centre Commercial California',   'latitude' => 33.5650, 'longitude' => -7.6130]),
            Store::create(['name' => 'Carrefour',  'logo' => 'carrefour.png', 'city' => 'Casablanca', 'address' => 'Morocco Mall, Ain Diab',         'latitude' => 33.5500, 'longitude' => -7.6700]),
            Store::create(['name' => 'Acima',      'logo' => 'acima.png',     'city' => 'Casablanca', 'address' => 'Bd Massira Al Khadra',           'latitude' => 33.5770, 'longitude' => -7.6230]),
        ];

        // ─── 9. Products ───────────────────────────────────────────────
        // category_id: 1=Alimentaire, 2=Boissons, 3=Hygiène, 4=Ménager, 5=Bébé, 6=Épicerie
        $products = [
            ['category_id' => 1, 'name' => 'Huile d\'olive 1L',         'name_ar' => 'زيت الزيتون 1 لتر',      'brand' => 'Oued Souss',   'unit' => '1L'],
            ['category_id' => 1, 'name' => 'Lait Centrale 1L',          'name_ar' => 'حليب سنترال 1 لتر',      'brand' => 'Centrale',     'unit' => '1L'],
            ['category_id' => 6, 'name' => 'Riz Uncle Ben\'s 1kg',      'name_ar' => 'أرز 1 كيلو',             'brand' => 'Uncle Ben\'s', 'unit' => '1kg'],
            ['category_id' => 6, 'name' => 'Sucre 1kg',                 'name_ar' => 'سكر 1 كيلو',             'brand' => 'Cosumar',      'unit' => '1kg'],
            ['category_id' => 6, 'name' => 'Farine 1kg',                'name_ar' => 'دقيق 1 كيلو',            'brand' => 'Fandy',        'unit' => '1kg'],
            ['category_id' => 2, 'name' => 'Thé Sultan 200g',           'name_ar' => 'شاي سلطان 200 غ',        'brand' => 'Sultan',       'unit' => '200g'],
            ['category_id' => 2, 'name' => 'Café Dubois 250g',          'name_ar' => 'قهوة دوبوا 250 غ',       'brand' => 'Dubois',       'unit' => '250g'],
            ['category_id' => 2, 'name' => 'Eau Sidi Ali 1.5L',         'name_ar' => 'ماء سيدي علي 1.5 لتر',   'brand' => 'Sidi Ali',     'unit' => '1.5L'],
            ['category_id' => 2, 'name' => 'Coca Cola 1L',              'name_ar' => 'كوكا كولا 1 لتر',        'brand' => 'Coca-Cola',    'unit' => '1L'],
            ['category_id' => 3, 'name' => 'Javel Nassr 1L',            'name_ar' => 'جافيل نصر 1 لتر',        'brand' => 'Nassr',        'unit' => '1L'],
            ['category_id' => 3, 'name' => 'Savon Tide 3kg',            'name_ar' => 'صابون تايد 3 كيلو',      'brand' => 'Tide',         'unit' => '3kg'],
            ['category_id' => 5, 'name' => 'Couches Pampers',           'name_ar' => 'حفاضات بامبرز',          'brand' => 'Pampers',      'unit' => 'pack'],
            ['category_id' => 1, 'name' => 'Beurre Président 200g',     'name_ar' => 'زبدة بريزيدون 200 غ',    'brand' => 'Président',    'unit' => '200g'],
            ['category_id' => 1, 'name' => 'Fromage Vache Qui Rit',     'name_ar' => 'جبن البقرة الضاحكة',     'brand' => 'Vache Qui Rit','unit' => '16 portions'],
            ['category_id' => 6, 'name' => 'Tomate concentrée 400g',    'name_ar' => 'طماطم مركزة 400 غ',      'brand' => 'Aïcha',        'unit' => '400g'],
            ['category_id' => 1, 'name' => 'Huile Lesieur 1L',          'name_ar' => 'زيت لوسيور 1 لتر',       'brand' => 'Lesieur',      'unit' => '1L'],
            ['category_id' => 6, 'name' => 'Pâtes Dari 500g',           'name_ar' => 'معكرونة داري 500 غ',     'brand' => 'Dari',         'unit' => '500g'],
            ['category_id' => 6, 'name' => 'Sardines Titus',            'name_ar' => 'سردين تيتوس',            'brand' => 'Titus',        'unit' => '125g'],
            ['category_id' => 1, 'name' => 'Œufs (pack 30)',            'name_ar' => 'بيض (30 حبة)',            'brand' => null,           'unit' => '30 pcs'],
            ['category_id' => 1, 'name' => 'Pain de mie',               'name_ar' => 'خبز المائدة',            'brand' => 'Jawhara',      'unit' => '500g'],
            ['category_id' => 3, 'name' => 'Shampooing Elsève 250ml',   'name_ar' => 'شامبو إلسيف 250 مل',    'brand' => 'L\'Oréal',     'unit' => '250ml'],
            ['category_id' => 4, 'name' => 'Papier hygiénique Okay x12','name_ar' => 'ورق حمام أوكي x12',     'brand' => 'Okay',         'unit' => '12 rouleaux'],
        ];

        $createdProducts = [];
        foreach ($products as $prod) {
            $createdProducts[] = Product::create(array_merge($prod, ['image' => null]));
        }

        // ─── 10. Product Prices ─────────────────────────────────────────
        // Prices: [BIM, Marjane, Carrefour, Acima]
        $prices = [
            /* Huile d'olive */     [35.00, 42.00, 40.00, 43.00],
            /* Lait Centrale */     [7.00,  8.00,  7.50,  8.50],
            /* Riz 1kg */           [12.00, 14.00, 13.50, 14.50],
            /* Sucre 1kg */         [7.00,  7.50,  7.50,  8.00],
            /* Farine 1kg */        [5.00,  6.00,  5.50,  6.50],
            /* Thé Sultan */        [12.00, 14.00, 13.00, 14.50],
            /* Café Dubois */       [25.00, 28.00, 27.00, 29.00],
            /* Eau Sidi Ali */      [4.00,  5.00,  4.50,  5.00],
            /* Coca Cola */         [8.00,  9.50,  9.00,  10.00],
            /* Javel Nassr */       [6.00,  7.50,  7.00,  8.00],
            /* Savon Tide 3kg */    [38.00, 42.00, 40.00, 43.00],
            /* Couches Pampers */   [65.00, 72.00, 69.00, 74.00],
            /* Beurre Président */  [14.00, 16.00, 15.50, 16.50],
            /* Vache Qui Rit */     [18.00, 20.00, 19.50, 21.00],
            /* Tomate concentrée */ [5.50,  7.00,  6.50,  7.50],
            /* Huile Lesieur */     [16.00, 18.00, 17.50, 19.00],
            /* Pâtes Dari */        [5.00,  6.50,  6.00,  7.00],
            /* Sardines Titus */    [7.00,  8.50,  8.00,  9.00],
            /* Œufs pack 30 */      [33.00, 36.00, 35.00, 37.00],
            /* Pain de mie */       [8.00,  9.50,  9.00,  10.00],
            /* Shampooing Elsève */ [22.00, 26.00, 24.50, 27.00],
            /* Papier Okay x12 */   [15.00, 18.00, 17.00, 19.00],
        ];

        foreach ($createdProducts as $i => $product) {
            for ($s = 0; $s < 4; $s++) {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'store_id'   => $stores[$s]->id,
                    'price'      => $prices[$i][$s],
                    'old_price'  => null,
                ]);
            }
        }

        // ─── 11. Promotions ────────────────────────────────────────────
        $today = Carbon::today();
        $twoWeeks = Carbon::today()->addWeeks(2);

        $promos = [
            ['product_index' => 0,  'store_index' => 1, 'discount_percent' => 20], // Huile d'olive @ Marjane
            ['product_index' => 8,  'store_index' => 0, 'discount_percent' => 15], // Coca Cola @ BIM
            ['product_index' => 10, 'store_index' => 2, 'discount_percent' => 25], // Tide @ Carrefour
            ['product_index' => 11, 'store_index' => 3, 'discount_percent' => 30], // Pampers @ Acima
            ['product_index' => 5,  'store_index' => 0, 'discount_percent' => 10], // Thé Sultan @ BIM
            ['product_index' => 15, 'store_index' => 1, 'discount_percent' => 15], // Huile Lesieur @ Marjane
        ];

        foreach ($promos as $promo) {
            $product = $createdProducts[$promo['product_index']];
            $store = $stores[$promo['store_index']];
            $originalPrice = $prices[$promo['product_index']][$promo['store_index']];
            $promoPrice = round($originalPrice * (1 - $promo['discount_percent'] / 100), 2);

            // Update the ProductPrice old_price
            ProductPrice::where('product_id', $product->id)
                ->where('store_id', $store->id)
                ->update(['old_price' => $originalPrice, 'price' => $promoPrice]);

            Promotion::create([
                'product_id'       => $product->id,
                'store_id'         => $store->id,
                'discount_percent' => $promo['discount_percent'],
                'promo_price'      => $promoPrice,
                'start_date'       => $today,
                'end_date'         => $twoWeeks,
            ]);
        }

        // ─── 12. Transactions ───────────────────────────────────────────
        $transactions = [
            // Income
            ['type' => 'income',  'amount' => 8000, 'category' => 'Salaire',      'description' => 'Salaire mensuel Avril',     'date' => '2026-04-01'],
            ['type' => 'income',  'amount' => 2000, 'category' => 'Freelance',    'description' => 'Projet freelance web',       'date' => '2026-04-05'],
            // Expenses
            ['type' => 'expense', 'amount' => 3000, 'category' => 'Loyer',        'description' => 'Loyer appartement',          'date' => '2026-04-01'],
            ['type' => 'expense', 'amount' => 350,  'category' => 'Courses',      'description' => 'Courses Marjane',            'date' => '2026-04-02'],
            ['type' => 'expense', 'amount' => 25,   'category' => 'Transport',    'description' => 'Taxi centre-ville',          'date' => '2026-04-02'],
            ['type' => 'expense', 'amount' => 15,   'category' => 'Café',         'description' => 'Café avec un ami',           'date' => '2026-04-03'],
            ['type' => 'expense', 'amount' => 120,  'category' => 'Restaurant',   'description' => 'Dîner restaurant',           'date' => '2026-04-04'],
            ['type' => 'expense', 'amount' => 200,  'category' => 'Internet',     'description' => 'Abonnement Fibre Inwi',      'date' => '2026-04-05'],
            ['type' => 'expense', 'amount' => 150,  'category' => 'Électricité',  'description' => 'Facture LYDEC électricité',  'date' => '2026-04-06'],
            ['type' => 'expense', 'amount' => 80,   'category' => 'Eau',          'description' => 'Facture LYDEC eau',          'date' => '2026-04-06'],
            ['type' => 'expense', 'amount' => 100,  'category' => 'Téléphone',    'description' => 'Forfait Orange',             'date' => '2026-04-07'],
            ['type' => 'expense', 'amount' => 300,  'category' => 'Essence',      'description' => 'Plein essence Afriquia',     'date' => '2026-04-08'],
            ['type' => 'expense', 'amount' => 45,   'category' => 'Transport',    'description' => 'Tram aller-retour',          'date' => '2026-04-09'],
            ['type' => 'expense', 'amount' => 250,  'category' => 'Courses',      'description' => 'Courses BIM',                'date' => '2026-04-10'],
            ['type' => 'expense', 'amount' => 80,   'category' => 'Vêtements',    'description' => 'T-shirt H&M',                'date' => '2026-04-11'],
            ['type' => 'expense', 'amount' => 35,   'category' => 'Café',         'description' => 'Café et pâtisserie',         'date' => '2026-04-12'],
            ['type' => 'expense', 'amount' => 60,   'category' => 'Pharmacie',    'description' => 'Médicaments pharmacie',      'date' => '2026-04-13'],
            ['type' => 'expense', 'amount' => 150,  'category' => 'Courses',      'description' => 'Courses Carrefour',          'date' => '2026-04-15'],
        ];

        foreach ($transactions as $txn) {
            Transaction::create(array_merge($txn, [
                'user_id' => $user->id,
                'date'    => Carbon::parse($txn['date']),
            ]));
        }

        // ─── 13. Budget ────────────────────────────────────────────────
        Budget::create([
            'user_id'       => $user->id,
            'month'         => '2026-04',
            'income_target' => 10000,
            'expense_limit' => 7000,
        ]);

        // ─── 14. Documents ─────────────────────────────────────────────
        Document::create([
            'user_id'         => $user->id,
            'title'           => 'Carte d\'identité nationale',
            'type'            => 'CIN',
            'document_number' => 'AB123456',
            'issue_date'      => Carbon::parse('2020-01-15'),
            'expiry_date'     => Carbon::parse('2030-01-15'),
            'file_path'       => null,
            'notes'           => null,
            'reminder_sent'   => false,
        ]);

        Document::create([
            'user_id'         => $user->id,
            'title'           => 'Permis de conduire',
            'type'            => 'Permis',
            'document_number' => 'P789012',
            'issue_date'      => Carbon::parse('2022-06-01'),
            'expiry_date'     => Carbon::parse('2032-06-01'),
            'file_path'       => null,
            'notes'           => null,
            'reminder_sent'   => false,
        ]);

        Document::create([
            'user_id'         => $user->id,
            'title'           => 'Assurance automobile',
            'type'            => 'Assurance',
            'document_number' => 'ASS-2024-001',
            'issue_date'      => Carbon::parse('2025-09-01'),
            'expiry_date'     => Carbon::parse('2026-09-01'),
            'file_path'       => null,
            'notes'           => 'Assurance tous risques',
            'reminder_sent'   => false,
        ]);
    }
}
