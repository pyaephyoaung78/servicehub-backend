<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModerateBookingReviewRequest;
use App\Models\BookingReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminBookingReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'hidden'])],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $reviews = BookingReview::query()
            ->with(['booking', 'customer', 'service', 'staffProfile.user', 'reviewedBy'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['rating'] ?? null, fn ($query, $rating) => $query->where('rating', $rating))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($reviewQuery) use ($search) {
                    $reviewQuery
                        ->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('service', fn ($serviceQuery) => $serviceQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.booking-reviews.index', [
            'reviews' => $reviews,
            'statuses' => ['pending', 'approved', 'hidden'],
        ]);
    }

    public function moderate(
        ModerateBookingReviewRequest $request,
        BookingReview $bookingReview
    ): RedirectResponse {
        DB::transaction(function () use ($request, $bookingReview) {
            $review = BookingReview::query()
                ->lockForUpdate()
                ->findOrFail($bookingReview->id);

            if ($review->status !== 'pending') {
                throw ValidationException::withMessages([
                    'review' => ['Only pending reviews can be moderated.'],
                ]);
            }

            $review->update([
                'status' => $request->validated('status'),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.booking-reviews.index')
            ->with('success', 'Review '.($request->validated('status') === 'approved' ? 'approved.' : 'hidden from customers.'));
    }
}
