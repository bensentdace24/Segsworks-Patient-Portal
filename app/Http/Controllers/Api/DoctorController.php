<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DoctorService;

class DoctorController extends Controller
{
    public function __construct(private DoctorService $doctorService) {}

    /** Return the normalized doctor directory supplied by the external Segworks service. */
    public function index()
    {
        return response()->json($this->doctorService->getAll());
    }
}
