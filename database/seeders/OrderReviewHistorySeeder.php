<?php

namespace Database\Seeders;

use App\Models\AssessmentCriterion;
use App\Models\Menu;
use App\Models\MenuReview;
use App\Models\MenuReviewScore;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

/**
 * Demo order + customer-questionnaire history sized so the automatically
 * computed Menu Rekomendasi ranking reproduces a known reference result:
 * Bakmi Goreng Nyemek #1 (96.0%), Bakmi Goreng #2 (94.0%), Bakmi Godog #3
 * (92.0%), Rica-Rica Ayam Kampung ½ Ekor #4 (79.7%), Nasi Goreng Biasa #5
 * (74.0%) — with every other menu scoring lower than all five.
 *
 * Run on demand: php artisan db:seed --class=OrderReviewHistorySeeder
 */
class OrderReviewHistorySeeder extends Seeder
{
    /** [name, phone] pool used to give seeded orders varied, plausible customers. */
    private array $customers = [
        ['Budi Santoso', '081234500001'],
        ['Siti Aminah', '081234500002'],
        ['Agus Wijaya', '081234500003'],
        ['Dewi Lestari', '081234500004'],
        ['Rudi Hartono', '081234500005'],
        ['Nur Fadila', '081234500006'],
        ['Eko Prasetyo', '081234500007'],
        ['Rina Marlina', '081234500008'],
        ['Hendra Gunawan', '081234500009'],
        ['Yuni Kartika', '081234500010'],
        ['Fajar Nugroho', '081234500011'],
        ['Wulan Sari', '081234500012'],
        ['Bambang Purnomo', '081234500013'],
        ['Lina Marlina', '081234500014'],
    ];

    public function run(): void
    {
        $criteriaIds = AssessmentCriterion::pluck('id', 'slug');
        $tableIds = RestaurantTable::pluck('id')->all();

        // Raw scores per general criterion match the reference dataset exactly
        // (harga and waktu-penyajian are cost criteria, so lower = better; the
        // rest are benefit criteria). "orders" is the TOTAL order-frequency
        // this menu must end up with — any order history already in the
        // database counts toward it.
        $winners = [
            'Bakmi Goreng' => ['harga' => 3, 'rasa' => 5, 'porsi' => 4, 'waktu-penyajian' => 2, 'tampilan-menu' => 5, 'orders' => 14],
            'Bakmi Godog' => ['harga' => 3, 'rasa' => 5, 'porsi' => 3, 'waktu-penyajian' => 2, 'tampilan-menu' => 3, 'orders' => 20],
            'Bakmi Goreng Nyemek' => ['harga' => 3, 'rasa' => 5, 'porsi' => 5, 'waktu-penyajian' => 2, 'tampilan-menu' => 5, 'orders' => 15],
            'Nasi Goreng Biasa' => ['harga' => 3, 'rasa' => 3, 'porsi' => 4, 'waktu-penyajian' => 2, 'tampilan-menu' => 3, 'orders' => 10],
            'Rica-Rica Ayam Kampung ½ ekor' => ['harga' => 4, 'rasa' => 4, 'porsi' => 5, 'waktu-penyajian' => 3, 'tampilan-menu' => 4, 'orders' => 13],
        ];

        foreach ($winners as $menuName => $profile) {
            $menu = Menu::where('name', $menuName)->first();

            if (! $menu) {
                $this->command?->warn("Menu not found, skipping: {$menuName}");

                continue;
            }

            $this->seedWinner($menu, $profile, $criteriaIds, $tableIds);
        }

        // Every other menu: a deliberately mediocre-to-poor profile and a
        // modest order count, always well under the busiest winner (20) above,
        // so the reference five stay on top of the ranking no matter what
        // order/review history already existed for these menus.
        $otherMenus = Menu::whereNotIn('name', array_keys($winners))->get();

        foreach ($otherMenus as $menu) {
            $profile = [
                'harga' => 5,
                'rasa' => rand(1, 2),
                'porsi' => rand(1, 2),
                'waktu-penyajian' => 5,
                'tampilan-menu' => rand(1, 2),
            ];

            $this->seedLoser($menu, $profile, $criteriaIds, $tableIds);
        }

        $this->command?->info('Order + review history seeded.');
    }

    private function seedWinner(Menu $menu, array $profile, $criteriaIds, array $tableIds): void
    {
        $existingCount = OrderItem::where('menu_id', $menu->id)->count();
        $toAdd = max(0, $profile['orders'] - $existingCount);

        $newOrders = [];
        for ($i = 0; $i < $toAdd; $i++) {
            $newOrders[] = $this->makeOrder($menu, $tableIds);
        }

        // Some of these menus already have a handful of customer reviews from
        // earlier manual testing, but with scores that don't match the
        // reference dataset — replace just those so the average lines up
        // exactly, without touching the orders themselves (they still count
        // toward order frequency either way).
        $existingReviews = MenuReview::where('menu_id', $menu->id)->get();

        foreach ($existingReviews as $review) {
            $review->scores()->delete();
            $this->attachScores($review, $profile, $criteriaIds);
        }

        // Top up to 3 reviews total using the freshly added orders.
        $reviewsNeeded = max(0, 3 - $existingReviews->count());
        foreach (array_slice($newOrders, 0, $reviewsNeeded) as $order) {
            $review = MenuReview::create(['order_id' => $order->id, 'menu_id' => $menu->id]);
            $this->attachScores($review, $profile, $criteriaIds);
        }
    }

    private function seedLoser(Menu $menu, array $profile, $criteriaIds, array $tableIds): void
    {
        // 4 fresh orders dedicated to reviews is enough to pull the average
        // for any menu safely into "bad" territory even if it already had a
        // couple of favorable manual test reviews, plus a few more purely for
        // order-frequency variety.
        for ($i = 0; $i < 4; $i++) {
            $order = $this->makeOrder($menu, $tableIds);

            $review = MenuReview::create(['order_id' => $order->id, 'menu_id' => $menu->id]);
            $this->attachScores($review, $profile, $criteriaIds);
        }

        for ($i = 0, $extra = rand(1, 5); $i < $extra; $i++) {
            $this->makeOrder($menu, $tableIds);
        }
    }

    private function attachScores(MenuReview $review, array $profile, $criteriaIds): void
    {
        foreach (['harga', 'rasa', 'porsi', 'waktu-penyajian', 'tampilan-menu'] as $slug) {
            MenuReviewScore::create([
                'menu_review_id' => $review->id,
                'assessment_criterion_id' => $criteriaIds[$slug],
                'score' => $profile[$slug],
            ]);
        }
    }

    private function makeOrder(Menu $menu, array $tableIds): Order
    {
        [$name, $phone] = $this->customers[array_rand($this->customers)];
        $quantity = rand(1, 2);

        $order = new Order([
            'table_id' => $tableIds ? $tableIds[array_rand($tableIds)] : null,
            'customer_name' => $name,
            'customer_phone' => $phone,
            'total_price' => $menu->price * $quantity,
            'status' => 'selesai',
        ]);

        $timestamp = now()->subDays(rand(0, 10))->subMinutes(rand(0, 1439));
        $order->created_at = $timestamp;
        $order->updated_at = $timestamp;
        $order->save();

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => $quantity,
            'price' => $menu->price,
        ]);

        return $order;
    }
}
