<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FilterController extends Controller
{
    public function index()
    {
        // Pull in all filters for data table

        return Inertia::render('Filters', [
            'filters' => Filter::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Validate the filter (we don't care)

        // Store the filter in the database
        Filter::create(['filter' => $request->filter])->save();

        // Redirect to the index page
        return to_route('filters.index');
    }
}
