<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DoctorService;

class DoctorController extends Controller
{
    public function __construct(private DoctorService $doctorService) {}

    public function index()
    {
        return response()->json($this->doctorService->getAll());
    }
}
