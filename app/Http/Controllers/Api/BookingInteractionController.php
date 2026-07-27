<?php

namespace App\Http\Controllers\Api;

use App\Enums\AssignmentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingMessageRequest;
use App\Http\Requests\StoreServiceProofRequest;
use App\Http\Resources\BookingMessageResource;
use App\Http\Resources\ServiceProofResource;
use App\Models\Booking;
use App\Models\BookingMessage;
use App\Models\ServiceProof;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BookingInteractionController extends Controller
{
    use ApiResponse;

    public function messages(Request $request, Booking $booking): JsonResponse
    {
        $this->ensureCanView($request, $booking);

        $messages = $booking->messages()->with('sender')->oldest()->get();

        return $this->successResponse('Booking messages retrieved successfully.', [
            'messages' => BookingMessageResource::collection($messages),
        ]);
    }

    public function storeMessage(StoreBookingMessageRequest $request, Booking $booking): JsonResponse
    {
        $this->ensureCanSend($request, $booking);
        $data = $request->validated();
        $attachment = $request->file('attachment');
        $path = $attachment?->store('booking-messages/'.$booking->id, 'local');

        $message = $booking->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $data['body'] ?? null,
            'attachment_path' => $path,
            'attachment_original_name' => $attachment?->getClientOriginalName(),
            'attachment_mime_type' => $attachment?->getMimeType(),
            'attachment_size' => $attachment?->getSize(),
        ]);

        $message->load('sender');

        return $this->successResponse('Message sent successfully.', [
            'message' => new BookingMessageResource($message),
        ], 201);
    }

    public function messageAttachment(Request $request, Booking $booking, BookingMessage $message)
    {
        $this->ensureCanView($request, $booking);
        abort_unless($message->booking_id === $booking->id, 404);
        abort_unless($message->attachment_path && Storage::disk('local')->exists($message->attachment_path), 404);

        return response()->file(Storage::disk('local')->path($message->attachment_path), [
            'Content-Type' => $message->attachment_mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($message->attachment_original_name ?? 'attachment').'"',
        ]);
    }

    public function proofs(Request $request, Booking $booking): JsonResponse
    {
        $this->ensureCanView($request, $booking);

        return $this->successResponse('Service proofs retrieved successfully.', [
            'proofs' => ServiceProofResource::collection($booking->serviceProofs()->with('staffProfile.user')->oldest('captured_at')->get()),
        ]);
    }

    public function storeProof(StoreServiceProofRequest $request, Booking $booking): JsonResponse
    {
        $staffProfile = $this->ensureActiveAssignedStaff($request, $booking);

        if ($booking->status !== BookingStatus::InProgress) {
            throw ValidationException::withMessages(['booking' => ['Service proof can only be uploaded while work is in progress.']]);
        }

        $data = $request->validated();
        $image = $request->file('image');
        $path = $image->store('service-proofs/'.$booking->id.'/'.$data['kind'], 'local');

        $proof = $booking->serviceProofs()->create([
            'staff_profile_id' => $staffProfile->id,
            'kind' => $data['kind'],
            'image_path' => $path,
            'image_original_name' => $image->getClientOriginalName(),
            'image_mime_type' => $image->getMimeType(),
            'image_size' => $image->getSize(),
            'note' => $data['note'] ?? null,
            'captured_at' => now(),
        ]);

        $proof->load('staffProfile.user');

        return $this->successResponse('Service proof uploaded successfully.', [
            'proof' => new ServiceProofResource($proof),
        ], 201);
    }

    public function proofFile(Request $request, Booking $booking, ServiceProof $proof)
    {
        $this->ensureCanView($request, $booking);
        abort_unless($proof->booking_id === $booking->id, 404);
        abort_unless(Storage::disk('local')->exists($proof->image_path), 404);

        return response()->file(Storage::disk('local')->path($proof->image_path), [
            'Content-Type' => $proof->image_mime_type,
            'Content-Disposition' => 'inline; filename="'.addslashes($proof->image_original_name).'"',
        ]);
    }

    private function ensureCanView(Request $request, Booking $booking): void
    {
        if ($request->user()->role === 'admin' || $booking->customer_id === $request->user()->id) return;
        $this->ensureActiveAssignedStaff($request, $booking);
    }

    private function ensureCanSend(Request $request, Booking $booking): void
    {
        if ($booking->customer_id === $request->user()->id) return;
        $this->ensureActiveAssignedStaff($request, $booking);
    }

    private function ensureActiveAssignedStaff(Request $request, Booking $booking)
    {
        $staffProfile = $request->user()->staffProfile;
        abort_unless($staffProfile && $staffProfile->is_active, 403);
        abort_unless($booking->assignments()->where('staff_profile_id', $staffProfile->id)->where('status', AssignmentStatus::Accepted->value)->exists(), 403);

        return $staffProfile;
    }
}
