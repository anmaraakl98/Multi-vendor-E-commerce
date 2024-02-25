<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeliveryCostController extends Controller
{
    public function index()
    {
        $user = Auth::user();
            $deliveryCosts = DeliveryCost::all();
    
            return response()->json([
                'status' => 'success',
                'data' => $deliveryCosts,
            ]);
        
    }

    public function store(Request $request)
    {
        $user = Auth::user();
       
        $validatedData = $request->validate([
            'distance' => 'required|integer|unique:delivery_costs',
            'cost' => 'required|numeric',
        ]);

        $deliveryCost = DeliveryCost::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery cost created successfully.',
            'data' => $deliveryCost,
        ], 201);
        
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $deliveryCost = DeliveryCost::find($id);
            if(!$deliveryCost){
                return response()->json([
                    'status' => 'error',
                    'message' => 'it is  not found.',
                ],Response::HTTP_NOT_FOUND);
            }
        $validatedData = $request->validate([
            'distance' => ['required', 'integer', Rule::unique('delivery_costs')
                                                      ->ignore($deliveryCost->id)],
            'cost' => 'required|numeric',
        ]);

        $deliveryCost->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery cost updated successfully.',
            'data' => $deliveryCost,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $deliveryCost = DeliveryCost::findOrFail($id);
        $deliveryCost->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery cost deleted successfully.',
        ]);
   
    }
}
