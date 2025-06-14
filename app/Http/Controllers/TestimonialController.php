<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the testimonials.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = request()->get('page', 1);
        $cacheKey = "testimonials_page_{$page}";
        $testimonials = Cache::remember($cacheKey, 300, function () {
            return Testimonial::all();
        });

        return response()->json([
            'status' => 'success',
            'data' => $testimonials,
        ]);
    }

    /**
     * Remove the specified testimonial from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);

        if (! $testimonial) {
            return response()->json([
                'status' => 'error',
                'message' => 'Testimonial not found',
            ], 404);
        }

        $testimonial->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Testimonial deleted successfully',
        ]);
    }
}
