<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingReviewRequest;
use App\Http\Requests\RebookBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingReviewResource;
use App\Http\Resources\ServiceResource;
use App\Models\Booking;
use App\Models\CustomerFavoriteService;
use App\Models\Service;
use App\Services\BookingTimelineService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerRetentionController extends Controller
{
    use ApiResponse;

    public function reviews(Request $request): JsonResponse
    {
        $reviews = $request->user()->bookingReviews()->with(['service', 'staffProfile.user'])->latest()->paginate(20);
        return $this->successResponse('Reviews retrieved successfully.', BookingReviewResource::collection($reviews)->response()->getData(true));
    }

    public function storeReview(StoreBookingReviewRequest $request, int $booking): JsonResponse
    {
        $ownedBooking = Booking::query()->where('customer_id', $request->user()->id)->with('latestAssignment')->findOrFail($booking);
        if ($ownedBooking->status !== BookingStatus::Completed) throw ValidationException::withMessages(['booking' => ['Only completed bookings can be reviewed.']]);
        if ($ownedBooking->review()->exists()) throw ValidationException::withMessages(['booking' => ['A review has already been submitted for this booking.']]);
        $review = $ownedBooking->review()->create(['customer_id' => $request->user()->id, 'service_id' => $ownedBooking->service_id, 'staff_profile_id' => $ownedBooking->latestAssignment?->staff_profile_id, 'rating' => $request->validated('rating'), 'comment' => $request->validated('comment'), 'status' => 'pending']);
        $review->load(['service', 'staffProfile.user']);
        return $this->successResponse('Review submitted for moderation.', ['review' => new BookingReviewResource($review)], 201);
    }

    public function favorites(Request $request): JsonResponse
    {
        $services = Service::query()->where('is_active', true)->whereHas('customerFavorites', fn ($query) => $query->where('customer_id', $request->user()->id))->with('category')->get();
        return $this->successResponse('Favourite services retrieved successfully.', ['services' => ServiceResource::collection($services)]);
    }

    public function toggleFavorite(Request $request, Service $service): JsonResponse
    {
        abort_unless($service->is_active, 404);
        $favorite = CustomerFavoriteService::query()->where(['customer_id' => $request->user()->id, 'service_id' => $service->id])->first();
        if ($favorite) { $favorite->delete(); return $this->successResponse('Service removed from favourites.', ['is_favorite' => false]); }
        CustomerFavoriteService::create(['customer_id' => $request->user()->id, 'service_id' => $service->id]);
        return $this->successResponse('Service saved to favourites.', ['is_favorite' => true], 201);
    }

    public function rebook(RebookBookingRequest $request, int $booking, BookingTimelineService $timeline): JsonResponse
    {
        $source = Booking::query()->where('customer_id', $request->user()->id)->findOrFail($booking);
        $service = Service::query()->whereKey($source->service_id)->where('is_active', true)->firstOrFail();
        $newBooking = DB::transaction(function () use ($request, $source, $service, $timeline) {
            $booking = Booking::create(['customer_id' => $request->user()->id, 'service_id' => $service->id, 'service_name' => $service->name, 'service_price' => $service->base_price, 'scheduled_at' => $request->validated('scheduled_at'), 'phone' => $source->phone, 'address' => $source->address, 'customer_note' => $source->customer_note, 'status' => BookingStatus::Pending]);
            $timeline->record($booking, 'booking_rebooked', 'Booking created from previous service', 'The customer selected a new appointment time.', $request->user(), ['source_booking_id' => $source->id]);
            return $booking;
        });
        $newBooking->load('service.category');
        return $this->successResponse('A new booking request was created.', ['booking' => new BookingResource($newBooking)], 201);
    }
}
