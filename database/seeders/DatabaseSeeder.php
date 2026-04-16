<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
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
        // ─── Utilisateurs ────────────────────────────────────────────
        User::firstOrCreate(['email' => 'admin@example.com'], [
            'name'      => 'Administrateur',
            'password'  => Hash::make('password123'),
            'is_admin'  => true,
        ]);

        User::firstOrCreate(['email' => 'user@example.com'], [
            'name'     => 'Utilisateur Test',
            'password' => Hash::make('password'),
        ]);

        // ─── Catégories ──────────────────────────────────────────────
        $categories = [
            ['name' => 'Électronique',    'slug' => 'electronique'],
            ['name' => 'Mode & Vêtements', 'slug' => 'mode-vetements'],
            ['name' => 'Maison & Déco',   'slug' => 'maison-deco'],
            ['name' => 'Sport & Loisirs', 'slug' => 'sport-loisirs'],
            ['name' => 'Alimentation',    'slug' => 'alimentation'],
            ['name' => 'Beauté & Santé',  'slug' => 'beaute-sante'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $elec    = Category::where('slug', 'electronique')->first();
        $mode    = Category::where('slug', 'mode-vetements')->first();
        $maison  = Category::where('slug', 'maison-deco')->first();
        $sport   = Category::where('slug', 'sport-loisirs')->first();
        $food    = Category::where('slug', 'alimentation')->first();
        $beaute  = Category::where('slug', 'beaute-sante')->first();

        // ─── Produits ────────────────────────────────────────────────
        $products = [
            // Électronique
            [
                'title'          => 'Smartphone Samsung Galaxy A54',
                'description'    => 'Écran AMOLED 6.4", 128 Go, appareil photo 50 MP, batterie 5000 mAh. Idéal pour un usage quotidien.',
                'price'          => 185000,
                'discount_price' => 169000,
                'image'          => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $elec->id,
            ],
            [
                'title'          => 'Enceinte Bluetooth Portable Sony',
                'description'    => 'Enceinte compacte, étanche IP67, autonomie 16h, son puissant, idéale pour l’extérieur.',
                'price'          => 22000,
                'discount_price' => 18500,
                'image'          => 'https://images.unsplash.com/photo-1464983953574-0892a716854b?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $elec->id,
            ],
            [
                'title'          => 'Clé USB 128Go SanDisk Ultra',
                'description'    => 'Clé USB 3.0, transfert rapide, design compact, compatible PC/Mac.',
                'price'          => 9500,
                'discount_price' => 7900,
                'image'          => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $elec->id,
            ],
            [
                'title'          => 'Écouteurs Bluetooth JBL Tune 510BT',
                'description'    => 'Son puissant JBL Pure Bass, 40h d\'autonomie, charge rapide, pliables.',
                'price'          => 25000,
                'discount_price' => null,
                'image'          => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $elec->id,
            ],
            [
                'title'          => 'Tablette Lenovo Tab M10 Plus',
                'description'    => 'Écran Full HD 10.3", 64 Go, 4 Go RAM, batterie 5000 mAh, Android 12.',
                'price'          => 120000,
                'discount_price' => 105000,
                'image'          => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $elec->id,
            ],
            [
                'title'          => 'Montre connectée Samsung Galaxy Watch 5',
                'description'    => 'Suivi de santé avancé, GPS intégré, résistante à l\'eau 50 m, batterie 2 jours.',
                'price'          => 75000,
                'discount_price' => null,
                'image'          => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop',
                'in_stock'       => false,
                'category_id'    => $elec->id,
            ],
            // Mode
            [
                'title'          => 'Robe Wax Africaine Brodée',
                'description'    => 'Robe longue en tissu wax 100% coton, broderies traditionnelles, coupe ajustée.',
                'price'          => 18000,
                'discount_price' => 14000,
                'image'          => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $mode->id,
            ],
            [
                'title'          => 'Boubou Grand Bazin Riche Homme',
                'description'    => 'Boubou 3 pièces en bazin riche, teinture naturelle, broderie over-lock. Disponible en plusieurs couleurs.',
                'price'          => 35000,
                'discount_price' => null,
                'image'          => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $mode->id,
            ],
            // Maison & Déco
            [
                'title'          => 'Ventilateur de Table Nasco 16"',
                'description'    => 'Ventilateur silencieux 3 vitesses, oscillation automatique, grille de protection.',
                'price'          => 22000,
                'discount_price' => 19500,
                'image'          => 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $maison->id,
            ],
            [
                'title'          => 'Canapé 3 Places en Tissu Gris',
                'description'    => 'Canapé moderne confortable, structure bois massif, tissu anti-taches, pieds chromés.',
                'price'          => 280000,
                'discount_price' => 245000,
                'image'          => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $maison->id,
            ],
            // Sport & Loisirs
            [
                'title'          => 'Football Adidas Al Rihla',
                'description'    => 'Ballon officiel taille 5, couture thermosoudée, excellente trajectoire.',
                'price'          => 12000,
                'discount_price' => null,
                'image'          => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $sport->id,
            ],
            [
                'title'          => 'Vélo VTT 26" Shimano 21 Vitesses',
                'description'    => 'Cadre aluminium léger, fourche suspendue, freins à disque mécaniques, dérailleur Shimano.',
                'price'          => 145000,
                'discount_price' => 129000,
                'image'          => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $sport->id,
            ],
            // Alimentation
            [
                'title'          => 'Riz Parfumé Thaïlandais 25 kg',
                'description'    => 'Riz long grain de qualité supérieure, parfum naturel, sac de 25 kg. Idéal pour les familles.',
                'price'          => 19500,
                'discount_price' => null,
                'image'          => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $food->id,
            ],
            [
                'title'          => 'Huile de Palme Bio 5 litres',
                'description'    => 'Huile de palme rouge non raffinée, naturellement riche en beta-carotène et vitamine E.',
                'price'          => 7500,
                'discount_price' => 6800,
                'image'          => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $food->id,
            ],
            // Beauté & Santé
            [
                'title'          => 'Crème Hydratante Shea Butter 500ml',
                'description'    => 'Crème nourrissante au karité pur, sans paraben, idéale pour peaux sèches, parfum léger.',
                'price'          => 5500,
                'discount_price' => null,
                'image'          => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $beaute->id,
            ],
            [
                'title'          => 'Savon Naturel Neem & Citron',
                'description'    => 'Savon artisanal anti-bactérien au neem et citron, 150g. Convient à tous types de peau.',
                'price'          => 2000,
                'discount_price' => null,
                'image'          => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?w=600&auto=format&fit=crop',
                'in_stock'       => true,
                'category_id'    => $beaute->id,
            ],
        ];

        foreach ($products as $prod) {
            Product::firstOrCreate(
                ['title' => $prod['title']],
                $prod
            );
        }

        $this->command->info('Base de données chargée : ' . count($categories) . ' catégories, ' . count($products) . ' produits.');
    }
}
