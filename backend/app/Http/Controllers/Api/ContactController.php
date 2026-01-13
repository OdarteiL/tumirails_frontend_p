<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contactService) {}

    public function store(ContactRequest $request): JsonResponse
    {
        $contact = $this->contactService->store($request->validated());

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
            'message' => 'Contact message sent successfully.',
        ], 201);
    }

    public function index(): JsonResponse
    {
        $contacts = $this->contactService->all();

        return response()->json([
            'success' => true,
            'data' => ContactResource::collection($contacts),
        ]);
    }

    public function show(Contact $contact): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
        ]);
    }
}
