<?php

namespace App\Http\Controllers;

use App\Models\MenuReview;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Services\MenuRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MenuReviewController extends Controller
{
    public function __construct(private MenuRecommendationService $recommendationService) {}

    /**
     * The comprehensive menu evaluation questionnaire for everything the
     * customer purchased in this order — one set of criteria per menu.
     */
    public function create(Order $order)
    {
        $order->load('items.menu', 'reviews.scores');
        $criteria = $this->recommendationService->getCustomerAnswerableCriteria();

        $items = $order->items
            ->filter(fn ($item) => $item->menu)
            ->unique('menu_id')
            ->values()
            ->map(fn ($item) => [
                'menu_id' => $item->menu_id,
                'name' => $item->menu->name,
                'image_path' => $item->menu->image_path,
            ]);

        $answers = [];
        foreach ($order->reviews as $review) {
            foreach ($review->scores as $score) {
                $answers[$review->menu_id][$score->assessment_criterion_id] = $score->score;
            }
        }

        $table = $order->table_id ? RestaurantTable::find($order->table_id) : null;

        return Inertia::render('Order/Review', [
            'order' => $order->only('id'),
            'items' => $items,
            'criteria' => $criteria,
            'answers' => $answers,
            'table' => $table ? ['qr_token' => $table->qr_token] : null,
        ]);
    }

    /**
     * Store the customer's answers for the menu items they purchased in this
     * order. Only menus that are actually part of the order, and criteria
     * that are actually active, can be answered.
     */
    public function store(Request $request, Order $order)
    {
        $data = $request->validate([
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.menu_id' => ['required', 'integer'],
            'answers.*.criterion_id' => ['required', 'integer'],
            'answers.*.score' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $purchasedMenuIds = $order->items()->pluck('menu_id')->unique();
        $answerableCriterionIds = $this->recommendationService->getCustomerAnswerableCriteria()->pluck('id');

        DB::transaction(function () use ($data, $order, $purchasedMenuIds, $answerableCriterionIds) {
            foreach ($data['answers'] as $entry) {
                if (! $purchasedMenuIds->contains($entry['menu_id']) || ! $answerableCriterionIds->contains($entry['criterion_id'])) {
                    continue;
                }

                $review = MenuReview::firstOrCreate([
                    'order_id' => $order->id,
                    'menu_id' => $entry['menu_id'],
                ]);

                $review->scores()->updateOrCreate(
                    ['assessment_criterion_id' => $entry['criterion_id']],
                    ['score' => $entry['score']]
                );
            }
        });

        return back()->with('success', 'Terima kasih atas penilaian Anda!');
    }
}
